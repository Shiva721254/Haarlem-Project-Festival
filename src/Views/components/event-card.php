<?php
declare(strict_types=1);

/** @var array{id:int,title:string,event_date:string,category?:string} $event */


$title = (string)($event['title'] ?? '');
$date  = (string)($event['event_date'] ?? '');
$cat   = (string)($event['category'] ?? '');
?>
<article class="card">
    <div class="card-top">
        <div class="badge"><?= $cat !== '' ? h($cat) : 'General' ?></div>
        <div class="date"><?= h($date) ?></div>
    </div>

    <h3 class="card-title"><?= h($title) ?></h3>

    <div class="card-actions">
        <a class="btn btn-outline"
           href="/schedule<?= $cat !== '' ? '?category=' . rawurlencode(trim($cat)) : '' ?>">
            View schedule
        </a>
    </div>
</article>
