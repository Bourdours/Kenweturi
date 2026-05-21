<?php

namespace App\Controllers;

use App\Models\ReportModel;
use App\Models\JourneyModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Services;

/**
 * Contrôleur gérant les signalements de trajets.
 * Permet à un utilisateur connecté de signaler un trajet problématique.
 */
class ReportController extends BaseController
{

    /**
     * Traite la soumission d'un signalement pour un trajet donné.
     *
     * @param int $journeyId L'id du trajet à signaler
     */
    public function create(int $journeyId)
    {
        // Vérification de la session
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }


        $reporterId = (int) session()->get('user_id');

        $reportModel  = new ReportModel();
        $journeyModel = new JourneyModel();

        // Vérification que le trajet existe
        $journey = $journeyModel->find($journeyId);
        if (!$journey) {
            throw new PageNotFoundException("Trajet introuvable.");
        }

        $journeyOwnerId = (int) $journey['user_id'];

        // Un utilisateur ne peut pas signaler son propre trajet
        if ($reporterId === $journeyOwnerId) {
            return redirect()->back()
                ->with('error', 'Vous ne pouvez pas signaler votre propre trajet.');
        }

        // Anti-doublon 
        if ($reportModel->alreadyReported($reporterId, $journeyId)) {
            return redirect()->back()
                ->with('error', 'Vous avez déjà signalé ce trajet.');
        }

        // Validation et insertion via le model
        $data = [
            'title'       => $this->request->getPost('titleReport'),
            'description' => $this->request->getPost('reasonReport'),
            'journey_id'  => $journeyId,
            'user_id'     => $reporterId,
        ];

        if (!$reportModel->insert($data)) {
            return redirect()->back()
                ->withInput()
                ->with('validationErrors', $reportModel->errors());
        }

        // Notification admin par email
        $this->notifyAdmin($journey, $data['description'], $reporterId);

        return redirect()->back()
            ->with('success', 'Signalement envoyé. Notre équipe le traitera sous 48h.');
    }

    /**
     * Envoie un email de notification à l'administrateur
     * lors d'un nouveau signalement.
     *
     * @param array  $journey     Les données du trajet signalé
     * @param string $description La description du signalement
     * @param int    $reporterId  L'id de l'utilisateur qui signale
     */
    private function notifyAdmin(array $journey, string $description, int $reporterId): void
    {
        $email = Services::email();

        $email->setTo(getenv('ADMIN_EMAIL') ?: 'admin@exemple.com');
        $email->setSubject('[Signalement] Nouveau signalement à traiter');
        $email->setMessage("
            Un nouveau signalement a été soumis.<br><br>
            <strong>Signalé par (user_id) :</strong> {$reporterId}<br>
            <strong>Trajet signalé (journey_id) :</strong> {$journey['id']}<br>
            <strong>Propriétaire du trajet (user_id) :</strong> {$journey['user_id']}<br>
            <strong>Description :</strong> " . nl2br(esc($description)) . "<br><br>
            À traiter sous 48h dans l'interface d'administration.
        ");

        $email->send();
    }
}
