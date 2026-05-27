<?php

namespace App\Controllers;

use App\Models\ReportModel;
use App\Models\UserModel;
use App\Libraries\MailerExample;

class AdminController extends BaseController
{
    private ReportModel $reportModel;
    private UserModel   $userModel;

    public function __construct()
    {
        $this->reportModel = new ReportModel();
        $this->userModel   = new UserModel();
    }

    /**
     * Vérifie que l'utilisateur connecté est bien un administrateur.
     * Si ce n'est pas le cas, redirige vers la page d'accueil avec un message d'erreur.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|null  Redirection si non admin, null sinon
     */
    private function requireAdmin()
    {
        if (!session()->get('isAdmin')) {
            return redirect()->to(site_url('/'))
                ->with('error', 'Accès réservé aux administrateurs.');
        }
        return null;
    }

    public function index()
    {
        // Vérifie les droits administrateur avant tout traitement
        if ($response = $this->requireAdmin()) {
            return $response;
        }

        // Récupère l'onglet actif depuis l'URL
        $tab = $this->request->getGet('tab') ?? 'registrations';
        $reports      = [];
        $pendingUsers = [];

        if ($tab === 'registrations') {
            // Charge les utilisateurs dont l'inscription est en attente de validation
            $pendingUsers = $this->userModel->getPendingUsersWithCity();
        } elseif ($tab === 'reports') {
            // Charge les signalements encore ouverts
            $reports = $this->reportModel->getOpenReports();
        }

        // Compte le nombre total d'inscriptions en attente
        $nPendingUsers = $this->userModel->where('status', 'pending')->countAllResults();

        return view('Admin/index', [
            'title'         => 'Administration',
            'tab'           => $tab,
            'reports'       => $reports,
            'pendingUsers'  => $pendingUsers,
            'nPendingUsers' => $nPendingUsers,
        ]);
    }


    /**
     * Valide ou rejette l'inscription d'un utilisateur.
     * Met à jour son statut en base de données et lui envoie un email de notification.
     *
     * @param  int  $id  Identifiant de l'utilisateur à traiter
     * @return \CodeIgniter\HTTP\Response
     */
    public function updateRegistration(int $id)
    {
        // Vérifie les droits administrateur avant tout traitement
        if ($response = $this->requireAdmin()) {
            return $response;
        }

        // Récupère et valide l'action soumise via le formulaire 
        $action = $this->request->getPost('actionAdmin');
        if (!in_array($action, ['validate', 'reject'], true)) {
            return redirect()->to(site_url('admin?tab=registrations'))
                ->with('error', 'Action de validation invalide.');
        }

        // Vérifie que l'utilisateur ciblé existe bien en base
        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to(site_url('admin?tab=registrations'))
                ->with('error', 'Utilisateur introuvable.');
        }

        // Détermine le nouveau statut selon l'action choisie
        $newStatus = ($action === 'validate') ? 'active' : 'rejected';
        $this->userModel->update($id, ['status' => $newStatus]);

        // Prépare le sujet et le template d'email selon l'action
        $subject = ($action === 'validate') ? 'Votre compte a été validé !' : 'Votre demande d’inscription a été refusée';
        $emailView = ($action === 'validate') ? 'Emails/adminApproved' : 'Emails/adminRejected';

        // Envoie l'email de notification à l'utilisateur
        $mailer = new MailerExample();
        $mailer->sendHtml(
            $user['email'],
            $subject,
            view($emailView, [
                'firstname' => $user['firstname'],
                'lastname'  => $user['lastname']
            ])
        );

        $message = ($action === 'validate') ? 'L’utilisateur a été activé.' : 'L’utilisateur a été refusé.';
        return redirect()->to(site_url('admin?tab=registrations'))
            ->with('success', $message);
    }

    /**
     * Traite un signalement ouvert en appliquant une action administrative :
     * - 'warn'  : envoie un avertissement par email à l'utilisateur signalé
     * - 'ban'   : bannit l'utilisateur signalé
     * - 'close' : clôture le signalement sans action supplémentaire
     *
     * @param  int  $id  Identifiant du signalement à résoudre
     * @return \CodeIgniter\HTTP\Response
     */
    public function resolveReport(int $id)
    {
        // Vérifie les droits administrateur avant tout traitement
        if ($response = $this->requireAdmin()) {
            return $response;
        }

        // Récupère l'action choisie et le commentaire administrateur saisi dans le formulaire
        $action  = $this->request->getPost('actionAdmin');
        $comment = trim($this->request->getPost('commentAdmin') ?? '');

        // Valide que l'action est autorisée et qu'un commentaire a bien été fourni
        if (!in_array($action, ['warn', 'ban', 'close'], true) || empty($comment)) {
            return redirect()->to(site_url('admin?tab=reports'))
                ->with('error', 'Action ou commentaire invalide.');
        }

        // Récupère le signalement ainsi que les informations du propriétaire du trajet associé
        $report = $this->reportModel->getWithJourneyOwner($id);
        if (!$report) {
            return redirect()->to(site_url('admin?tab=reports'))
                ->with('error', 'Signalement introuvable.');
        }

        $reportedUserId = $report['reported_user_id'];
        $reportedUser   = $this->userModel->find($reportedUserId);

        if ($action === 'ban') {
            // Bannissement
            $this->userModel->ban($reportedUserId);
        } elseif ($action === 'warn' && $reportedUser) {
            // Envoi d'un email d'avertissement à l'utilisateur signalé avec le commentaire admin
            $mailer = new MailerExample();
            $mailer->sendHtml(
                $reportedUser['email'],
                'Avertissement - Kenweturi',
                $this->buildEmailHtml($reportedUser['firstname'], $reportedUser['lastname'], $comment)
            );
        }

        $this->reportModel->resolve($id, $action, $comment, session()->get('user_id'));
        return redirect()->to(site_url('admin?tab=reports'))
            ->with('success', 'Signalement traité avec succès.');
    }


    /**
     * Génère le contenu HTML de l'email d'avertissement envoyé à un utilisateur signalé.
     *
     * @param  string  $firstname  Prénom de l'utilisateur averti
     * @param  string  $lastname   Nom de l'utilisateur averti
     * @param  string  $comment    Commentaire de l'administrateur expliquant l'avertissement
     * @return string              HTML de l'email généré
     */
    private function buildEmailHtml(string $firstname, string $lastname, string $comment): string
    {
        return view('Auth/Emails/adminWarn', [
            'firstname' => $firstname,
            'lastname'  => $lastname,
            'comment'   => $comment,
        ]);
    }
}
