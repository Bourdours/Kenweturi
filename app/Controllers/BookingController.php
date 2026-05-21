<?php

namespace App\Controllers;

use App\Models\BookingModel;
use \CodeIgniter\HTTP\RedirectResponse; 

/**
 * Contrôleur gérant l'objet booking
 */
class BookingController extends BaseController
{


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
        // À implémenter quand la colonne status sera ajoutée en base
    }

    /**
     * Refuse une réservation (par le driver).
     * POST /dashboard/bookings/:id/reject
     *
     * @return RedirectResponse
     */
    public function reject(int $id): RedirectResponse
    {
        // À implémenter quand la colonne status sera ajoutée en base
    }

}