<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
/* Page d'accueil */
$routes->get('/', 'Home::index');

/* Inscription */
$routes->get('/register', 'AuthController::showRegisterForm');
$routes->post('/register', 'AuthController::register');

// Route pour afficher de la page login et traitement du formulaire
$routes->get('login', 'AuthController::showLoginForm');
$routes->post('login', 'AuthController::login');

// Route pour traiter la déconnexion
$routes->get('logout', 'AuthController::logout');

// Route journeys
$routes->get('journeys', 'Journeys\JourneysController::show');
$routes->get('journeys/new', 'Journeys\JourneysController::new');
$routes->post('journeys', 'Journeys\JourneysController::store');

// Mot de passe oublié
$routes->get('forgotPassword', 'AuthController::showForgotPasswordForm');
$routes->post('forgotPassword', 'AuthController::forgotPassword');

// Réinitialisation du mot de passe
$routes->get('resetPassword',   'AuthController::showResetPasswordForm');
$routes->post('resetPassword', 'AuthController::resetPassword'); 

// Profile
$routes->get('profile', 'UserController::show');
$routes->get('profile/update', 'UserController::showEditForm');
$routes->post('profile/update', 'UserController::update');