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

<form method="GET" action="/schedule" class="mb-3">
    <label for="category" class="form-label">Filter by category:</label>
    <select id="category" name="category" class="form-select">
        <option value="">All categories</option>
        <?php foreach ($categories as $c): ?>
            <option value="<?= h($c) ?>" <?= ($selectedCategory === $c) ? 'selected' : '' ?>>
                <?= h($c) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary ml-1">Apply</button>

    <?php if ($selectedCategory !== ''): ?>
        <a href="/schedule" class="link-primary ml-2">Clear</a>
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
                            <small class="text-muted">(<?= h((string)$e['category']) ?>)</small>
                        <?php endif; ?>

                        <?php $eventTickets = $ticketsForEvents[(int)$e['id']] ?? []; ?>
                        <?php if ($eventTickets !== []): ?>
                            <div class="mt-1 mb-1">
                                <?php foreach ($eventTickets as $ticket): ?>
                                    <?php $available = (int)$ticket['quantity_available'] - (int)$ticket['quantity_sold']; ?>
                                    <form method="POST" action="/cart/add" class="d-flex gap-1 align-center mb-1">
                                        <?= \App\Framework\Csrf::field() ?>
                                        <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
                                        <span>
                                            <strong><?= h((string)$ticket['ticket_type']) ?></strong>
                                            - €<?= number_format((float)$ticket['price'], 2) ?>
                                            <small class="text-muted">(Available: <?= $available ?>)</small>
                                        </span>
                                        <input type="number" name="quantity" min="1" max="10" value="1" class="form-input-qty">
                                        <button type="submit" class="btn btn-primary">Add to cart</button>
                                    </form>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="mt-1"><small class="text-muted">No tickets configured yet.</small></div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endforeach; ?>
<?php endif; ?>
