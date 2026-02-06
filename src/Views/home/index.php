<?php
declare(strict_types=1);

/** @var array<int, array<string, mixed>> $events */

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function fmtDate(string $date): string {
    // input: YYYY-MM-DD
    $ts = strtotime($date);
    return $ts ? date('D, d M Y', $ts) : $date;
}

$content = '';

// Hero
$content .= '
<section class="hero">
  <h1>Dance Festival Haarlem</h1>
  <p>Discover events, artists, and schedules across the city. Built for fast browsing and easy planning — aligned with our Figma design.</p>
  <div class="hero-actions">
    <a class="btn primary" href="/schedule">View Schedule</a>
    <a class="btn" href="/tickets">Get Tickets</a>
  </div>
</section>
';

// Upcoming events
$content .= '
<section class="section">
  <div class="section-head">
    <h2>Upcoming events</h2>
    <a href="/schedule">See all →</a>
  </div>
';

if (empty($events)) {
    $content .= '<p class="muted">No events found.</p></section>';
} else {
    $content .= '<div class="grid">';
    foreach ($events as $ev) {
        $title = e((string)($ev['title'] ?? ''));
        $cat   = e((string)($ev['category'] ?? ''));
        $date  = e(fmtDate((string)($ev['event_date'] ?? '')));
        $venue = e((string)($ev['venue'] ?? ''));
        $price = $ev['price'] !== null ? number_format((float)$ev['price'], 2) : null;

        $content .= '
          <article class="card">
            <span class="badge">' . $cat . '</span>
            <h3>' . $title . '</h3>
            <div class="meta">
              <div><strong>Date:</strong> ' . $date . '</div>
              ' . ($venue !== '' ? '<div><strong>Venue:</strong> ' . $venue . '</div>' : '') . '
              ' . ($price !== null ? '<div class="price">€ ' . e($price) . '</div>' : '') . '
            </div>
          </article>
        ';
    }
    $content .= '</div></section>';
}

$title = 'Home • Haarlem Festival';
return require __DIR__ . '/../layouts/main.php';
