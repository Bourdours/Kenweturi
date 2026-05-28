<?php

namespace App\Controllers;

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
        $booking = $this->bookingModel
            ->where('user_id', session()->get('user_id'))
            ->find($id);

        if (!$booking)
            return redirect()->to('/dashboard')->with('error', 'Réservation introuvable.');

        $this->bookingModel->delete($id);

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

        // Vérification que la réservation existe et appartient à un trajet du driver
        $db = \Config\Database::connect();

        $booking = $db->table('booking')
            ->select('booking.*, journey.user_id as driver_id')
            ->join('journey', 'journey.id = booking.journey_id')
            ->where('booking.id', $id)
            ->where('journey.user_id', $userId)
            ->get()->getRowArray();
        
        

        if (!$booking)
            return redirect()->to('/dashboard/bookings')->with('error', 'Réservation introuvable.');

        if ($booking['status'] !== 'pending')
            return redirect()->to('/dashboard/bookings')->with('error', 'Cette réservation a déjà été traitée.');

        $this->bookingModel->update($id, ['status' => 'accepted']);

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

        $db = \Config\Database::connect();

        $booking = $db->table('booking')
            ->select('booking.*, journey.user_id as driver_id')
            ->join('journey', 'journey.id = booking.journey_id')
            ->where('booking.id', $id)
            ->where('journey.user_id', $userId)
            ->get()->getRowArray();

        if (!$booking)
            return redirect()->to('/dashboard/bookings')->with('error', 'Réservation introuvable.');

        if ($booking['status'] !== 'pending')
            return redirect()->to('/dashboard/bookings')->with('error', 'Cette réservation a déjà été traitée.');

        $this->bookingModel->update($id, ['status' => 'rejected']);

        return redirect()->to('/dashboard/bookings')->with('success', 'Réservation refusée.');
    }

}