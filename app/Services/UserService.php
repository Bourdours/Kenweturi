<?php

namespace App\Services;

use App\Libraries\MailerExample;

use App\Models\UserModel;
use App\Models\RememberTokenModel;

use App\Services\BookingService;
use App\Services\JourneyService;

use App\Exceptions\UserNotFoundException;
use App\Exceptions\CannotDeleteSelfException;
use App\Exceptions\CannotDeleteSuperadminException;
use App\Exceptions\AdminDeletionForbiddenException;
use App\Exceptions\LastAdminException;
use App\Exceptions\InvalidPasswordException;

use CodeIgniter\I18n\Time;

use Config\Database;

class UserService
{
    protected UserModel      $userModel;
    protected RememberTokenModel $rememberTokenModel;
    protected BookingService $bookingService;
    protected JourneyService $journeyService;

    public function __construct()
    {
        $this->userModel            = new UserModel();
        $this->rememberTokenModel   = new RememberTokenModel();
        $this->bookingService       = new BookingService();
        $this->journeyService       = new JourneyService();
    }

    /**
     * Suppression d'un utilisateur par un administrateur.
     *
     * @return array{email:string, firstname:string, lastname:string}
     *
     * @throws UserNotFoundException
     * @throws CannotDeleteSelfException
     * @throws CannotDeleteSuperadminException
     * @throws AdminDeletionForbiddenException
     * @throws LastAdminException
     */
    public function deleteByAdmin(int $targetId, int $currentUserId, string $currentRole): array
    {
        $target = $this->userModel->find($targetId);
        if ($target === null) {
            throw new UserNotFoundException();
        }

        $this->ensureAdminDeletionAllowed($target, $targetId, $currentUserId, $currentRole);

        return $this->performDeletion($targetId, $target);
    }

    /**
     * Suppression par l'utilisateur de son propre compte.
     * La seule règle d'autorisation est la vérification du mot de passe.
     *
     * @return array{email:string, firstname:string, lastname:string}
     *
     * @throws UserNotFoundException
     * @throws InvalidPasswordException
     */
    public function deleteOwnAccount(int $userId, string $password): array
    {
        $target = $this->userModel->find($userId);
        if ($target === null) {
            throw new UserNotFoundException();
        }

        $this->ensureSelfDeletionAllowed($target, $password);

        return $this->performDeletion($userId, $target);
    }

    /**
     * Logique commune de suppression.
     *
     * Les données de notification sont capturées AVANT toute écriture. Les
     * écritures sont regroupées dans une transaction (tout ou rien). Les emails
     * ne sont envoyés qu'APRÈS un commit réussi.
     *
     * @return array{email:string, firstname:string, lastname:string}
     *         Coordonnées capturées avant anonymisation.
     */
    private function performDeletion(int $targetId, array $target): array
    {
        // Coordonnées du compte supprimé (capturées avant anonymisation)
        $targetContact = [
            'email'     => $target['email'],
            'firstname' => $target['firstname'],
            'lastname'  => $target['lastname'],
        ];

        // Chemin de l'avatar capturé AVANT anonymisation (anonymize() le passe à null)
        $avatarPath = $target['avatar'] ?? null;

        // Capture des informations de notification avant suppression
        $journeys                   = $this->journeyService->getActiveJourneysWithCities($targetId);
        $journeyIds                 = array_column($journeys, 'id');
        // Drivers à prévenir (le user était passager accepté)
        $driverNotificationsData    = $this->bookingService->getActivePassengerBookings($targetId);
        // Passagers acceptés à prévenir (le user était conducteur)
        $passengerNotificationsData = $this->journeyService->getCancellationNotifications($journeyIds);

        // ============ Écritures en base, rollback si erreur ============

        $db = Database::connect();
        $db->transBegin();

        try {
            // Réservations du user (passager)
            $this->bookingService->rejectAllByPassenger($targetId);

            // Trajets du user (conducteur) + réservations de ses passagers
            $this->bookingService->rejectAllForJourneys($journeyIds);
            $this->journeyService->cancelAllByDriver($targetId);

            // Invalidation des tokens « se souvenir de moi » (reconnexion auto impossible)
            $this->rememberTokenModel->deleteAll($targetId);

            // Anonymisation et soft delete du user
            $this->userModel->anonymize($targetId);
            $this->userModel->markAsDeleted($targetId); // Update du status
            // Suppression de la photo de profil : la référence en base passe à null via anonymize() ci-dessus.
            // Le fichier physique est supprimé APRÈS commit (voir deleteAvatarFile plus bas), car unlink() n'est pas transactionnel.
            $this->userModel->delete($targetId); //Renseigne simplement la date du champs deleted_at, car useSoftDelete=true dans UserModel

            if ($db->transStatus() === false) {
                throw new \RuntimeException("Échec SQL lors de la suppression du compte #{$targetId}");
            }

            if ($db->transCommit() === false) {  // ← commit exécuté ICI, par ce test
                throw new \RuntimeException("Échec du commit lors de la suppression du compte #{$targetId}");
            }
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }

        // ============ Effets de bord après commit (best-effort) ============

        $this->deleteAvatarFile($avatarPath); // Suppression du fichier physique de l'avatar
        $this->bookingService->notifyDriverCancellations($driverNotificationsData);
        $this->journeyService->notifyPassengersJourneyCancelled($passengerNotificationsData);

        return $targetContact;
    }

    /**
     * Règles d'autorisation pour une suppression effectuée par un administrateur.
     */
    private function ensureAdminDeletionAllowed(array $target, int $targetId, int $currentUserId, string $currentRole): void
    {
        // Pas d'auto-suppression
        if ($targetId === $currentUserId) {
            throw new CannotDeleteSelfException();
        }

        // Personne ne peut supprimer un super-administrateur
        if ($target['role'] === 'superadmin') {
            throw new CannotDeleteSuperadminException();
        }

        // Un admin simple ne peut pas supprimer un autre admin
        if ($target['role'] === 'admin' && $currentRole !== 'superadmin') {
            throw new AdminDeletionForbiddenException();
        }

        // On ne supprime jamais le dernier administrateur actif
        if ($target['role'] === 'admin' && $this->userModel->countActiveByRole('admin') <= 1) {
            throw new LastAdminException();
        }
    }

    /**
     * Règle d'autorisation pour l'auto-suppression : le mot de passe fourni
     * doit correspondre à celui du compte.
     */
    private function ensureSelfDeletionAllowed(array $target, string $password): void
    {
        if (!password_verify($password, $target['password_hash'])) {
            throw new InvalidPasswordException();
        }
    }

    /**
     * Notifie l'utilisateur que son compte a été supprimé PAR UN ADMINISTRATEUR.
     * Appelée par AdminController après le retour de deleteByAdmin() (donc après commit).
     *
     * @param array{email:string, firstname:string, lastname:string} $contact
     */
    public function notifyAdminDeletion(array $contact): void
    {
        $mailer = new MailerExample();
        $mailer->sendHtml(
            $contact['email'],
            'Votre compte a été supprimé',
            view('Emails/adminDeletedAccount', [
                'firstname' => $contact['firstname'],
                'lastname'  => $contact['lastname'],
            ])
        );
    }

    /**
     * Notifie l'utilisateur que son compte a été supprimé PAR LUI-MÊME.
     * Appelée par UserController après le retour de deleteOwnAccount() (donc après commit).
     *
     * @param array{email:string, firstname:string, lastname:string} $contact
     */
    public function notifySelfDeletion(array $contact): void
    {
        $mailer = new MailerExample();
        $mailer->sendHtml(
            $contact['email'],
            'Votre compte a été supprimé',
            view('Emails/accountDeleted', [
                'firstname' => $contact['firstname'],
                'lastname'  => $contact['lastname'],
                'date'      => ucfirst(Time::now('Europe/Paris', 'fr_FR')->toLocalizedString('d MMMM yyyy à HH:mm')),
                'support'   => env('mailer.from'),
            ])
        );
    }

    /**
     * Supprime le fichier physique de l'avatar (best-effort).
     * Appelée APRÈS commit : un échec ici ne doit jamais annuler une suppression
     * de compte déjà validée en base.
     */
    private function deleteAvatarFile(?string $avatarPath): void
    {
        if ($avatarPath === null || $avatarPath === '') {
            return;
        }

        $fullPath = FCPATH . $avatarPath;

        if (! is_file($fullPath)) {
            return;
        }

        if (! @unlink($fullPath)) {
            log_message('error', 'Échec suppression avatar : {path}', ['path' => $avatarPath]);
        }
    }

    /**
     * Supprime l'avatar d'un utilisateur.
     *
     * @param int $userId ID de l'utilisateur concerné.
     * @return bool True si la suppression a réussi.
     */
    public function deleteAvatar(int $userId): bool
    {
        $target = $this->userModel->find($userId);
        if ($target === null) {
            throw new UserNotFoundException();
        }

        $avatarPath = $target['avatar'] ?? null;

        if ($avatarPath === null || $avatarPath === '') {
            return false;
        }

        $this->userModel->update($userId, ['avatar' => null]);

        $this->deleteAvatarFile($avatarPath);

        return true;
    }
}
