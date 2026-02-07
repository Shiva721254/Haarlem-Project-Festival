<?php
declare(strict_types=1);
session_start();


require_once __DIR__ . '/../vendor/autoload.php';

use App\Framework\Router;
use App\Framework\Response;

$router = new Router();

$webRoutes   = require __DIR__ . '/../src/routes/web.php';
$adminRoutes = require __DIR__ . '/../src/routes/admin.php';

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

$response = $router->dispatch($method, $path);
$response->send();
