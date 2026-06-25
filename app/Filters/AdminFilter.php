<?php

namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $userId = session()->get('user_id');

        // Pas connecté → renvoi vers la page de connexion
        if (! $userId) {
            session()->set('redirect_url', current_url());
            return redirect()->to('/login')
                ->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        // Resynchronisation avec la base : si le rôle a changé depuis la connexion,
        // on met à jour la session pour éviter les autorisations obsolètes.
        $user = (new UserModel())->find($userId);

        if (! $user || ! $user['is_admin']) {
            session()->set('isAdmin', false);
            session()->set('role', $user['role'] ?? 'user');
            return redirect()->to(site_url('/'))
                ->with('error', 'Accès réservé aux administrateurs.');
        }

        // Resync session
        session()->set('isAdmin', true);
        if (session()->get('role') !== $user['role']) {
            session()->set('role', $user['role']);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // rien
    }
}
