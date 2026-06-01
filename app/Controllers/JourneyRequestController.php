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

    public function showAll(): string
    {
        return view('JourneyRequests/journeyRequestShowAll', [
            'title'           => 'Demandes de trajet',
            'journeyRequests' => $this->journeyRequestModel->findAll()
        ]);
    }

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

    public function showCreateForm(): string
    {
        return view('JourneyRequests/journeyRequestAdd', [
            'title' => 'Publier une demande de trajet'
        ]);
    }

    public function create(): RedirectResponse
    {
        $data = [
            'start_datetime'    => $this->request->getPost('start_datetime'),
            'seats'             => $this->request->getPost('seats'),
            'message'           => $this->request->getPost('message'),
            'user_id'           => session()->get('user_id'),
            'location_start_id' => $this->request->getPost('location_start_id'),
            'location_end_id'   => $this->request->getPost('location_end_id'),
        ];

        if (!$this->journeyRequestModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->journeyRequestModel->errors());
        }

        return redirect()->to('/journey-requests')->with('success', 'Demande publiée avec succès.');
    }

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