<?php

namespace App\Controllers;

use App\Libraries\MailerExample;

use App\Services\BookingService;

use App\Models\BookingModel;
use App\Models\JourneyModel;

use \CodeIgniter\HTTP\RedirectResponse;

use App\Exceptions\BookingAlreadyAcceptedException;
use App\Exceptions\BookingNotAssignedToDriverException;
use App\Exceptions\BookingNotFoundException;
use App\Exceptions\JourneyFullException;

/**
 * Contrôleur gérant l'objet booking
 */
class BookingController extends BaseController
{

    protected BookingModel $bookingModel;
    protected JourneyModel $journeyModel;

    protected BookingService $bookingService;

    public function __construct()
    {
        $this->bookingModel = new BookingModel();
        $this->journeyModel = new JourneyModel();

        $this->bookingService = new BookingService();
    }

    /**
     * Détail d'une réservation.
     * GET /dashboard/bookings/:id
     */
    public function show(int $id): string|RedirectResponse
    {
        $userId = (int) session('user_id');

        $booking = $this->bookingModel->findWithDetails($id, $userId);

        $journey = $this->journeyModel->find($booking['journey_id']);

        $remainingSeats = $this->bookingModel->countRemainingSeats($journey['id'],$journey['seats']);

        if (!$booking)
            return redirect()->to('/dashboard/bookings')->with('error', 'Réservation introuvable.');

        $passengers = $this->bookingModel->findPassengersByJourney((int) $booking['journey_id']);

        $is_driver = (int) $booking['driver_id'] === $userId;

        $person_firstname  = $is_driver ? $booking['passenger_firstname']  : $booking['driver_firstname'];
        $person_lastname   = $is_driver ? $booking['passenger_lastname']   : $booking['driver_lastname'];
        $person_avatar     = $is_driver ? $booking['passenger_avatar']     : $booking['driver_avatar'];
        $person_is_student = $is_driver ? $booking['passenger_is_student'] : $booking['driver_is_student'];
        $person_label      = $is_driver ? 'Passager' : 'Conducteur';

        return view('Bookings/bookingShow', [
            'title'             => 'Détail de la réservation',
            'back'              => $this->validateBackUrl($this->request->getGet('back')),
            'booking'           => $booking,
            'is_driver'         => $is_driver,
            'passengers'        => $passengers,
            'person_firstname'  => $person_firstname,
            'person_lastname'   => $person_lastname,
            'person_avatar'     => $person_avatar,
            'person_is_student' => $person_is_student,
            'person_label'      => $person_label,
            'isPending'         => $booking['status'] === "pending",
            'isFull'            => $remainingSeats == 0,
        ]);
    }

    public function create(int $id): RedirectResponse{

        $userId = session('user_id');

        // --- Vérification que le trajet existe et n'est pas annulé
        $journey = $this->journeyModel->findActive($id);

        if (!$journey)
            return redirect()->to('/journeys');

        // --- Vérification que c'est pas le driver
        if ($journey['user_id'] === $userId)
            return redirect()->to('/journeys/' . $id)
                ->with('errors', ['booking' => 'Vous ne pouvez pas réserver votre propre trajet.']);

        // --- Vérification pas déjà réservé
        $existing = $this->bookingModel->findUserBooking($id,$userId);

        if ($existing)
            return redirect()->to('/journeys/' . $id)
                ->with('errors', ['booking' => 'Vous avez déjà réservé ce trajet.']);

        // --- Vérification places restantes
        $remainingSeats = $this->bookingModel->countRemainingSeats($journey['id'],$journey['seats']);

        if ($remainingSeats == 0)
            return redirect()->to('/journeys/' . $id)
                ->with('errors', ['booking' => 'Plus de place disponible.']);

        // --- Insertion de la réservation
        $bookingId = $this->bookingModel->insert([
            'booking_date' => date('Y-m-d H:i:s'),
            'seat_numbers' => 1,
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
    public function accept(int $bookingId): RedirectResponse
    {
        $driverId = (int) session()->get('user_id');

        try{

            $this->bookingService->accept($bookingId,$driverId);
            $this->bookingService->confirmToPassenger($bookingId,$driverId);
            return redirect()->to('/dashboard/bookings/' . $bookingId)->with('success', 'Réservation acceptée.');


        }catch(BookingNotFoundException) {

            return redirect()->to('/dashboard/bookings/' . $bookingId)
                ->with('error', 'Cette réservation est introuvable');

        }catch(BookingNotAssignedToDriverException) {

            return redirect()->to('/dashboard/bookings')
                ->with('error', 'Vous n\'êtes pas le conducteur du trajet de cette réservation');

        }catch(JourneyFullException) {

            return redirect()->to('/dashboard/bookings/' . $bookingId)
                ->with('error', 'Le trajet est complet, plus de places disponibles.');

        }catch(BookingAlreadyAcceptedException) {

            return redirect()->to('/dashboard/bookings/' . $bookingId)
                ->with('error', 'Cette réservation a déjà été acceptée');

        }
        catch(\throwable $e){
            
            log_message('error', 'Booking accept failed: {message}', ['message' => $e->getMessage()]);
            return redirect()->to('/dashboard')->with('error','Un problème est survenu');

        }

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
