<?php
declare(strict_types=1);

/**
 * @var array<int, array{ticket_id:int,event_title:string,ticket_type:string,event_date:string,quantity:int,price:float,line_total:float}> $lines
 * @var float $total
 * @var int|null $order_id
 */

$stripe_key = $_ENV['STRIPE_PUBLIC_KEY'] ?? '';
?>

<h1>Checkout Review</h1>
<p>Review your order before payment.</p>

<table class="data-table">
    <thead>
        <tr>
            <th class="text-left">Event</th>
            <th class="text-left">Ticket</th>
            <th class="text-left">Date</th>
            <th class="text-right">Qty</th>
            <th class="text-right">Price</th>
            <th class="text-right">Line Total</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($lines as $line): ?>
            <tr>
                <td><?= h($line['event_title']) ?></td>
                <td><?= h($line['ticket_type']) ?></td>
                <td><?= h($line['event_date']) ?></td>
                <td class="text-right"><?= (int)$line['quantity'] ?></td>
                <td class="text-right">€<?= number_format((float)$line['price'], 2) ?></td>
                <td class="text-right">€<?= number_format((float)$line['line_total'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<p><strong>Order Total: €<?= number_format($total, 2) ?></strong></p>

<?php if ($order_id && $stripe_key): ?>
    <div class="info-box mt-3">
        <h2>Payment</h2>
        <p>Click the button below to proceed to secure payment with Stripe.</p>
        
        <form method="POST" action="/payment/checkout?order_id=<?= urlencode((string)$order_id) ?>" class="mt-2">
            <button type="submit" class="btn btn-primary font-lg">
                Proceed to Payment
            </button>
        </form>
    </div>
<?php else: ?>
    <div class="alert alert-warning mt-3">
        <p class="mb-0"><strong>Payment Service</strong> is not currently available. Please try again later.</p>
    </div>
<?php endif; ?>

<div class="mt-2">
    <a href="/cart" class="link-primary">Back to cart</a>
</div>
