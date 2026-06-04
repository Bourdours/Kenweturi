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

use App\Services\JourneyService;

use App\Exceptions\ExternalApiException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\AddressValidationException;

class JourneyController extends BaseController{

    protected TrackModel $trackModel;
    protected JourneyModel $journeyModel;
    protected CarModel $carModel;
    protected BookingModel $bookingModel;
    protected LocationModel $locationModel;
    protected CityModel $cityModel;
    protected StageModel $stageModel;

    protected JourneyService $journeyService;

    public function __construct(){

        $this->trackModel = new TrackModel();
        $this->journeyModel = new JourneyModel();
        $this->carModel = new CarModel();
        $this->bookingModel = new BookingModel();
        $this->locationModel = new LocationModel();
        $this->cityModel = new CityModel();
        $this->stageModel = new StageModel();

        $this->journeyService = new JourneyService();
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
     * Valide les données du formulaire, récupère le tracé via les APIs externes
     * (géocodage + routage), puis insère l'ensemble (track, locations, journey,
     * stages) en base via une transaction.
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

            $locationsData = $this->journeyService->fetchAllLocationsData($createFormData['location']);
            $geoJsonTrack  = $this->journeyService->fetchTrackOrFail($locationsData);
            $journeyId     = $this->journeyService->persistJourney($userId, $createFormData, $locationsData, $geoJsonTrack);

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

        // ====== Matching avec les demandes de trajet existantes
        try {
            $this->journeyService->notifyMatchingRequests($journeyId, $geoJsonTrack);
        } catch (\Throwable $e) {
            log_message('error', 'notifyMatchingRequests: ' . $e->getMessage());
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
        $journey['end_datetime'] = $this->journeyService->getArrivalDateTime($journey['id'])
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
            ->first();
        $isBooked = $userBooking !== null && $userBooking['status'] === 'accepted';
        $isPending = $userBooking !== null && $userBooking['status'] === 'pending';

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
            'isPending'       => $isPending,
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

    $matchingJourneys = $this->journeyService->findMatchingJourneys($candidates, $start, $end);

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
    private function getCreateValidationRules(int $maxSeats = 9): array {

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
    private function getCreateValidationMessages(int $maxSeats = 9): array {

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
    private function getLocationsCreateFormData(): array {

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
    private function getJourneyCreateFormData(): array {

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
    private function getCreateFormData(): array {

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
     * Nettoie une valeur d'adresse soumise dans le formulaire.
     *
     * Retire les balises HTML et les espaces en début/fin. Renvoie une chaîne
     * vide si la valeur n'est pas une chaîne.
     *
     * @param  mixed $value Valeur brute issue du POST
     * @return string       Adresse nettoyée (chaîne vide si entrée invalide)
     */
    private function sanitizeAddress($value): string {

        if (!is_string($value)) {
            return '';
        }
        return trim(strip_tags($value));

    }

}