<div class="orders-container">
    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb-nav">
        <a href="/admin" class="breadcrumb-link">🏠 Admin</a>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-current">Order Management</span>
    </div>

    <!-- Header Section -->
    <div class="orders-header">
        <div>
            <h1 class="page-title">📦 Order Management</h1>
            <p class="page-subtitle">Track and manage all customer orders</p>
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="stats-container">
        <div class="stat-box">
            <div class="stat-icon total-orders">📊</div>
            <div class="stat-details">
                <div class="stat-value"><?= h($orderCount) ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>

        <div class="stat-box">
            <div class="stat-icon completed-orders">✓</div>
            <div class="stat-details">
                <div class="stat-value"><?= h($completedCount) ?></div>
                <div class="stat-label">Completed Orders</div>
            </div>
        </div>

        <div class="stat-box">
            <div class="stat-icon total-revenue">💰</div>
            <div class="stat-details">
                <div class="stat-value">€<?= h(number_format($totalRevenue, 2)) ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>

        <div class="stat-box">
            <div class="stat-icon pending-orders">⏱️</div>
            <div class="stat-details">
                <div class="stat-value"><?= h($orderCount - $completedCount) ?></div>
                <div class="stat-label">Pending Orders</div>
            </div>
        </div>
    </div>

    <!-- Orders Table Section -->
    <?php if (empty($orders)): ?>
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <h3>No orders yet</h3>
            <p>Orders will appear here once customers start placing them</p>
        </div>
    <?php else: ?>
        <div class="orders-table-card">
            <div class="table-header">
                <h2>All Orders</h2>
                <div class="table-info"><?= h($orderCount) ?> total</div>
            </div>

            <div class="table-wrapper">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th class="col-id">Order ID</th>
                            <th class="col-customer">Customer</th>
                            <th class="col-email">Email</th>
                            <th class="col-amount">Amount</th>
                            <th class="col-status">Status</th>
                            <th class="col-date">Date</th>
                            <th class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <?php
                                $statusClass = '';
                                $statusLabel = ucfirst($order['status']);
                                
                                if ($order['status'] === 'completed') {
                                    $statusClass = 'status-completed';
                                    $statusLabel = '✓ Completed';
                                } elseif ($order['status'] === 'pending') {
                                    $statusClass = 'status-pending';
                                    $statusLabel = '⏱️ Pending';
                                } elseif ($order['status'] === 'cancelled' || $order['status'] === 'failed') {
                                    $statusClass = 'status-failed';
                                    $statusLabel = '✕ ' . ucfirst($order['status']);
                                }
                                
                                $date = new DateTime($order['created_at']);
                                $formattedDate = $date->format('Mar d, Y');
                                $formattedTime = $date->format('H:i');
                            ?>
                            <tr class="table-row">
                                <td class="col-id">
                                    <div class="order-id-badge">#<?= h($order['id']) ?></div>
                                </td>
                                <td class="col-customer">
                                    <span class="customer-name"><?= h($order['customer_name'] ?? 'Guest') ?></span>
                                </td>
                                <td class="col-email">
                                    <a href="mailto:<?= h($order['customer_email']) ?>" class="email-link">
                                        <?= h($order['customer_email']) ?>
                                    </a>
                                </td>
                                <td class="col-amount">
                                    <span class="amount-value">€<?= h(number_format((float)$order['total_amount'], 2)) ?></span>
                                </td>
                                <td class="col-status">
                                    <span class="status-badge <?= h($statusClass) ?>">
                                        <?= h($statusLabel) ?>
                                    </span>
                                </td>
                                <td class="col-date">
                                    <div class="date-column">
                                        <span class="date-value"><?= h($formattedDate) ?></span>
                                        <span class="date-time"><?= h($formattedTime) ?></span>
                                    </div>
                                </td>
                                <td class="col-actions">
                                    <a href="/admin/orders/detail?id=<?= h($order['id']) ?>" class="action-button view-btn">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="page-footer">
        <a href="/admin" class="back-button">← Back to Admin</a>
    </div>
</div>

<style>
.orders-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px 20px;
    background: #f5f5f5;
    min-height: 100vh;
}

/* Breadcrumb */
.breadcrumb-nav {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 24px;
    font-size: 14px;
}

.breadcrumb-link {
    color: #0078d4;
    text-decoration: none;
    transition: color 0.2s;
}

.breadcrumb-link:hover {
    color: #005a9e;
    text-decoration: underline;
}

.breadcrumb-separator {
    color: #ccc;
}

.breadcrumb-current {
    color: #666;
}

/* Header */
.orders-header {
    margin-bottom: 40px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.page-title {
    margin: 0 0 8px;
    font-size: 32px;
    font-weight: 600;
    color: #000;
}

.page-subtitle {
    margin: 0;
    font-size: 14px;
    color: #666;
}

/* Statistics */
.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-box {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
    transition: all 0.2s;
}

.stat-box:hover {
    border-color: #0078d4;
    box-shadow: 0 4px 8px rgba(0, 120, 212, 0.12);
}

.stat-icon {
    font-size: 32px;
    min-width: 56px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background: #f0f0f0;
}

.stat-icon.total-orders {
    background: #f0f7ff;
}

.stat-icon.completed-orders {
    background: #f0fff4;
    color: #107c10;
}

.stat-icon.total-revenue {
    background: #fff9e6;
    color: #ccaa00;
}

.stat-icon.pending-orders {
    background: #fff0f0;
    color: #d13438;
}

.stat-details {
    flex: 1;
}

.stat-value {
    font-size: 28px;
    font-weight: bold;
    color: #000;
    line-height: 1.2;
}

.stat-label {
    font-size: 12px;
    color: #666;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 4px;
}

/* Empty State */
.empty-state {
    background: white;
    border: 2px dashed #ddd;
    border-radius: 8px;
    padding: 60px 30px;
    text-align: center;
    margin-bottom: 20px;
}

.empty-icon {
    font-size: 48px;
    margin-bottom: 16px;
}

.empty-state h3 {
    margin: 0 0 8px;
    color: #000;
    font-size: 18px;
}

.empty-state p {
    margin: 0;
    color: #666;
    font-size: 14px;
}

/* Table */
.orders-table-card {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
    margin-bottom: 30px;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px;
    border-bottom: 1px solid #e0e0e0;
}

.table-header h2 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #000;
}

.table-info {
    font-size: 13px;
    color: #999;
    background: #f5f5f5;
    padding: 4px 12px;
    border-radius: 4px;
}

.table-wrapper {
    overflow-x: auto;
}

.orders-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.orders-table thead {
    background: #f5f5f5;
    border-bottom: 2px solid #e0e0e0;
}

.orders-table th {
    padding: 12px 16px;
    text-align: left;
    font-weight: 600;
    color: #666;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.orders-table tbody tr {
    border-bottom: 1px solid #f0f0f0;
    transition: background 0.2s;
}

.orders-table tbody tr:hover {
    background: #fafafa;
}

.orders-table td {
    padding: 12px 16px;
    color: #000;
    vertical-align: middle;
}

/* Column Widths */
.col-id { width: 80px; }
.col-customer { width: 140px; }
.col-email { width: 180px; }
.col-amount { width: 100px; }
.col-status { width: 120px; }
.col-date { width: 140px; }
.col-actions { width: 150px; }

/* Order ID Badge */
.order-id-badge {
    background: #f0f7ff;
    color: #0078d4;
    padding: 4px 10px;
    border-radius: 4px;
    font-weight: 600;
    font-size: 13px;
}

/* Customer Name */
.customer-name {
    font-weight: 500;
    color: #000;
}

/* Email Link */
.email-link {
    color: #0078d4;
    text-decoration: none;
    transition: color 0.2s;
}

.email-link:hover {
    color: #005a9e;
    text-decoration: underline;
}

/* Amount */
.amount-value {
    font-weight: 600;
    color: #000;
}

/* Status Badges */
.status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
}

.status-completed {
    background: #f0fff4;
    color: #107c10;
}

.status-pending {
    background: #fff9e6;
    color: #ccaa00;
}

.status-failed {
    background: #fef0f0;
    color: #d13438;
}

/* Date Column */
.date-column {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.date-value {
    font-weight: 500;
    color: #000;
}

.date-time {
    font-size: 12px;
    color: #999;
}

/* Action Button */
.action-button {
    display: inline-block;
    padding: 6px 14px;
    background: #0078d4;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.2s;
    white-space: nowrap;
}

.action-button:hover {
    background: #106ebe;
    text-decoration: none;
    box-shadow: 0 2px 8px rgba(0, 120, 212, 0.2);
}

.action-button.view-btn {
    background: #0078d4;
}

/* Footer */
.page-footer {
    margin-top: 30px;
    display: flex;
    gap: 12px;
}

.back-button {
    display: inline-block;
    padding: 8px 16px;
    background: white;
    color: #666;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.2s;
}

.back-button:hover {
    background: #f5f5f5;
    border-color: #bbb;
    color: #000;
}

/* Responsive */
@media (max-width: 1024px) {
    .stats-container {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .orders-container {
        padding: 20px 15px;
    }

    .page-title {
        font-size: 24px;
    }

    .stats-container {
        grid-template-columns: 1fr;
    }

    .orders-header {
        flex-direction: column;
    }

    .table-header {
        flex-direction: column;
        gap: 12px;
        align-items: flex-start;
    }

    .col-email { display: none; }
    .col-customer { width: 120px; }
    .col-id { width: 70px; }

    .orders-table th,
    .orders-table td {
        padding: 10px 12px;
        font-size: 12px;
    }

    .action-button {
        padding: 4px 10px;
        font-size: 12px;
    }
}

@media (max-width: 480px) {
    .stats-container {
        grid-template-columns: 1fr;
    }

    .col-date { display: none; }
    .col-status { width: 100px; }
    .col-amount { width: 90px; }
    .col-actions { width: 80px; }

    .stat-box {
        padding: 15px;
    }

    .stat-icon {
        font-size: 24px;
    }

    .stat-value {
        font-size: 20px;
    }
}
</style>
