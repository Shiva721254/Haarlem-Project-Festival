<?php
declare(strict_types=1);

/**
 * @var array{id:int,user_id:int|null,customer_email:string,customer_name:string|null,total_amount:float,status:string,stripe_payment_intent_id:string|null,created_at:string,updated_at:string,items:array} $order
 */
?>

<h1>Order Confirmation</h1>

<div class="alert alert-success">
    <p class="mb-0">✓ Payment successful! Your order has been confirmed.</p>
</div>

<div class="info-box">
    <strong>Order ID:</strong> #<?= $order['id'] ?><br>
    <strong>Customer:</strong> <?= h($order['customer_name'] ?: $order['customer_email']) ?><br>
    <strong>Email:</strong> <?= h($order['customer_email']) ?><br>
    <strong>Order Date:</strong> <?= date('Y-m-d H:i', strtotime($order['created_at'])) ?><br>
    <strong>Status:</strong> <span class="text-success font-bold"><?= ucfirst($order['status']) ?></span>
</div>

<h2>Order Items</h2>

<table class="data-table">
    <thead>
        <tr>
            <th class="text-left">Event</th>
            <th class="text-left">Ticket Type</th>
            <th class="text-center">Date</th>
            <th class="text-right">Qty</th>
            <th class="text-right">Unit Price</th>
            <th class="text-right">Total</th>
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
                <td><?= h($item['event_title']) ?></td>
                <td><?= h($item['ticket_type']) ?></td>
                <td class="text-center"><?= h($item['event_date']) ?></td>
                <td class="text-right"><?= (int)$item['quantity'] ?></td>
                <td class="text-right">€<?= number_format((float)$item['price_at_purchase'], 2) ?></td>
                <td class="text-right">€<?= number_format($item_total, 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="text-right mb-3">
    <p><strong>Total Amount Paid: €<?= number_format((float)$order['total_amount'], 2) ?></strong></p>
</div>

<div class="alert alert-info">
    <h3>What's Next?</h3>
    <ul>
        <li>A confirmation email has been sent to <strong><?= h($order['customer_email']) ?></strong></li>
        <li>Check your email for ticket details and instructions</li>
        <li>Save your order ID (#<?= $order['id'] ?>) for future reference</li>
        <li>Bring your tickets to the events (digital or printed)</li>
    </ul>
</div>

<div class="d-flex gap-2 mt-3">
    <a href="/schedule" class="btn btn-primary">Browse More Events</a>
    <a href="/" class="btn btn-secondary">Back to Home</a>
</div>
