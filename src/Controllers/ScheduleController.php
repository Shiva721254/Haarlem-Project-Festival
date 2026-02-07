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

    // Validate selected category against allowed list
    $allowed = \App\Config\EventCategories::all();
    if ($selected !== '' && !in_array($selected, $allowed, true)) {
        $selected = '';
    }

    $repo = new EventRepository();
    $events = $repo->allForSchedule($selected === '' ? null : $selected);

    // Group by date
    $grouped = [];
    foreach ($events as $e) {
        $date = (string)($e['event_date'] ?? '');
        $grouped[$date][] = $e;
    }

    // Render content
    ob_start();
    $groups = $grouped;
    $categories = $allowed;
    $selectedCategory = $selected;
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
