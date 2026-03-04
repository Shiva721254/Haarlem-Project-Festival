<?php
declare(strict_types=1);

// Load environment variables from .env file
if (file_exists(__DIR__ . '/../.env')) {
    $env_lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($env_lines as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue; // Skip comments
        }
        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $key = trim($key);
        $value = trim($value);
        if ($key && !isset($_ENV[$key])) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

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
