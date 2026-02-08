<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Framework\Response;
use App\Repositories\EventRepository;

final class ScheduleController
{
    public function index(): Response
    {
        $selected = trim((string)($_GET['category'] ?? ''));

        $allowed = \App\Config\EventCategories::all();
        if ($selected !== '' && !in_array($selected, $allowed, true)) {
            $selected = '';
        }

        $repo = new EventRepository();
        $events = $repo->allForSchedule($selected === '' ? null : $selected);

        $grouped = [];
        foreach ($events as $e) {
            $date = (string)($e['event_date'] ?? '');
            $grouped[$date][] = $e;
        }

        ob_start();
        $groups = $grouped;
        $categories = $allowed;
        $selectedCategory = $selected;
        require __DIR__ . '/../Views/schedule/index.php';
        $content = (string)ob_get_clean();

        ob_start();
        $title = 'Schedule';
        require __DIR__ . '/../Views/layout/app.php';
        $html = (string)ob_get_clean();

        return Response::html($html);
    }
}
