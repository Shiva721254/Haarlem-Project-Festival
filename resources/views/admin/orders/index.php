<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">
                <i class="fas fa-shopping-bag"></i> Order Management
            </h1>

            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-center bg-light">
                        <div class="card-body">
                            <h3 class="card-title"><?= h($orderCount) ?></h3>
                            <p class="card-text text-muted">Total Orders</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center bg-light">
                        <div class="card-body">
                            <h3 class="card-title"><?= h($completedCount) ?></h3>
                            <p class="card-text text-muted">Completed</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center bg-light">
                        <div class="card-body">
                            <h3 class="card-title">&euro;<?= h(number_format($totalRevenue, 2)) ?></h3>
                            <p class="card-text text-muted">Total Revenue</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders Table -->
            <?php if (empty($orders)): ?>
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle"></i> No orders yet.
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-list"></i> All Orders (<?= h($orderCount) ?>)
                        </h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Order ID</th>
                                    <th>Customer</th>
                                    <th>Email</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th class="pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                        <?php foreach ($orders as $order): ?>
                            <?php
                                $statusClass = 'bg-secondary';
                                if ($order['status'] === 'completed') {
                                    $statusClass = 'bg-success';
                                } elseif ($order['status'] === 'pending') {
                                    $statusClass = 'bg-warning text-dark';
                                } elseif ($order['status'] === 'cancelled' || $order['status'] === 'failed') {
                                    $statusClass = 'bg-danger';
                                }
                            ?>
                            <tr>
                                <td class="ps-3"><strong>#<?= h($order['id']) ?></strong></td>
                                <td><?= h($order['customer_name'] ?? 'Guest') ?></td>
                                <td><a href="mailto:<?= h($order['customer_email']) ?>"><?= h($order['customer_email']) ?></a></td>
                                <td><strong>&euro;<?= h(number_format((float)$order['total_amount'], 2)) ?></strong></td>
                                <td>
                        <span class="badge <?= h($statusClass) ?>">
                            <?= h(ucfirst($order['status'])) ?>
                        </span>
                                </td>
                                <td>
                        <?php
                            $date = new DateTime($order['created_at']);
                            echo h($date->format('M d, Y H:i'));
                        ?>
                                </td>
                                <td class="pe-3">
                        <a href="/admin/orders/detail?id=<?= h($order['id']) ?>" class="btn btn-sm btn-primary">
                            View
                        </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <div class="mt-4">
                <a href="/admin" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Admin
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .table th {
        border-top: none;
        font-weight: 600;
    }
    
    .card {
        border: 1px solid #dee2e6;
    }
</style>
