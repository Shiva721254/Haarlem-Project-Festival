<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Framework\Response;
use App\Repositories\EventRepository;
use App\Repositories\TicketRepository;

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

        $eventIds = array_values(array_map(static fn(array $event): int => (int)$event['id'], $events));
        $ticketRepo = new TicketRepository();
        $tickets = $ticketRepo->findByEventIds($eventIds);
        $ticketsByEvent = [];
        foreach ($tickets as $ticket) {
            $eventId = (int)$ticket['event_id'];
            $ticketsByEvent[$eventId][] = $ticket;
        }

        ob_start();
        $groups = $grouped;
        $categories = $allowed;
        $selectedCategory = $selected;
        $ticketsForEvents = $ticketsByEvent;
        require __DIR__ . '/../../resources/views/schedule/index.php';
        $content = (string)ob_get_clean();

        ob_start();
        $title = 'Schedule';
        require __DIR__ . '/../../resources/views/layout/app.php';
        $html = (string)ob_get_clean();

        return Response::html($html);
    }
}
