<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\JourneyRequestModel;
use App\Models\CityModel;
use App\Models\LocationModel;
use App\Services\JourneyService;
use CodeIgniter\HTTP\RedirectResponse;

class JourneyRequestController extends BaseController
{
    private const NOT_FOUND = 'Demande introuvable.';

    protected JourneyRequestModel $journeyRequestModel;
    protected CityModel $cityModel;
    protected LocationModel $locationModel;

    public function __construct() {
        $this->journeyRequestModel = new JourneyRequestModel();
        $this->cityModel           = new CityModel();
        $this->locationModel       = new LocationModel();
    }

    /**
     * Affiche la liste de toutes les demandes de trajet.
     * GET /journey-requests
     */
    public function showAll(): string
    {
        $filterCityStart = $this->request->getGet('cityStart');
        $filterCityEnd   = $this->request->getGet('cityEnd');
        $filterDate      = $this->request->getGet('date');

        $builder = $this->journeyRequestModel
            ->select('journey_request.*,
                    u.firstname, u.lastname, u.avatar, u.is_student,
                    loc_start.address as address_start,
                    loc_end.address   as address_end,
                    city_start.name   as city_start_name,
                    city_end.name     as city_end_name')
            ->join('user u',             'u.id = journey_request.user_id')
            ->join('location loc_start', 'loc_start.id = journey_request.location_start_id')
            ->join('location loc_end',   'loc_end.id = journey_request.location_end_id')
            ->join('city city_start',    'city_start.id = loc_start.city_id')
            ->join('city city_end',      'city_end.id = loc_end.city_id')
            ->where('journey_request.start_datetime >=', date('Y-m-d H:i:s'))
            ->orderBy('journey_request.start_datetime', 'ASC');

        if ($filterCityStart) {
            $builder->like('city_start.name', $filterCityStart);
        }

        if ($filterCityEnd) {
            $builder->like('city_end.name', $filterCityEnd);
        }

        if ($filterDate) {
            $builder->where('DATE(journey_request.start_datetime)', $filterDate);
        }

        $journeyRequests = $builder->findAll();

        $userId            = (int) session()->get('user_id');
        $userRequestsCount = $userId
            ? $this->journeyRequestModel->where('user_id', $userId)->countAllResults()
            : 0;

        return view('JourneyRequests/journeyRequestShowAll', [
            'title'             => 'Demandes de trajet',
            'journeyRequests'   => $journeyRequests,
            'filterCityStart'   => $filterCityStart,
            'filterCityEnd'     => $filterCityEnd,
            'filterDate'        => $filterDate,
            'userRequestsCount' => $userRequestsCount,
        ]);
    }

    /**
     * Affiche le détail d'une demande de trajet.
     * GET /journey-requests/:id
     */
    public function show(int $id): string|RedirectResponse
    {
        $journeyRequest = $this->journeyRequestModel->findWithDetails($id, (int) session()->get('user_id'));

        if (!$journeyRequest) {
            return redirect()->to('/journey-requests')->with('error', self::NOT_FOUND);
        }

        return view('JourneyRequests/journeyRequestShow', [
            'title'          => 'Détail de la demande',
            'journeyRequest' => $journeyRequest
        ]);
    }

    /**
     * Affiche le formulaire de création d'une demande de trajet.
     * GET /journey-requests/new
     */
    public function showCreateForm(): string
    {
        return view('JourneyRequests/newJourneyRequest', [
            'title' => 'Publier une demande de trajet'
        ]);
    }

    /**
     * Valide et enregistre une nouvelle demande de trajet.
     * POST /journey-requests/new
     */
    public function create(): RedirectResponse
    {
        $userId = (int) session()->get('user_id');

        // ====== Validation des données du formulaire
        if (!$this->validate($this->getValidationRules(), $this->getValidationMessages())) {
            return redirect()->to('/journey-requests/new')->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // ====== Récupération des données POST
        $startDate = $this->request->getPost('startDate');
        $startTime = $this->request->getPost('startTime');
        [$h, $m]   = explode(':', $startTime);
        $startDatetime = (new \DateTimeImmutable($startDate))->setTime((int)$h, (int)$m, 0);

        $startCityId = $this->cityModel->findOrCreateCity(
            $this->request->getPost('startCity'),
            $this->request->getPost('startZipcode')
        );
        $startLocationId = $this->locationModel->insert([
            'latitude'  => (float) $this->request->getPost('startLat'),
            'longitude' => (float) $this->request->getPost('startLng'),
            'address'   => $this->request->getPost('startAddress'),
            'note'      => '',
            'city_id'   => $startCityId,
        ]);

        $endCityId = $this->cityModel->findOrCreateCity(
            $this->request->getPost('endCity'),
            $this->request->getPost('endZipcode')
        );
        $endLocationId = $this->locationModel->insert([
            'latitude'  => (float) $this->request->getPost('endLat'),
            'longitude' => (float) $this->request->getPost('endLng'),
            'address'   => $this->request->getPost('endAddress'),
            'note'      => '',
            'city_id'   => $endCityId,
        ]);

        // ====== Insertion de la demande
        $data = [
            'start_datetime'    => $startDatetime->format('Y-m-d H:i:s'),
            'seats'             => $this->request->getPost('seats') ?: null,
            'radius_km'         => (float) ($this->request->getPost('radius_km') ?: 1),
            'message'           => $this->request->getPost('message') ?: null,
            'user_id'           => $userId,
            'location_start_id' => $startLocationId,
            'location_end_id'   => $endLocationId,
        ];

        if (!$this->journeyRequestModel->save($data)) {
            return redirect()->to('/journey-requests/new')->withInput()->with('errors', $this->journeyRequestModel->errors());
        }

        return redirect()->to('/journey-requests')->with('success', 'Demande publiée avec succès.');
    }

    /**
     * Affiche le formulaire de modification d'une demande de trajet.
     * GET /journey-requests/:id/edit
     */
    public function showEditForm(int $id): string|RedirectResponse
    {
        $journeyRequest = $this->journeyRequestModel->findWithDetails($id, (int) session()->get('user_id'));

        if (!$journeyRequest) {
            return redirect()->to('/journey-requests')->with('error', self::NOT_FOUND);
        }

        return view('JourneyRequests/journeyRequestEdit', [
            'title'          => 'Modifier la demande',
            'journeyRequest' => $journeyRequest,
            'back'           => $this->validateBackUrl($this->request->getGet('back')),
        ]);
    }

    /**
     * Valide et enregistre les modifications d'une demande de trajet.
     * POST /journey-requests/:id/edit
     */
    public function update(int $id): RedirectResponse
    {
        $userId         = (int) session()->get('user_id');
        $journeyRequest = $this->journeyRequestModel->where('user_id', $userId)->find($id);

        if (!$journeyRequest) {
            return redirect()->to('/journey-requests')->with('error', self::NOT_FOUND);
        }

        $startCityId = $this->cityModel->findOrCreateCity(
            $this->request->getPost('startCity'),
            $this->request->getPost('startZipcode')
        );
        $startLocationId = $this->locationModel->insert([
            'latitude'  => (float) $this->request->getPost('startLat'),
            'longitude' => (float) $this->request->getPost('startLng'),
            'address'   => $this->request->getPost('startAddress'),
            'note'      => '',
            'city_id'   => $startCityId,
        ]);

        $endCityId = $this->cityModel->findOrCreateCity(
            $this->request->getPost('endCity'),
            $this->request->getPost('endZipcode')
        );
        $endLocationId = $this->locationModel->insert([
            'latitude'  => (float) $this->request->getPost('endLat'),
            'longitude' => (float) $this->request->getPost('endLng'),
            'address'   => $this->request->getPost('endAddress'),
            'note'      => '',
            'city_id'   => $endCityId,
        ]);

        $startDate = $this->request->getPost('startDate');
        $startTime = $this->request->getPost('startTime');
        [$h, $m]   = explode(':', $startTime);
        $startDatetime = (new \DateTimeImmutable($startDate))->setTime((int)$h, (int)$m, 0);

        $data = [
            'location_start_id' => $startLocationId,
            'location_end_id'   => $endLocationId,
            'start_datetime'    => $startDatetime->format('Y-m-d H:i:s'),
            'seats'             => $this->request->getPost('seats') ?: null,
            'radius_km'         => (float) ($this->request->getPost('radius_km') ?: 1),
            'message'           => $this->request->getPost('message'),
        ];

        if (!$this->journeyRequestModel->update($id, $data)) {
            return redirect()->to(site_url("journey-requests/$id/edit"))->withInput()->with('errors', $this->journeyRequestModel->errors());
        }

        (new JourneyService())->notifyMatchingJourneys($id, $userId);

        $back = $this->validateBackUrl($this->request->getPost('back'));

        return redirect()->to($back ?: site_url('journey-requests/' . $id))->with('success', 'Demande modifiée avec succès.');
    }

    /**
     * Supprime une demande de trajet.
     * POST /journey-requests/:id/cancel
     */
    public function delete(int $id): RedirectResponse
    {
        $journeyRequest = $this->journeyRequestModel->where('user_id', session()->get('user_id'))->find($id);

        if (!$journeyRequest) {
            return redirect()->to('/journey-requests')->with('error', self::NOT_FOUND);
        }

        $this->journeyRequestModel->delete($id);

        return redirect()->to('/journey-requests')->with('success', 'Demande annulée avec succès.');
    }

    /**
     * Retourne les règles de validation du formulaire de demande de trajet.
     * Utilisées pour la création et la modification.
     *
     * @return array
     */
    private function getValidationRules(): array
    {
        return [
            'startDate'    => 'required|valid_date',
            'startTime'    => 'required|regex_match[/^([01]\d|2[0-3]):[0-5]\d$/]',
            'message'      => 'permit_empty|max_length[2000]',
            'startAddress' => 'required|max_length[255]',
            'endAddress'   => 'required|max_length[255]',
            'startLat'     => 'required|decimal',
            'startLng'     => 'required|decimal',
            'endLat'       => 'required|decimal',
            'endLng'       => 'required|decimal',
            'startCity'    => 'required|max_length[50]',
            'startZipcode' => 'required|max_length[10]',
            'endCity'      => 'required|max_length[50]',
            'endZipcode'   => 'required|max_length[10]',
        ];
    }

    /**
     * Retourne les messages de validation du formulaire de demande de trajet.
     * Utilisées pour la création et la modification.
     *
     * @return array
     */
    private function getValidationMessages(): array
    {
        return [
            'startDate' => [
                'required'   => 'Veuillez renseigner une date de départ.',
                'valid_date' => 'Veuillez renseigner une date valide.',
            ],
            'startTime' => [
                'required'    => 'Veuillez renseigner une heure de départ.',
                'regex_match' => 'Veuillez renseigner une heure valide (HH:MM).',
            ],
            'message' => [
                'max_length' => 'Le message doit contenir au maximum 2000 caractères.',
            ],
            'startAddress' => [
                'required'   => 'Veuillez sélectionner une adresse de départ valide.',
                'max_length' => 'L\'adresse de départ est trop longue.',
            ],
            'endAddress' => [
                'required'   => 'Veuillez sélectionner une adresse d\'arrivée valide.',
                'max_length' => 'L\'adresse d\'arrivée est trop longue.',
            ],
            'startLat' => [
                'required' => 'Veuillez sélectionner une adresse de départ valide.',
                'decimal'  => 'Latitude de départ invalide.',
            ],
            'startLng' => [
                'required' => 'Veuillez sélectionner une adresse de départ valide.',
                'decimal'  => 'Longitude de départ invalide.',
            ],
            'endLat' => [
                'required' => 'Veuillez sélectionner une adresse d\'arrivée valide.',
                'decimal'  => 'Latitude d\'arrivée invalide.',
            ],
            'endLng' => [
                'required' => 'Veuillez sélectionner une adresse d\'arrivée valide.',
                'decimal'  => 'Longitude d\'arrivée invalide.',
            ],
            'startCity' => [
                'required' => 'Veuillez sélectionner une adresse de départ valide.',
            ],
            'startZipcode' => [
                'required' => 'Veuillez sélectionner une adresse de départ valide.',
            ],
            'endCity' => [
                'required' => 'Veuillez sélectionner une adresse d\'arrivée valide.',
            ],
            'endZipcode' => [
                'required' => 'Veuillez sélectionner une adresse d\'arrivée valide.',
            ],
        ];
    }
}