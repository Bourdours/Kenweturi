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

// Routes dashboard
$routes->get('dashboard/journeys',  'DashboardController::showJourneys');
$routes->get('dashboard/bookings',  'DashboardController::showBookings');
$routes->get('dashboard/reports',   'DashboardController::showReports');
$routes->get('dashboard/reports/(:num)', 'DashboardController::showReport/$1');
$routes->get('dashboard',           'DashboardController::show');

// Routes bookings
$routes->post('dashboard/bookings/(:num)/delete', 'BookingController::delete/$1');
$routes->post('dashboard/bookings/(:num)/accept', 'BookingController::accept/$1');
$routes->post('dashboard/bookings/(:num)/reject', 'BookingController::reject/$1');

// Routes journeys
$routes->get('journeys',                 'JourneyController::showAll');
$routes->get('journeys/new',             'JourneyController::showCreateForm');
$routes->post('journeys/new',            'JourneyController::create');
$routes->get('journeys/(:num)',          'JourneyController::show/$1');
$routes->post('journeys/(:num)/book',    'JourneyController::book/$1');
$routes->post('journeys/(:num)/cancel',  'JourneyController::cancel/$1');
$routes->post('journeys/(:num)/delete',  'JourneyController::delete/$1');

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
$routes->get('profile/edit', 'UserController::showEditForm');
$routes->post('car/create', 'CarController::create'); // Ajouter un véhicule

// Pages légales
$routes->get('cgu',              'LegalController::cgu');
$routes->get('confidentialite',  'LegalController::confidentialite');
$routes->get('mentions-legales', 'LegalController::mentions');

// Pages entreprise
$routes->get('a-propos',          'PageController::about');
$routes->get('comment-ca-marche', 'PageController::howItWorks');
$routes->get('blog',              'PageController::blog');
$routes->get('contact',           'PageController::contact');
$routes->post('contact',          'PageController::sendContact');
