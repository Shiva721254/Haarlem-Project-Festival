<?php
declare(strict_types=1);

/**
 * @var array<int, array{ticket_id:int,event_title:string,ticket_type:string,event_date:string,quantity:int,price:float,line_total:float}> $lines
 * @var float $total
 */
?>

<h1>Your Cart</h1>

<?php if ($lines === []): ?>
    <p>Your cart is empty.</p>
    <p><a href="/schedule" class="link-primary">Browse events and tickets</a></p>
<?php else: ?>
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-left">Event</th>
                <th class="text-left">Ticket</th>
                <th class="text-left">Date</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Price</th>
                <th class="text-right">Line Total</th>
                <th class="text-right">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lines as $line): ?>
                <tr>
                    <td><?= h($line['event_title']) ?></td>
                    <td><?= h($line['ticket_type']) ?></td>
                    <td><?= h($line['event_date']) ?></td>
                    <td class="text-right">
                        <form method="POST" action="/cart/update" class="d-inline-flex gap-1 align-center justify-end">
                            <?= \App\Framework\Csrf::field() ?>
                            <input type="hidden" name="ticket_id" value="<?= (int)$line['ticket_id'] ?>">
                            <input type="number" name="quantity" min="0" max="10" value="<?= (int)$line['quantity'] ?>" class="qty-input" onchange="this.form.submit();">
                        </form>
                    </td>
                    <td class="text-right">€<?= number_format((float)$line['price'], 2) ?></td>
                    <td class="text-right">€<?= number_format((float)$line['line_total'], 2) ?></td>
                    <td class="text-right">
                        <form method="POST" action="/cart/remove" class="d-inline">
                            <?= \App\Framework\Csrf::field() ?>
                            <input type="hidden" name="ticket_id" value="<?= (int)$line['ticket_id'] ?>">
                            <button type="submit" class="btn btn-danger">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p><strong>Total: €<?= number_format($total, 2) ?></strong></p>

    <div class="d-flex gap-2 mt-2">
        <a href="/schedule" class="btn btn-secondary">Continue shopping</a>
        <form method="POST" action="/cart/clear" class="d-inline">
            <?= \App\Framework\Csrf::field() ?>
            <button type="submit" class="btn btn-danger">Clear cart</button>
        </form>
        <a href="/checkout" class="btn btn-primary">Review checkout</a>
    </div>
<?php endif; ?>
