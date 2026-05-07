<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('api', ['namespace' => 'App\Controllers\Api'], function($routes) {
    // Auth Routes
    $routes->post('auth/register', 'AuthController::register');
    $routes->post('auth/login', 'AuthController::login');
    $routes->post('auth/logout', 'AuthController::logout');
    $routes->get('auth/me', 'AuthController::me');

    // User Routes
    $routes->get('users/search', 'UserController::search');
    $routes->get('users/(:any)', 'UserController::show/$1');

    // Protected Routes (require auth filter)
    $routes->group('', ['filter' => 'auth'], function($routes) {
        // Profile Update
        $routes->post('users/update', 'UserController::updateProfile');

        // Posts
        $routes->get('posts', 'PostController::index');
        $routes->post('posts', 'PostController::create');
        $routes->get('posts/(:num)', 'PostController::show/$1');
        $routes->delete('posts/(:num)', 'PostController::delete/$1');

        // Social
        $routes->post('posts/(:num)/like', 'SocialController::toggleLike/$1');
        $routes->post('posts/(:num)/comment', 'SocialController::addComment/$1');
        $routes->post('posts/(:num)/repost', 'SocialController::repost/$1');

        // Network
        $routes->post('users/(:num)/follow', 'NetworkController::toggleFollow/$1');
        $routes->post('users/(:num)/block', 'NetworkController::toggleBlock/$1');

        // Stats
        $routes->get('users/(:num)/stats', 'UserController::getStats/$1');
    });
});
