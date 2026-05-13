<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\JourneyRequestModel;
use CodeIgniter\HTTP\RedirectResponse;

class JourneyRequestController extends BaseController
{
    private const NOT_FOUND = 'Demande introuvable.';

    /**
     * Affiche la liste des demandes de trajet.
     *
     * @return string
     */
    public function showAll(): string
    {
        $journeyRequestModel = new JourneyRequestModel();
        $data = [
            'title'           => 'Demandes de trajet',
            'journeyRequests' => $journeyRequestModel->findAll()
        ];

        return view('JourneyRequests/journeyRequestShowAll', $data);
    }

    /**
     * Affiche le détail d'une demande de trajet.
     *
     * @return string|RedirectResponse
     */
    public function show(int $id): string|RedirectResponse
    {
        $journeyRequestModel = new JourneyRequestModel();
        $journeyRequest = $journeyRequestModel->find($id);

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
     *
     * @return string
     */
    public function showCreateForm(): string
    {
        return view('JourneyRequests/journeyRequestAdd', [
            'title' => 'Publier une demande de trajet'
        ]);
    }

    /**
     * Traite les données envoyées par le formulaire.
     *
     * @return RedirectResponse
     */
    public function create(): RedirectResponse
    {
        $journeyRequestModel = new JourneyRequestModel();
        $data = [
            'start_datetime'    => $this->request->getPost('start_datetime'),
            'seats'             => $this->request->getPost('seats'),
            'message'           => $this->request->getPost('message'),
            'user_id'           => session()->get('user_id'),
            'location_start_id' => $this->request->getPost('location_start_id'),
            'location_end_id'   => $this->request->getPost('location_end_id'),
        ];

        if (!$journeyRequestModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $journeyRequestModel->errors());
        }

        return redirect()->to('/journey-requests')->with('success', 'Demande publiée avec succès.');
    }

    /**
     * Affiche le formulaire de modification d'une demande de trajet.
     *
     * @return string|RedirectResponse
     */
    public function showEditForm(int $id): string|RedirectResponse
    {
        $journeyRequestModel = new JourneyRequestModel();
        $journeyRequest = $journeyRequestModel->where('user_id', session()->get('user_id'))->find($id);

        if (!$journeyRequest) {
            return redirect()->to('/journey-requests')->with('error', self::NOT_FOUND);
        }

        return view('JourneyRequests/journeyRequestEdit', [
            'title'          => 'Modifier la demande',
            'journeyRequest' => $journeyRequest
        ]);
    }

    /**
     * Gère la modification d'une demande de trajet.
     *
     * @return RedirectResponse
     */
    public function update(int $id): RedirectResponse
    {
        $journeyRequestModel = new JourneyRequestModel();
        $journeyRequest = $journeyRequestModel->where('user_id', session()->get('user_id'))->find($id);

        if (!$journeyRequest) {
            return redirect()->to('/journey-requests')->with('error', self::NOT_FOUND);
        }

        $data = [
            'start_datetime' => $this->request->getPost('start_datetime'),
            'seats'          => $this->request->getPost('seats'),
            'message'        => $this->request->getPost('message'),
        ];

        if (!$journeyRequestModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $journeyRequestModel->errors());
        }

        return redirect()->to('/journey-requests/' . $id)->with('success', 'Demande modifiée avec succès.');
    }

    /**
     * Supprime une demande de trajet.
     *
     * @return RedirectResponse
     */
    public function delete(int $id): RedirectResponse
    {
        $journeyRequestModel = new JourneyRequestModel();
        $journeyRequest = $journeyRequestModel->where('user_id', session()->get('user_id'))->find($id);

        if (!$journeyRequest) {
            return redirect()->to('/journey-requests')->with('error', self::NOT_FOUND);
        }

        $journeyRequestModel->delete($id);

        return redirect()->to('/journey-requests')->with('success', 'Demande annulée avec succès.');
    }
}