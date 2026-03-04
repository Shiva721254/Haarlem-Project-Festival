<?php
declare(strict_types=1);

/**
 * @var array<int, array{ticket_id:int,event_title:string,ticket_type:string,event_date:string,quantity:int,price:float,line_total:float}> $lines
 * @var float $total
 */
?>

<h1>Checkout Review</h1>
<p>Review your order before payment.</p>

<table style="width:100%; border-collapse: collapse; margin-bottom:20px;">
    <thead>
        <tr>
            <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Event</th>
            <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Ticket</th>
            <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px;">Date</th>
            <th style="text-align:right; border-bottom:1px solid #ddd; padding:8px;">Qty</th>
            <th style="text-align:right; border-bottom:1px solid #ddd; padding:8px;">Price</th>
            <th style="text-align:right; border-bottom:1px solid #ddd; padding:8px;">Line Total</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($lines as $line): ?>
            <tr>
                <td style="padding:8px; border-bottom:1px solid #f0f0f0;"><?= h($line['event_title']) ?></td>
                <td style="padding:8px; border-bottom:1px solid #f0f0f0;"><?= h($line['ticket_type']) ?></td>
                <td style="padding:8px; border-bottom:1px solid #f0f0f0;"><?= h($line['event_date']) ?></td>
                <td style="padding:8px; border-bottom:1px solid #f0f0f0; text-align:right;"><?= (int)$line['quantity'] ?></td>
                <td style="padding:8px; border-bottom:1px solid #f0f0f0; text-align:right;">€<?= number_format((float)$line['price'], 2) ?></td>
                <td style="padding:8px; border-bottom:1px solid #f0f0f0; text-align:right;">€<?= number_format((float)$line['line_total'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<p><strong>Order Total: €<?= number_format($total, 2) ?></strong></p>

<div style="display:flex; gap:10px; margin-top:12px;">
    <a href="/cart">Back to cart</a>
    <button type="button" disabled title="Payment integration in next sprint">Proceed to payment (next sprint)</button>
</div>
