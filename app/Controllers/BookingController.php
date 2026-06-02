<?php

namespace App\Controllers;

use App\Libraries\MailerExample;
use App\Models\BookingModel;
use App\Models\JourneyModel;
use \CodeIgniter\HTTP\RedirectResponse;

/**
 * Contrôleur gérant l'objet booking
 */
class BookingController extends BaseController
{

    protected BookingModel $bookingModel;
    protected JourneyModel $journeyModel;

    public function __construct()
    {
        $this->bookingModel = new BookingModel();
        $this->journeyModel = new JourneyModel();
    }

    public function create(int $id): RedirectResponse{

        $userId = session('user_id');

        // --- Vérification que le trajet existe et n'est pas annulé
        $journey = $this->journeyModel->where('id', $id)
                        ->where('canceled_at', null)
                        ->first();

        if (!$journey)
            return redirect()->to('/journeys');

        // --- Vérification que c'est pas le driver
        if ($journey['user_id'] === $userId)
            return redirect()->to('/journeys/' . $id)
                ->with('errors', ['booking' => 'Vous ne pouvez pas réserver votre propre trajet.']);

        // --- Vérification pas déjà réservé
        $existing = $this->bookingModel->where('journey_id', $id)
                                       ->where('user_id', $userId)
                                       ->first();

        if ($existing)
            return redirect()->to('/journeys/' . $id)
                ->with('errors', ['booking' => 'Vous avez déjà réservé ce trajet.']);

        // --- Vérification places restantes
        $bookSeats = $this->bookingModel->selectSum('seat_numbers')
                                        ->where('journey_id', $id)
                                        ->get()->getRowArray();
        $remainingSeats = $journey['seats'] - ($bookSeats['seat_numbers'] ?? 0);

        $seatsRequested = $this->request->getPost('seat_numbers') ?? 1;

        if ($seatsRequested > $remainingSeats)
            return redirect()->to('/journeys/' . $id)
                ->with('errors', ['booking' => 'Plus assez de places disponibles.']);

        // --- Insertion de la réservation
        $bookingId = $this->bookingModel->insert([
            'booking_date' => date('Y-m-d H:i:s'),
            'seat_numbers' => $seatsRequested,
            'journey_id'   => $id,
            'user_id'      => $userId
        ]);

        if ($bookingId) {
            $booking = $this->bookingModel->findWithDetails((int) $bookingId, $userId);
            if ($booking) {
                $date = date('d/m/Y', strtotime($booking['start_datetime'])) . ' à ' . date('H:i', strtotime($booking['start_datetime']));
                $mailer = new MailerExample();
                $mailer->sendHtml(
                    $booking['driver_email'],
                    'Nouvelle demande de réservation',
                    view('Emails/bookingRequest', [
                        'firstname'          => $booking['driver_firstname'],
                        'passengerFirstname' => $booking['passenger_firstname'],
                        'passengerLastname'  => $booking['passenger_lastname'],
                        'cityStart'          => $booking['city_start_name'],
                        'cityEnd'            => $booking['city_end_name'],
                        'date'               => $date,
                    ])
                );
            }
        }

        return redirect()->to('/journeys/' . $id)
            ->with('success', 'Réservation effectuée avec succès.');
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