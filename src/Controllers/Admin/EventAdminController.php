<?php
declare(strict_types=1);



namespace App\Controllers\Admin;

use App\Framework\Response;
use App\Repositories\EventRepository;
use App\Framework\Flash;


final class EventAdminController
{
    private function render(string $viewPath, array $vars, string $title): Response
    {
        extract($vars, EXTR_SKIP);

        ob_start();
        require $viewPath;
        $content = (string)ob_get_clean();

        ob_start();
        require __DIR__ . '/../../Views/layout/app.php';
        $html = (string)ob_get_clean();

        return Response::html($html);
    }

    private function redirect(string $to): Response
    {
        return Response::html('', 302)->withHeader('Location', $to);
    }

    public function index(): Response
    {
        $repo = new EventRepository();
        $events = $repo->allOrderedByDate();

        return $this->render(
            __DIR__ . '/../../Views/admin/events/index.php',
            ['events' => $events],
            'Admin • Events'
        );
    }

    public function create(): Response
    {
        return $this->render(
            __DIR__ . '/../../Views/admin/events/form.php',
            [
                'mode' => 'create',
               'event' => ['title' => '', 'event_date' => '', 'category' => ''],
                'errors' => [],
            ],
            'Admin • New Event'
        );
    }

   public function store(): Response
{
    $title    = trim((string)($_POST['title'] ?? ''));
    $date     = trim((string)($_POST['event_date'] ?? ''));
    $category = trim((string)($_POST['category'] ?? ''));

    $errors = [];
    if ($title === '') $errors[] = 'Title is required.';
    if (!$this->isValidDate($date)) $errors[] = 'Event date must be YYYY-MM-DD.';
    if ($category === '') $errors[] = 'Category is required.';

    if ($errors) {
        return $this->render(
            __DIR__ . '/../../Views/admin/events/form.php',
            [
                'mode' => 'create',
                'event' => ['title' => $title, 'event_date' => $date, 'category' => $category],
                'errors' => $errors,
            ],
            'Admin • New Event'
        );
    }

    $repo = new \App\Repositories\EventRepository();
    $repo->create($title, $date, $category);

    \App\Framework\Flash::set('success', 'Event created successfully.');
    return $this->redirect('/admin/events');
}


    public function edit(): Response
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) return Response::html('Bad Request', 400);

        $repo = new EventRepository();
        $event = $repo->findById($id);

        if ($event === null) return Response::html('Not Found', 404);

        return $this->render(
            __DIR__ . '/../../Views/admin/events/form.php',
            [
                'mode' => 'edit',
                'event' => $event,
                'errors' => [],
            ],
            'Admin • Edit Event'
        );
    }

    public function update(): Response
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) return Response::html('Bad Request', 400);

        $title = trim((string)($_POST['title'] ?? ''));
        $date  = trim((string)($_POST['event_date'] ?? ''));
        $category = trim((string)($_POST['category'] ?? ''));

        $errors = [];
        if ($title === '') $errors[] = 'Title is required.';
        if (!$this->isValidDate($date)) $errors[] = 'Event date must be YYYY-MM-DD.';
        if ($category === '') $errors[] = 'Category is required.';


        if ($errors) {
            return $this->render(
                __DIR__ . '/../../Views/admin/events/form.php',
                [
                    'mode' => 'edit',
                    'event' => ['id' => $id, 'title' => $title, 'event_date' => $date, 'category' => $category],
                    'errors' => $errors,
                ],
                'Admin • Edit Event'
            );
        }

        $repo = new EventRepository();
        $repo->updateById($id, $title, $date, $category);
        Flash::set('success', 'Event updated successfully.');

        return $this->redirect('/admin/events');
    }

   public function delete(): Response
{
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) return Response::html('Bad Request', 400);

    $repo = new EventRepository();
    $deleted = $repo->deleteById($id);

    if ($deleted > 0) {
        Flash::set('success', 'Event deleted successfully.');
    } else {
        Flash::set('error', 'Event not found.');
    }

    return $this->redirect('/admin/events');
}


    private function isValidDate(string $date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return false;
        [$y, $m, $d] = array_map('intval', explode('-', $date));
        return checkdate($m, $d, $y);
    }

    
}
