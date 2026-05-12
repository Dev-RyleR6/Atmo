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
    $routes->get('feed', 'PostController::index');
    $routes->post('posts/create', 'PostController::create');
    $routes->post('posts/edit/(:num)', 'PostController::edit/$1');
    $routes->post('posts/delete/(:num)', 'PostController::delete/$1');
    $routes->post('posts/toggleLike/(:num)', 'PostController::toggleLike/$1');
    $routes->post('posts/addComment/(:num)', 'PostController::addComment/$1');
    $routes->post('posts/toggleRepost/(:num)', 'PostController::toggleRepost/$1');
    
    // Profile
    $routes->get('profile', 'UserController::profile');
    $routes->get('profile/(:segment)', 'UserController::profile/$1');
    $routes->post('profile/update', 'UserController::updateProfile');
    
    // Followers/Following
    $routes->get('followers', 'UserController::followers');
    $routes->get('followers/(:segment)', 'UserController::followers/$1');
    $routes->get('following', 'UserController::following');
    $routes->get('following/(:segment)', 'UserController::following/$1');
    
    // Social / Network
    $routes->post('users/toggleFollow/(:num)', 'NetworkController::toggleFollow/$1');
    $routes->get('users/search', 'UserController::search');
});

// API Routes
$routes->group('api', function($routes) {
    $routes->get('users/search', 'Api\UserController::search');
    $routes->get('users/(:segment)', 'Api\UserController::show/$1');
    $routes->get('users/followers/(:segment)', 'Api\UserController::followers/$1');
    $routes->get('users/following/(:segment)', 'Api\UserController::following/$1');
});
