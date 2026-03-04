<?php
declare(strict_types=1);

/**
 * @var array<string, array<int, array{id:int,title:string,event_date:string,category?:string}>> $groups
 * @var array<int, string> $categories
 * @var string $selectedCategory
 * @var array<int, array<int, array{id:int,event_id:int,ticket_type:string,price:string|float|int,quantity_available:int,quantity_sold:int}>> $ticketsForEvents
 */
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

                        <?php $eventTickets = $ticketsForEvents[(int)$e['id']] ?? []; ?>
                        <?php if ($eventTickets !== []): ?>
                            <div style="margin-top:8px; margin-bottom:8px;">
                                <?php foreach ($eventTickets as $ticket): ?>
                                    <?php $available = (int)$ticket['quantity_available'] - (int)$ticket['quantity_sold']; ?>
                                    <form method="POST" action="/cart/add" style="display:flex; gap:8px; align-items:center; margin:6px 0;">
                                        <?= \App\Framework\Csrf::field() ?>
                                        <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
                                        <span>
                                            <strong><?= h((string)$ticket['ticket_type']) ?></strong>
                                            - €<?= number_format((float)$ticket['price'], 2) ?>
                                            <small style="color:#666;">(Available: <?= $available ?>)</small>
                                        </span>
                                        <input type="number" name="quantity" min="1" max="10" value="1" style="width:70px;">
                                        <button type="submit">Add to cart</button>
                                    </form>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div style="margin-top:8px;"><small style="color:#666;">No tickets configured yet.</small></div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endforeach; ?>
<?php endif; ?>
