<?php

namespace App\Controllers;

use App\Libraries\MailerExample;
use App\Models\JourneyModel;
use App\Models\ReportModel;
use App\Models\UserModel;
use App\Models\LocationModel;
use App\Models\CityModel;
use App\Services\GeocodingService;

use App\Services\UserService;

use App\Exceptions\UserNotFoundException;
use App\Exceptions\CannotDeleteSelfException;
use App\Exceptions\CannotDeleteSuperadminException;
use App\Exceptions\AdminDeletionForbiddenException;
use App\Exceptions\LastAdminException;

use CodeIgniter\HTTP\RedirectResponse;

class AdminController extends BaseController
{
    private ReportModel       $reportModel;
    private UserModel         $userModel;
    private LocationModel     $locationModel;
    private CityModel         $cityModel;
    private GeocodingService  $geocodingService;
    private UserService       $userService;

    public function __construct()
    {
        $this->reportModel      = new ReportModel();
        $this->userModel        = new UserModel();
        $this->locationModel    = new LocationModel();
        $this->cityModel        = new CityModel();
        $this->geocodingService = new GeocodingService();
        $this->userService      = new UserService();
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
        $favorite        = null;

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
        } elseif ($tab === 'settings') {
            $favorite = $this->locationModel->getFavorite();
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
            'favorite'        => $favorite,
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

        if (!$user) {
            return redirect()->to(site_url('admin?tab=registrations'))
                ->with('error', 'Utilisateur introuvable.');
        }

        if ($user['status'] !== 'pending') {
            return redirect()->to(site_url('admin?tab=registrations'))
                ->with('error', 'Action non autorisée.');
        }

        if ($user['role'] !== 'user') {
            return redirect()->back()->with('error', 'Vous ne pouvez supprimer que les utilisateurs ayant le rôle "Utilisateur"');
        }

        // Détermine le nouveau statut selon l'action choisie
        $newStatus  = ($action === 'validate') ? 'active' : 'rejected';
        $updateData = ['status' => $newStatus];

        if ($action === 'validate') {
            $updateData['is_student'] = (int) $this->request->getPost('is_student');
        }

        $this->userModel->update($id, $updateData);

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
     * Modifie le statut étudiant/formateur d'un utilisateur.
     *
     * @param  int  $id  Identifiant de l'utilisateur cible
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function updateStudent(int $id)
    {
        $isStudent = (int) $this->request->getPost('is_student');

        if (! in_array($isStudent, [0, 1], true)) {
            return redirect()->to(site_url('admin?tab=admins'))
                ->with('error', 'Valeur invalide.');
        }

        $target = $this->userModel->find($id);
        if (! $target) {
            return redirect()->to(site_url('admin?tab=admins'))
                ->with('error', 'Utilisateur introuvable.');
        }

        $this->userModel->update($id, ['is_student' => $isStudent]);

        $label = $isStudent ? 'étudiant' : 'formateur';
        return redirect()->to(site_url('admin?tab=admins'))
            ->with('success', "{$target['firstname']} est maintenant {$label}.");
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
    public function deleteUser(int $id): RedirectResponse
    {
        $currentUserId = (int) session()->get('user_id');
        $currentRole   = (string) session()->get('role');

        try {

            $contact = $this->userService->deleteByAdmin($id, $currentUserId, $currentRole);

        } catch (UserNotFoundException) {

            return redirect()->to(site_url('admin?tab=admins'))
                ->with('error', 'Utilisateur introuvable.');

        } catch (CannotDeleteSelfException) {

            return redirect()->to(site_url('admin?tab=admins'))
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');

        } catch (CannotDeleteSuperadminException) {

            return redirect()->to(site_url('admin?tab=admins'))
                ->with('error', 'Impossible de supprimer un super-administrateur.');

        } catch (AdminDeletionForbiddenException) {

            return redirect()->to(site_url('admin?tab=admins'))
                ->with('error', 'Seul un super-administrateur peut supprimer un administrateur.');

        } catch (LastAdminException) {

            return redirect()->to(site_url('admin?tab=admins'))
                ->with('error', 'Impossible de supprimer le dernier administrateur.');

        } catch (\Throwable $e) {

            log_message('error', 'Deletion failed for user n°{id}', ['id' => $id]);
            return redirect()->to(site_url('admin?tab=admins'))
                ->with('error', 'Un problème est survenu.');

        }

        // Email à part : un échec d'envoi ne doit pas annuler la suppression (comme accept)
        try {

            $this->userService->notifyAdminDeletion($contact);

        } catch (\Throwable) {

            log_message('error', 'User deletion mail failed for user {id}', ['id' => $id]);

        }

        return redirect()->to(site_url('admin?tab=admins'))
            ->with('success', "{$contact['firstname']} a été supprimé et ses trajets annulés.");
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

    /**
     * POST /admin/settings
     * Traitement : géocode l'adresse saisie, crée la location et la définit comme favorite
     */
    public function updateSettings()
    {
        $address = trim($this->request->getPost('favoriteAddress') ?? '');

        if (empty($address)) {
            return redirect()->back()->withInput()
                ->with('errors', ['favoriteAddress' => 'L\'adresse est obligatoire.']);
        }

        $data = $this->geocodingService->getLocationData($address);

        if ($data === null) {
            return redirect()->back()->withInput()
                ->with('errors', ['favoriteAddress' => "Adresse introuvable : $address"]);
        }

        if (empty($data['street']) && empty($data['locality'])) {
            return redirect()->back()->withInput()
                ->with('errors', ['favoriteAddress' => "L'adresse \"$address\" doit contenir un nom de rue."]);
        }

        $cityId = $this->cityModel->findOrCreateCity($data['city'], $data['postcode']);

        $locationId = $this->locationModel->insert([
            'latitude'  => $data['latitude'],
            'longitude' => $data['longitude'],
            'address'   => $data['name'],
            'city_id'   => $cityId,
        ]);

        if ($locationId === false) {
            return redirect()->back()->withInput()
                ->with('errors', $this->locationModel->errors());
        }

        $this->locationModel->setFavorite($locationId);

        return redirect()->to(site_url('admin?tab=settings'))->with('success', 'Adresse favorite mise à jour.');
    }

    /**
     * POST /admin/settings/clear
     * Traitement : retire l'adresse favorite actuelle (désactive le flag, ne supprime pas la location)
     */
    public function clearSettings()
    {
        $this->locationModel->clearFavorite();

        return redirect()->to(site_url('admin?tab=settings'))->with('success', 'Adresse favorite retirée.');
    }
}
