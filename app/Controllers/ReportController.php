<?php

namespace App\Controllers;

use App\Models\ReportModel;
use App\Models\JourneyModel;
use App\Models\UserModel;
use App\Models\BookingModel;
use App\Models\NotificationPrefModel;
use App\Libraries\MailerExample;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Services;

/**
 * Contrôleur gérant les signalements de trajets.
 * Permet à un utilisateur connecté de signaler un trajet problématique.
 */
class ReportController extends BaseController
{
    protected ReportModel $reportModel;
    protected JourneyModel $journeyModel;
    protected BookingModel $bookingModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->reportModel  = new ReportModel();
        $this->journeyModel = new JourneyModel();
        $this->bookingModel = new BookingModel();
        $this->userModel    = new UserModel();
    }

    /**
     * Traite la soumission d'un signalement pour un trajet donné.
     *
     * @param int $journeyId L'id du trajet à signaler
     */
    public function create(int $journeyId)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $reporterId = (int) session()->get('user_id');

        $journey = $this->journeyModel->find($journeyId);
        if (!$journey) {
            throw new PageNotFoundException("Trajet introuvable.");
        }

        if ($this->reportModel->alreadyReported($reporterId, $journeyId)) {
            return redirect()->back()
                ->with('error', 'Vous avez déjà signalé ce trajet.');
        }

        $isDriver = $reporterId === (int) $journey['user_id'];

        $hasBooked = $this->bookingModel
            ->join('journey', 'journey.id = booking.journey_id')
            ->where('booking.journey_id', $journeyId)
            ->where('booking.user_id', $reporterId)
            ->where('journey.start_datetime <', date('Y-m-d H:i:s'))
            ->countAllResults() > 0;

        if (!$isDriver && !$hasBooked) {
            return redirect()->back()
                ->with('error', 'Vous devez avoir effectué ce trajet pour le signaler.');
        }

        $reportedUserId = (int) $this->request->getPost('reportedUserId');
        if ($reportedUserId === $reporterId) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Vous ne pouvez pas vous signaler vous-même.');
        }

        // Vérifie que l'utilisateur signalé est bien un participant du trajet
        $driver     = $this->userModel->find((int) $journey['user_id']);
        $passengers = $this->bookingModel->findPassengersByJourney($journeyId);

        $validIds = [];
        if ($driver) {
            $validIds[] = (int) $driver['id'];
        }
        foreach ($passengers as $p) {
            $validIds[] = (int) $p['user_id'];
        }
        $validIds = array_diff($validIds, [$reporterId]);

        if (!in_array($reportedUserId, $validIds, true)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Utilisateur signalé invalide.');
        }

        $data = [
            'title'            => $this->request->getPost('titleReport'),
            'description'      => $this->request->getPost('reasonReport'),
            'journey_id'       => $journeyId,
            'user_id'          => $reporterId,
            'reported_user_id' => $reportedUserId,
        ];

        if (!$this->reportModel->insert($data)) {
            return redirect()->back()
                ->withInput()
                ->with('validationErrors', $this->reportModel->errors());
        }

        $this->notifyAdmin($journey, $data['description'], $reporterId);

        return redirect()->back()
            ->with('success', 'Signalement envoyé. Notre équipe le traitera sous 48h.');
    }

    /**
     * Affiche le formulaire de signalement
     */
    public function showCreateForm(int $journeyId): string|RedirectResponse
    {
        $journey = $this->journeyModel->find($journeyId);

        if (!$journey) {
            return redirect()->to('/journeys')->with('error', 'Trajet introuvable.');
        }

        $currentUserId = (int) session()->get('user_id');

        // Conducteur
        $driver = $this->userModel->find((int) $journey['user_id']);

        // Passagers acceptés (hors utilisateur courant)
        $passengers = $this->bookingModel->findPassengersByJourney($journeyId);

        // Liste des utilisateurs signalables (conducteur + passagers, hors soi-même)
        $reportableUsers = [];

        if ($driver && (int) $driver['id'] !== $currentUserId) {
            $reportableUsers[] = [
                'id'        => $driver['id'],
                'firstname' => $driver['firstname'],
                'lastname'  => $driver['lastname'],
                'role'      => 'Conducteur',
            ];
        }

        foreach ($passengers as $p) {
            if ((int) $p['user_id'] !== $currentUserId) {
                $reportableUsers[] = [
                    'id'        => $p['user_id'],
                    'firstname' => $p['firstname'],
                    'lastname'  => $p['lastname'],
                    'role'      => 'Passager',
                ];
            }
        }

        return view('Reports/reportShow', [
            'title'           => 'Signaler un trajet',
            'journey'         => $journey,
            'reportableUsers' => $reportableUsers,
        ]);
    }

    private function notifyAdmin(array $journey, string $description, int $reporterId): void
    {
        $notifPrefModel = new NotificationPrefModel();
        $admins         = $this->userModel->getActiveAdmins();
        $mailer         = new MailerExample();

        foreach ($admins as $admin) {
            if (!$notifPrefModel->wantsNotif((int) $admin['id'], 'admin_report')) continue;
            $adminId = (int) $admin['id'];
            $mailer->sendHtml(
                $admin['email'],
                '[Signalement] Nouveau signalement à traiter',
                view('Emails/adminNewReport', [
                    'reporterId'     => $reporterId,
                    'journeyId'      => $journey['id'],
                    'description'    => $description,
                    'prefLabel'      => NotificationPrefModel::PREFS['admin_report'],
                    'unsubscribeUrl' => site_url('unsubscribe?uid=' . $adminId . '&pref=admin_report&token=' . UserModel::unsubscribeToken($adminId, 'admin_report')),
                    'preferencesUrl' => site_url('profile/notifications'),
                ])
            );
        }
    }
}
