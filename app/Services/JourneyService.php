<?php

namespace App\Services;

use App\Libraries\MailerExample;

use App\Models\JourneyModel;
use App\Models\JourneyRequestModel;
use App\Models\TrackModel;
use App\Models\LocationModel;
use App\Models\StageModel;
use App\Models\CityModel;
use App\Models\CarModel;
use App\Models\BookingModel;

use App\Exceptions\ExternalApiException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\AddressValidationException;

use App\Services\RoutingService;
use App\Services\GeocodingService;

use DateTimeImmutable;

/**
 * Service métier lié aux trajets.
 *
 * Regroupe la logique de haut niveau qui ne relève pas directement
 * d'un controller : récupération des données API, persistance d'un
 * trajet complet en transaction, détection des demandes compatibles
 * avec un nouveau trajet et envoi des notifications.
 *
 * Ce service est également responsable des accès BDD liés aux tracés
 * (Track) et aux trajets (Journey) qui combinent chargement et calcul
 * géographique : il délègue ensuite les calculs purs à GeoService.
 */
class JourneyService
{
    protected JourneyModel $journeyModel;
    protected JourneyRequestModel $journeyRequestModel;
    protected TrackModel $trackModel;
    protected LocationModel $locationModel;
    protected StageModel $stageModel;
    protected CityModel $cityModel;
    protected CarModel $carModel;
    protected BookingModel $bookingModel;
    protected GeoService $geoService;

    protected RoutingService $routingService;
    protected GeocodingService $geocodingService;

    private const ABSOLUTE_MAX_SEATS = 9;

    public function __construct()
    {
        $this->journeyModel         = new JourneyModel();
        $this->journeyRequestModel  = new JourneyRequestModel();
        $this->geoService           = new GeoService();
        $this->trackModel           = new TrackModel();
        $this->locationModel        = new LocationModel();
        $this->stageModel           = new StageModel();
        $this->cityModel            = new CityModel();
        $this->carModel             = new CarModel();
        $this->bookingModel         = new BookingModel();

        $this->routingService       = new RoutingService();
        $this->geocodingService     = new GeocodingService();
    }

    /**
     * Récupère les données de localisation de chaque adresse du formulaire.
     *
     * Les étapes vides sont ignorées. Pour chaque adresse non vide :
     *  - une exception est levée si l'API ne renvoie aucune correspondance
     *  - une erreur de validation est collectée si l'adresse n'a pas de rue
     *    ni de localité (adresse trop imprécise)
     *
     * @param  array<string,string> $addresses Adresses indexées par clé (start, stage0, ..., end)
     * @return array<string,array>  Données de localisation indexées par les mêmes clés
     *
     * @throws ExternalApiException       Si l'API ne renvoie rien pour une adresse
     * @throws AddressValidationException Si une ou plusieurs adresses sont incomplètes
     */
    public function fetchAllLocationsData(array $addresses): array
    {
        $locationsData = [];
        $errors = [];

        foreach ($addresses as $key => $address) {

            $data = $this->geocodingService->getLocationData($address);
            // Un stage peut-être vide, dans ce cas on ne le prend pas en compte
            if(!empty($address)){
                if ($data === null && !empty($address)) throw new ExternalApiException("Adresse introuvable: $address");
                if (empty($data['street']) && empty($data['locality'])) {
                    $errors[$key . 'Address'] = "L'adresse \"$address\" doit contenir un nom de rue.";
                    continue;
                }
                $locationsData[$key] = $data;
            }

        }

        if (!empty($errors)) {
            throw new AddressValidationException($errors);
        }

        return $locationsData;
    }

    /**
     * Récupère le tracé GeoJSON reliant l'ensemble des locations via l'API de routage.
     *
     * @param  array $locationsData Données de localisation (avec 'latitude' et 'longitude')
     * @return string               Tracé au format GeoJSON
     *
     * @throws ExternalApiException Si l'API ne renvoie pas de tracé valide
     */
    public function fetchTrackOrFail(array $locationsData): string
    {
        $locationsCoordinates = [];
        foreach ($locationsData as $location) {
            $locationsCoordinates[] = [$location['latitude'], $location['longitude']];
        }

        $geoJsonTrack = $this->routingService->getTrack($locationsCoordinates);
        if ($geoJsonTrack === null) {
            throw new ExternalApiException('Calcul du tracé impossible.');
        }

        return $geoJsonTrack;
    }

    /**
     * Récupère et normalise les points du tracé d'un trajet à partir de son ID.
     *
     * Combine l'accès au TrackModel et le parsing GeoJSON délégué à GeoService.
     *
     * @param  int $trackId Identifiant du tracé
     * @return array<int, array{lat:float, lon:float}> Points du tracé ;
     *               tableau vide si le tracé est absent ou invalide
     */
    public function getTrackPoints(int $trackId): array
    {
        $track = $this->trackModel->find($trackId);
        if (empty($track['geojson'])) {
            return [];
        }

        return $this->geoService->parseTrackPointsFromGeoJson($track['geojson']);
    }

    /**
     * Calcule l'heure d'arrivée d'un trajet à partir de son tracé et de son heure de départ.
     *
     * Charge le trajet et son tracé en BDD, puis délègue à GeoService le calcul
     * pur de la durée à partir du GeoJSON.
     *
     * @param  int $journeyId Identifiant du trajet
     * @return DateTimeImmutable Heure d'arrivée estimée
     */
    public function getArrivalDateTime(int $journeyId): DateTimeImmutable
    {
        $journey = $this->journeyModel->find($journeyId);
        $track   = $this->trackModel->find($journey['track_id']);

        $duration             = $this->geoService->calculateTrackDuration($track['geojson']);
        $journeyStartDateTime = new DateTimeImmutable($journey['start_datetime']);

        return $journeyStartDateTime->modify('+' . $duration . ' seconds');
    }

    /**
     * Assemble l'ensemble des données nécessaires à l'affichage du détail d'un trajet.
     *
     * Charge le trajet, calcule sa date d'arrivée, récupère ses étapes
     * intermédiaires, ses passagers, le nombre de demandes en attente et la
     * réservation éventuelle de l'utilisateur courant (avec les drapeaux
     * isBooked / isPending dérivés du statut de cette réservation).
     *
     * @param  int $journeyId Identifiant du trajet
     * @param  int $userId    Identifiant de l'utilisateur courant
     * @return array|null     Données du trajet prêtes pour la vue, ou null si
     *                        le trajet n'existe pas
     */
    public function getJourneyDetails(int $journeyId, int $userId): ?array
    {
        $journey = $this->journeyModel->findWithDetails($journeyId);
        if (!$journey) {
            return null;
        }

        $journey['end_datetime'] = $this->getArrivalDateTime($journey['id'])
            ->format('Y-m-d H:i:s');

        $userBooking = $this->bookingModel->findUserBooking($journeyId, $userId);

        return [
            'journey'         => $journey,
            'stages'          => $this->stageModel->findByJourney($journeyId),
            'remainingSeats'  => $this->bookingModel->countRemainingSeats($journeyId, (int) $journey['seats']),
            'passengers'      => $this->bookingModel->findPassengersByJourney($journeyId),
            'pendingBookings' => $this->bookingModel->countPendingBookings($journeyId),
            'userBooking'     => $userBooking,
            'isBooked'        => $userBooking !== null && $userBooking['status'] === 'accepted',
            'isPending'       => $userBooking !== null && $userBooking['status'] === 'pending',
        ];
    }

    /**
     * Construit les entités location prêtes à être insérées en base.
     *
     * Pour chaque location, la ville est résolue ou créée à la volée via
     * CityModel::findOrCreateCity().
     *
     * @param  array $locationsData Données renvoyées par l'API de géocodage
     *                              (clés attendues : city, postcode, longitude,
     *                              latitude, name)
     * @return array<int, array{longitude:float, latitude:float, address:string,
     *               note:string, city_id:int}> Entités location prêtes pour l'insert
     */
    private function buildLocationEntities(array $locationsData): array
    {
        $locationEntities = [];

        foreach ($locationsData as $location) {

            $cityId = $this->cityModel->findOrCreateCity($location['city'], $location['postcode']);

            $locationEntities[] = [
                'longitude' => $location['longitude'],
                'latitude'  => $location['latitude'],
                'address'   => $location['name'],
                'note'      => '',
                'city_id'   => $cityId,
            ];
        }

        return $locationEntities;
    }

    /**
     * Construit la date/heure de départ du trajet à partir des champs du formulaire.
     *
     * @param  array{startDate:string, startTime:string} $journeyData Données du journey
     * @return DateTimeImmutable Date/heure de départ du trajet
     */
    private function buildJourneyStartDateTime(array $journeyData): DateTimeImmutable
    {
        $journeyStartTimeArray = explode(':', $journeyData['startTime']);
        $journeyStartHour      = (int) $journeyStartTimeArray[0];
        $journeyStartMinute    = (int) $journeyStartTimeArray[1];

        return (new DateTimeImmutable($journeyData['startDate']))
            ->setTime($journeyStartHour, $journeyStartMinute, 0);
    }

    /**
     * Calcule les heures de départ de chaque étape intermédiaire à partir
     * des durées de segments renvoyées par l'API de routage.
     *
     * Cas particulier : si le trajet n'a aucune étape (uniquement start → end),
     * l'API renvoie un seul segment et la fonction retourne un tableau vide.
     * Le dernier segment (étape finale → arrivée) est exclu du calcul.
     *
     * @param  array  $journeyData  Données du journey (utilisées pour la date/heure de départ)
     * @param  string $geoJsonTrack Tracé GeoJSON renvoyé par l'API
     * @return DateTimeImmutable[]  Heures de départ des étapes (vide si pas d'étape)
     *
     * @throws ExternalApiException Si le GeoJSON ne contient aucun segment exploitable
     */
    private function computeStageDepartures(array $journeyData, string $geoJsonTrack): array
    {
        $data     = json_decode($geoJsonTrack, true);
        $segments = $data['features'][0]['properties']['segments'] ?? [];

        if (empty($segments)) {
            throw new ExternalApiException('Tracé GeoJSON invalide: segments manquants.');
        }

        // Pas d'étape intermédiaire : un seul segment (start → end), rien à calculer.
        if (count($segments) === 1) {
            return [];
        }

        $departure                = $this->buildJourneyStartDateTime($journeyData);
        $stagesDeparturesDateTime = [];

        // On exclut le dernier segment qui mène à l'arrivée (pas une étape)
        for ($i = 0; $i < count($segments) - 1; $i++) {
            $departure                  = $departure->modify('+' . (int) $segments[$i]['duration'] . ' seconds');
            $stagesDeparturesDateTime[] = $departure;
        }

        return $stagesDeparturesDateTime;
    }

    /**
     * Persiste l'ensemble du trajet (track, locations, journey, stages) en transaction.
     *
     * Les étapes intermédiaires (stages) sont optionnelles : si le formulaire
     * n'en contient aucune, la boucle d'insertion des stages ne s'exécute pas.
     *
     * @param  int    $userId         Identifiant du conducteur
     * @param  array  $createFormData Données du formulaire (clés 'journey' et 'location')
     * @param  array  $locationsData  Données de localisation renvoyées par l'API de géocodage
     * @param  string $geoJsonTrack   Tracé GeoJSON renvoyé par l'API de routage
     * @return int                    Identifiant du trajet créé
     *
     * @throws ModelValidationException Si un model refuse l'insertion (track, location,
     *                                  journey ou stage)
     * @throws \RuntimeException        Si la transaction échoue
     */
    public function persistJourney(int $userId, array $createFormData, array $locationsData, string $geoJsonTrack): int
    {
        // ====== La voiture doit appartenir à l'utilisateur
        $carId = (int) $createFormData['journey']['car'];
        $car   = $this->carModel->findOwnedByUser($carId, $userId);
        if ($car === null) {
            throw new ModelValidationException(['car' => 'Véhicule invalide.']);
        }

        // ====== Préparation des entités hors transaction
        $locationEntities         = $this->buildLocationEntities($locationsData);
        $journeyStartDateTime     = $this->buildJourneyStartDateTime($createFormData['journey']);
        $stagesDeparturesDateTime = $this->computeStageDepartures($createFormData['journey'], $geoJsonTrack);


        // ====== Insertion dans la base
        $db = \Config\Database::connect();
        $db->transStart();

        // --- Insertion du tracé
        $trackId = $this->trackModel->insert(['geojson' => $geoJsonTrack]);
        if ($trackId === false) {
            throw new ModelValidationException($this->trackModel->errors());
        }

        // --- Insertion des locations
        $locationsId = [];
        foreach ($locationEntities as $location) {
            $locationId = $this->locationModel->insert($location);
            if ($locationId === false) {
                throw new ModelValidationException($this->locationModel->errors());
            }
            $locationsId[] = $locationId;
        }

        // --- Insertion du journey
        $journeyEntity = [
            'start_datetime'    => $journeyStartDateTime->format('Y-m-d H:i:s'),
            'seats'             => $createFormData['journey']['seats'],
            'note'              => $createFormData['journey']['note'],
            'smoking'           => $createFormData['journey']['smoking'],
            'car_id'            => $createFormData['journey']['car'],
            'track_id'          => $trackId,
            'user_id'           => $userId,
            'location_start_id' => array_shift($locationsId),
            'location_end_id'   => array_pop($locationsId),
        ];

        $journeyId = $this->journeyModel->insert($journeyEntity);
        if ($journeyId === false) {
            throw new ModelValidationException($this->journeyModel->errors());
        }

        // --- Insertion des stages (étapes intermédiaires restantes)
        //     Si le trajet n'a pas d'étape, $locationsId est vide et la boucle ne s'exécute pas.
        for ($i = 0; $i < count($locationsId); $i++) {
            $stageId = $this->stageModel->insert([
                'departure_time' => $stagesDeparturesDateTime[$i]->format('H:i:s'),
                'location_id'    => $locationsId[$i],
                'journey_id'     => $journeyId,
                'position'       => $i + 1,
            ]);
            if ($stageId === false) {
                throw new ModelValidationException($this->stageModel->errors());
            }
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            throw new \RuntimeException('Transaction échouée lors de la création du trajet.');
        }

        return $journeyId;
    }

    /**
     * Recherche les trajets correspondant aux filtres fournis.
     *
     * Charge tous les candidats (filtres non géographiques) via le model,
     * enrichit chacun du nombre de demandes en attente, puis applique le
     * filtrage géographique (proximité départ ET arrivée, dans le bon ordre).
     *
     * Le résultat n'est PAS paginé : la pagination reste une préoccupation
     * de présentation gérée par le controller.
     *
     * @param  array $filters Filtres normalisés (voir JourneyController::getShowAllFilter)
     * @return array[]        Trajets correspondants (non paginés)
     */
    public function searchJourneys(array $filters): array
    {
        $candidates = $this->journeyModel->findAllWithFilters($filters);
        $this->attachPendingBookingsCount($candidates);

        $start = ($filters['latStart'] !== null && $filters['lngStart'] !== null)
            ? ['lat' => $filters['latStart'], 'lon' => $filters['lngStart']]
            : null;

        $end = ($filters['latEnd'] !== null && $filters['lngEnd'] !== null)
            ? ['lat' => $filters['latEnd'], 'lon' => $filters['lngEnd']]
            : null;

        return $this->findMatchingJourneys($candidates, $start, $end);
    }

    /**
     * Ajoute à chaque candidat le nombre de demandes de réservation en attente.
     *
     * Une seule requête est effectuée pour l'ensemble des trajets
     * (countPendingByJourneys), puis le résultat est réparti par trajet.
     *
     * @param  array[] $candidates Trajets candidats (modifiés par référence)
     * @return void
     */
    private function attachPendingBookingsCount(array &$candidates): void
    {
        $journeyIds = array_column($candidates, 'id');
        if (empty($journeyIds)) {
            return;
        }

        $pendingByJourney = $this->bookingModel->countPendingByJourneys($journeyIds);

        foreach ($candidates as &$candidate) {
            $candidate['pending_bookings'] = $pendingByJourney[$candidate['id']] ?? 0;
        }
        unset($candidate);
    }

    /**
     * Filtre une liste de trajets candidats en ne gardant que ceux dont le tracé
     * passe à proximité du départ ET de l'arrivée recherchés.
     *
     * Pour chaque trajet : on récupère les points du tracé GeoJSON, on cherche le
     * point le plus proche du départ ; s'il est dans le rayon, on cherche le point
     * le plus proche de l'arrivée PARMI LES POINTS SUIVANTS (pour garantir que le
     * trajet va bien dans le sens départ → arrivée).
     *
     * Filtres partiels gérés : seul le départ, seule l'arrivée, ou aucun des deux
     * (auquel cas tous les trajets sont retournés).
     *
     * @param  array[]    $journeys      Trajets candidats (doivent contenir 'track_id')
     * @param  array|null $start         Point de départ ['lat' => float, 'lon' => float] ou null
     * @param  array|null $end           Point d'arrivée ['lat' => float, 'lon' => float] ou null
     * @param  float      $maxDistanceKm Rayon de tolérance en km (défaut : 10)
     * @return array[]   Sous-ensemble des trajets correspondants
     */
    public function findMatchingJourneys(array $journeys, ?array $start, ?array $end, float $maxDistanceKm = 10): array
    {
        if ($start === null && $end === null) {
            return $journeys;
        }

        $matchingJourneys = [];

        foreach ($journeys as $journey) {

            $points = $this->getTrackPoints((int) $journey['track_id']);

            if (empty($points)) {
                continue;
            }

            $startIndex = 0;

            // --- Contrainte sur le départ
            if ($start !== null) {
                $startIndex      = $this->geoService->findClosestPointIndex($points, $start);
                $distanceToStart = $this->geoService->calculateDistance($start, $points[$startIndex]);

                if ($distanceToStart > $maxDistanceKm) {
                    continue;
                }
            }

            // --- Contrainte sur l'arrivée (après le départ)
            if ($end !== null) {
                $endIndex      = $this->geoService->findClosestPointIndex($points, $end, $startIndex);
                $distanceToEnd = $this->geoService->calculateDistance($end, $points[$endIndex]);

                if ($distanceToEnd > $maxDistanceKm) {
                    continue;
                }
            }

            $matchingJourneys[] = $journey;
        }

        return $matchingJourneys;
    }

    /**
     * Recherche les demandes de trajet compatibles avec un trajet nouvellement créé
     * et envoie un mail de notification à chaque demandeur concerné.
     *
     * Une demande est considérée compatible si :
     *  - le tracé du trajet passe à moins de 10 km de son départ ET de son arrivée
     *    (dans le bon ordre départ → arrivée)
     *  - la date souhaitée est à ±30 minutes de l'heure de départ du trajet
     *
     * @param  int    $journeyId    Identifiant du trajet nouvellement créé
     * @param  string $geoJsonTrack Tracé GeoJSON du trajet
     * @return void
     */
    public function notifyMatchingRequests(int $journeyId, string $geoJsonTrack): void
    {
        // ====== Récupération du trajet créé
        $journey = $this->journeyModel->findWithDetails($journeyId);
        if (!$journey) return;

        // ====== Récupération de toutes les demandes en attente (hors conducteur)
        $requests = $this->journeyRequestModel->findPendingExcludingUser((int) $journey['user_id']);

        if (empty($requests)) return;

        // ====== Parse des points du tracé GeoJSON (factorisé dans GeoService)
        $points = $this->geoService->parseTrackPointsFromGeoJson($geoJsonTrack);
        if (empty($points)) return;

        $mailer = new MailerExample();

        // ====== Matching pour chaque demande
        foreach ($requests as $request) {

            // --- Filtre géographique
            $start = ['lat' => (float) $request['start_lat'], 'lon' => (float) $request['start_lng']];
            $end   = ['lat' => (float) $request['end_lat'],   'lon' => (float) $request['end_lng']];

            if (!$this->geoService->matchesTrackPoints($points, $start, $end)) {
                continue;
            }

            // --- Filtre temporel (±30 min)
            if (!empty($request['start_datetime'])) {
                $diff = abs(strtotime($journey['start_datetime']) - strtotime($request['start_datetime']));
                if ($diff > 1800) continue;
            }

            // --- Envoi du mail
            $date = date('d/m/Y', strtotime($journey['start_datetime']))
                  . ' à ' . date('H:i', strtotime($journey['start_datetime']));

            $mailer->sendHtml(
                $request['requester_email'],
                'Un trajet correspond à votre demande !',
                view('Emails/journeyRequestMatch', [
                    'firstname'  => $request['requester_firstname'],
                    'cityStart'  => $request['city_start_name'],
                    'cityEnd'    => $request['city_end_name'],
                    'date'       => $date,
                    'journeyUrl' => site_url('journeys/' . $journeyId),
                ])
            );
        }
    }

    public function getMaxSeatsForCar(int $carId, int $userId): int
    {
        if ($carId <= 0) return self::ABSOLUTE_MAX_SEATS;
        $car = $this->carModel->findOwnedByUser($carId, $userId);
        return $car ? (int) $car['seats'] - 1 : self::ABSOLUTE_MAX_SEATS;
    }

    /**
     * Retourne le trajet existant de l'utilisateur sur la même demi-journée
     * (matin : 00h–12h, après-midi : 12h–24h) que la date/heure fournies.
     *
     * Sert à prévenir la création de doublons par un même conducteur sur
     * une plage horaire incompatible.
     *
     * @param  int    $userId    Identifiant du conducteur
     * @param  string $startDate Date de départ au format Y-m-d
     * @param  string $startTime Heure de départ au format H:i
     * @return array|null Trajet existant sur la demi-journée, ou null si aucun
     */
    private function findExistingJourneyOnHalfDay(int $userId, string $startDate, string $startTime): ?array
    {
        $journeyStartDate = new DateTimeImmutable($startDate);
        $journeyStartTime = new DateTimeImmutable($startTime);

        $startHour        = (int) $journeyStartTime->format('H') < 12 ? 0 : 12;
        $dayStartDateTime = $journeyStartDate->setTime($startHour, 0, 0);
        $dayEndDateTime   = $dayStartDateTime->modify('+12 hours');

        return $this->journeyModel->findByUserInTimeRange(
            $userId,
            $dayStartDateTime->format('Y-m-d H:i:s'),
            $dayEndDateTime->format('Y-m-d H:i:s'),
        );
    }
}