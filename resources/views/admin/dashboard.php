<?php declare(strict_types=1); ?>

<div class="admin-dashboard">
    <div class="dashboard-header">
        <h1>👋 Admin Dashboard</h1>
        <p class="welcome-text">Welcome back, <strong><?= h($adminName ?? 'Admin') ?></strong></p>
    </div>

    <!-- Quick Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
                <div class="stat-value">0</div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🎪</div>
            <div class="stat-content">
                <div class="stat-value">0</div>
                <div class="stat-label">Active Events</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🎫</div>
            <div class="stat-content">
                <div class="stat-value">0</div>
                <div class="stat-label">Tickets Scanned</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-content">
                <div class="stat-value">€0</div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>
    </div>

    <!-- Admin Controls -->
    <div class="admin-controls">
        <h2>Admin Controls</h2>
        <div class="controls-grid">
            <!-- Events Management -->
            <a href="/admin/events" class="control-card events-card">
                <div class="card-icon">🎪</div>
                <div class="card-content">
                    <h3>Events Management</h3>
                    <p>Create, edit, and manage festival events</p>
                </div>
                <div class="card-arrow">→</div>
            </a>

            <!-- Orders Management -->
            <a href="/admin/orders" class="control-card orders-card">
                <div class="card-icon">📦</div>
                <div class="card-content">
                    <h3>Orders Management</h3>
                    <p>View and manage customer orders</p>
                </div>
                <div class="card-arrow">→</div>
            </a>

            <!-- Check-in System -->
            <a href="/admin/checkin" class="control-card checkin-card">
                <div class="card-icon">🎟️</div>
                <div class="card-content">
                    <h3>Check-in System</h3>
                    <p>Scan QR codes and verify attendees</p>
                </div>
                <div class="card-arrow">→</div>
            </a>

            <!-- Settings -->
            <a href="/admin/settings" class="control-card settings-card" style="opacity: 0.6; cursor: not-allowed;">
                <div class="card-icon">⚙️</div>
                <div class="card-content">
                    <h3>Settings</h3>
                    <p>Configure system settings (Coming soon)</p>
                </div>
                <div class="card-arrow">→</div>
            </a>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="recent-activity">
        <h2>Recent Activity</h2>
        <div class="activity-list">
            <div class="empty-state">
                <p>No recent activity</p>
            </div>
        </div>
    </div>
</div>

<style>
.admin-dashboard {
    max-width: 1200px;
    margin: 0 auto;
    padding: 30px 20px;
}

.dashboard-header {
    margin-bottom: 40px;
    border-bottom: 2px solid #0078d4;
    padding-bottom: 20px;
}

.dashboard-header h1 {
    margin: 0 0 8px;
    font-size: 32px;
    color: #000;
}

.welcome-text {
    margin: 0;
    color: #666;
    font-size: 14px;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

.stat-icon {
    font-size: 32px;
    min-width: 50px;
    text-align: center;
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 24px;
    font-weight: bold;
    color: #0078d4;
    margin-bottom: 4px;
}

.stat-label {
    font-size: 12px;
    color: #666;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Admin Controls */
.admin-controls {
    margin-bottom: 40px;
}

.admin-controls h2,
.recent-activity h2 {
    font-size: 20px;
    margin: 0 0 20px;
    color: #000;
    display: flex;
    align-items: center;
    gap: 8px;
}

.controls-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}

.control-card {
    background: white;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 25px;
    display: flex;
    align-items: center;
    gap: 20px;
    text-decoration: none;
    color: inherit;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.control-card:hover {
    border-color: #0078d4;
    box-shadow: 0 4px 16px rgba(0, 120, 212, 0.15);
    transform: translateY(-4px);
}

.control-card.settings-card:hover {
    border-color: #e0e0e0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transform: none;
}

.card-icon {
    font-size: 40px;
    min-width: 60px;
    text-align: center;
}

.card-content {
    flex: 1;
}

.card-content h3 {
    margin: 0 0 8px;
    font-size: 16px;
    font-weight: 600;
    color: #000;
}

.card-content p {
    margin: 0;
    font-size: 13px;
    color: #666;
}

.card-arrow {
    font-size: 24px;
    color: #0078d4;
    transition: transform 0.2s;
}

.control-card:hover .card-arrow {
    transform: translateX(4px);
}

/* Recent Activity */
.recent-activity {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 25px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.activity-list {
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.empty-state {
    text-align: center;
    color: #999;
}

.empty-state p {
    margin: 0;
    font-size: 14px;
}

/* Responsive */
@media (max-width: 768px) {
    .admin-dashboard {
        padding: 20px 15px;
    }

    .dashboard-header h1 {
        font-size: 24px;
    }

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }

    .controls-grid {
        grid-template-columns: 1fr;
    }

    .stat-card {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }

    .stat-icon {
        min-width: auto;
    }

    .control-card {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }

    .card-arrow {
        display: none;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>