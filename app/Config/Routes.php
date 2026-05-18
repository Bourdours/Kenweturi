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

// Routes journeys
$routes->get('journeys',                 'JourneysController::showAll');
$routes->get('journeys/new',             'JourneysController::showCreateForm');
$routes->post('journeys/new',            'JourneysController::create');
$routes->get('journeys/(:num)',          'JourneysController::show/$1');
$routes->post('journeys/(:num)/cancel',  'JourneysController::cancel/$1');
$routes->post('journeys/(:num)/delete',  'JourneysController::delete/$1');

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

$routes->post('profile/delete', 'UserController::delete');