<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\MailerExample;
use CodeIgniter\HTTP\RedirectResponse;

use App\Models\TrackModel;
use App\Models\JourneyModel;
use App\Models\CarModel;
use App\Models\LocationModel;
use App\Models\BookingModel;
use App\Models\CityModel;
use App\Models\StageModel;

use App\Exceptions\ExternalApiException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\AddressValidationException;
use DateTimeImmutable;

class JourneyController extends BaseController{

    protected TrackModel $trackModel;
    protected JourneyModel $journeyModel;
    protected CarModel $carModel;
    protected BookingModel $bookingModel;
    protected LocationModel $locationModel;
    protected CityModel $cityModel;
    protected StageModel $stageModel;

    public function __construct(){
        
        $this->trackModel = new TrackModel();
        $this->journeyModel = new JourneyModel();
        $this->carModel = new CarModel();
        $this->bookingModel = new BookingModel();
        $this->locationModel = new LocationModel();
        $this->cityModel = new CityModel();
        $this->stageModel = new StageModel();
    }

    /**
     * Affiche le formulaire de création d'un nouveau trajet.
     *
     * Récupère la liste des voitures appartenant à l'utilisateur connecté
     * pour permettre la sélection d'un véhicule dans le formulaire.
     *
     * @return string Vue HTML du formulaire de création de trajet
     */
    public function showCreateForm()
    {

        $userId = session('user_id');

        $userCars = $this->carModel->where(['user_id'=>$userId,])->findAll();

        return view('Journeys/newJourney', [
            'title' => "Publier un trajet",
            'cars' => $userCars,
        ]);
    }

    /**
     * Traite la soumission du formulaire de création d'un trajet.
     *
     * Vérifie que l'utilisateur est connecté, valide les données du formulaire,
     * récupère le tracé via les APIs externes (géocodage + routage), puis insère
     * l'ensemble (track, locations, journey, stages) en base via une transaction.
     *
     * Les erreurs sont gérées selon trois familles :
     *  - validation du formulaire HTTP        → redirection avec erreurs de champs
     *  - API externe indisponible             → redirection avec message générique
     *  - validation des models / transaction  → redirection avec erreurs du model
     *
     * @return RedirectResponse Redirection vers la page du trajet créé ou
     *                          retour au formulaire avec les erreurs
     */
    public function create()
    {
        $userId = session('user_id');

        // ====== Validation des données du formulaire
        $maxSeats                 = $this->getMaxAvailableSeatsFromPostedCar();
        $createValidationRules    = $this->getCreateValidationRules($maxSeats);
        $createValidationMessages = $this->getCreateValidationMessages($maxSeats);
        if (!$this->validate($createValidationRules, $createValidationMessages)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // ====== Récupération des données du formulaire
        $createFormData = $this->getCreateFormData();

        // ====== Traitement métier
        try {

            $locationsData = $this->fetchAllLocationsData($createFormData['location']);
            $geoJsonTrack  = $this->fetchTrackOrFail($locationsData);
            $journeyId     = $this->persistJourney($userId, $createFormData, $locationsData, $geoJsonTrack);

        } catch (ExternalApiException $e) {

            return redirect()->back()->withInput()
                ->with('errors', ['api' => 'Service de cartographie indisponible, réessayez plus tard.']);

        } catch (ModelValidationException $e) {

            return redirect()->back()->withInput()
                ->with('errors', $e->getErrors());

        } catch (AddressValidationException $e) {
            return redirect()->back()->withInput()
                ->with('errors', $e->getErrors());

        } catch (\Throwable $e) {

            return redirect()->back()->withInput()
                ->with('errors', ['db' => 'Une erreur est survenue lors de l\'enregistrement .']);

        }

        return redirect()->to('/journeys/' . $journeyId);
    }

    /**
     * Affiche le détail d'un trajet.
     *
     * Charge le trajet, calcule sa date d'arrivée, récupère ses étapes
     * intermédiaires, ses passagers et les demandes en attente. Vérifie
     * également si l'utilisateur courant a déjà une réservation acceptée
     * sur ce trajet pour adapter l'affichage.
     *
     * @param  int|string $id Identifiant du trajet à afficher
     * @return string|RedirectResponse Vue de détail, ou redirection vers la
     *                                 liste si le trajet n'existe pas
     */
    public function show($id): string|RedirectResponse
    {
        // ====== Récupération du trajet
        $journey = $this->journeyModel->findWithDetails((int) $id);

        if (!$journey) {
            return redirect()->to('/journeys');
        }


        // ====== Calcul de la date d'arrivée du trajet
        $journey['end_datetime'] = $this->getArrivaleDateTime($journey['id'])
            ->format('Y-m-d H:i:s');

        // ====== Récupération des étapes intermédiaires
        $stages = $this->stageModel->findByJourney((int) $id);

        // ====== Calcul des places restantes
        $remainingSeats = $this->bookingModel->countRemainingSeats(
            (int) $id,
            (int) $journey['seats'],
        );

        // ====== Récupération des passagers
        $passengers = $this->bookingModel->findPassengersByJourney((int) $id);

        // ====== Récupération des reservations en cours pour un trajet
        $pendingBookings = $this->bookingModel->countPendingBookings((int) $id);

        // ====== Réservation de l'utilisateur courant sur ce trajet (si elle existe)
        $userBooking = $this->bookingModel
            ->where('journey_id', (int) $id)
            ->where('user_id', session('user_id'))
            ->where('status', 'accepted')
            ->first();
        $isBooked = $userBooking !== null;

        // ====== Récupération des filtres de réservation
        $availableSeats = $this->request->getGet('seats') ?? 1;
        $boardingCity   = $this->request->getGet('boardingCity');

        $back = $this->validateBackUrl($this->request->getGet('back'));

        return view('Journeys/journeyShow',[
            'title'          => 'Détail du trajet',
            'journey'        => $journey,
            'back'           => $back,
            'stages'         => $stages,
            'remainingSeats' => $remainingSeats,
            'availableSeats' => $availableSeats,
            'boardingCity'   => $boardingCity,
            'passengers'     => $passengers,
            'isBooked'       => $isBooked,
            'userBooking'    => $userBooking,
            'pendingBookings' => $pendingBookings,
        ]);
    }

/**
 * Affiche la liste des trajets correspondant aux filtres de recherche.
 *
 * Construit la requête SQL avec les filtres non géographiques (date, heure,
 * places disponibles, fumeur), puis applique un filtrage géographique en PHP
 * sur le tracé de chaque candidat (proximité départ ET arrivée).
 * Pagine ensuite le résultat final.
 *
 * @return string|RedirectResponse Vue HTML de la liste paginée des trajets
 */
public function showAll(): string|RedirectResponse
{
    // --- Récupération des filtres
    $filters = $this->getShowAllFilter();

    // --- Construction de la requête (filtres NON géographiques uniquement)
    $db = \Config\Database::connect();

    $builder = $db->table('journey')
        ->select("journey.*,
            city_start.name as city_start_name,
            city_end.name   as city_end_name,
            city_start.name as city_boarding_name,
            u.firstname     as driver_firstname,
            u.lastname      as driver_lastname,
            u.is_student    as driver_is_student,
            (journey.seats - COALESCE((SELECT SUM(b.seat_numbers) FROM booking b WHERE b.journey_id = journey.id AND b.status = 'accepted'), 0)) as remaining_seats")
        ->join('location loc_start', 'loc_start.id = journey.location_start_id')
        ->join('location loc_end',   'loc_end.id = journey.location_end_id')
        ->join('city city_start',    'city_start.id = loc_start.city_id')
        ->join('city city_end',      'city_end.id = loc_end.city_id')
        ->join('user u',             'u.id = journey.user_id')
        ->where('journey.canceled_at', null)
        ->where('u.deleted_at', null)
        ->orderBy('journey.start_datetime', 'ASC');

    if ($filters['filterDate'] && $filters['filterTime']) {
        $dateTimeCenter = strtotime($filters['filterDate'] . ' ' . $filters['filterTime'] . ':00');
        $dateTimeFrom   = date('Y-m-d H:i:s', $dateTimeCenter - 1800);
        $dateTimeTo     = date('Y-m-d H:i:s', $dateTimeCenter + 1800);
        $builder->where('journey.start_datetime >=', $dateTimeFrom)
                ->where('journey.start_datetime <=', $dateTimeTo);
    } elseif ($filters['filterDate']) {
        $builder->where('DATE(journey.start_datetime)', $filters['filterDate']);
        if ($filters['filterDate'] === date('Y-m-d')) {
            $builder->where('journey.start_datetime >=', date('Y-m-d H:i:s'));
        }
    } else {
        $builder->where('journey.start_datetime >=', date('Y-m-d H:i:s'));
    }

    if ($filters['availableSeats']) {
        $builder->having('remaining_seats >=', $filters['availableSeats']);
    }

    if ($filters['smoking'] !== null && $filters['smoking'] !== '') {
        $builder->where('journey.smoking', $filters['smoking']);
    }

    // --- Récupération de tous les candidats (sans filtre géographique)
    $candidates = $builder->get()->getResultArray();

    // --- Ajout du nombre de demandes en attente pour chaque trajet
    $journeyIds = array_column($candidates, 'id');
    if (!empty($journeyIds)) {
        $pendingCounts = $db->table('booking')
            ->select('journey_id, COUNT(*) as pending_bookings')
            ->where('status', 'pending')
            ->whereIn('journey_id', $journeyIds)
            ->groupBy('journey_id')
            ->get()
            ->getResultArray();

        $pendingByJourney = array_column($pendingCounts, 'pending_bookings', 'journey_id');

        foreach ($candidates as &$candidate) {
            $candidate['pending_bookings'] = $pendingByJourney[$candidate['id']] ?? 0;
        }
        unset($candidate);
    }

    // --- Filtrage géographique en PHP (matching sur le tracé)
    $start = ($filters['latStart'] !== null && $filters['lngStart'] !== null)
        ? ['lat' => $filters['latStart'], 'lon' => $filters['lngStart']]
        : null;

    $end = ($filters['latEnd'] !== null && $filters['lngEnd'] !== null)
        ? ['lat' => $filters['latEnd'], 'lon' => $filters['lngEnd']]
        : null;

    $matchingJourneys = $this->findMatchingJourneys($candidates, $start, $end);

    // --- Pagination en PHP
    $perPage  = 5;
    $total    = count($matchingJourneys);
    $journeys = array_slice($matchingJourneys, ($filters['page'] - 1) * $perPage, $perPage);
    $pager    = \Config\Services::pager();

    return view('Journeys/journeyShowAll', [
        'title'    => 'Rechercher un trajet',
        'journeys' => $journeys,
        'pager'    => $pager,
        'total'    => $total,
        'perPage'  => $perPage,
        // Spread du tableau : passe startAddress, endAddress, latStart, etc.
        ...$filters,
    ]);
}

    /**
     * Retourne les règles de validation du formulaire de création de trajet.
     *
     * La borne max de 'seats' est calculée dynamiquement à partir de la capacité
     * de la voiture sélectionnée (capacité - 1 pour le conducteur). Si la voiture
     * ne peut pas être résolue, on retombe sur une borne par défaut : la règle
     * sur 'car' invalidera le formulaire de toute façon.
     *
     * @param  int $maxSeats Nombre maximum de places réservables (défaut : 9)
     * @return array<string, string> Règles de validation CodeIgniter indexées par champ
     */
    public function getCreateValidationRules(int $maxSeats = 9): array {

        return [
            'startDate'     => 'required|valid_date',
            'startTime'     => 'required|regex_match[/^([01]\d|2[0-3]):[0-5]\d$/]',
            'seats'         => 'required|integer|greater_than[0]|less_than_equal_to[' . $maxSeats . ']',
            'note'          => 'permit_empty|max_length[500]',
            'smoking'       => 'in_list[0,1]',
            'startAddress'  => 'required|string|max_length[255]',
            'endAddress'    => 'required|string|max_length[255]',
            'car'           => 'required|integer|greater_than[0]',
        ];

    }

    /**
     * Retourne les messages d'erreur personnalisés associés aux règles de validation.
     *
     * @param  int $maxSeats Nombre maximum de places réservables (défaut : 9),
     *                       injecté dans le message d'erreur du champ 'seats'
     * @return array<string, array<string, string>> Messages indexés par champ puis par règle
     */   
    public function getCreateValidationMessages(int $maxSeats = 9): array {

        return [
            'startDate'    => [
                'required'   => 'La date de départ est obligatoire.',
                'valid_date' => 'La date de départ n\'est pas valide.',
            ],
            'startTime'    => [
                'required'      => 'L\'heure de départ est obligatoire.',
                'regex_match'   => 'L\'heure de départ n\'est pas valide (format HH:MM).',
            ],
            'seats'        => [
                'required'      => 'Le nombre de places est obligatoire.',
                'integer'       => 'Le nombre de places doit être un entier.',
                'greater_than'  => 'Le nombre de places doit être supérieur à 0.',
                'less_than_equal_to' => "Le nombre de places ne peut pas dépasser {$maxSeats} (capacité de votre voiture).",
            ],
            'note'         => [
                'max_length'    => 'La note ne peut pas dépasser 500 caractères.',
            ],
            'startAddress' => [
                'required'      => 'L\'adresse de départ est obligatoire.',
                'max_length'    => 'L\'adresse de départ ne peut pas dépasser 255 caractères.',
            ],
            'endAddress'   => [
                'required'      => 'L\'adresse d\'arrivée est obligatoire.',
                'max_length'    => 'L\'adresse d\'arrivée ne peut pas dépasser 255 caractères.',
            ],
            'car'          => [
                'required'      => 'Veuillez sélectionner un véhicule.',
                'integer'       => 'Le véhicule sélectionné n\'est pas valide.',
                'greater_than'  => 'Veuillez sélectionner un véhicule.',
            ],
            'smoking'      => [
                'in_list'       => 'Veuillez indiquer si le covoiturage est fumeur ou non.',
            ],
        ];

    }

    /**
     * Détermine le nombre maximum de places réservables en fonction de la voiture
     * sélectionnée dans le POST (capacité de la voiture - 1 pour le conducteur).
     *
     * Si la voiture est invalide, n'appartient pas à l'utilisateur ou n'est pas
     * trouvée, retourne la valeur par défaut absolue (9).
     *
     * @return int Nombre maximum de places réservables pour la voiture
     */
    private function getMaxAvailableSeatsFromPostedCar(): int
    {
        
        $absoluteMax = 9;

        $userId = session('user_id');
        $carId  = $this->request->getPost('car');

        if (!is_numeric($carId) || (int) $carId <= 0) {
            return $absoluteMax;
        }

        $car = $this->carModel->where([
            'id'      => (int) $carId,
            'user_id' => $userId,
        ])->first();

        if (!$car) {
            return $absoluteMax;
        }

        return (int) $car['seats'] - 1;
    }

    /**
     * Récupère et nettoie les adresses (départ, étapes, arrivée) postées dans le formulaire.
     *
     * Les étapes intermédiaires sont indexées par 'stage0', 'stage1', etc.
     * Les clés réservées sont 'start' et 'end'.
     *
     * @return array<string, string> Adresses nettoyées indexées par clé
     */   
    public function getLocationsCreateFormData(): array{

        $locations['start'] = $this->sanitizeAddress($this->request->getPost('startAddress'));

        $stagesAddresses = $this->request->getPost('stagesAddresses');

        if (is_array($stagesAddresses)) {
            foreach ($stagesAddresses as $key => $stageAddress) {
                $locations['stage' . (int) $key] = $this->sanitizeAddress($stageAddress);
            }
        }

        $locations['end'] = $this->sanitizeAddress($this->request->getPost('endAddress'));

        return $locations;

    }

    /**
     * Récupère les champs du trajet (hors adresses) postés dans le formulaire.
     *
     * @return array{startDate:string, startTime:string, seats:mixed, note:mixed,
     *               smoking:mixed, car:mixed} Données brutes du POST
     */   
    public function getJourneyCreateFormData(){

        return [
            'startDate'     => $this->request->getPost('startDate'),
            'startTime'     => $this->request->getPost('startTime'),
            'seats'         => $this->request->getPost('seats'),
            'note'          => $this->request->getPost('note'),
            'smoking'       => $this->request->getPost('smoking'),
            'car'           => $this->request->getPost('car'),
        ];

    }

    /**
     * Agrège l'ensemble des données du formulaire de création de trajet.
     *
     * @return array{location: array<string,string>, journey: array} Données du formulaire
     *               structurées en deux sous-tableaux : 'location' et 'journey'
     */    
    public function getCreateFormData():array{

        return [
            "location"=>$this->getLocationsCreateFormData(),
            "journey"=>$this->getJourneyCreateFormData(),
        ];

    }

    /**
     * Récupère et normalise les filtres de recherche depuis la query string.
     *
     * Les coordonnées géographiques sont converties en float, ou null si
     * absentes/vides (un paramètre vide comme ?startLat= ne doit pas être
     * interprété comme la coordonnée 0.0).
     *
     * @return array{
     *     startAddress: ?string,
     *     endAddress: ?string,
     *     latStart: ?float,
     *     lngStart: ?float,
     *     latEnd: ?float,
     *     lngEnd: ?float,
     *     filterDate: ?string,
     *     filterTime: ?string,
     *     availableSeats: int,
     *     smoking: ?string,
     *     page: int
     * }
     */
    private function getShowAllFilter(): array
    {
        return [
            'startAddress'   => $this->request->getGet('startAddress'),
            'endAddress'     => $this->request->getGet('endAddress'),
            'latStart'       => $this->parseFloatOrNull($this->request->getGet('startLat')),
            'lngStart'       => $this->parseFloatOrNull($this->request->getGet('startLng')),
            'latEnd'         => $this->parseFloatOrNull($this->request->getGet('endLat')),
            'lngEnd'         => $this->parseFloatOrNull($this->request->getGet('endLng')),
            'filterDate'     => $this->request->getGet('date'),
            'filterTime'     => $this->request->getGet('time'),
            'availableSeats' => (int) ($this->request->getGet('availableSeats') ?? 1),
            'smoking'        => $this->request->getGet('smoking'),
            'page'           => (int) ($this->request->getGet('page') ?? 1),
        ];
    }

    /**
     * Convertit une valeur de query string en float, ou null si vide/absente.
     *
     * @param  mixed $value
     * @return float|null
     */
    private function parseFloatOrNull($value): ?float
    {
        return ($value !== null && $value !== '') ? (float) $value : null;
    }

    /**
     * Récupère les données de localisation d'une adresse via l'API de la Géoplateforme (IGN/BAN).
     *
     * @param  string $adresse Adresse en texte libre (ex : "8 bd du Port 95000 Cergy")
     * @param  int    $limit   Nombre max de résultats (défaut : 1)
     * @return array|null      Propriétés de l'adresse (avec latitude/longitude),
     *                         ou null si rien trouvé ou en cas d'erreur réseau
     */
    private function sanitizeAddress($value): string{
        
        if (!is_string($value)) {
            return '';
        }
        return trim(strip_tags($value));

    }


    /**
     * Récupère les données de localisation d'une adresse via l'API de la Géoplateforme (IGN/BAN).
     *
     * @param string $adresse Adresse en texte libre (ex : "8 bd du Port 95000 Cergy")
     * @param int    $limit   Nombre max de résultats (défaut : 1)
     * @return array|null     Propriétés de l'adresse, ou null si rien trouvé / erreur
     */
    function getLocationData(string $adresse, int $limit = 1): ?array{

        $url = 'https://data.geopf.fr/geocodage/search?' . http_build_query([
            'q'     => $adresse,
            'index' => 'address',
            'limit' => $limit,
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false || $httpCode !== 200) {
            return null;
        }

        $data = json_decode($response, true);
        if (empty($data['features'])) {
            return null;
        }

        $feature = $data['features'][0];
        $result  = $feature['properties'] ?? [];

        if (isset($feature['geometry']['coordinates'])) {
            [$longitude, $latitude] = $feature['geometry']['coordinates'];
            $result['longitude'] = $longitude;
            $result['latitude']  = $latitude;
        }

        return $result;
    }

    /**
     * Récupère les données de localisation d'un ensemble d'adresses.
     *
     * @param  array<string,string> $addresses Adresses indexées par clé (start, stage0, ..., end)
     * @return array|null Données de localisation indexées par les mêmes clés.
     *                    Une entrée vaut null si l'adresse n'a pas pu être géolocalisée.
     */
    function getLocationsData(array $addresses):array | null{

        $addressesData=[];

        foreach($addresses as $key => $address){

            $addressesData[$key]=$this->getLocationData($address);

        }

        return $addressesData;

    }

    /**
     * Récupère un itinéraire au format GeoJSON via l'API OpenRouteService.
     *
     * @param  array<int, array{0: float, 1: float}> $coordinates Tableau de coordonnées
     *         au format [[lat, long], [lat, long], ...]
     * @return string|null Réponse GeoJSON brute, ou null en cas d'erreur de l'API
     */
    function getTrack(array $coordinates): ?string{

        $url = 'https://api.openrouteservice.org/v2/directions/driving-car/geojson';
        $apiKey = $_ENV['ORS_API_KEY'];

        $coordFields=[];

        foreach($coordinates as $coordinate){

            $lat = $coordinate[0];
            $long = $coordinate[1];

            $coordFields[] = '['.$long.','.$lat.']';

        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json, application/geo+json',
                'Authorization: '.$apiKey
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => '{"coordinates":['.implode(",",$coordFields).']}',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);


        if ($response === false || $httpCode !== 200) {
            return null;
        }

        return $response;
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

        return $this->journeyModel
            ->where('user_id', $userId)
            ->where('start_datetime >=', $dayStartDateTime->format('Y-m-d H:i:s'))
            ->where('start_datetime <',  $dayEndDateTime->format('Y-m-d H:i:s'))
            ->first();
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
    function fetchAllLocationsData(array $addresses): array {

        $locationsData = [];
        $errors = [];

        foreach ($addresses as $key => $address) {

            $data = $this->getLocationData($address);
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
    private function fetchTrackOrFail(array $locationsData): string
    {
        $locationsCoordinates = [];
        foreach ($locationsData as $location) {
            $locationsCoordinates[] = [$location['latitude'], $location['longitude']];
        }

        $geoJsonTrack = $this->getTrack($locationsCoordinates);
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
    private function persistJourney(int $userId, array $createFormData, array $locationsData, string $geoJsonTrack): int
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
     * Calcule l'heure d'arrivée d'un trajet à partir de son tracé et de son heure de départ.
     *
     * @param  int $journeyId Identifiant du trajet
     * @return DateTimeImmutable Heure d'arrivée estimée
     */
    private function getArrivaleDateTime(int $journeyId): DateTimeImmutable {
    
        $trackId = $this->journeyModel->select(['track_id'])->where([
            'id'=>$journeyId,
        ])->first();

        $track = $this->trackModel->where([
            'id'=>$trackId,
        ])->first();

        $trackDuration = $this->calculateTrackDuration(json_decode($track['geojson']));
        $journeyStartDateTime = new DateTimeImmutable($this->journeyModel->select(['start_datetime'])->where(['id'=>$journeyId])->first()['start_datetime']);

        return $journeyStartDateTime->modify('+'.$trackDuration.' seconds');
    }


    /**
     * Calcule la durée totale d'un trajet à partir de son GeoJSON,
     * en sommant la durée de chacun de ses segments.
     *
     * @param  object|null $track Trajet au format GeoJSON décodé en objet
     * @return int Durée totale du trajet, en secondes
     */
    private function calculateTrackDuration(?object $track): int {

        $duration=0;
        $segments = $track->features[0]->properties->segments ?? null;

        foreach($segments as $segment){
            $duration += $segment->duration;
        }

        return (int) $duration;
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
     * @return array[] Sous-ensemble des trajets correspondants
     */
    private function findMatchingJourneys(array $journeys, ?array $start, ?array $end, float $maxDistanceKm = 10): array
    {
        // Aucun critère géographique : pas de filtrage.
        if ($start === null && $end === null) {
            return $journeys;
        }

        $matchingJourneys = [];

        foreach ($journeys as $journey) {

            // --- Récupération des points du tracé
            $points = $this->getTrackPoints((int) $journey['track_id']);

            $trackIsEmpty = empty($points);
            if ($trackIsEmpty) {
                continue;
            }

            // Par défaut, on commence la recherche de l'arrivée au début du tracé.
            // Si un départ est demandé, ce point de départ sera mis à jour ci-dessous.
            $startIndex = 0;

            // --- Contrainte sur le départ
            if ($start !== null) {

                $startIndex          = $this->findClosestPointIndex($points, $start);
                $distanceToStart     = $this->calculateDistance($start, $points[$startIndex]);
                $startIsTooFar       = $distanceToStart > $maxDistanceKm;

                if ($startIsTooFar) {
                    continue;
                }
            }

            // --- Contrainte sur l'arrivée
            // La recherche démarre à $startIndex pour garantir le sens départ -> arrivée.
            if ($end !== null) {

                $endIndex          = $this->findClosestPointIndex($points, $end, $startIndex);
                $distanceToEnd     = $this->calculateDistance($end, $points[$endIndex]);
                $endIsTooFar       = $distanceToEnd > $maxDistanceKm;

                if ($endIsTooFar) {
                    continue;
                }
            }

            // --- Trajet validé : il passe à proximité du départ ET de l'arrivée
            $matchingJourneys[] = $journey;
        }

        return $matchingJourneys;
    }

    /**
     * Récupère et normalise les points du tracé d'un trajet.
     *
     * Les coordonnées GeoJSON sont au format [longitude, latitude] et sont
     * normalisées en tableaux associatifs ['lat', 'lon'] pour la suite des calculs.
     *
     * @param  int $trackId Identifiant du tracé
     * @return array<int, array{lat:float, lon:float}> Points du tracé ;
     *               tableau vide si le tracé est absent ou invalide
     */
    private function getTrackPoints(int $trackId): array
    {
        $track = $this->trackModel->find($trackId);
        if (empty($track['geojson'])) {
            return [];
        }

        $data        = json_decode($track['geojson'], true);
        $coordinates = $data['features'][0]['geometry']['coordinates'] ?? [];

        $points = [];
        foreach ($coordinates as $coordinate) {
            // GeoJSON => [longitude, latitude]
            $points[] = ['lat' => (float) $coordinate[1], 'lon' => (float) $coordinate[0]];
        }

        return $points;
    }

    /**
     * Renvoie l'indice du point du tracé le plus proche d'une cible.
     *
     * La recherche peut être restreinte à partir d'un indice donné, ce qui
     * permet d'imposer un ordre (par exemple : arrivée après le départ).
     *
     * @param  array<int, array{lat:float, lon:float}> $points Points du tracé
     * @param  array{lat:float, lon:float}             $target Point cible
     * @param  int                                     $fromIndex Indice de départ de la recherche
     * @return int|null Indice du point le plus proche, ou null si la plage est vide
     */
    private function findClosestPointIndex(array $points, array $target, int $fromIndex = 0): ?int
    {
        $closestIndex = null;
        $minDistance  = INF;
        $count        = count($points);

        for ($i = $fromIndex; $i < $count; $i++) {
            $distance = $this->calculateDistance($target, $points[$i]);
            if ($distance < $minDistance) {
                $minDistance  = $distance;
                $closestIndex = $i;
            }
        }

        return $closestIndex;
    }

    /**
     * Calcule la distance en kilomètres entre deux points géographiques
     * via la formule de Haversine (Terre supposée sphérique, rayon 6371 km).
     *
     * @param  array{lat:float, lon:float} $coord1 Premier point
     * @param  array{lat:float, lon:float} $coord2 Second point
     * @return float Distance entre les deux points, en kilomètres
     */
    private function calculateDistance(array $coord1, array $coord2): float
    {
        $earthRadiusKm = 6371;

        $lat1 = deg2rad($coord1['lat']);
        $lon1 = deg2rad($coord1['lon']);
        $lat2 = deg2rad($coord2['lat']);
        $lon2 = deg2rad($coord2['lon']);

        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        $a = sin($dLat / 2) ** 2
        + cos($lat1) * cos($lat2) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }

}