<?php
declare(strict_types=1);

/**
 * Render a PHP view from resources/views.
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
    $base = __DIR__ . '/../../resources/views/';
    $path = $base . str_replace(['..', '\\'], ['', '/'], $name) . '.php';

    if (!is_file($path)) {
        throw new RuntimeException("View not found: " . $path);
    }

    extract($data, EXTR_SKIP);

    ob_start();
    require $path;
    $content = (string)ob_get_clean();
    
    // Don't wrap layout files themselves
    $isLayoutFile = str_starts_with($name, 'layout/');
    
    if (!$isLayoutFile) {
        // Wrap in layout
        $layoutPath = $base . 'layout/app.php';
        extract(['title' => $data['title'] ?? 'Haarlem Festival', 'content' => $content], EXTR_SKIP);
        
        ob_start();
        require $layoutPath;
        return (string)ob_get_clean();
    }
    
    return $content;
}
