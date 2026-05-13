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

// Route journeys
$routes->get('journeys', 'Journeys\JourneysController::show');
$routes->get('journeys/new', 'Journeys\JourneysController::new');
$routes->post('journeys', 'Journeys\JourneysController::store');

// Mot de passe oublié
$routes->get('forgotPassword', 'AuthController::showForgotPassword');
$routes->post('forgotPassword', 'AuthController::handleForgotPassword');

// Réinitialisation du mot de passe
$routes->get('resetPassword',   'AuthController::showResetPassword');
$routes->post('resetPassword', 'AuthController::handleResetPassword'); 
