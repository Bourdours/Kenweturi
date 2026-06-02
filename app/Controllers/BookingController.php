<?php

namespace App\Controllers;

use App\Libraries\MailerExample;
use App\Models\BookingModel;
use \CodeIgniter\HTTP\RedirectResponse;

/**
 * Contrôleur gérant l'objet booking
 */
class BookingController extends BaseController
{

    protected BookingModel $bookingModel;

    public function __construct()
    {
        $this->bookingModel = new BookingModel();
    }
    /**
     * 
     * Gère la supression d'une réservation et la redirection avec message de succès.
     * 
     * @return RedirectResponse
     */
    public function delete(int $id): RedirectResponse
    {
        $userId = (int) session()->get('user_id');

        $booking = $this->bookingModel->findWithDetails($id, $userId);

        if (!$booking || (int) $booking['user_id'] !== $userId)
            return redirect()->to('/dashboard')->with('error', 'Réservation introuvable.');

        $this->bookingModel->delete($id);

        $date = date('d/m/Y', strtotime($booking['start_datetime'])) . ' à ' . date('H:i', strtotime($booking['start_datetime']));

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
            ])
        );

        return redirect()->to('/dashboard')->with('success', 'Réservation annulée avec succès !');
    }

    /**
     * Accepte une réservation (par le driver).
     * POST /dashboard/bookings/:id/accept
     *
     * @return RedirectResponse
     */
    public function accept(int $id): RedirectResponse
    {
        $userId = (int) session()->get('user_id');

        $booking = $this->bookingModel->findWithDetails($id, $userId);

        if (!$booking || (int) $booking['driver_id'] !== $userId)
            return redirect()->to('/dashboard/bookings')->with('error', 'Réservation introuvable.');

        if ($booking['status'] !== 'pending')
            return redirect()->to('/dashboard/bookings')->with('error', 'Cette réservation a déjà été traitée.');

        $this->bookingModel->update($id, ['status' => 'accepted']);

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

        return redirect()->to('/dashboard/bookings')->with('success', 'Réservation acceptée.');
    }

    /**
     * Refuse une réservation (par le driver).
     * POST /dashboard/bookings/:id/reject
     *
     * @return RedirectResponse
     */
    public function reject(int $id): RedirectResponse
    {
        $userId = (int) session()->get('user_id');

        $booking = $this->bookingModel->findWithDetails($id, $userId);

        if (!$booking || (int) $booking['driver_id'] !== $userId)
            return redirect()->to('/dashboard/bookings')->with('error', 'Réservation introuvable.');

        if ($booking['status'] !== 'pending')
            return redirect()->to('/dashboard/bookings')->with('error', 'Cette réservation a déjà été traitée.');

        $this->bookingModel->update($id, ['status' => 'rejected']);

        $date = date('d/m/Y', strtotime($booking['start_datetime'])) . ' à ' . date('H:i', strtotime($booking['start_datetime']));

        $mailer = new MailerExample();
        $mailer->sendHtml(
            $booking['passenger_email'],
            'Votre réservation n\'a pas été retenue',
            view('Emails/bookingRejected', [
                'firstname' => $booking['passenger_firstname'],
                'cityStart' => $booking['city_start_name'],
                'cityEnd'   => $booking['city_end_name'],
                'date'      => $date,
            ])
        );

        return redirect()->to('/dashboard/bookings')->with('success', 'Réservation refusée.');
    }

}