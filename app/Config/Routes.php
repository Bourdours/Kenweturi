<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
/* Page d'accueil */
$routes->get('/', 'Home::index');
/* Inscription */
$routes->get('/register', 'AuthController::register');
/* */
$routes->get('/login', 'AuthController::login');
$routes->post('/register', 'AuthController::handleRegister');
$routes->post('handleRegister', 'AuthController::handleRegister');

// Route pour afficher la page (déjà existante normalement)
$routes->get('login', 'AuthController::login');

// Route pour TRAITER le formulaire de connexion
$routes->post('login', 'AuthController::handleLogin');

// Route pour traiter la déconnexion
$routes->get('logout', 'AuthController::logout');
$routes->get('/test/(:num)/test/(:num)', 'Home::test/$1/$2');

$routes->get('journeys', 'Journeys\JourneysController::show');
$routes->get('journeys/new', 'Journeys\JourneysController::new');
$routes->post('journeys', 'Journeys\JourneysController::store');

// Mot de passe oublié
$routes->get('forgotPassword', 'AuthController::forgotPassword');
$routes->post('forgotPassword', 'AuthController::handleForgotPassword');

// Réinitialisation du mot de passe
$routes->get('resetPassword',   'AuthController::resetPassword');
$routes->post('resetPassword', 'AuthController::handleResetPassword'); 
