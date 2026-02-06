<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Framework\Response;
use App\Repositories\EventRepository;

final class ScheduleController
{
    public function index(): Response
    {
        $repo = new EventRepository();
        $events = $repo->allOrderedByDate(); // we’ll add this method below

        // Group by date: ['2026-03-10' => [..events..], ...]
        $grouped = [];
        foreach ($events as $e) {
            $date = (string)($e['event_date'] ?? '');
            $grouped[$date][] = $e;
        }

       
       // Render content
        ob_start();
        $groups = $grouped;
        require __DIR__ . '/../Views/schedule/index.php';
        $content = (string)ob_get_clean();

        // Render layout
        ob_start();
        $title = 'Schedule';
        require __DIR__ . '/../Views/layout/app.php';
        $html = (string)ob_get_clean();

        return Response::html($html);

    }
}
