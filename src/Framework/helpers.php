<?php
declare(strict_types=1);

/**
 * Render a PHP view from src/Views.
 *
 * Example: view('auth/login', ['error' => '...'])
 */

if (!function_exists('h')) {
    function h(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

function view(string $name, array $data = []): string
{
    $base = __DIR__ . '/../Views/';
    $path = $base . str_replace(['..', '\\'], ['', '/'], $name) . '.php';

    if (!is_file($path)) {
        throw new RuntimeException("View not found: " . $path);
    }

    extract($data, EXTR_SKIP);

    ob_start();
    require $path;
    return (string)ob_get_clean();
}
