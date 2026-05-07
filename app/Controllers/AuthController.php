<?php

namespace App\Controllers;

use \App\Models\UserModel;
use DateTime;

/**
 * Contrôleur gérant l'authentification (Inscription, Connexion)
 */
class AuthController extends BaseController
{
    /**
     * Affiche le formulaire d'inscription
     * 
     * @return string
     */
    public function register()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/');
        }
        return view('Auth/register');
    }

    /**
     * Affiche le formulaire de connexion
     * 
     * @return string
     */
    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/');
        }
        return view('Auth/login');
    }

    /**
     * Traite les données envoyées par le formulaire d'inscription
     * 
     * Gère la création/récupération de la ville, l'enregistrement de l'utilisateur
     * et la redirection avec message de succès.
     * 
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function handleRegister()
    {
        $rules = [
            'email'        => 'required|valid_email',
            'password'     => 'required|min_length[8]|regex_match[/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/]',
            'passConfirm' => 'required|matches[password]',
            'birthDate'   => 'required|valid_date[Y-m-d]',
        ];

        $messages = [
            'passConfirm' => [
                'matches'  => 'Les mots de passe ne correspondent pas.',
                'required' => 'Veuillez confirmer votre mot de passe.'
            ],
            'password' => [
                'required'    => 'Le mot de passe est obligatoire.',
            'min_length'  => 'Le mot de passe doit faire au moins 8 caractères.',
            'regex_match' => 'Le mot de passe doit contenir au moins un caractère spécial (ex: @, #, !, $).'
            ]
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

        // Initialisation de la connexion à la base de données et du modèle utilisateur
        $db = \Config\Database::connect();
        $userModel = new UserModel();

        // Récupération des données liées à la ville depuis le formulaire
        $cityName = $this->request->getPost('cityName');
        $zipCode  = $this->request->getPost('postalCode');

        // Gestion de la table 'cities' (Ville)
        // On vérifie si la ville existe déjà pour éviter les doublons
        $cityBuilder = $db->table('city');
        $existingCity = $cityBuilder->getWhere(['name' => $cityName])->getRow();

        // Si elle existe, on récupère son ID existant
        if ($existingCity) {
            $cityId = $existingCity->id;
        } else {
            // Si elle n'existe pas, on l'ajoute dans la table 'cities'
            $cityBuilder->insert([
                'name' => $cityName,
                'zipcode' => $zipCode
            ]);

            // On récupère l'ID généré
            $cityId = $db->insertID();
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
        if (!$userModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $userModel->errors());
        }

        // Redirection vers la page de connexion avec un message flash
        return redirect()->to('/login')->with('success', 'Compte créé avec succès !');
    }

    /**
     * Gère la tentative de connexion de l'utilisateur (authentification)
     * 
     * Vérifie l'email, le mot de passe haché et initialise la session.
     * 
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function handleLogin()
    {
        $session = session();
        $userModel = new UserModel();

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // On cherche l'utilisateur par son email
        $user = $userModel->where('email', $email)->first();

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
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'Vous avez été déconnecté.');
    }
}
