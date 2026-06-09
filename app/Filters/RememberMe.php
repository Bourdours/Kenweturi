<?php

namespace App\Filters;

use App\Models\UserModel;
use App\Models\RememberTokenModel;
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


        $tokenModel = new RememberTokenModel();
        $tokenRow   = $tokenModel->findByToken($token);

        if (!$tokenRow) return;


        $userModel = new UserModel();
        $user      = $userModel->find($tokenRow['user_id']);

        if ($user && !$user['is_banned'] && $user['status'] === 'active') {

            $newToken = bin2hex(random_bytes(32));

            $tokenModel->deleteOne($token);

            $tokenModel->insert([
                'user_id'    => $user['id'],
                'token'      => hash('sha256', $newToken),
                'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days')),
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
                'userPassword' => $user['password_hash'],
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
