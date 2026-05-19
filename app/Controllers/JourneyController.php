<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;

use App\Models\TrackModel;
use App\Models\JourneyModel;
use App\Models\CarModel;
use App\Models\LocationModel;
use App\Models\BookingModel;
use DateTimeImmutable;

class JourneyController extends BaseController{

    protected TrackModel $trackModel;
    protected JourneyModel $journeyModel;
    protected CarModel $carModel;
    protected BookingModel $bookingModel;
    protected LocationModel $locationModel;

    public function __construct(){
        $this->trackModel = new TrackModel();
        $this->journeyModel = new JourneyModel();
        $this->carModel = new CarModel();
        $this->bookingModel = new BookingModel();
        $this->locationModel = new LocationModel();
    }

    public function showCreateForm(): string
    {
        return view('Journeys/newJourney', [
            'title' => "Publier un trajet"
        ]);
    }

    /**create
     * 
     * Vérifie que l'utilisateur est connecté, récupère les donneés du formulaires et les donne
     * au model pour les insérer.
     * 
     * Les vérifications des données sont effectuées dans les models : journeyModel & trackModel
     * 
     */
    public function create(){

        $userId = session('user_id');
        if (empty($userId)) return redirect()->to('/login');
 
        // --- Récupération des données
        $trackData         = $this->request->getPost('geoJson');
        $locationStartData = [
            'longitude' => $this->request->getPost('long_start'),
            'latitude'  => $this->request->getPost('lat_start'),
        ];
        $locationEndData = [
            'longitude' => $this->request->getPost('long_end'),
            'latitude'  => $this->request->getPost('lat_end'),
        ];
        $journeyData = [
            'start_datetime' => $this->request->getPost('startDateTime'),
            'seats'          => $this->request->getPost('seats'),
            'note'           => $this->request->getPost('note'),
            'smoking'        => $this->request->getPost('smoking'),
            'user_id'        => $userId,
        ];
 
        // --- Vérification : pas de trajet existant sur la même demi-journée
        $journeyStartDate = new DateTimeImmutable($this->request->getPost('startDate'));
        $journeyStartTime = new DateTimeImmutable($this->request->getPost('startTime'));
        $startHour        = $journeyStartTime->format('H') < 12 ? 0 : 12;
        $dayStartDateTime = $journeyStartDate->setTime(0, $startHour, 0);
        $dayEndDateTime   = $dayStartDateTime->modify('+12 hours');
 
        $existingJourney = $this->journeyModel
            ->where('user_id', $userId)
            ->where('start_datetime >=', $dayStartDateTime->format('Y-m-d H:i:s'))
            ->where('start_datetime <',  $dayEndDateTime->format('Y-m-d H:i:s'))
            ->first();
 
        if ($existingJourney) {
            return redirect()->back()->withInput()
                ->with('errors', ['journey' => 'Vous avez déjà un trajet sur cette demi-journée.']);
        }
 
        // --- Validation via les models
        $errors = [];
 
        if (!$this->trackModel->validate(['geojson' => $trackData]))
            $errors = array_merge($errors, $this->trackModel->errors());
 
        if (!$this->locationModel->validate($locationStartData))
            $errors = array_merge($errors, array_map(fn($e) => "Départ : $e", $this->locationModel->errors()));
 
        if (!$this->locationModel->validate($locationEndData))
            $errors = array_merge($errors, array_map(fn($e) => "Arrivée : $e", $this->locationModel->errors()));
 
        if (!$this->journeyModel->validate($journeyData))
            $errors = array_merge($errors, $this->journeyModel->errors());
 
        if (!empty($errors))
            return redirect()->back()->withInput()->with('errors', $errors);
 
        // --- Insertion dans la base
        $db = \Config\Database::connect();
        $db->transStart();
 
        $trackId         = $this->trackModel->insert(['geojson' => $trackData]);
        $locationStartId = $this->locationModel->insert($locationStartData);
        $locationEndId   = $this->locationModel->insert($locationEndData);
 
        $journeyData['track_id']          = $trackId;
        $journeyData['location_start_id'] = $locationStartId;
        $journeyData['location_end_id']   = $locationEndId;
 
        $journeyId = $this->journeyModel->insert($journeyData);
 
        $db->transComplete();
 
        if (!$db->transStatus())
            return redirect()->back()->withInput()
                ->with('errors', ['db' => 'Une erreur est survenue lors de l\'enregistrement.']);
 
        return redirect()->to('/journeys/' . $journeyId);
    }


    public function show($id): string
    {
        return view('Journeys/journeyShow',[
            'title' => "Chercher un trajet"
        ]);
    }

    public function showAll(): string
    {
        // --- Récupération des filtres
        $startAddress   = $this->request->getGet('startAddress');
        $endAddress     = $this->request->getGet('endAddress');
        $latStart       = $this->request->getGet('startLat');
        $lngStart       = $this->request->getGet('startLng');
        $latEnd         = $this->request->getGet('endLat');
        $lngEnd         = $this->request->getGet('endLng');
        $filterDate     = $this->request->getGet('date');
        $filterTime     = $this->request->getGet('time');
        $availableSeats = $this->request->getGet('availableSeats') ?? 1;
        $smoking        = $this->request->getGet('smoking');
        $page           = $this->request->getGet('page') ?? 1;

        // --- Construction de la requête
        $db      = \Config\Database::connect();
        $builder = $db->table('journey')
            ->select('journey.*, city_start.name as city_start_name, city_end.name as city_end_name, u.firstname as driver_firstname, u.lastname as driver_lastname')
            ->join('location loc_start', 'loc_start.id = journey.location_start_id')
            ->join('location loc_end',   'loc_end.id = journey.location_end_id')
            ->join('city city_start',    'city_start.id = loc_start.city_id')
            ->join('city city_end',      'city_end.id = loc_end.city_id')
            ->join('user u',             'u.id = journey.user_id')
            ->join('stage', 'stage.journey_id = journey.id', 'left')
            ->join('location loc_stage', 'loc_stage.id = stage.location_id', 'left')
            ->where('journey.canceled_at', null)
            ->orderBy('journey.start_datetime', 'ASC');

        if ($latStart && $lngStart)
            $builder->groupStart()
                        ->where("(6371 * acos(cos(radians($latStart)) * cos(radians(loc_start.latitude)) * cos(radians(loc_start.longitude) - radians($lngStart)) + sin(radians($latStart)) * sin(radians(loc_start.latitude)))) <=", 10)
                        ->orWhere("(6371 * acos(cos(radians($latStart)) * cos(radians(loc_stage.latitude)) * cos(radians(loc_stage.longitude) - radians($lngStart)) + sin(radians($latStart)) * sin(radians(loc_stage.latitude)))) <=", 10)
                    ->groupEnd();

        if ($latEnd && $lngEnd)
            $builder->groupStart()
                        ->where("(6371 * acos(cos(radians($latEnd)) * cos(radians(loc_end.latitude)) * cos(radians(loc_end.longitude) - radians($lngEnd)) + sin(radians($latEnd)) * sin(radians(loc_end.latitude)))) <=", 10)
                        ->orWhere("(6371 * acos(cos(radians($latEnd)) * cos(radians(loc_stage.latitude)) * cos(radians(loc_stage.longitude) - radians($lngEnd)) + sin(radians($latEnd)) * sin(radians(loc_stage.latitude)))) <=", 10)
                    ->groupEnd();

        if ($filterDate && $filterTime) {
            $dateTimeFrom = date('Y-m-d H:i:s', strtotime($filterDate . ' ' . $filterTime . ':00') - 1800);
            $dateTimeTo   = $filterDate . ' ' . $filterTime . ':00';
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
        $perPage  = 10;
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
}