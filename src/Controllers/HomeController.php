<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Framework\Response;
use App\Services\EventService;

final class HomeController
{
    public function index(): Response
    {
        $service = new EventService();
        $events  = $service->homepageEvents();

        // Render page content
        ob_start();
        require __DIR__ . '/../Views/home/index.php';
        $content = (string)ob_get_clean();

        // Render layout (app.php uses $title + $content)
        ob_start();
        $title = 'Home';
        require __DIR__ . '/../Views/layout/app.php';
        $html = (string)ob_get_clean();

        return Response::html($html);
    }
}
