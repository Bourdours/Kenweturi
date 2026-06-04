<?php

namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class RememberMe implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session()->get('isLoggedIn')) return;

        $token = $request->getCookie('remember_token');
        if (!$token) return;

        $userModel = new UserModel();
        $user = $userModel
            ->where('remember_token', hash('sha256', $token))
            ->where('remember_token_expiry >', date('Y-m-d H:i:s'))
            ->first();

        if ($user && !$user['is_banned'] && $user['status'] === 'active') {
            $newToken = bin2hex(random_bytes(32));
            $userModel->update($user['id'], [
                'remember_token'        => hash('sha256', $newToken),
                'remember_token_expiry' => date('Y-m-d H:i:s', strtotime('+30 days')),
            ]);

            session()->regenerate();
            session()->set([
                'user_id'    => $user['id'],
                'firstname'  => $user['firstname'],
                'lastname'   => $user['lastname'],
                'email'      => $user['email'],
                'role'       => $user['role'],
                'isAdmin'    => (bool) $user['is_admin'],
                'avatar'     => $user['avatar'] ?? null,
                'isLoggedIn' => true,
            ]);

            service('response')->setCookie([
                'name'     => 'remember_token',
                'value'    => $newToken,
                'expire'   => 30 * 24 * 60 * 60,
                'httponly' => true,
                'secure'   => (ENVIRONMENT === 'production'),
                'samesite' => 'Strict',
            ]);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}