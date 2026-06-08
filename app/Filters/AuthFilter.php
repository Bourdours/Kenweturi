<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $isLoggedIn = session('isLoggedIn');

        if (! $isLoggedIn) {
            session()->set('redirect_url', current_url());
            return redirect()->to('/login')
                ->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $userModel = new UserModel();
        $user = $userModel->find(session()->get('user_id'));

        if (!$user || session()->get('userPassword') !== $user['password_hash']) {
            session()->destroy();
            return redirect()->to('/login')->with('error', 'Session expirée.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
