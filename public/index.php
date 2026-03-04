<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Framework\Router;
use App\Framework\Response;
use App\Framework\Auth;
use App\Framework\Flash;
use App\Framework\SessionManager;

SessionManager::start();

$router = new Router();

$webRoutes   = require __DIR__ . '/../routes/web.php';
$adminRoutes = require __DIR__ . '/../routes/admin.php';

foreach ([$webRoutes, $adminRoutes] as $routes) {
    foreach ($routes as $route) {
        [$httpMethod, $path, $handler] = $route;
        $router->add($httpMethod, $path, $handler);
    }
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri    = $_SERVER['REQUEST_URI'] ?? '/';
$path   = (string)(parse_url($uri, PHP_URL_PATH) ?? '/');

if ($path !== '/' && str_ends_with($path, '/')) {
    $path = rtrim($path, '/');
}

/**
 * SERVER-SIDE ADMIN GUARD
 * - blocks /admin/* unless user is admin
 * - allows /login and /logout (which are NOT /admin anyway)
 */
if (str_starts_with($path, '/admin')) {
    if (!\App\Framework\Auth::isAdmin()) {
        \App\Framework\Flash::set('error', 'Admin access required. Please log in.');
        \App\Framework\Response::redirect('/login')->send();
        exit;
    }
}


$response = $router->dispatch($method, $path);
$response->send();
