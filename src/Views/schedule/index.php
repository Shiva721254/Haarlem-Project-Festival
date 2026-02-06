<?php
declare(strict_types=1);

/** @var array<string, array<int, array{id:int,title:string,event_date:string}>> $groups */

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>

<h1>Festival Schedule</h1>

<?php foreach ($groups as $date => $events): ?>
    <section class="day">
        <h2><?= h($date) ?></h2>
        <ul>
            <?php foreach ($events as $e): ?>
                <li><?= h((string)$e['title']) ?></li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endforeach; ?>
