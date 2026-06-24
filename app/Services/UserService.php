<?php

namespace App\Services;

use App\Libraries\MailerExample;

use App\Models\UserModel;

use App\Services\BookingService;
use App\Services\JourneyService;

use App\Exceptions\UserNotFoundException;
use App\Exceptions\CannotDeleteSelfException;
use App\Exceptions\CannotDeleteSuperadminException;
use App\Exceptions\AdminDeletionForbiddenException;
use App\Exceptions\LastAdminException;

use Config\Database;

class UserService
{
    protected UserModel      $userModel;
    protected BookingService $bookingService;
    protected JourneyService $journeyService;

    public function __construct()
    {
        $this->userModel      = new UserModel();
        $this->bookingService = new BookingService();
        $this->journeyService = new JourneyService();
    }

    /**
     * Supprime un utilisateur. Les écritures sont regroupées dans une
     * transaction (tout ou rien) ; les emails ne sont envoyés qu'APRÈS un
     * commit réussi, à partir de données capturées AVANT les écritures.
     *
     * @return array{email:string, firstname:string, lastname:string}
     *
     * @throws UserNotFoundException
     * @throws CannotDeleteSelfException
     * @throws CannotDeleteSuperadminException
     * @throws AdminDeletionForbiddenException
     * @throws LastAdminException
     */
    public function delete(int $targetId, int $currentUserId, string $currentRole): array
    {

        // Vérification du user
        $target = $this->userModel->find($targetId);
        if ($target === null) {
            throw new UserNotFoundException();
        }

        // Vérification que la suppression correspond au règle
        $this->ensureDeletionAllowed($target, $targetId, $currentUserId, $currentRole);

        // Coordonnées de l'utilisateur supprimé (pour son propre email)
        $targetContact = [
            'email'     => $target['email'],
            'firstname' => $target['firstname'],
            'lastname'  => $target['lastname'],
        ];

        // Capture des informations liées user avant suppression des données dans la base
        $journeys                = $this->journeyService->getActiveJourneysWithCities($targetId);
        $journeyIds              = array_column($journeys, 'id');
        // Données pour notifier les drivers de chaque journey auquel le user a un booking d'accepté
        $driverNotificationsData     = $this->bookingService->getActivePassengerBookings($targetId);
        // Données pour notifier les passengers acceptés de chaque journey auquel le user est driver
        $passengerNotificationsData  = $this->journeyService->getCancellationNotifications($journeyIds);

        // ============ Écritures dans la base, avec rollback si une erreur survient ============

        $db = Database::connect();
        $db->transBegin();

        try {
            // Annulation des reservations du user
            $this->bookingService->rejectAllByPassenger($targetId);

            // Annulation des Trajets de l'utilisateur (conducteur) + réservations de ses passagers
            $this->bookingService->rejectAllForJourneys($journeyIds);
            $this->journeyService->cancelAllByDriver($targetId);

            // Soft delete du user
            $this->userModel->anonymize($targetId);
            $this->userModel->delete($targetId);

            $db->transCommit();
        } catch (\Throwable $e) {
            // Si une erreur apparait, annulation des écritures en base et pas d'envoi d'email.
            $db->transRollback();
            throw $e;
        }

        // ============ Envoi emails après modification dans la base ============

        $this->bookingService->notifyDriverCancellations($driverNotificationsData);
        $this->journeyService->notifyPassengersJourneyCancelled($passengerNotificationsData);

        return $targetContact;
    }

    private function ensureDeletionAllowed(array $target, int $targetId, int $currentUserId, string $currentRole): void
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
     * Notifie l'utilisateur de la suppression de son compte (appelée par le
     * contrôleur, après le retour de delete(), donc après commit).
     *
     * @param array{email:string, firstname:string, lastname:string} $contact
     */
    public function notifyDeletion(array $contact): void
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
}