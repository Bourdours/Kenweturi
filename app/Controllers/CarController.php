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
            'brand'     => $this->request->getPost('brand'),
            'model'     => $this->request->getPost('model'),
            'color'     => $this->request->getPost('color'),
            'seats'     => $this->request->getPost('seats'),
            'user_id'   => session()->get('user_id'),  // depuis la session
        ];

        if (!$carModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $carModel->errors());
        }

        return redirect()->to('/cars')->with('success', 'Voiture ajoutée avec succès !');
    }


    /** 
     * 
     * Affiche la liste des voitures d'un utilisateur.
     * 
     * @return string
     */
    public function index()
    {
        $carModel = new CarModel();

        $data = [
            'cars' => $carModel->where('user_id', session()->get('user_id'))->findAll()
        ];

        return view('car/index', $data);
    }


    /** 
     * 
     * Créer la voiture d'un utilisateur.
     * 
     * @return string
     */
    public function showCreateForm()
    {
        return view('car/create');
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
            return redirect()->to('/cars')->with('error', 'Voiture introuvable.');
        }

        $data = [
            'car' => $car
        ];

        return view('car/edit', $data);
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

        $data = [
            'id'        => $id,
            'brand'     => $this->request->getPost('brand'),
            'model'     => $this->request->getPost('model'),
            'color'     => $this->request->getPost('color'),
            'seats'     => $this->request->getPost('seats'),
            'user_id'   => session()->get('user_id'),  // depuis la session
        ];

        if (!$carModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $carModel->errors());
        }

        return redirect()->to('/cars')->with('success', 'Voiture modifiée avec succès !');
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
            return redirect()->to('/cars')->with('error', 'Voiture introuvable.');
        }

        $carModel->delete($id);

        return redirect()->to('/cars')->with('success', 'Voiture supprimée avec succès !');
    }

}