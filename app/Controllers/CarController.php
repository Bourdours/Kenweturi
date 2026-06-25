<?php

namespace App\Controllers;

use App\Models\CarModel;
use \CodeIgniter\HTTP\RedirectResponse;


/**
 * Contrôleur gérant l'objet car
 */
class CarController extends BaseController
{

    /** 
     * 
     * Créer la voiture d'un utilisateur.
     * 
     * @return string
     */
    public function showCreateForm()
    {
        return view('Car/create', [
            'back' => $this->request->getGet('back')
        ]);
    }

    /**
     * Traite les données envoyées par le formulaire 
     * 
     * Gère la création d'une voiture et la redirection avec message de succès.
     * 
     * @return RedirectResponse
     */
    public function create()
    {
        $carModel = new CarModel();
        $data = [
            'brand'   => $this->request->getPost('brand'),
            'model'   => $this->request->getPost('model'),
            'color'   => $this->request->getPost('color'),
            'seats'   => $this->request->getPost('seats'),
            'user_id' => session()->get('user_id'),
        ];

        if (!$carModel->save($data)) {
            return redirect()->to(site_url('profile/edit'))->withInput()->with('errors', $carModel->errors());
        }

        $back = $this->request->getPost('back');
        if (empty($back)) {
            $back = site_url('profile/edit');
        }
        return redirect()->to($back)->with('success', 'Voiture ajoutée avec succès !');
    }

    /** 
     * 
     * Modifie la voiture d'un utilisateur.
     * 
     * @return string|RedirectResponse
     */
    public function showEditForm($id)
    {
        $carModel = new CarModel();

        $car = $carModel->where('user_id', session()->get('user_id'))->find($id);

        if (!$car) {
            return redirect()->to('/dashboard')->with('error', 'Voiture introuvable.');
        }

        $data = [
            'car' => $car
        ];

        return view('Profile/carUpdate', $data);
    }


    /**
     * 
     * Gère la modification d'une voiture et la redirection avec message de succès.
     * 
     * @return RedirectResponse
     */
    public function update($id)
    {
        $carModel = new CarModel();

        $car = $carModel->where('user_id', session()->get('user_id'))->find($id);
        if (!$car) {
            return redirect()->to('/dashboard')->with('error', 'Voiture introuvable.');
        }

        $data = [
            'id'      => $id,
            'brand'   => $this->request->getPost('brand'),
            'model'   => $this->request->getPost('model'),
            'color'   => $this->request->getPost('color'),
            'seats'   => $this->request->getPost('seats'),
            'user_id' => session()->get('user_id'), // requis par la validation du model (ownership déjà vérifiée ci-dessus)
        ];

        if (!$carModel->save($data)) {
            return redirect()->to(site_url("car/$id/edit"))->withInput()->with('errors', $carModel->errors());
        }

        return redirect()->to(site_url('profile/edit'))->with('success', 'Voiture modifiée avec succès !');
    }


    /**
     * 
     * Gère la supression d'une voiture et la redirection avec message de succès.
     * 
     * @return RedirectResponse
     */
    public function delete($id)
    {
        $carModel = new CarModel();

        $car = $carModel->where('user_id', session()->get('user_id'))->find($id);

        if (!$car) {
            return redirect()->to('/dashboard')->with('error', 'Voiture introuvable.');
        }

        $carModel->delete($id);

        return redirect()->to(site_url('profile/edit'))->with('success', 'Voiture supprimée avec succès !');
    }
}
