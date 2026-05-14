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

class JourneysController extends BaseController{

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

    public function showAll(): RedirectResponse
    {
        return redirect()->to('/journeys');
    }
}