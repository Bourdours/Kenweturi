<?php
namespace App\Controllers\Journeys;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;

class JourneysController extends BaseController
{
    public function new(): string
    {
        return view('Journeys/journeyNew', [
            'title' => "Publier un trajet"
        ]);
    }

    public function show(): string
    {
        return view('Journeys/journeyShow',[
            'title' => "Chercher un trajet"
        ]);
    }

    public function store(): RedirectResponse
    {
        return redirect()->to('/journeys');
    }
}