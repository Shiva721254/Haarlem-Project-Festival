<?php
$this->layout('layout/app', ['title' => 'My Orders'])
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">
                <i class="fas fa-receipt"></i> My Orders
            </h1>
            
            <?php if (empty($orders)): ?>
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle"></i>
                    You haven't made any orders yet. 
                    <a href="/schedule" class="alert-link">Browse our events</a> to get started!
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><strong>#<?php h($order['id']); ?></strong></td>
                                    <td>
                        <?php 
                            $date = new DateTime($order['created_at']);
                            h($date->format('M d, Y'));
                        ?>
                                    </td>
                                    <td>
                        <strong>&euro;<?php h(number_format((float)$order['total_amount'], 2)); ?></strong>
                                    </td>
                                    <td>
                        <?php
                            $status = $order['status'];
                            $badgeClass = 'bg-secondary';
                            if ($status === 'completed') {
                                $badgeClass = 'bg-success';
                            } elseif ($status === 'pending') {
                                $badgeClass = 'bg-warning text-dark';
                            } elseif ($status === 'failed' || $status === 'cancelled') {
                                $badgeClass = 'bg-danger';
                            }
                        ?>
                        <span class="badge <?php h($badgeClass); ?>">
                            <?php h(ucfirst($status)); ?>
                        </span>
                                    </td>
                                    <td>
                        <a href="/profile/orders/view?id=<?= h($order['id']) ?>" class="btn btn-sm btn-primary">
                            View Details
                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <a href="/schedule" class="btn btn-outline-secondary">
                        <i class="fas fa-plus"></i> Order More Tickets
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    table {
        margin-top: 1.5rem;
    }
    
    .table-hover tbody tr:hover {
        background-color: #f5f5f5;
    }
</style>
<?php
$this->layout('layout/app', ['title' => 'Order #' . $order['id']])
?>

<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>
                    <i class="fas fa-receipt"></i> Order #<?php h($order['id']); ?>
                </h1>
                <a href="/profile/orders" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Orders
                </a>
            </div>

            <!-- Order Status Banner -->
            <div class="alert alert-success mb-4" role="alert">
                <i class="fas fa-check-circle"></i>
                <strong>Payment Successful!</strong> Your order has been confirmed.
            </div>

            <div class="row">
                <!-- Order Summary -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Order Details</h5>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-4">Order ID:</dt>
                                <dd class="col-sm-8"><code><?php h($order['id']); ?></code></dd>

                                <dt class="col-sm-4">Order Date:</dt>
                                <dd class="col-sm-8">
                        <?php 
                            $date = new DateTime($order['created_at']);
                            h($date->format('F d, Y \a\t H:i'));
                        ?>
                                </dd>

                                <dt class="col-sm-4">Status:</dt>
                                <dd class="col-sm-8">
                        <?php
                            $status = $order['status'];
                            $badgeClass = 'bg-secondary';
                            if ($status === 'completed') {
                                $badgeClass = 'bg-success';
                            } elseif ($status === 'pending') {
                                $badgeClass = 'bg-warning text-dark';
                            } elseif ($status === 'failed' || $status === 'cancelled') {
                                $badgeClass = 'bg-danger';
                            }
                        ?>
                        <span class="badge <?php h($badgeClass); ?>">
                            <?php h(ucfirst($status)); ?>
                        </span>
                                </dd>

                                <dt class="col-sm-4">Total Amount:</dt>
                                <dd class="col-sm-8">
                        <strong class="text-success" style="font-size: 1.25rem;">
                            &euro;<?php h(number_format((float)$order['total_amount'], 2)); ?>
                        </strong>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Customer Info -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">Customer Information</h5>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-4">Name:</dt>
                                <dd class="col-sm-8"><?php h($order['customer_name'] ?? 'N/A'); ?></dd>

                                <dt class="col-sm-4">Email:</dt>
                                <dd class="col-sm-8">
                        <a href="mailto:<?php h($order['customer_email']); ?>">
                            <?php h($order['customer_email']); ?>
                        </a>
                                </dd>

                                <dt class="col-sm-4">Confirmation:</dt>
                                <dd class="col-sm-8">
                        <span class="badge bg-info">Email sent</span>
                                </dd>
                            </dl>
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
                                <td><strong><?php h($item['event_title']); ?></strong></td>
                                <td><?php h($item['ticket_type']); ?></td>
                                <td>
                        <?php 
                            $eventDate = new DateTime($item['event_date']);
                            h($eventDate->format('M d, Y'));
                        ?>
                                </td>
                                <td class="text-center"><?php h($item['quantity']); ?></td>
                                <td class="text-right">&euro;<?php h(number_format($unitPrice, 2)); ?></td>
                                <td class="text-right"><strong>&euro;<?php h(number_format($totalPrice, 2)); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Next Steps -->
            <div class="card mt-4 border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-clipboard-check"></i> Next Steps</h5>
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li><strong>Check your email</strong> - Confirmation with ticket details has been sent</li>
                        <li><strong>Save your Order ID</strong> - You'll need it to access your tickets: <code>#<?php h($order['id']); ?></code></li>
                        <li><strong>Bring your tickets</strong> - Present them at the event entrance</li>
                        <li><strong>Enjoy the festival!</strong> - Have a great time at Haarlem Festival 🎉</li>
                    </ol>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-4 mb-4">
                <a href="/schedule" class="btn btn-primary btn-lg">
                    <i class="fas fa-home"></i> Browse More Events
                </a>
                <a href="/profile/orders" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-list"></i> View All Orders
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    dl.row dt {
        font-weight: 600;
        color: #495057;
    }
    
    code {
        background-color: #f8f9fa;
        padding: 0.25rem 0.5rem;
        border-radius: 3px;
        color: #d63384;
    }
    
    table th {
        border-top: none;
    }
    
    .text-right {
        text-align: right;
    }
</style>
