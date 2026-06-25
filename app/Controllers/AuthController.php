<?php

namespace App\Controllers;

use \CodeIgniter\HTTP\RedirectResponse;

use App\Libraries\MailerExample;
use \App\Models\UserModel;
use App\Models\RememberTokenModel;
use App\Models\NotificationPrefModel;
use DateTime;

/**
 * Contrôleur gérant l'authentification (Inscription, Connexion)
 */
class AuthController extends BaseController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Affiche le formulaire d'inscription
     * 
     * @return string
     */
    public function showRegisterForm()
    {
        return view(
            'Auth/register',
            [
                'title' => 'Inscription'
            ]

        );
    }

    /**
     * Affiche le formulaire de connexion
     * 
     * @return string
     */
    public function showLoginForm()
    {
        return view('Auth/login', [
            'title' => 'Connexion',
            'accountDeleted' => $this->request->getGet('deleted') === '1',
        ]);
    }

    /**
     * Traite les données envoyées par le formulaire d'inscription
     * 
     * Gère la création/récupération de la ville, l'enregistrement de l'utilisateur
     * et la redirection avec message de succès.
     * 
     * @return RedirectResponse
     */
    public function register()
    {
        $rules = [
            'email'        => 'required|valid_email',
            'password'    => 'required|min_length[8]|regex_match[/^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*?_~\-()]).*$/]',
            'passConfirm' => 'required|matches[password]',
            'birthDate'   => 'required|valid_date[Y-m-d]',
        ];

        $messages = [
            'email' => [
                'required'    => 'L\'adresse email est obligatoire.',
                'valid_email' => 'Veuillez saisir une adresse email valide.',
            ],
            'password' => [
                'required'    => 'Le mot de passe est obligatoire.',
                'min_length'  => 'Le mot de passe doit faire au moins 8 caractères.',
                'regex_match' => 'Le mot de passe doit contenir au moins : une majuscule, un chiffre et un caractère spécial (ex: @, #, !, $).',
            ],
            'passConfirm' => [
                'required' => 'Veuillez confirmer votre mot de passe.',
                'matches'  => 'La confirmation ne correspond pas au mot de passe saisi.',
            ],
            'birthDate' => [
                'required'   => 'La date de naissance est obligatoire.',
                'valid_date' => 'Veuillez saisir une date de naissance valide.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->to('/register')->withInput()->with('errors', $this->validator->getErrors());
        }

        $birthDateStr = $this->request->getPost('birthDate');
        $birthDateObj = new DateTime($birthDateStr);
        $today        = new DateTime();

        // Calcul de la différence (l'âge)
        $age = $today->diff($birthDateObj)->y;

        // Vérification de la majorité
        if ($age < 18) {
            return redirect()->to('/register')->withInput()->with('errors', [
                'birthDate' => 'Vous devez avoir au moins 18 ans pour vous inscrire.'
            ]);
        }

        // l'année de naissance ne peut pas être antérieure à 1920
        if ((int)$birthDateObj->format('Y') < 1920) {
            return redirect()->to('/register')->withInput()->with('errors', [
                'birthDate' => 'Veuillez saisir une date de naissance réaliste.'
            ]);
        }

        // Préparation des données de l'utilisateur
        $data = [
            'firstname'     => $this->request->getPost('firstName'),
            'lastname'      => $this->request->getPost('lastName'),
            'email'         => $this->request->getPost('email'),
            'gender'        => $this->request->getPost('gender'),
            'birth_date'    => $this->request->getPost('birthDate'),
            'is_student'    => 1,
            'password_hash' => $this->request->getPost('password'),
        ];

        // Tentative de sauvegarde de l'utilisateur via le Model
        if (!$this->userModel->save($data)) {
            return redirect()->to('/register')->withInput()->with('errors', $this->userModel->errors());
        }

        $userId = $this->userModel->getInsertID();

        // Génération et stockage du token de vérification d'email
        $token = bin2hex(random_bytes(32));
        $this->userModel->setEmailToken($userId, $token);

        // Envoi de l'email de confirmation à l'utilisateur
        $verifyLink = base_url('verifyEmail?token=' . $token);
        $emailBody  = view('Emails/emailVerification', [
            'firstname'  => $data['firstname'],
            'verifyLink' => $verifyLink,
        ]);
        $mailer = new MailerExample();
        $mailer->sendHtml($data['email'], 'Confirmez votre adresse email — Kenweturi', $emailBody);

        // Redirection vers la page de connexion avec un message flash
        return redirect()->to('/login')->with('success', 'Inscription reçue ! Consultez votre boîte mail pour confirmer votre adresse email.');
    }

    /**
     * Gère la tentative de connexion de l'utilisateur (authentification)
     * 
     * Vérifie l'email, le mot de passe haché et initialise la session.
     * 
     * @return RedirectResponse
     */
    public function login()
    {
        $session = session();

        $throttler = service('throttler');
        if ($throttler->check(md5($this->request->getIPAddress() . 'login'), 10, MINUTE) === false) {
            return redirect()->to('/login')->withInput()->with('error', 'Trop de tentatives de connexion. Veuillez patienter 1 minute avant de réessayer.');
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // On cherche l'utilisateur par son email
        $user = $this->userModel->findByEmail($email);

        if ($user) {
            //  Vérification du bannissement
            if ((bool)$user['is_banned'] === true) {
                return redirect()->to('/login')->withInput()->with('error', 'Votre compte a été suspendu par l\'équipe de modération.');
            }
            //  Vérification de la confirmation email
            if ($user['status'] === 'unverified') {
                return redirect()->to('/login')->withInput()->with('error', 'Veuillez confirmer votre adresse email avant de vous connecter. Consultez votre boîte mail.');
            }
            //  Vérification du status
            if ($user['status'] === 'pending') {
                return redirect()->to('/login')->withInput()->with('error', 'Votre inscription est en cours de validation par un administrateur. Vous recevrez un e-mail dès qu\'elle sera acceptée.');
            }
            //  Vérification si l'utilisateur a été rejeté
            if ($user['status'] === 'rejected') {
                return redirect()->to('/login')->withInput()->with('error', 'Désolé, votre demande d\'inscription sur la plateforme n\'a pas été validée.');
            }
            // Vérification du mot de passe
            if (password_verify($password, $user['password_hash'])) {

                $sessionData = [
                    'user_id'   => $user['id'],
                    'firstname' => $user['firstname'],
                    'lastname'  => $user['lastname'],
                    'email'     => $user['email'],
                    'role'       => $user['role'],
                    'isAdmin'  => (bool) $user['is_admin'],
                    'avatar'     => $user['avatar'] ?? null,
                    'isLoggedIn' => true,
                    'userPassword' => $user['password_hash'],
                ];

                $session->regenerate();
                $session->set($sessionData);

                // Si l'utilisateur a coché "Se souvenir de moi"
                if ($this->request->getPost('rememberMe')) {
                    // Génération d'un token aléatoire sécurisé
                    $token = bin2hex(random_bytes(32));
                    // Durée de validité : 30 jours en secondes
                    $expiry = 30 * 24 * 60 * 60;

                    // Sauvegarde du token hashé en base (le token brut sert au cookie)
                    $tokenModel = new RememberTokenModel();
                    $tokenModel->insert([
                        'user_id'    => $user['id'],
                        'token'      => hash('sha256', $token),
                        'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days')),
                    ]);

                    // Redirection avec le cookie sécurisé
                    $redirectUrl = session()->get('redirect_url') ?? '/';
                    session()->remove('redirect_url');
                    return redirect()->to($redirectUrl)
                        ->with('success', 'Ravi de vous revoir, ' . $user['firstname'] . ' !')
                        ->setCookie([
                            'name'     => 'remember_token',
                            'value'    => $token,
                            'expire'   => $expiry,
                            'httponly' => true,
                            'secure'   => (ENVIRONMENT === 'production'),
                            'samesite' => 'Strict',
                        ]);
                }
                // Redirection vers l'URL d'origine (ou l'accueil par défaut)
                $redirectUrl = session()->get('redirect_url') ?? '/';
                session()->remove('redirect_url');
                return redirect()->to($redirectUrl)->with('success', 'Ravi de vous revoir, ' . $user['firstname'] . ' !');
            } else {
                // Mauvais mot de passe
                return redirect()->to('/login')->withInput()->with('error', 'Identifiants invalides.');
            }
        } else {
            // Email non trouvé
            return redirect()->to('/login')->withInput()->with('error', 'Identifiants invalides.');
        }
    }

    /**
     * Déconnecte l'utilisateur et détruit la session
     * 
     * @return RedirectResponse
     */
    public function logout()
    {
        // Suppression du token en base
        $tokenModel = new RememberTokenModel();
        $token = $this->request->getCookie('remember_token');

        if ($token) {
            $tokenModel->deleteOne($token);
        }

        session()->destroy();
        return redirect()->to('/login')
            ->with('success', 'Vous avez été déconnecté.')
            ->setCookie([
                'name'     => 'remember_token',
                'value'    => '',
                'expire'   => -1,
                'httponly' => true,
                'secure'   => (ENVIRONMENT === 'production'),
                'samesite' => 'Strict',
            ]);
    }

    /**
     * Valide le token de confirmation d'email et fait passer le compte en 'pending'
     *
     * @return RedirectResponse
     */
    public function verifyEmail(): RedirectResponse
    {
        $token = $this->request->getGet('token');

        if (!$token) {
            return redirect()->to('/login')->with('error', 'Lien de vérification invalide.');
        }

        $user = $this->userModel->findByValidEmailToken($token);

        if (!$user) {
            return redirect()->to('/login')->with('error', 'Ce lien de confirmation est invalide ou a expiré. Veuillez vous réinscrire.');
        }

        if ($user['status'] !== 'unverified') {
            return redirect()->to('/login')->with('success', 'Votre adresse email est déjà confirmée.');
        }

        // Passage en 'pending' et suppression du token
        $this->userModel->update($user['id'], [
            'status'             => 'pending',
            'email_token'        => null,
            'email_token_expiry' => null,
        ]);

        // Notification aux admins (selon leur préférence)
        $admins         = $this->userModel->getActiveAdmins();
        $notifPrefModel = new NotificationPrefModel();
        $mailer         = new MailerExample();
        foreach ($admins as $admin) {
            if (!$notifPrefModel->wantsNotif((int) $admin['id'], 'admin_registration')) continue;
            $adminId   = (int) $admin['id'];
            $emailBody = view('Emails/newRegistration', [
                'firstname'      => $user['firstname'],
                'lastname'       => $user['lastname'],
                'email'          => $user['email'],
                'prefLabel'      => NotificationPrefModel::PREFS['admin_registration'],
                'unsubscribeUrl' => site_url('unsubscribe?uid=' . $adminId . '&pref=admin_registration&token=' . UserModel::unsubscribeToken($adminId, 'admin_registration')),
                'preferencesUrl' => site_url('profile/notifications'),
            ]);
            $mailer->sendHtml($admin['email'], 'Nouvelle demande d\'inscription', $emailBody);
        }

        return redirect()->to('/login')->with('success', 'Adresse email confirmée ! Notre équipe va examiner votre demande et vous recevrez une réponse par email.');
    }

    /**
     * Affiche la page de mot de passe oublié
     *
     * @return string
     */
    public function showForgotPasswordForm()
    {

        return view('Auth/forgotPassword', [
            'title' => 'Mot de passe oublié'
        ]);
    }

    /**
     * Traite l'email soumis et envoie le lien de réinitialisation
     * 
     * @return RedirectResponse
     */
    public function forgotPassword()
    {
        $throttler = service('throttler');
        if ($throttler->check(md5($this->request->getIPAddress() . 'forgotpwd'), 3, MINUTE) === false) {
            return redirect()->back()->with('success', 'Si un compte existe avec cet email, vous recevrez un lien de réinitialisation.');
        }

        $email = $this->request->getPost('email');

        $user = $this->userModel->findByEmail($email);

        if (!$user || $user['deleted_at'] !== null) {
            return redirect()->back()->withInput()->with('success', 'Si un compte existe avec cet email, vous recevrez un lien de réinitialisation.');
        }

        // Génération du token unique
        $token = bin2hex(random_bytes(32));

        // Sauvegarde du token hashé en base (le token brut part uniquement dans l'email)
        $this->userModel->setResetToken($user['id'], $token);

        // Envoi de l'email
        $resetLink = base_url('resetPassword?token=' . $token);

        $emailBody = view('Emails/resetPassword', [
            'firstname' => $user['firstname'],
            'resetLink' => $resetLink,
        ]);

        try {
            $mail = \Config\Services::mailer();
            $mail->addAddress($user['email']);
            $mail->Subject = 'Réinitialisation de votre mot de passe — Kenweturi';
            $mail->Body    = $emailBody;
            $mail->send();
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            log_message('error', 'Mailer error: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Si un compte existe avec cet email, vous recevrez un lien de réinitialisation.');
    }

    /**
     * Affiche le formulaire de réinitialisation du mot de passe
     * 
     * @return string
     */
    public function showResetPasswordForm()
    {
        $token = $this->request->getGet('token');

        if (!$token) {
            return redirect()->to('/forgotPassword')->with('error', 'Lien invalide.');
        }

        $user = $this->userModel->findByValidResetToken($token);

        if (!$user) {
            return redirect()->to('/forgotPassword')->with('error', 'Ce lien est invalide ou a expiré.');
        }

        return view('Auth/resetPassword', [
            'token'      => $token,
            'tokenValid' => true,
            'title'      => 'Réinitialisation du mot de passe'
        ]);
    }

    /**
     * Traite le nouveau mot de passe soumis
     * 
     * @return RedirectResponse
     */
    public function resetPassword()
    {
        $token = $this->request->getPost('token');

        $rules = [
            'password'        => 'required|min_length[8]|regex_match[/^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*?_~\-()]).*$/]',
            'confirmPassword' => 'required|matches[password]',
        ];
        $messages = [
            'password' => [
                'required'    => 'Le mot de passe est obligatoire.',
                'min_length'  => 'Le mot de passe doit faire au moins 8 caractères.',
                'regex_match' => 'Le mot de passe doit contenir au moins : une majuscule, un chiffre et un caractère spécial.',
            ],
            'confirmPassword' => [
                'required' => 'Veuillez confirmer votre mot de passe.',
                'matches'  => 'La confirmation ne correspond pas au mot de passe saisi.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $password = $this->request->getPost('password');

        $user = $this->userModel->findByValidResetToken($token);

        if (!$user) {
            return redirect()->to('/forgotPassword')->with('error', 'Ce lien est invalide ou a expiré.');
        }

        // Réinitialise le mot de passe et invalide tous les tokens associés
        $this->userModel->resetPassword($user['id'], $password);

        if (session()->has('isLoggedIn') && session()->get('user_id') == $user['id']) {
            $updatedUser = $this->userModel->find($user['id']);
            session()->set('userPassword', $updatedUser['password_hash']);
        }

        $tokenModel = new RememberTokenModel();
        $tokenModel->deleteAll($user['id']);

        $mailer = new MailerExample();
        $mailer->sendHtml(
            $user['email'],
            'Votre mot de passe a été modifié',
            view('Emails/passwordChanged', [
                'firstname' => $user['firstname'],
                'lastname'  => $user['lastname'],
                'date'      => ucfirst(\CodeIgniter\I18n\Time::now('Europe/Paris', 'fr_FR')->toLocalizedString('d MMMM yyyy à HH:mm')),
                'support'   => env('mailer.from'),
            ])
        );

        return redirect()->to('/login')->with('success', 'Mot de passe réinitialisé avec succès !');
    }
}
