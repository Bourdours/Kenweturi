<?php

namespace App\Services;

use App\Libraries\MailerExample;

use App\Services\JourneyService;

use App\Models\JourneyModel;
use App\Models\BookingModel;

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

    public function __construct()
    {
        $this->journeyService       = new JourneyService();

        $this->journeyModel         = new JourneyModel();
        $this->bookingModel         = new BookingModel();
    }

    public function accept(int $bookingId,int $driverId):void{

        $booking = $this->bookingModel->find($bookingId);
        $journey = $this->journeyModel->find($booking['journey_id']);

        if($booking === null)                   throw new BookingNotFoundException();
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

    public function confirmToPassenger(int $bookingId, int $driverId){

        $booking = $this->bookingModel->findWithDetails($bookingId, $driverId);

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
            ])
        );

    }
    
}