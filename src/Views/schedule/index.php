<?php
declare(strict_types=1);

/**
 * @var array<string, array<int, array{id:int,title:string,event_date:string,category?:string}>> $groups
 * @var array<int, string> $categories
 * @var string $selectedCategory
 */

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>

<h1>Festival Schedule</h1>

<form method="GET" action="/schedule" style="margin: 12px 0 20px;">
    <label for="category"><strong>Filter by category:</strong></label><br>
    <select id="category" name="category" style="margin-top:8px; width: 260px;">
        <option value="">All categories</option>
        <?php foreach ($categories as $c): ?>
            <option value="<?= h($c) ?>" <?= ($selectedCategory === $c) ? 'selected' : '' ?>>
                <?= h($c) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit" style="margin-left:8px;">Apply</button>

    <?php if ($selectedCategory !== ''): ?>
        <a href="/schedule" style="margin-left:10px;">Clear</a>
    <?php endif; ?>
</form>

<?php if (empty($groups)): ?>
    <p>No events found<?= $selectedCategory !== '' ? ' for category ' . h($selectedCategory) : '' ?>.</p>
<?php else: ?>
    <?php foreach ($groups as $date => $events): ?>
        <section class="day">
            <h2><?= h($date) ?></h2>
            <ul>
                <?php foreach ($events as $e): ?>
                    <li>
                        <?= h((string)$e['title']) ?>
                        <?php if (!empty($e['category'])): ?>
                            <small style="color:#666;">(<?= h((string)$e['category']) ?>)</small>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endforeach; ?>
<?php endif; ?>
