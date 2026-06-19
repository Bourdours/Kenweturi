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

    public function accept(int $bookingId,int $driverId):void{

        $booking = $this->bookingModel->find($bookingId);
        if($booking === null)                   throw new BookingNotFoundException();

        $journey = $this->journeyModel->find($booking['journey_id']);
        if($journey['user_id'] != $driverId)    throw new BookingNotAssignedToDriverException();

        if($booking['status'] === 'accepted')   throw new BookingAlreadyAcceptedException();

        $nbOfRemainingSeats = $this->journeyService->countRemainingSeats($journey['id']);

        if($nbOfRemainingSeats === 0)           throw new JourneyFullException();
        else $this->bookingModel->update($bookingId,['status'=>'accepted']);

    }

    public function rejectAllPending(int $journeyId){

        $bookings = $this->bookingModel->where('journey_id',$journeyId)->where('status','pending')->findAll();

        foreach($bookings as $booking){

            $this->bookingModel->update($booking['id'],['status'=>'rejected']);

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
    
}