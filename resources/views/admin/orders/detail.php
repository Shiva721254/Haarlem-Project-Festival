<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>
                    <i class="fas fa-receipt"></i> Order #<?= h($order['id']) ?>
                </h1>
                <a href="/admin/orders" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Orders
                </a>
            </div>

            <div class="row">
                <!-- Order Summary -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-5">Order ID:</dt>
                                <dd class="col-sm-7"><code><?= h($order['id']) ?></code></dd>

                                <dt class="col-sm-5">Created:</dt>
                                <dd class="col-sm-7">
                        <?php
                            $date = new DateTime($order['created_at']);
                            echo h($date->format('F d, Y \a\t H:i'));
                        ?>
                                </dd>

                                <dt class="col-sm-5">Customer:</dt>
                                <dd class="col-sm-7"><?= h($order['customer_name'] ?? 'Guest') ?></dd>

                                <dt class="col-sm-5">Email:</dt>
                                <dd class="col-sm-7">
                        <a href="mailto:<?= h($order['customer_email']) ?>">
                            <?= h($order['customer_email']) ?>
                        </a>
                                </dd>

                                <dt class="col-sm-5">Total:</dt>
                                <dd class="col-sm-7">
                        <strong class="text-success" style="font-size: 1.25rem;">
                            &euro;<?= h(number_format((float)$order['total_amount'], 2)) ?>
                        </strong>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Status Management -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">Order Status</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Current Status:</label>
                                <div>
                        <?php
                            $status = $order['status'];
                            $statusClass = 'bg-secondary';
                            if ($status === 'completed') {
                                $statusClass = 'bg-success';
                            } elseif ($status === 'pending') {
                                $statusClass = 'bg-warning text-dark';
                            } elseif ($status === 'cancelled' || $status === 'failed') {
                                $statusClass = 'bg-danger';
                            }
                        ?>
                        <span class="badge <?= h($statusClass) ?> fs-6 p-2">
                            <?= h(ucfirst($status)) ?>
                        </span>
                                </div>
                            </div>

                            <div class="mb-0">
                                <label for="statusSelect" class="form-label">Change Status:</label>
                                <form method="POST" action="/admin/orders/status">
                                    <div class="input-group">
                        <select id="statusSelect" name="status" class="form-select">
                            <option value="">-- Select Status --</option>
                            <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="failed" <?= $order['status'] === 'failed' ? 'selected' : '' ?>>Failed</option>
                            <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                                    <input type="hidden" name="order_id" value="<?= h($order['id']) ?>">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card mt-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Order Items</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($items)): ?>
                        <p class="p-3 text-muted">No items in this order.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light text-dark">
                                    <tr>
                                        <th>Event</th>
                                        <th>Ticket Type</th>
                                        <th>Date</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-right">Unit Price</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                        <?php foreach ($items as $item): ?>
                            <?php
                                $unitPrice = (float)$item['price_at_purchase'];
                                $totalPrice = $unitPrice * $item['quantity'];
                            ?>
                            <tr>
                                <td><strong><?= h($item['event_title']) ?></strong></td>
                                <td><?= h($item['ticket_type']) ?></td>
                                <td>
                        <?php
                            $eventDate = new DateTime($item['event_date']);
                            echo h($eventDate->format('M d, Y'));
                        ?>
                                </td>
                                <td class="text-center"><?= h($item['quantity']) ?></td>
                                <td class="text-right">&euro;<?= h(number_format($unitPrice, 2)) ?></td>
                                <td class="text-right"><strong>&euro;<?= h(number_format($totalPrice, 2)) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Payment Info -->
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Payment Information</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Stripe Intent ID:</dt>
                        <dd class="col-sm-8">
                    <?php if ($order['stripe_payment_intent_id']): ?>
                        <code><?= h($order['stripe_payment_intent_id']) ?></code>
                    <?php else: ?>
                        <span class="text-muted">Not yet processed</span>
                    <?php endif; ?>
                        </dd>

                        <dt class="col-sm-4">Last Updated:</dt>
                        <dd class="col-sm-8">
                    <?php
                        $updated = new DateTime($order['updated_at']);
                        echo h($updated->format('F d, Y H:i'));
                    ?>
                        </dd>
                    </dl>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-4 mb-4">
                <a href="/admin/orders" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Orders
                </a>
                <a href="mailto:<?= h($order['customer_email']) ?>" class="btn btn-outline-info">
                    <i class="fas fa-envelope"></i> Email Customer
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    code {
        background-color: #f8f9fa;
        padding: 0.25rem 0.5rem;
        border-radius: 3px;
        color: #d63384;
    }
    
    .text-right {
        text-align: right;
    }
    
    table th {
        border-top: none;
    }
</style>
