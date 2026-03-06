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
                                <th>Tickets</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><strong>#<?= h($order['id']) ?></strong></td>
                                    <td>
                        <?php 
                            $date = new DateTime($order['created_at']);
                            echo h($date->format('M d, Y'));
                        ?>
                                    </td>
                                    <td>
                        <?= h((string)($order['used_tickets'] ?? 0)) ?> / <?= h((string)($order['total_tickets'] ?? 0)) ?> used
                                    </td>
                                    <td>
                        <strong>&euro;<?= h(number_format((float)$order['total_amount'], 2)) ?></strong>
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
                        <span class="badge <?= h($badgeClass) ?>">
                            <?= h(ucfirst($status)) ?>
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
