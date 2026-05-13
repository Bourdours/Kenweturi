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
     * Affiche le formulaire de réservation.
     *
     * @param int $journeyId
     * @return string
     */
    public function showBookForm(int $journeyId): string
    {
        return view('booking/create', ['journey_id' => $journeyId]);
    }

    /**
     * Traite les données envoyées par le formulaire 
     * 
     * Gère la création d'une réservation et la redirection avec message de succès.
     * 
     * @return RedirectResponse
     */
    public function create()
    {
        $bookingModel = new BookingModel();

        $data = [
            'booking_date' => $this->request->getPost('booking_date'),
            'seat_numbers' => $this->request->getPost('seat_numbers'),
            'journey_id' => $this->request->getPost('journey_id'),
            'user_id'   => session()->get('user_id'),
        ];

        if (!$bookingModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $bookingModel->errors());
        }

        $journeyId = $this->request->getPost('journey_id');
        return redirect()->to('/journey/' . $journeyId . '/get')->with('success', 'Réservation faite avec succès !');
    }

    /** 
     * 
     * Affiche la liste des réservations d'un utilisateur.
     * 
     * @return string
     */
    public function showAll()
    {
        $bookingModel = new BookingModel();

        $data = [
            'bookings' => $bookingModel->where('user_id', session()->get('user_id'))->findAll()
        ];

        return view('booking/index', $data);
    }

    /**
     * Affiche le formulaire de modification d'une réservation.
     *
     * @param int $id
     * @return string|RedirectResponse
     */
    public function showEditForm(int $id): string|RedirectResponse
    {
        $bookingModel = new BookingModel();

        $booking = $bookingModel
            ->where('user_id', session()->get('user_id'))
            ->find($id);

        if (!$booking) {
            return redirect()->to('/dashboard')->with('error', 'Réservation introuvable.');
        }

        return view('booking/edit', ['booking' => $booking]);
    }

    /**
     * 
     * Gère la modification d'une reservation et la redirection avec message de succès.
     * 
     * @return RedirectResponse
     */
    public function update(int $id): RedirectResponse
    {
        $bookingModel = new BookingModel();

        $booking = $bookingModel
            ->where('user_id', session()->get('user_id'))
            ->find($id);

        if (!$booking) {
            return redirect()->to('/dashboard')->with('error', 'Réservation introuvable.');
        }

        $data = [
            'id'           => $id,
            'booking_date' => $this->request->getPost('booking_date'),
            'seat_numbers' => $this->request->getPost('seat_numbers'),
        ];

        if (!$bookingModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $bookingModel->errors());
        }

        $journeyId = $this->request->getPost('journey_id');
        return redirect()->to('/journey/' . $journeyId . '/get')->with('success', 'Réservation modifiée avec succès !');
    }


    /**
     * 
     * Gère la supression d'une réservation et la redirection avec message de succès.
     * 
     * @return RedirectResponse
     */
    public function delete(int $id): RedirectResponse
    {
        $bookingModel = new BookingModel();
        $booking = $bookingModel->where('user_id', session()->get('user_id'))->find($id);
        if (!$booking) {
            return redirect()->to('/dashboard')->with('error', 'Réservation introuvable.');
        }
        $journeyId = $booking['journey_id'];
        return redirect()->to('/journey/' . $journeyId . '/get')->with('success', 'Réservation annulée avec succès !');
    }

}