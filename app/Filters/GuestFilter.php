<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class GuestFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $isLoggedIn = session('isLoggedIn');

        if ($isLoggedIn) {
            return redirect()->to('/dashboard')
                ->with('info', 'Vous êtes déjà connecté.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        
    }
}
