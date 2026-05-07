<?php
namespace App\Controllers\Journeys;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;

class JourneysController extends BaseController
{
    public function new(): string
    {
        return view('Journeys/journeyNew');
    }

    public function show(): string
    {
        return view('Journeys/journeyShow');
    }

    public function store(): RedirectResponse
    {
        return redirect()->to('/journeys');
    }
}