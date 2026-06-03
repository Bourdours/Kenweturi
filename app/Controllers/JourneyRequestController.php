<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\JourneyRequestModel;
use CodeIgniter\HTTP\RedirectResponse;

class JourneyRequestController extends BaseController
{
    private const NOT_FOUND = 'Demande introuvable.';

    protected JourneyRequestModel $journeyRequestModel;

    public function __construct() {
        $this->journeyRequestModel = new JourneyRequestModel();
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
                    city_start.name as city_start_name,
                    city_end.name   as city_end_name')
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

        return view('JourneyRequests/journeyRequestShowAll', [
            'title'           => 'Demandes de trajet',
            'journeyRequests' => $journeyRequests,
            'filterCityStart' => $filterCityStart,
            'filterCityEnd'   => $filterCityEnd,
            'filterDate'      => $filterDate,
        ]);
    }

    /**
     * Affiche le détail d'une demande de trajet.
     * GET /journey-requests/:id
     */
    public function show(int $id): string|RedirectResponse
    {
        $journeyRequest = $this->journeyRequestModel->find($id);

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

        // ====== Validation
        if (!$this->validate([
            'startDate'    => 'required|valid_date',
            'startTime'    => 'required|regex_match[/^([01]\d|2[0-3]):[0-5]\d$/]',
            'seats'        => 'permit_empty|integer|greater_than[0]|less_than_equal_to[8]',
            'message'      => 'permit_empty|max_length[2000]',
            'startAddress' => 'required|string|max_length[255]',
            'endAddress'   => 'required|string|max_length[255]',
            'startLat'     => 'required|decimal',
            'startLng'     => 'required|decimal',
            'endLat'       => 'required|decimal',
            'endLng'       => 'required|decimal',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // ====== Récupération des données POST
        $startDate = $this->request->getPost('startDate');
        $startTime = $this->request->getPost('startTime');
        [$h, $m]   = explode(':', $startTime);
        $startDatetime = (new \DateTimeImmutable($startDate))->setTime((int)$h, (int)$m, 0);

        // ====== Création des locations (city + location)
        $cityModel     = new \App\Models\CityModel();
        $locationModel = new \App\Models\LocationModel();

        $startCityId = $cityModel->findOrCreateCity(
            $this->request->getPost('startCity'),
            $this->request->getPost('startZipcode')
        );
        $startLocationId = $locationModel->insert([
            'latitude'  => (float) $this->request->getPost('startLat'),
            'longitude' => (float) $this->request->getPost('startLng'),
            'address'   => $this->request->getPost('startAddress'),
            'note'      => '',
            'city_id'   => $startCityId,
        ]);

        $endCityId = $cityModel->findOrCreateCity(
            $this->request->getPost('endCity'),
            $this->request->getPost('endZipcode')
        );
        $endLocationId = $locationModel->insert([
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
            'message'           => $this->request->getPost('message') ?: null,
            'user_id'           => $userId,
            'location_start_id' => $startLocationId,
            'location_end_id'   => $endLocationId,
        ];

        if (!$this->journeyRequestModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->journeyRequestModel->errors());
        }

        return redirect()->to('/journey-requests')->with('success', 'Demande publiée avec succès.');
    }

    /**
     * Affiche le formulaire de modification d'une demande de trajet.
     * GET /journey-requests/:id/edit
     */
    public function showEditForm(int $id): string|RedirectResponse
    {
        $journeyRequest = $this->journeyRequestModel->where('user_id', session()->get('user_id'))->find($id);

        if (!$journeyRequest) {
            return redirect()->to('/journey-requests')->with('error', self::NOT_FOUND);
        }

        return view('JourneyRequests/journeyRequestEdit', [
            'title'          => 'Modifier la demande',
            'journeyRequest' => $journeyRequest
        ]);
    }

    /**
     * Valide et enregistre les modifications d'une demande de trajet.
     * POST /journey-requests/:id/edit
     */
    public function update(int $id): RedirectResponse
    {
        $journeyRequest = $this->journeyRequestModel->where('user_id', session()->get('user_id'))->find($id);

        if (!$journeyRequest) {
            return redirect()->to('/journey-requests')->with('error', self::NOT_FOUND);
        }

        $data = [
            'start_datetime' => $this->request->getPost('start_datetime'),
            'seats'          => $this->request->getPost('seats'),
            'message'        => $this->request->getPost('message'),
        ];

        if (!$this->journeyRequestModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $this->journeyRequestModel->errors());
        }

        return redirect()->to('/journey-requests/' . $id)->with('success', 'Demande modifiée avec succès.');
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
}