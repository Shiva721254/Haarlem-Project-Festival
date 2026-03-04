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

<?php if ($order_id && $stripe_key): ?>
    <div style="margin-top:20px; border-top:1px solid #f0f0f0; padding-top:20px;">
        <h2>Payment</h2>
        <p>Click the button below to proceed to secure payment with Stripe.</p>
        
        <form method="POST" action="/payment/checkout?order_id=<?= urlencode((string)$order_id) ?>" style="margin-top:12px;">
            <button 
                type="submit" 
                style="background-color:#5469D4; color:white; padding:10px 20px; border:none; border-radius:4px; cursor:pointer; font-size:16px;"
            >
                Proceed to Payment
            </button>
        </form>
    </div>
<?php else: ?>
    <div style="margin-top:20px; padding:12px; background-color:#fff3cd; border:1px solid #ffeaa7; border-radius:4px;">
        <p style="margin:0; color:#856404;"><strong>Payment Service</strong> is not currently available. Please try again later.</p>
    </div>
<?php endif; ?>

<div style="margin-top:12px;">
    <a href="/cart" style="text-decoration:none; color:#5469D4;">Back to cart</a>
</div>
