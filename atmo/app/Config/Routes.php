<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Web Auth Routes
$routes->get('login', 'AuthController::loginView');
$routes->post('login', 'AuthController::loginAction');
$routes->get('register', 'AuthController::registerView');
$routes->post('register', 'AuthController::registerAction');
$routes->get('logout', 'AuthController::logoutAction');

// Protected Routes (require auth web filter)
$routes->group('', ['filter' => 'auth_web'], function($routes) {
    // Feed
    $routes->get('feed', 'PostController::index');
    $routes->post('posts/create', 'PostController::create');
    
    // Profile
    $routes->get('profile', 'UserController::profile');
    $routes->post('profile/update', 'UserController::updateProfile');
    
    // Social / Network
    $routes->post('users/toggleFollow/(:num)', 'NetworkController::toggleFollow/$1');
    $routes->get('users/search', 'UserController::search');
});
