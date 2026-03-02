<?php
declare(strict_types=1);

/** @var array<int, array{id:int,title:string,event_date:string,category?:string}> $events */


?>

<section class="hero">
    <div class="hero-inner">
        <div class="hero-text">
            <h1>Haarlem Festival</h1>
            <p class="lead">
                Discover music, culture, and family-friendly experiences across Haarlem.
            </p>

            <div class="hero-cta">
                <a class="btn btn-primary" href="/schedule">Explore schedule</a>
             <?php if (\App\Framework\Auth::isAdmin()): ?>
    <a class="btn btn-outline" href="/admin/events">Admin events</a>
<?php endif; ?>

            </div>
        </div>

        <div class="hero-panel">
    <div class="hero-panel-title">Next events</div>

    <?php if (empty($events)): ?>
        <p class="muted" style="margin:0;">No upcoming events yet.</p>
    <?php else: ?>
        <ul class="hero-list">
            <?php foreach (array_slice($events, 0, 3) as $e): ?>
                <li>
                    <span class="hero-dot"></span>
                    <span class="hero-item-title"><?= h((string)$e['title']) ?></span>
                    <span class="hero-item-date"><?= h((string)$e['event_date']) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

    </div>
</section>

<section class="section">
    <div class="section-head">
        <h2>Featured events</h2>
        <a class="link" href="/schedule">See all</a>
    </div>

    <?php if (empty($events)): ?>
        <p>No events available yet.</p>
    <?php else: ?>
        <div class="grid">
            <?php foreach (array_slice($events, 0, 6) as $event): ?>
                <?php require __DIR__ . '/../components/event-card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
