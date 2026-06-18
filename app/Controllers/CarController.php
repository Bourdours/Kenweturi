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
            return redirect()->back()->withInput()->with('errors', $carModel->errors());
        }

        $back = $this->request->getPost('back');
        if (empty($back)) {
            $back = site_url('profile/edit');
        } 
        return redirect()->to($back)->with('success', 'Voiture ajoutée avec succès !');
    }

    // /** 
    //  * 
    //  * Affiche une voitures d'un utilisateur.
    //  * 
    //  * @return string
    //  */
    // public function show(int $id): string|RedirectResponse
    // {
    //     $carModel = new CarModel();
    //     $car = $carModel->where('user_id', session()->get('user_id'))->find($id);
    //     if (!$car) {
    //         return redirect()->to('/dashboard')->with('error', 'Voiture introuvable.');
    //     }
    //     return view('car/show', ['car' => $car]);
    // }

    // /** 
    //  * 
    //  * Affiche la liste des voitures d'un utilisateur.
    //  * 
    //  * @return string
    //  */
    // public function showAll()
    // {
    //     $carModel = new CarModel();

    //     $data = [
    //         'cars' => $carModel->where('user_id', session()->get('user_id'))->findAll()
    //     ];

    //     return view('/cars/get', $data);
    // }


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
            return redirect()->back()->withInput()->with('errors', $carModel->errors());
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
