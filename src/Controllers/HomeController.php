<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Framework\Response;
use App\Repositories\EventRepository;

final class HomeController
{
    public function index(): Response
    {
        $repo = new EventRepository();

        // Get events for homepage (limit later if needed)
        $events = $repo->allOrderedByDate();

        return Response::html(view('home/index', [
            'events' => $events,
        ]));
    }
}
