<?php

namespace App\Services;

use App\Models\JourneyModel;
use App\Models\TrackModel;
use App\Models\LocationModel;
use App\Models\StageModel;
use App\Models\CityModel;
use App\Models\CarModel;
use App\Models\UserModel;

use App\Exceptions\ExternalApiException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\AddressValidationException;

use App\Services\RoutingService;
use App\Services\GeocodingService;

use DateTimeImmutable;


class CreateJourneyService
{
    protected JourneyModel $journeyModel;
    protected TrackModel $trackModel;
    protected LocationModel $locationModel;
    protected StageModel $stageModel;
    protected CityModel $cityModel;
    protected CarModel $carModel;
    protected UserModel $userModel;

    protected RoutingService $routingService;
    protected GeocodingService $geocodingService;

    public function __construct()
    {
        $this->journeyModel         = new JourneyModel();
        $this->trackModel           = new TrackModel();
        $this->locationModel        = new LocationModel();
        $this->stageModel           = new StageModel();
        $this->cityModel            = new CityModel();
        $this->carModel             = new CarModel();
        $this->userModel            = new UserModel();

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
     * Construit les données nécessaires à l'affichage de la page de prévisualisation
     * d'un trajet, avant sa persistance en base.
     *
     * @param  int    $userId          Identifiant du conducteur
     * @param  array  $createFormData  Données du formulaire (clés 'journey' et 'location')
     * @param  array  $locationsData   Données de géocodage (clés 'start', 'stage0'…, 'end')
     * @param  string $geoJsonTrack    Tracé GeoJSON renvoyé par l'API de routage
     * @return array{journey:array, stages:array, remainingSeats:int}
     */
    public function buildPreviewData(int $userId, array $createFormData, array $locationsData, string $geoJsonTrack): array
    {
        $user = $this->userModel->find($userId);
        $car  = $this->carModel->find($createFormData['journey']['car']);

        $startDateTime = $this->buildJourneyStartDateTime($createFormData['journey']);

        $geoData       = json_decode($geoJsonTrack, true);
        $segments      = $geoData['features'][0]['properties']['segments'] ?? [];
        $totalDuration = (int) array_sum(array_column($segments, 'duration'));
        $endDateTime   = $startDateTime->modify('+' . $totalDuration . ' seconds');

        $stageDepartures = $this->computeStageDepartures($createFormData['journey'], $geoJsonTrack);

        $locStart = $locationsData['start'];
        $locEnd   = $locationsData['end'];

        $journey = [
            'id'               => null,
            'user_id'          => $userId,
            'driver_id'        => $userId,
            'start_datetime'   => $startDateTime->format('Y-m-d H:i:s'),
            'end_datetime'     => $endDateTime->format('Y-m-d H:i:s'),
            'seats'            => $createFormData['journey']['seats'],
            'note'             => $createFormData['journey']['note'],
            'smoking'          => $createFormData['journey']['smoking'],
            'city_start_name'  => $locStart['city'],
            'address_start'    => $locStart['name'],
            'lat_start'        => $locStart['latitude'],
            'lng_start'        => $locStart['longitude'],
            'city_end_name'    => $locEnd['city'],
            'address_end'      => $locEnd['name'],
            'lat_end'          => $locEnd['latitude'],
            'lng_end'          => $locEnd['longitude'],
            'driver_firstname' => $user['firstname'],
            'driver_lastname'  => $user['lastname'],
            'driver_avatar'    => $user['avatar'] ?? null,
            'driver_is_student' => $user['is_student'] ?? false,
            'car_brand'        => $car['brand'],
            'car_model'        => $car['model'],
            'car_color'        => $car['color'],
            'track_geojson'    => $geoJsonTrack,
            'canceled_at'      => null,
        ];

        $stages     = [];
        $stageIndex = 0;
        foreach ($locationsData as $key => $loc) {
            if (!str_starts_with($key, 'stage')) continue;
            $stages[] = [
                'city_name'      => $loc['city'],
                'address'        => $loc['name'],
                'latitude'       => $loc['latitude'],
                'longitude'      => $loc['longitude'],
                'departure_time' => $stageDepartures[$stageIndex]->format('H:i:s'),
            ];
            $stageIndex++;
        }

        return [
            'journey'        => $journey,
            'stages'         => $stages,
            'remainingSeats' => (int) $createFormData['journey']['seats'],
        ];
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
}