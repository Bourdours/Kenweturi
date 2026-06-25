<?php

namespace App\Services;

use App\Libraries\MailerExample;

use App\Services\JourneyService;

use App\Models\JourneyModel;
use App\Models\BookingModel;
use App\Models\NotificationPrefModel;
use App\Models\UserModel;

use App\Exceptions\BookingAlreadyAcceptedException;
use App\Exceptions\BookingNotAssignedToDriverException;
use App\Exceptions\BookingNotFoundException;
use App\Exceptions\JourneyFullException;

/**

 */
class BookingService
{
    protected JourneyService $journeyService;

    protected JourneyModel $journeyModel;
    protected BookingModel $bookingModel;
    protected NotificationPrefModel $notifPrefModel;

    public function __construct()
    {
        $this->journeyService  = new JourneyService();
        $this->journeyModel    = new JourneyModel();
        $this->bookingModel    = new BookingModel();
        $this->notifPrefModel  = new NotificationPrefModel();
    }

    public function accept(int $bookingId, int $driverId): void
    {

        $booking = $this->bookingModel->find($bookingId);
        if ($booking === null)                   throw new BookingNotFoundException();

        $journey = $this->journeyModel->find($booking['journey_id']);
        if ($journey['user_id'] != $driverId)    throw new BookingNotAssignedToDriverException();

        if ($booking['status'] === 'accepted')   throw new BookingAlreadyAcceptedException();

        $nbOfRemainingSeats = $this->journeyService->countRemainingSeats($journey['id']);

        if ($nbOfRemainingSeats === 0)           throw new JourneyFullException();
        else $this->bookingModel->update($bookingId, ['status' => 'accepted']);
    }

    public function rejectAllPending(int $journeyId)
    {

        $bookings = $this->bookingModel->where('journey_id', $journeyId)->where('status', 'pending')->findAll();

        foreach ($bookings as $booking) {

            $this->bookingModel->update($booking['id'], ['status' => 'rejected']);
        }
    }

    public function confirmToPassenger(int $bookingId, int $driverId): void
    {
        $booking     = $this->bookingModel->findWithDetails($bookingId, $driverId);
        $passengerId = (int) $booking['user_id'];

        if (!$this->notifPrefModel->wantsNotif($passengerId, 'booking_accepted')) {
            return;
        }

        $date = date('d/m/Y', strtotime($booking['start_datetime'])) . ' à ' . date('H:i', strtotime($booking['start_datetime']));

        $mailer = new MailerExample();
        $mailer->sendHtml(
            $booking['passenger_email'],
            'Votre réservation a été acceptée',
            view('Emails/bookingAccepted', [
                'firstname'       => $booking['passenger_firstname'],
                'driverFirstname' => $booking['driver_firstname'],
                'driverLastname'  => $booking['driver_lastname'],
                'cityStart'       => $booking['city_start_name'],
                'cityEnd'         => $booking['city_end_name'],
                'date'            => $date,
                'prefLabel'       => NotificationPrefModel::PREFS['booking_accepted'],
                'unsubscribeUrl'  => site_url('unsubscribe?uid=' . $passengerId . '&pref=booking_accepted&token=' . UserModel::unsubscribeToken($passengerId, 'booking_accepted')),
                'preferencesUrl'  => site_url('profile/notifications'),
            ])
        );
    }

    public function getDetails(int $id, int $userId): array
    {
        $booking = $this->bookingModel->findWithDetails($id, $userId);
        if ($booking === null) {
            throw new BookingNotFoundException();
        }

        $journey        = $this->journeyModel->find($booking['journey_id']);
        $remainingSeats = $this->bookingModel->countRemainingSeats($journey['id'], $journey['seats']);
        $passengers     = $this->bookingModel->findPassengersByJourney((int) $booking['journey_id']);
        $isDriver       = (int) $booking['driver_id'] === $userId;

        return [
            'booking'           => $booking,
            'is_driver'         => $isDriver,
            'passengers'        => $passengers,
            'person_firstname'  => $isDriver ? $booking['passenger_firstname']  : $booking['driver_firstname'],
            'person_lastname'   => $isDriver ? $booking['passenger_lastname']   : $booking['driver_lastname'],
            'person_avatar'     => $isDriver ? $booking['passenger_avatar']     : $booking['driver_avatar'],
            'person_is_student' => $isDriver ? $booking['passenger_is_student'] : $booking['driver_is_student'],
            'person_label'      => $isDriver ? 'Passager' : 'Conducteur',
            'isAccepted'        => $booking['status'] === 'accepted',
            'isPending'         => $booking['status'] === 'pending',
            'isFull'            => $remainingSeats <= 0,
        ];
    }

    /**
     * Refuse toutes les réservations d'un passager (ses propres réservations).
     * Utilisé lors de la suppression de son compte.
     */
    public function rejectAllByPassenger(int $userId): void
    {
        $this->bookingModel->rejectAllByPassenger($userId);
    }

    /**
     * Refuse toutes les réservations (en attente ou acceptées) portant sur une
     * liste de trajets. Utilisé quand un conducteur est supprimé : ses trajets
     * sont annulés, donc ses passagers ne doivent pas garder de réservation valide.
     *
     * @param int[] $journeyIds
     */
    public function rejectAllForJourneys(array $journeyIds): void
    {
        $this->bookingModel->rejectAllForJourneys($journeyIds);
    }

    /**
     * Réservations actives d'un passager — à CAPTURER avant tout rejet
     * (sert à prévenir les conducteurs après commit).
     *
     * @return array<int,array>
     */
    public function getActivePassengerBookings(int $userId): array
    {
        return $this->bookingModel->findActiveByPassengerWithDetails($userId);
    }

    /**
     * Prévient (best-effort) les conducteurs à partir de payloads pré-capturés.
     * Ne refait aucune requête : sûr à appeler APRÈS commit.
     *
     * @param array<int,array> $bookings
     */
    public function notifyDriverCancellations(array $bookings): void
    {
        foreach ($bookings as $booking) {
            try {
                $this->notifyDriverCancellation($booking);
            } catch (\Throwable $e) {
                log_message('error', 'Booking cancellation mail failed (booking {id}): {type}', [
                    'id'   => $booking['id'] ?? null,
                    'type' => get_class($e),
                ]);
            }
        }
    }

    /**
     * Envoie au conducteur le mail d'annulation d'une réservation.
     * Même contenu que l'annulation manuelle (BookingController::delete).
     *
     * @param array $booking Réservation enrichie
     */
    private function notifyDriverCancellation(array $booking): void
    {
        $driverId = (int) $booking['driver_id'];
        if (!$this->notifPrefModel->wantsNotif($driverId, 'booking_cancelled')) {
            return;
        }

        $date = date('d/m/Y', strtotime($booking['start_datetime']))
            . ' à ' . date('H:i', strtotime($booking['start_datetime']));

        $mailer = new MailerExample();
        $mailer->sendHtml(
            $booking['driver_email'],
            'Annulation d\'une réservation',
            view('Emails/bookingCancelled', [
                'firstname'          => $booking['driver_firstname'],
                'passengerFirstname' => $booking['passenger_firstname'],
                'passengerLastname'  => $booking['passenger_lastname'],
                'cityStart'          => $booking['city_start_name'],
                'cityEnd'            => $booking['city_end_name'],
                'date'               => $date,
                'prefLabel'          => NotificationPrefModel::PREFS['booking_cancelled'],
                'unsubscribeUrl'     => site_url('unsubscribe?uid=' . $driverId . '&pref=booking_cancelled&token=' . UserModel::unsubscribeToken($driverId, 'booking_cancelled')),
                'preferencesUrl'     => site_url('profile/notifications'),
            ])
        );
    }
}
