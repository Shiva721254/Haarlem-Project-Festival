<?php
declare(strict_types=1);

/**
 * @var array{id:int,user_id:int|null,customer_email:string,customer_name:string|null,total_amount:float,status:string,stripe_payment_intent_id:string|null,created_at:string,updated_at:string,items:array} $order
 */
?>

<h1>Order Confirmation</h1>

<div style="background-color:#d4edda; border:1px solid #c3e6cb; border-radius:4px; padding:12px; margin-bottom:20px;">
    <p style="color:#155724; margin:0;">✓ Payment successful! Your order has been confirmed.</p>
</div>

<div style="margin-bottom:20px; padding:12px; border:1px solid #ddd; border-radius:4px;">
    <strong>Order ID:</strong> #<?= $order['id'] ?><br>
    <strong>Customer:</strong> <?= h($order['customer_name'] ?: $order['customer_email']) ?><br>
    <strong>Email:</strong> <?= h($order['customer_email']) ?><br>
    <strong>Order Date:</strong> <?= date('Y-m-d H:i', strtotime($order['created_at'])) ?><br>
    <strong>Status:</strong> <span style="color:#28a745; font-weight:bold;"><?= ucfirst($order['status']) ?></span>
</div>

<h2>Order Items</h2>

<table style="width:100%; border-collapse: collapse; margin-bottom:20px;">
    <thead>
        <tr style="background-color:#f8f9fa;">
            <th style="text-align:left; border-bottom:2px solid #ddd; padding:8px;">Event</th>
            <th style="text-align:left; border-bottom:2px solid #ddd; padding:8px;">Ticket Type</th>
            <th style="text-align:center; border-bottom:2px solid #ddd; padding:8px;">Date</th>
            <th style="text-align:right; border-bottom:2px solid #ddd; padding:8px;">Qty</th>
            <th style="text-align:right; border-bottom:2px solid #ddd; padding:8px;">Unit Price</th>
            <th style="text-align:right; border-bottom:2px solid #ddd; padding:8px;">Total</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $line_total = 0;
        foreach ($order['items'] as $item): 
            $item_total = $item['price_at_purchase'] * $item['quantity'];
            $line_total += $item_total;
        ?>
            <tr>
                <td style="padding:8px; border-bottom:1px solid #f0f0f0;"><?= h($item['event_title']) ?></td>
                <td style="padding:8px; border-bottom:1px solid #f0f0f0;"><?= h($item['ticket_type']) ?></td>
                <td style="padding:8px; border-bottom:1px solid #f0f0f0; text-align:center;"><?= h($item['event_date']) ?></td>
                <td style="padding:8px; border-bottom:1px solid #f0f0f0; text-align:right;"><?= (int)$item['quantity'] ?></td>
                <td style="padding:8px; border-bottom:1px solid #f0f0f0; text-align:right;">€<?= number_format((float)$item['price_at_purchase'], 2) ?></td>
                <td style="padding:8px; border-bottom:1px solid #f0f0f0; text-align:right;">€<?= number_format($item_total, 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div style="text-align:right; margin-bottom:20px;">
    <p><strong>Total Amount Paid: €<?= number_format((float)$order['total_amount'], 2) ?></strong></p>
</div>

<div style="background-color:#f8f9fa; border:1px solid #ddd; border-radius:4px; padding:12px; margin-bottom:20px;">
    <h3>What's Next?</h3>
    <ul>
        <li>A confirmation email has been sent to <strong><?= h($order['customer_email']) ?></strong></li>
        <li>Check your email for ticket details and instructions</li>
        <li>Save your order ID (#<?= $order['id'] ?>) for future reference</li>
        <li>Bring your tickets to the events (digital or printed)</li>
    </ul>
</div>

<div style="display:flex; gap:10px; margin-top:20px;">
    <a href="/schedule" style="padding:10px 20px; background-color:#007bff; color:white; text-decoration:none; border-radius:4px;">Browse More Events</a>
    <a href="/" style="padding:10px 20px; background-color:#6c757d; color:white; text-decoration:none; border-radius:4px;">Back to Home</a>
</div>
