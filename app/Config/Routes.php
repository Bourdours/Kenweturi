<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
/* Page d'accueil */
$routes->get('/', 'Home::index');
/* Inscription */
$routes->get('/inscription', 'AuthController::register');
/* */
$routes->get('/login', 'AuthController::login');
$routes->post('/inscription', 'AuthController::handleRegister');
$routes->post('handleRegister', 'AuthController::handleRegister');
$routes->get('/test/(:num)/test/(:num)', 'Home::test/$1/$2');
