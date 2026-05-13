<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
/* Page d'accueil */
$routes->get('/', 'Home::index');

/* Inscription */
$routes->get('/register', 'AuthController::showRegister');
$routes->post('/register', 'AuthController::handleRegister');

// Route pour afficher de la page login et traitement du formulaire
$routes->get('login', 'AuthController::showLogin');
$routes->post('login', 'AuthController::handleLogin');

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
$routes->get('forgotPassword', 'AuthController::showForgotPassword');
$routes->post('forgotPassword', 'AuthController::handleForgotPassword');

// Réinitialisation du mot de passe
$routes->get('resetPassword',   'AuthController::showResetPassword');
$routes->post('resetPassword', 'AuthController::handleResetPassword'); 
