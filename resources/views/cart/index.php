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
    <p><a href="/schedule">Browse events and tickets</a></p>
<?php else: ?>
    <table style="width:100%; border-collapse: collapse; margin-bottom:20px;">
        <thead>
            <tr>
                <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Event</th>
                <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Ticket</th>
                <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Date</th>
                <th style="text-align:right; border-bottom:1px solid #ddd; padding:8px;">Qty</th>
                <th style="text-align:right; border-bottom:1px solid #ddd; padding:8px;">Price</th>
                <th style="text-align:right; border-bottom:1px solid #ddd; padding:8px;">Line Total</th>
                <th style="text-align:right; border-bottom:1px solid #ddd; padding:8px;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lines as $line): ?>
                <tr>
                    <td style="padding:8px; border-bottom:1px solid #f0f0f0;"><?= h($line['event_title']) ?></td>
                    <td style="padding:8px; border-bottom:1px solid #f0f0f0;"><?= h($line['ticket_type']) ?></td>
                    <td style="padding:8px; border-bottom:1px solid #f0f0f0;"><?= h($line['event_date']) ?></td>
                    <td style="padding:8px; border-bottom:1px solid #f0f0f0; text-align:right;">
                        <form method="POST" action="/cart/update" style="display:inline-flex; gap:6px; align-items:center; justify-content:flex-end;">
                            <?= \App\Framework\Csrf::field() ?>
                            <input type="hidden" name="ticket_id" value="<?= (int)$line['ticket_id'] ?>">
                            <input type="number" name="quantity" min="0" max="10" value="<?= (int)$line['quantity'] ?>" style="width:60px;" onchange="this.form.submit();">
                        </form>
                    </td>
                    <td style="padding:8px; border-bottom:1px solid #f0f0f0; text-align:right;">€<?= number_format((float)$line['price'], 2) ?></td>
                    <td style="padding:8px; border-bottom:1px solid #f0f0f0; text-align:right;">€<?= number_format((float)$line['line_total'], 2) ?></td>
                    <td style="padding:8px; border-bottom:1px solid #f0f0f0; text-align:right;">
                        <form method="POST" action="/cart/remove" style="display:inline;">
                            <?= \App\Framework\Csrf::field() ?>
                            <input type="hidden" name="ticket_id" value="<?= (int)$line['ticket_id'] ?>">
                            <button type="submit">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p><strong>Total: €<?= number_format($total, 2) ?></strong></p>

    <div style="display:flex; gap:10px; margin-top:12px;">
        <a href="/schedule">Continue shopping</a>
        <form method="POST" action="/cart/clear" style="display:inline;">
            <?= \App\Framework\Csrf::field() ?>
            <button type="submit">Clear cart</button>
        </form>
        <a href="/checkout">Review checkout</a>
    </div>
<?php endif; ?>
