<?php

namespace App\Controllers;

use \CodeIgniter\HTTP\RedirectResponse;

use App\Libraries\MailerExample;
use \App\Models\UserModel;
use \App\Models\CityModel;
use DateTime;
use App\Services\GeocodingService;

/**
 * Contrôleur gérant l'authentification (Inscription, Connexion)
 */
class AuthController extends BaseController
{
    private UserModel $userModel;
    private CityModel $cityModel;

    protected GeocodingService $geocodingService;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->cityModel = new CityModel();
        $this->geocodingService = new GeocodingService();
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
            'title' => 'Connexion'
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
            'cityName'    => 'required|min_length[2]',
            'postalCode'  => 'required|exact_length[5]|numeric',
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
            // Messages pour la ville
            'cityName' => [
                'required'   => 'La ville est obligatoire.',
                'min_length' => 'Le nom de la ville est trop court.',
            ],
            'postalCode' => [
                'required'     => 'Le code postal est obligatoire.',
                'exact_length' => 'Le code postal doit comporter exactement 5 chiffres.',
                'numeric'      => 'Le code postal doit être numérique.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $birthDateStr = $this->request->getPost('birthDate');
        $birthDateObj = new DateTime($birthDateStr);
        $today        = new DateTime();

        // Calcul de la différence (l'âge)
        $age = $today->diff($birthDateObj)->y;

        // Vérification de la majorité
        if ($age < 18) {
            return redirect()->back()->withInput()->with('errors', [
                'birthDate' => 'Vous devez avoir au moins 18 ans pour vous inscrire.'
            ]);
        }

        // l'année de naissance ne peut pas être antérieure à 1920
        if ((int)$birthDateObj->format('Y') < 1920) {
            return redirect()->back()->withInput()->with('errors', [
                'birthDate' => 'Veuillez saisir une date de naissance réaliste.'
            ]);
        }

        // Récupération des données liées à la ville depuis le formulaire
        $cityName = $this->request->getPost('cityName');
        $zipCode  = $this->request->getPost('postalCode');

        // Gestion de la table 'cities' (Ville)

        $cityNameChecked = $this->geocodingService->getCheckedCityName($cityName, $zipCode);

        if ($cityNameChecked === null) {
            return redirect()->back()->withInput()->with('errors', [
                'cityName' => 'Impossible de vérifier la ville. Veuillez réessayer.'
            ]);
        }

        if ($cityNameChecked === false) {
            return redirect()->back()->withInput()->with('errors', [
                'cityName' => 'La ville et le code postal ne correspondent pas à une commune valide.'
            ]);
        }

        // On remplace la saisie par la forme officielle avant insertion en BDD
        $cityName = $cityNameChecked;

        // Vérification si la ville existe ou pas dans la base pour éviter les doublons
        $cityId = $this->cityModel->findOrCreateCity($cityName, $zipCode);

        // Préparation des données de l'utilisateur
        $data = [
            'firstname'     => $this->request->getPost('firstName'),
            'lastname'      => $this->request->getPost('lastName'),
            'email'         => $this->request->getPost('email'),
            'gender'        => $this->request->getPost('gender'),
            'birth_date'    => $this->request->getPost('birthDate'),
            'is_student'    => $this->request->getPost('isStudent') === 'on' ? 1 : 0,
            'password_hash' => $this->request->getPost('password'),
            'city_id'       => $cityId,
        ];

        // Tentative de sauvegarde de l'utilisateur via le Model
        if (!$this->userModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->userModel->errors());
        }

        // Notification aux admins
        $admins = $this->userModel->getActiveAdmins();
        $emailBody = view('Emails/newRegistration', [
            'firstname' => $data['firstname'],
            'lastname'  => $data['lastname'],
            'email'     => $data['email'],
        ]);
        $mailer = new MailerExample();
        foreach ($admins as $admin) {
            $mailer->sendHtml($admin['email'], 'Nouvelle demande d\'inscription', $emailBody);
        }

        // Redirection vers la page de connexion avec un message flash
        return redirect()->to('/login')->with('success', 'Inscription reçue ! Notre équipe va examiner votre demande et vous recevrez une réponse par email.');
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
            return redirect()->back()->withInput()->with('error', 'Trop de tentatives de connexion. Veuillez patienter 1 minute avant de réessayer.');
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // On cherche l'utilisateur par son email
        $user = $this->userModel->findByEmail($email);

        if ($user) {
            //  Vérification du bannissement
            if ((bool)$user['is_banned'] === true) {
                return redirect()->back()->withInput()->with('error', 'Votre compte a été suspendu par l\'équipe de modération.');
            }
            //  Vérification du status
            if ($user['status'] === 'pending') {
                return redirect()->back()->withInput()->with('error', 'Votre inscription est en cours de validation par un administrateur. Vous recevrez un e-mail dès qu\'elle sera acceptée.');
            }
            //  Vérification si l'utilisateur a été rejeté
            if ($user['status'] === 'rejected') {
                return redirect()->back()->withInput()->with('error', 'Désolé, votre demande d\'inscription sur la plateforme n\'a pas été validée.');
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
                    $this->userModel->setRememberToken($user['id'], $token);

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
                return redirect()->back()->withInput()->with('error', 'Identifiants invalides.');
            }
        } else {
            // Email non trouvé
            return redirect()->back()->withInput()->with('error', 'Identifiants invalides.');
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
        $userId = session()->get('user_id');
        if ($userId) {
            $user = $this->userModel->find($userId);
            if ($user && $user['remember_token'] !== null) {
                $this->userModel->clearRememberToken($userId);
            }
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