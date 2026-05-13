<?php

namespace App\Controllers;

use App\Models\ReportModel;
use CodeIgniter\HTTP\RedirectResponse;

class ReportController extends BaseController
{
    /**
     * Affiche le formulaire de création d'un signalement.
     *
     * @return string
     */
    public function showCreateForm(): string
    {
        return view('report/add');
    }

    /**
     * Traite les données envoyées par le formulaire.
     * Gère la création d'un signalement et la redirection avec message de succès.
     *
     * @return RedirectResponse
     */
    public function create(): RedirectResponse
    {
        $reportModel = new ReportModel();
        $data = [
            'title'       => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'journey_id'  => $this->request->getPost('journey_id'),
            'user_id'     => session()->get('user_id'),
        ];

        if (!$reportModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $reportModel->errors());
        }

        return redirect()->to('/dashboard')->with('success', 'Signalement envoyé avec succès !');
    }

    /**
     * Affiche un signalement.
     *
     * @return string|RedirectResponse
     */
    public function show(int $id): string|RedirectResponse
    {
        $reportModel = new ReportModel();
        $report = $reportModel->where('user_id', session()->get('user_id'))->find($id);

        if (!$report) {
            return redirect()->to('/dashboard')->with('error', 'Signalement introuvable.');
        }

        return view('report/show', ['report' => $report]);
    }

    /**
     * Affiche la liste des signalements de l'utilisateur connecté.
     *
     * @return string
     */
    public function showAll(): string
    {
        $reportModel = new ReportModel();
        $data = [
            'reports' => $reportModel->where('user_id', session()->get('user_id'))->findAll()
        ];

        return view('report/index', $data);
    }

    /**
     * Gère la suppression d'un signalement et la redirection avec message de succès.
     *
     * @return RedirectResponse
     */
    public function delete(int $id): RedirectResponse
    {
        $reportModel = new ReportModel();
        $report = $reportModel->where('user_id', session()->get('user_id'))->find($id);

        if (!$report) {
            return redirect()->to('/dashboard')->with('error', 'Signalement introuvable.');
        }

        $reportModel->delete($id);
        return redirect()->to('/dashboard')->with('success', 'Signalement supprimé avec succès !');
    }
}