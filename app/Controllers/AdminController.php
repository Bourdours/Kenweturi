<?php

namespace App\Controllers;

use App\Libraries\MailerExample;
use App\Models\JourneyModel;
use App\Models\ReportModel;
use App\Models\UserModel;

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
     * Page principale d'administration : affiche l'onglet demandé
     * (inscriptions en attente, signalements, ou gestion des admins).
     */
    public function index()
    {
        // Récupère l'onglet actif depuis l'URL
        $tab             = $this->request->getGet('tab') ?? 'registrations';
        $reports         = [];
        $closedReports   = [];
        $pendingUsers    = [];
        $allUsers        = [];
        $superadminCount = 0;
        $adminCount      = 0;

        if ($tab === 'registrations') {
            // Charge les utilisateurs dont l'inscription est en attente de validation
            $pendingUsers = $this->userModel->getPendingUsersWithCity();
        } elseif ($tab === 'reports') {
            // Charge les signalements encore ouverts et les clôturés
            $reports       = $this->reportModel->getOpenReports();
            $closedReports = $this->reportModel->getClosedReports();
        } elseif ($tab === 'admins') {
            $allUsers        = $this->userModel->where('status', 'active')->findAll();
            $superadminCount = $this->userModel->where('status', 'active')->where('role', 'superadmin')->countAllResults();
            $adminCount      = $this->userModel->where('status', 'active')->where('role', 'admin')->countAllResults();
        }

        // Compteurs globaux pour les badges/onglets
        $nPendingUsers = $this->userModel->where('status', 'pending')->countAllResults();
        $nOpenReports  = $this->reportModel->where('status', 'open')->countAllResults();

        return view('Admin/index', [
            'title'           => 'Administration',
            'tab'             => $tab,
            'reports'         => $reports,
            'closedReports'   => $closedReports,
            'nOpenReports'    => $nOpenReports,
            'pendingUsers'    => $pendingUsers,
            'nPendingUsers'   => $nPendingUsers,
            'allUsers'        => $allUsers,
            'superadminCount' => $superadminCount,
            'adminCount'      => $adminCount,
        ]);
    }

    /**
     * Valide ou rejette l'inscription d'un utilisateur.
     * Met à jour son statut en base de données et lui envoie un email de notification.
     *
     * @param  int  $id  Identifiant de l'utilisateur à traiter
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function updateRegistration(int $id)
    {
        // Récupère et valide l'action soumise via le formulaire
        $action = $this->request->getPost('actionAdmin');
        if (! in_array($action, ['validate', 'reject'], true)) {
            return redirect()->to(site_url('admin?tab=registrations'))
                ->with('error', 'Action de validation invalide.');
        }

        // Vérifie que l'utilisateur ciblé existe bien en base
        $user = $this->userModel->find($id);
        if (! $user) {
            return redirect()->to(site_url('admin?tab=registrations'))
                ->with('error', 'Utilisateur introuvable.');
        }

        // Détermine le nouveau statut selon l'action choisie
        $newStatus = ($action === 'validate') ? 'active' : 'rejected';
        $this->userModel->update($id, ['status' => $newStatus]);

        // Prépare le sujet et le template d'email selon l'action
        $subject   = ($action === 'validate') ? 'Votre compte a été validé !' : 'Votre demande d’inscription a été refusée';
        $emailView = ($action === 'validate') ? 'Emails/adminApproved' : 'Emails/adminRejected';

        // Envoie l'email de notification à l'utilisateur
        $mailer = new MailerExample();
        $mailer->sendHtml(
            $user['email'],
            $subject,
            view($emailView, [
                'firstname' => $user['firstname'],
                'lastname'  => $user['lastname'],
            ])
        );

        $message = ($action === 'validate') ? 'L’utilisateur a été activé.' : 'L’utilisateur a été refusé.';
        return redirect()->to(site_url('admin?tab=registrations'))
            ->with('success', $message);
    }

    /**
     * Modifie le rôle d'un utilisateur.
     * Réservé aux super-administrateurs (protégé par le filtre superadmin).
     *
     * @param  int  $id  Identifiant de l'utilisateur cible
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function updateRole(int $id)
    {
        $newRole = $this->request->getPost('role');

        if (! in_array($newRole, ['user', 'admin'], true)) {
            return redirect()->to(site_url('admin?tab=admins'))
                ->with('error', 'Rôle invalide.');
        }

        $target = $this->userModel->find($id);
        if (! $target) {
            return redirect()->to(site_url('admin?tab=admins'))
                ->with('error', 'Utilisateur introuvable.');
        }

        if ($target['role'] === 'superadmin') {
            return redirect()->to(site_url('admin?tab=admins'))
                ->with('error', 'Impossible de modifier un super-administrateur.');
        }

        $this->userModel->update($id, [
            'role'     => $newRole,
            'is_admin' => $newRole === 'admin' ? 1 : 0,
        ]);

        $message = $newRole === 'admin'
            ? "{$target['firstname']} est maintenant administrateur."
            : "{$target['firstname']} a été rétrogradé en utilisateur.";

        return redirect()->to(site_url('admin?tab=admins'))->with('success', $message);
    }

    /**
     * Supprime un utilisateur (soft delete) et annule ses trajets en cours.
     *
     * Règles d'autorisation :
     * - Un admin peut supprimer un utilisateur simple, mais pas un admin ni un superadmin.
     * - Un superadmin peut supprimer n'importe qui sauf un autre superadmin.
     * - On ne peut pas se supprimer soi-même.
     * - Le dernier admin actif ne peut pas être supprimé.
     * - Le dernier superadmin actif ne peut pas être supprimé.
     *
     * @param  int  $id  Identifiant de l'utilisateur à supprimer
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function deleteUser(int $id)
    {
        $target      = $this->userModel->find($id);
        $currentRole = session()->get('role');

        if (! $target) {
            return redirect()->to(site_url('admin?tab=admins'))
                ->with('error', 'Utilisateur introuvable.');
        }

        // 1. Pas d'auto-suppression
        if ($id === (int) session()->get('user_id')) {
            return redirect()->to(site_url('admin?tab=admins'))
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        // 2. Personne ne peut supprimer un super-administrateur
        if ($target['role'] === 'superadmin') {
            return redirect()->to(site_url('admin?tab=admins'))
                ->with('error', 'Impossible de supprimer un super-administrateur.');
        }

        // 3. Un admin simple ne peut pas supprimer un autre admin
        //    (seul un superadmin peut supprimer un admin)
        if ($target['role'] === 'admin' && $currentRole !== 'superadmin') {
            return redirect()->to(site_url('admin?tab=admins'))
                ->with('error', 'Seul un super-administrateur peut supprimer un administrateur.');
        }

        // 4. Garde-fou : on n'autorise pas la suppression du dernier administrateur
        //    (au moins un admin doit rester en plus du superadmin)
        if ($target['role'] === 'admin') {
            $adminCount = $this->userModel
                ->where('status', 'active')
                ->where('role', 'admin')
                ->countAllResults();

            if ($adminCount <= 1) {
                return redirect()->to(site_url('admin?tab=admins'))
                    ->with('error', 'Impossible de supprimer le dernier administrateur.');
            }
        }

        // Soft delete (remplit deleted_at)
        $this->userModel->delete($id);

        // Annulation de tous ses trajets actifs
        $journeyModel = new JourneyModel();
        $journeyModel->where('user_id', $id)
            ->where('canceled_at', null)
            ->set(['canceled_at' => date('Y-m-d H:i:s')])
            ->update();

        // Notification email
        $mailer = new MailerExample();
        $mailer->sendHtml(
            $target['email'],
            'Votre compte a été supprimé',
            view('Emails/adminDeletedAccount', [
                'firstname' => $target['firstname'],
                'lastname'  => $target['lastname'],
            ])
        );

        return redirect()->to(site_url('admin?tab=admins'))
            ->with('success', "{$target['firstname']} a été supprimé et ses trajets annulés.");
    }

    /**
     * Affiche le détail d'un signalement.
     */
    public function showReport(int $id)
    {
        $report = $this->reportModel->getReportDetail($id);
        if (! $report) {
            return redirect()->to(site_url('admin?tab=reports'))
                ->with('error', 'Signalement introuvable.');
        }

        return view('Admin/report_show', [
            'title'  => 'Signalement #' . $id,
            'report' => $report,
        ]);
    }

    /**
     * Traite un signalement ouvert en appliquant une action administrative :
     * - 'warn'  : envoie un avertissement par email à l'utilisateur signalé
     * - 'ban'   : bannit l'utilisateur signalé
     * - 'close' : clôture le signalement sans action supplémentaire
     *
     * @param  int  $id  Identifiant du signalement à résoudre
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function resolveReport(int $id)
    {
        // Récupère l'action choisie et le commentaire administrateur saisi dans le formulaire
        $action  = $this->request->getPost('actionAdmin');
        $comment = trim($this->request->getPost('commentAdmin') ?? '');

        // Valide que l'action est autorisée et qu'un commentaire a bien été fourni
        if (! in_array($action, ['warn', 'ban', 'close'], true) || empty($comment)) {
            return redirect()->to(site_url('admin?tab=reports'))
                ->with('error', 'Action ou commentaire invalide.');
        }

        // Récupère le signalement ainsi que les informations du propriétaire du trajet associé
        $report = $this->reportModel->getWithJourneyOwner($id);
        if (! $report) {
            return redirect()->to(site_url('admin?tab=reports'))
                ->with('error', 'Signalement introuvable.');
        }

        $reportedUserId = $report['reported_user_id'];
        $reportedUser   = $this->userModel->find($reportedUserId);

        if ($action === 'ban' && $reportedUser) {
            $this->userModel->ban($reportedUserId);
            $mailer = new MailerExample();
            $mailer->sendHtml(
                $reportedUser['email'],
                'Votre compte a été suspendu',
                view('Emails/accountBanned', [
                    'firstname' => $reportedUser['firstname'],
                    'lastname'  => $reportedUser['lastname'],
                ])
            );
        } elseif ($action === 'warn' && $reportedUser) {
            // Envoi d'un email d'avertissement à l'utilisateur signalé avec le commentaire admin
            $mailer = new MailerExample();
            $mailer->sendHtml(
                $reportedUser['email'],
                'Avertissement - Kenweturi',
                $this->renderWarnEmail($reportedUser['firstname'], $reportedUser['lastname'], $comment)
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
    private function renderWarnEmail(string $firstname, string $lastname, string $comment): string
    {
        return view('Emails/adminWarn', [
            'firstname' => $firstname,
            'lastname'  => $lastname,
            'comment'   => $comment,
        ]);
    }
}