<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

/* =========================================================
 *  DEV ONLY — prévisualisation des emails
 * ========================================================= */
if (ENVIRONMENT === 'development') {
    $routes->get('dev/email/(:segment)', 'DevController::emailPreview/$1');
}

/* =========================================================
 *  ROUTES PUBLIQUES (accessibles à tous, connectés ou non)
 * ========================================================= */

// Page d'accueil
$routes->get('/', 'Home::index');

// Pages légales
$routes->get('cgu',              'LegalController::cgu');
$routes->get('confidentialite',  'LegalController::confidentialite');
$routes->get('mentions-legales', 'LegalController::mentions');

// Pages de présentation / entreprise
$routes->get('a-propos',          'PageController::about');
$routes->get('comment-ca-marche', 'PageController::howItWorks');
$routes->get('blog',              'PageController::blog');
$routes->get('contact',           'PageController::contact');
$routes->post('contact',          'PageController::sendContact');


/* =========================================================
 *  ROUTES INVITÉS (interdites aux utilisateurs connectés)
 * ========================================================= */
$routes->group('', ['filter' => 'guest'], function ($routes) {

    // Inscription
    $routes->get('register',  'AuthController::showRegisterForm');
    $routes->post('register', 'AuthController::register');

    // Connexion
    $routes->get('login',  'AuthController::showLoginForm');
    $routes->post('login', 'AuthController::login');

    // Mot de passe oublié
    $routes->get('forgotPassword',  'AuthController::showForgotPasswordForm');
    $routes->post('forgotPassword', 'AuthController::forgotPassword');

    // Réinitialisation du mot de passe
    $routes->get('resetPassword',  'AuthController::showResetPasswordForm');
    $routes->post('resetPassword', 'AuthController::resetPassword');
});


/* =========================================================
 *  ROUTES PROTÉGÉES (connexion obligatoire)
 * ========================================================= */
$routes->group('', ['filter' => 'auth'], function ($routes) {

    // Déconnexion
    $routes->get('logout', 'AuthController::logout');

    // Dashboard
    $routes->get('dashboard',                 'DashboardController::show');
    $routes->get('dashboard/journeys',        'DashboardController::showJourneys');
    $routes->get('dashboard/bookings',        'DashboardController::showBookings');
    $routes->get('dashboard/bookings/(:num)', 'DashboardController::showBooking/$1');
    $routes->get('dashboard/reports',                    'DashboardController::showReports');
    $routes->get('dashboard/reports/(:num)',             'DashboardController::showReport/$1');
    $routes->get('dashboard/journey-requests',           'DashboardController::showJourneyRequests');
    $routes->get('dashboard/journey-requests/(:num)',    'DashboardController::showJourneyRequest/$1');

    // Bookings
    $routes->post('dashboard/bookings/(:num)/delete', 'BookingController::delete/$1');
    $routes->post('dashboard/bookings/(:num)/accept', 'BookingController::accept/$1');
    $routes->post('dashboard/bookings/(:num)/reject', 'BookingController::reject/$1');
    $routes->post('journeys/(:num)/book',             'BookingController::create/$1');

    // Journeys
    $routes->get('journeys',                'JourneyController::showAll');
    $routes->get('journeys/new',            'JourneyController::showCreateForm');
    $routes->post('journeys/new',           'JourneyController::create');
    $routes->get('journeys/(:num)',         'JourneyController::show/$1');
    $routes->post('journeys/(:num)/cancel', 'JourneyController::cancel/$1');
    $routes->post('journeys/(:num)/delete', 'JourneyController::delete/$1');

    // Journey Requests
    $routes->get('journey-requests',                'JourneyRequestController::showAll');
    $routes->get('journey-requests/new',            'JourneyRequestController::showCreateForm');
    $routes->post('journey-requests/new',           'JourneyRequestController::create');
    $routes->get('journey-requests/(:num)',         'JourneyRequestController::show/$1');
    $routes->get('journey-requests/(:num)/edit',    'JourneyRequestController::showEditForm/$1');
    $routes->post('journey-requests/(:num)/edit',   'JourneyRequestController::update/$1');
    $routes->post('journey-requests/(:num)/cancel', 'JourneyRequestController::delete/$1');

    // Profile
    $routes->get('profile',         'UserController::show');
    $routes->get('profile/edit',    'UserController::showEditForm');
    $routes->get('profile/update',  'UserController::showEditForm');
    $routes->post('profile/update', 'UserController::update');
    $routes->post('profile/delete', 'UserController::delete');

    // Users
    $routes->get('users/(:num)', 'UserController::show/$1');

    // Reports
    $routes->get('journeys/(:num)/report',  'ReportController::showCreateForm/$1');
    $routes->post('journeys/(:num)/report', 'ReportController::create/$1');

    // Cars
    $routes->post('car/create',        'CarController::create');
    $routes->get('car/(:num)/edit',    'CarController::showEditForm/$1');
    $routes->post('car/(:num)/update', 'CarController::update/$1');
    $routes->post('car/(:num)/delete', 'CarController::delete/$1');
});


/* =========================================================
 *  ROUTES ADMIN (réservées aux administrateurs)
 * ========================================================= */
$routes->group('admin', ['filter' => 'admin'], function ($routes) {
    $routes->get('/',                       'AdminController::index');
    $routes->get('reports/(:num)',          'AdminController::showReport/$1');
    $routes->post('reports/(:num)/resolve', 'AdminController::resolveReport/$1');
    $routes->post('users/(:num)/validate',  'AdminController::updateRegistration/$1');
    $routes->post('users/(:num)/delete',    'AdminController::deleteUser/$1');
});

/* =========================================================
 *  ROUTES SUPER-ADMIN (réservées aux super-administrateurs)
 * ========================================================= */
$routes->group('admin', ['filter' => 'superadmin'], function ($routes) {
    $routes->post('users/(:num)/role', 'AdminController::updateRole/$1');
});