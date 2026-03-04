<?php
declare(strict_types=1);

/**
 * Bootstrap Configuration
 * 
 * Central initialization and configuration for the Haarlem Festival application.
 */

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

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../storage/logs/error.log');

// Application timezone
date_default_timezone_set('Europe/Amsterdam');

// Return router with registered routes
use App\Framework\Router;
use App\Framework\Response;
use App\Framework\Auth;
use App\Framework\Flash;

$router = new Router();

// Load routes
$webRoutes   = require __DIR__ . '/../routes/web.php';
$adminRoutes = require __DIR__ . '/../routes/admin.php';

// Register routes
foreach ([$webRoutes, $adminRoutes] as $routes) {
    foreach ($routes as $route) {
        [$httpMethod, $path, $handler] = $route;
        $router->add($httpMethod, $path, $handler);
    }
}

// Admin guard middleware
function guardAdminRoutes(string $path): ?Response
{
    if (str_starts_with($path, '/admin')) {
        if (!Auth::isAdmin()) {
            Flash::set('error', 'Admin access required. Please log in.');
            return Response::redirect('/login');
        }
    }
    return null;
}

return [
    'router' => $router,
    'guardAdminRoutes' => 'guardAdminRoutes',
];
