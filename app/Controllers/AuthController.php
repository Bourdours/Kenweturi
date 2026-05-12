<?php

namespace App\Controllers;

use \CodeIgniter\HTTP\RedirectResponse;

use \App\Models\UserModel;
use \App\Models\CityModel;
use App\Libraries\MailerExample;
use DateTime;

/**
 * Contrôleur gérant l'authentification (Inscription, Connexion)
 */
class AuthController extends BaseController
{
    private UserModel $userModel;
    private CityModel $cityModel;

    public function __construct()
    {

        $this->userModel = new UserModel();
        $this->cityModel = new CityModel();
    }

    /**
     * Affiche le formulaire d'inscription
     * 
     * @return string
     */
    public function showRegister()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/');
        }
        return view('Auth/register', [
             'title' => 'Inscription'
        ]
        
        );
    }

    /**
     * Affiche le formulaire de connexion
     * 
     * @return string
     */
    public function showLogin()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/');
        }
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
    public function handleRegister()
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
        // On vérifie si la ville existe déjà pour éviter les doublons
        $existingCity = $this->cityModel->where('name', $cityName)->first();

        // Si elle existe, on récupère son ID existant
        if ($existingCity) {
            $cityId = $existingCity['id'];
        } else {
            // Si elle n'existe pas, on l'ajoute dans la table 'cities'
            $this->cityModel->insert([
                'name' => $cityName,
                'zipcode' => $zipCode
            ]);

            // On récupère l'ID généré
            $cityId = $this->cityModel->insertID();
        }

        // Préparation des données de l'utilisateur
        $data = [
            'firstname'     => $this->request->getPost('firstName'),
            'lastname'      => $this->request->getPost('lastName'),
            'email'         => $this->request->getPost('email'),
            'gender'        => $this->request->getPost('gender'),
            'birth_date'    => $this->request->getPost('birthDate'),
            'is_student'    => $this->request->getPost('isStudent') === 'on' ? 1 : 0,
            'password_hash' => $this->request->getPost('password'),
            'city_id'       => $cityId
        ];

        // Tentative de sauvegarde de l'utilisateur via le Model
        if (!$this->userModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->userModel->errors());
        }

        // Redirection vers la page de connexion avec un message flash
        return redirect()->to('/login')->with('success', 'Compte créé avec succès !');
    }

    /**
     * Gère la tentative de connexion de l'utilisateur (authentification)
     * 
     * Vérifie l'email, le mot de passe haché et initialise la session.
     * 
     * @return RedirectResponse
     */
    public function handleLogin()
    {
        $session = session();

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // On cherche l'utilisateur par son email
        $user = $this->userModel->where('email', $email)->first();

        if ($user) {
            // Vérification du mot de passe 
            if (password_verify($password, $user['password_hash'])) {

                $sessionData = [
                    'user_id'   => $user['id'],
                    'firstname' => $user['firstname'],
                    'lastname'  => $user['lastname'],
                    'email'     => $user['email'],
                    'is_admin'  => $user['is_admin'],
                    'isLoggedIn' => true,
                ];

                $session->set($sessionData);

                // Redirection vers l'accueil avec un message de bienvenue
                return redirect()->to('/')->with('success', 'Ravi de vous revoir, ' . $user['firstname'] . ' !');
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
        session()->destroy();
        return redirect()->to('/login')->with('success', 'Vous avez été déconnecté.');
    }

    /**
     * Affiche la page de mot de passe oublié
     * 
     * @return string
     */
    public function showForgotPassword()
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
    public function handleForgotPassword()
    {

        $email = $this->request->getPost('email');

        $user = $this->userModel->where('email', $email)->first();

        if (!$user) {
            return redirect()->back()->withInput()->with('success', 'Si un compte existe avec cet email, vous recevrez un lien de réinitialisation.');
        }

        // Génération du token unique
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Sauvegarde du token en base
        $this->userModel->update($user['id'], [
            'reset_token'        => $token,
            'reset_token_expiry' => $expiry,
        ]);

        // Envoi de l'email
        $resetLink = base_url('resetPassword?token=' . $token);

        $mailer = new MailerExample();
        $mailer->sendHtml(
            $user['email'],
            'Réinitialisation de votre mot de passe',
            '<p>Cliquez sur ce lien pour réinitialiser votre mot de passe : <a href="' . $resetLink . '">Réinitialiser</a></p>'
        );
    
        return redirect()->back()->with('success', 'Si un compte existe avec cet email, vous recevrez un lien de réinitialisation.');
    }

    /**
     * Affiche le formulaire de réinitialisation du mot de passe
     * 
     * @return string
     */
    public function showResetPassword()
    {
        $token = $this->request->getGet('token');

        if (!$token) {
            return redirect()->to('/forgotPassword')->with('error', 'Lien invalide.');
        }

        $user = $this->userModel
            ->where('reset_token', $token)
            ->where('reset_token_expiry >', date('Y-m-d H:i:s'))
            ->first();

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
    public function handleResetPassword()
    {
        $token           = $this->request->getPost('token');
        $password        = $this->request->getPost('password');
        $confirmPassword = $this->request->getPost('confirmPassword');

        if ($password !== $confirmPassword) {
            return redirect()->back()->with('error', 'Les mots de passe ne correspondent pas.');
        }

        $user = $this->userModel
            ->where('reset_token', $token)
            ->where('reset_token_expiry >', date('Y-m-d H:i:s'))
            ->first();

        if (!$user) {
            return redirect()->to('/forgotPassword')->with('error', 'Ce lien est invalide ou a expiré.');
        }

        $this->userModel->update($user['id'], [
            'password_hash'      => password_hash($password, PASSWORD_DEFAULT),
            'reset_token'        => null,
            'reset_token_expiry' => null,
        ]);

        return redirect()->to('/login')->with('success', 'Mot de passe réinitialisé avec succès !');
    }
}
