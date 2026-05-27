<?php
namespace App\Controllers;

use App\Controllers\BaseController;
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

use DateTimeImmutable;
use DateTime;

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
     * create
     * 
     * Vérifie que l'utilisateur est connecté, valide les données du formulaire,
     * récupère le tracé via les APIs externes, puis insère le trajet en base.
     * 
     * Les erreurs sont gérées selon trois familles :
     *  - validation du formulaire HTTP
     *  - API externe indisponible (géocodage / routage)
     *  - validation des models / erreur de transaction
     */
    public function create()
    {
        $userId = session('user_id');

        // ====== Validation des données du formulaire
        $createValidationRules = $this->getCreateValidationRules();
        if (!$this->validate($createValidationRules)) {
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

        } catch (\Throwable $e) {

            return redirect()->back()->withInput()
                ->with('errors', ['db' => 'Une erreur est survenue lors de l\'enregistrement .']);

        }

        return redirect()->to('/journeys/' . $journeyId);
    }


    public function show($id): string|RedirectResponse
    {
        // ====== Récupération du trajet
        $journey = $this->journeyModel->findWithDetails((int) $id);

        if (!$journey) {
            return redirect()->to('/journeys');
        }

        // ====== Enrichissement du trajet
        $journey['end_datetime'] = $this->getArrivaleDateTime($journey['id'])
            ->format('Y-m-d H:i:s');

        // ====== Récupération des étapes intermédiaires
        $stages = $this->stageModel->findByJourney((int) $id);

        // ====== Calcul des places restantes
        $remainingSeats = $this->bookingModel->countRemainingSeats(
            (int) $id,
            (int) $journey['seats'],
        );

        // ====== Récupération des filtres de réservation
        $availableSeats = $this->request->getGet('seats') ?? 1;
        $boardingCity   = $this->request->getGet('boardingCity');

        $back = $this->request->getGet('back');

        return view('Journeys/journeyShow',[
            'title'          => 'Détail du trajet',
            'journey'        => $journey,
            'back'           => $back,
            'stages'         => $stages,
            'remainingSeats' => $remainingSeats,
            'availableSeats' => $availableSeats,
            'boardingCity'   => $boardingCity,
        ]);
    }

    public function showAll(): string|RedirectResponse 
    {
        // --- Récupération des filtres
        $startAddress   = $this->request->getGet('startAddress');
        $endAddress     = $this->request->getGet('endAddress');
        $latStart       = $this->request->getGet('startLat') !== null ? (float) $this->request->getGet('startLat') : null;
        $lngStart       = $this->request->getGet('startLng') !== null ? (float) $this->request->getGet('startLng') : null;
        $latEnd         = $this->request->getGet('endLat')   !== null ? (float) $this->request->getGet('endLat')   : null;
        $lngEnd         = $this->request->getGet('endLng')   !== null ? (float) $this->request->getGet('endLng')   : null;
        $filterDate     = $this->request->getGet('date');
        $filterTime     = $this->request->getGet('time');
        $availableSeats = (int) ($this->request->getGet('availableSeats') ?? 1);
        $smoking        = $this->request->getGet('smoking');
        $page           = (int) ($this->request->getGet('page') ?? 1);
 
        // --- Construction de la requête
        $db = \Config\Database::connect();
 
        if ($latStart && $lngStart) {
            $selectBoarding = "CASE WHEN (6371 * acos(cos(radians($latStart)) * cos(radians(loc_start.latitude)) * cos(radians(loc_start.longitude) - radians($lngStart)) + sin(radians($latStart)) * sin(radians(loc_start.latitude)))) <= 10 THEN city_start.name ELSE COALESCE((SELECT c.name FROM stage s JOIN location l ON l.id = s.location_id JOIN city c ON c.id = l.city_id WHERE s.journey_id = journey.id AND s.location_id != journey.location_end_id AND (6371 * acos(cos(radians($latStart)) * cos(radians(l.latitude)) * cos(radians(l.longitude) - radians($lngStart)) + sin(radians($latStart)) * sin(radians(l.latitude)))) <= 10 ORDER BY s.position ASC LIMIT 1), city_start.name) END";
        } else {
            $selectBoarding = 'city_start.name';
        }
 
        $builder = $db->table('journey')
            ->select("journey.*, city_start.name as city_start_name, city_end.name as city_end_name, u.firstname as driver_firstname, u.lastname as driver_lastname, (journey.seats - COALESCE((SELECT SUM(b.seat_numbers) FROM booking b WHERE b.journey_id = journey.id), 0)) as remaining_seats, $selectBoarding as city_boarding_name")
            ->join('location loc_start', 'loc_start.id = journey.location_start_id')
            ->join('location loc_end',   'loc_end.id = journey.location_end_id')
            ->join('city city_start',    'city_start.id = loc_start.city_id')
            ->join('city city_end',      'city_end.id = loc_end.city_id')
            ->join('user u',             'u.id = journey.user_id')
            ->where('journey.canceled_at', null)
            ->where('u.deleted_at', null)
            ->orderBy('journey.start_datetime', 'ASC');
 
        if ($latStart && $lngStart) {
            $distStart     = "(6371 * acos(cos(radians($latStart)) * cos(radians(loc_start.latitude)) * cos(radians(loc_start.longitude) - radians($lngStart)) + sin(radians($latStart)) * sin(radians(loc_start.latitude)))) <= 10";
            $subStageStart = "journey.id IN (SELECT s.journey_id FROM stage s JOIN location l ON l.id = s.location_id WHERE l.id != journey.location_end_id AND (6371 * acos(cos(radians($latStart)) * cos(radians(l.latitude)) * cos(radians(l.longitude) - radians($lngStart)) + sin(radians($latStart)) * sin(radians(l.latitude)))) <= 10)";
            $builder->groupStart()
                        ->where($distStart, null, false)
                        ->orWhere($subStageStart, null, false)
                    ->groupEnd();
        }
 
        if ($latEnd && $lngEnd) {
            $distEnd     = "(6371 * acos(cos(radians($latEnd)) * cos(radians(loc_end.latitude)) * cos(radians(loc_end.longitude) - radians($lngEnd)) + sin(radians($latEnd)) * sin(radians(loc_end.latitude)))) <= 10";
            $subStageEnd = "journey.id IN (SELECT s.journey_id FROM stage s JOIN location l ON l.id = s.location_id WHERE l.id != journey.location_start_id AND (6371 * acos(cos(radians($latEnd)) * cos(radians(l.latitude)) * cos(radians(l.longitude) - radians($lngEnd)) + sin(radians($latEnd)) * sin(radians(l.latitude)))) <= 10)";
            $builder->groupStart()
                        ->where($distEnd, null, false)
                        ->orWhere($subStageEnd, null, false)
                    ->groupEnd();
        }
 
        if ($filterDate && $filterTime) {
            $dateTimeCenter = strtotime($filterDate . ' ' . $filterTime . ':00');
            $dateTimeFrom   = date('Y-m-d H:i:s', $dateTimeCenter - 1800);
            $dateTimeTo     = date('Y-m-d H:i:s', $dateTimeCenter + 1800);
            $builder->where('journey.start_datetime >=', $dateTimeFrom)
                    ->where('journey.start_datetime <=', $dateTimeTo);
        }
 
        elseif ($filterDate)
            $builder->where('DATE(journey.start_datetime)', $filterDate);
        else
            $builder->where('journey.start_datetime >=', date('Y-m-d H:i:s'));
 
        if ($availableSeats)
            $builder->where("(journey.seats - COALESCE((SELECT SUM(b.seat_numbers) FROM booking b WHERE b.journey_id = journey.id), 0)) >=", $availableSeats);
 
        if ($smoking !== null && $smoking !== '')
            $builder->where('journey.smoking', $smoking);
 
        // --- Pagination
        $perPage  = 5;
        $total    = $builder->countAllResults(false);
        $journeys = $builder->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();
        $pager    = \Config\Services::pager();
 
        return view('Journeys/journeyShowAll', [
            'title'          => 'Rechercher un trajet',
            'journeys'       => $journeys,
            'pager'          => $pager,
            'total'          => $total,
            'page'           => $page,
            'perPage'        => $perPage,
            'startAddress'   => $startAddress,
            'endAddress'     => $endAddress,
            'latStart'       => $latStart,
            'lngStart'       => $lngStart,
            'latEnd'         => $latEnd,
            'lngEnd'         => $lngEnd,
            'filterDate'     => $filterDate,
            'filterTime'     => $filterTime,
            'availableSeats' => $availableSeats,
            'smoking'        => $smoking,
        ]);
    } 
    
    public function book($id): RedirectResponse
    {
        $userId = session('user_id');

        // --- Vérification que le trajet existe et n'est pas annulé
        $journey = $this->journeyModel->where('id', $id)
                        ->where('canceled_at', null)
                        ->first();

        if (!$journey)
            return redirect()->to('/journeys');

        // --- Vérification que c'est pas le driver
        if ($journey['user_id'] === $userId)
            return redirect()->to('/journeys/' . $id)
                ->with('errors', ['booking' => 'Vous ne pouvez pas réserver votre propre trajet.']);

        // --- Vérification pas déjà réservé
        $existing = $this->bookingModel->where('journey_id', $id)
                                       ->where('user_id', $userId)
                                       ->first();

        if ($existing)
            return redirect()->to('/journeys/' . $id)
                ->with('errors', ['booking' => 'Vous avez déjà réservé ce trajet.']);

        // --- Vérification places restantes
        $bookSeats = $this->bookingModel->selectSum('seat_numbers')
                                        ->where('journey_id', $id)
                                        ->get()->getRowArray();
        $remainingSeats = $journey['seats'] - ($bookSeats['seat_numbers'] ?? 0);

        $seatsRequested = $this->request->getPost('seat_numbers') ?? 1;

        if ($seatsRequested > $remainingSeats)
            return redirect()->to('/journeys/' . $id)
                ->with('errors', ['booking' => 'Plus assez de places disponibles.']);

        // --- Insertion de la réservation
        $this->bookingModel->insert([
            'booking_date' => date('Y-m-d H:i:s'),
            'seat_numbers' => $seatsRequested,
            'journey_id'   => $id,
            'user_id'      => $userId
        ]);

        return redirect()->to('/journeys/' . $id)
            ->with('success', 'Réservation effectuée avec succès.');
    }

    /**
     * Retourne les règles de validation pour le formulaire de création de trajet.
     *
     * La borne max de 'seats' est calculée dynamiquement à partir de la capacité
     * de la voiture sélectionnée (capacité - 1 pour le conducteur). Si la voiture
     * ne peut pas être résolue, on retombe sur une borne par défaut : la règle
     * sur 'car' invalidera le formulaire de toute façon.
     */
    public function getCreateValidationRules(): array {

        $maxSeats = $this->getMaxAvailableSeatsFromPostedCar();

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
     * Détermine le nombre maximum de places réservables en fonction de la voiture
     * sélectionnée dans le POST (capacité de la voiture - 1 pour le conducteur).
     *
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

    public function getCreateFormData():array{

        return [
            "location"=>$this->getLocationsCreateFormData(),
            "journey"=>$this->getJourneyCreateFormData(),
        ];

    }

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


    function getLocationsData(array $addresses):array | null{

        $addressesData=[];

        foreach($addresses as $key => $address){

            $addressesData[$key]=$this->getLocationData($address);

        }

        return $addressesData;

    }

    /**
     * Renvoi une route au format geoJson
     * 
     * input array : coordinates : tableau de coordonnées au format [[lat,long],[lat,long]....]
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
     * (matin : 00h–12h, après-midi : 12h–24h) que la date/heure fournies,
     * ou null si aucun.
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
     * Lève une exception si une adresse ne peut pas être géolocalisée.
     *
     * @param array $addresses Tableau d'adresses textuelles indexé par clé (start, stage0, ..., end)
     * @return array           Tableau des données de localisation indexé par les mêmes clés
     * @throws ExternalApiException Si l'API ne renvoie rien pour une adresse
     */
    private function fetchAllLocationsData(array $addresses): array {

        $locationsData = [];

        foreach ($addresses as $key => $address) {

            $data = $this->getLocationData($address);
            // Un stage peut-être vide, dans ce cas on ne le prend pas en compte
            if(!empty($address)){
                if ($data === null && !empty($address)) throw new ExternalApiException("Adresse introuvable: $address");
                $locationsData[$key] = $data;
            }

        }

        return $locationsData;
        
    }

    /**
     * Récupère le tracé GeoJSON reliant l'ensemble des locations via l'API de routage.
     *
     * @param array $locationsData Données de localisation (avec latitude / longitude)
     * @return string              Tracé au format GeoJSON
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
     * @param int    $userId         Identifiant du conducteur
     * @param array  $createFormData Données du formulaire (clés 'journey' et 'location')
     * @param array  $locationsData  Données de localisation renvoyées par l'API de géocodage
     * @param string $geoJsonTrack   Tracé GeoJSON renvoyé par l'API de routage
     * @return int                   Identifiant du trajet créé
     * @throws ModelValidationException Si un model refuse l'insertion
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
     * @param array $locationsData Données renvoyées par l'API de géocodage
     * @return array               Tableau d'entités location
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
     * @param array $journeyData Données du journey (clés 'startDate' et 'startTime')
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
     *
     * @param array  $journeyData  Données du journey (pour la date/heure de départ)
     * @param string $geoJsonTrack Tracé GeoJSON renvoyé par l'API
     * @return DateTimeImmutable[] Tableau des heures de départ des étapes (vide si pas d'étape)
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
     * Calcule l'heure d'arrivée d'un trajet
     *
     * @param int $journeyId ID du journey
     * @return DateTimeImmutable Heure d'arrivée
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
     * Calcul le temps de trajet d'un geoJson
     *
     * @param object $track trajet au format geoJson
     * @return int Durée du trajet
     */
    private function calculateTrackDuration(?object $track): int {

        $duration=0;
        $segments = $track->features[0]->properties->segments ?? null;

        foreach($segments as $segment){
            $duration += $segment->duration;
        }

        return (int) $duration;
    }

}