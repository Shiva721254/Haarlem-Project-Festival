public function schedule(): string
{
    $title = 'Schedule • Haarlem Festival';
    $content = '<h1>Schedule</h1><p class="muted">Next: render schedule from events table.</p>';
    return require __DIR__ . '/../Views/layouts/main.php';
}
