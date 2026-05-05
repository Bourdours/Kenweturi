<?php

namespace App\Controllers;

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
        return view('Auth/register');
    }

     /**
     * Affiche le formulaire de connexion
     * 
     * @return string
     */
    public function login()
    {
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
    // Initialisation de la connexion à la base de données et du modèle utilisateur
    $db = \Config\Database::connect();
    $userModel = new \App\Models\UserModel();

    // Récupération des données liées à la ville depuis le formulaire
    $cityName = $this->request->getPost('cityName');
    $zipCode  = $this->request->getPost('zipCode');

    // Gestion de la table 'cities' (Ville)
    // On vérifie si la ville existe déjà pour éviter les doublons
    $cityBuilder = $db->table('cities');
    $existingCity = $cityBuilder->getWhere(['city' => $cityName])->getRow();

    // Si elle existe, on récupère son ID existant
    if ($existingCity) {
        $cityId = $existingCity->id;
    } else {
       // Si elle n'existe pas, on l'ajoute dans la table 'cities'
        $cityBuilder->insert([
            'city' => $cityName,
            'zipcode' => $zipCode
        ]);

        // On récupère l'ID généré
        $cityId = $db->insertID(); 
    }

    // Préparation des données de l'utilisateur
    $data = [
        'firstname'     => $this->request->getPost('firstname'),
        'lastname'      => $this->request->getPost('lastname'),
        'email'         => $this->request->getPost('email'),
        'gender'        => $this->request->getPost('gender'),
        'birth_date'    => $this->request->getPost('birth_date'),
        'password_hash' => $this->request->getPost('password'), 

        'user_status_id' => 1, 
        'is_admin'       => 0,
        'city_id' => $cityId
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
    $userModel = new \App\Models\UserModel();

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
                'isLoggedIn'=> true,
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