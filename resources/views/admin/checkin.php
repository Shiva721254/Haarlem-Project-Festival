<?php
$title = 'Event Check-in System';
?>

<div class="admin-checkin-container">
    <div class="checkin-header">
        <h1>🎟️ Event Check-in System</h1>
        <p class="subtitle">Scan attendee QR codes to verify and check in tickets</p>
    </div>

    <div class="checkin-grid">
        <!-- LEFT: QR Scanner & Verification -->
        <div class="checkin-scanner">
            <div class="scanner-card">
                <h2>Scan Ticket</h2>
                
                <div class="qr-input-section">
                    <label for="qrInput">QR Code or Order Number:</label>
                    <div class="input-group">
                        <input 
                            type="text" 
                            id="qrInput" 
                            class="qr-input" 
                            placeholder="Scan QR code or enter ORDER-12345"
                            autocomplete="off"
                        >
                        <button id="scanBtn" class="btn btn-primary">
                            📱 Camera
                        </button>
                    </div>
                    <small class="hint">Paste scanned data or type manually</small>
                </div>

                <!-- Verification Result -->
                <div id="verificationResult" class="verification-result hidden">
                    <div class="result-card" id="resultContent"></div>
                </div>

                <!-- Loading State -->
                <div id="loadingState" class="loading-state hidden">
                    <div class="spinner"></div>
                    <p>Verifying ticket...</p>
                </div>

                <!-- Error State -->
                <div id="errorState" class="error-state hidden">
                    <p id="errorMessage" class="error-text"></p>
                </div>
            </div>

            <!-- Scan History -->
            <div class="history-card">
                <h3>Recent Scans</h3>
                <div id="scanHistory" class="scan-history">
                    <div class="empty-state">No scans yet</div>
                </div>
            </div>
        </div>

        <!-- RIGHT: Statistics & Dashboard -->
        <div class="checkin-stats">
            <div class="stats-card">
                <h2>Today's Statistics</h2>
                
                <div class="stat-row">
                    <div class="stat-item">
                        <div class="stat-value" id="totalTickets">0</div>
                        <div class="stat-label">Total Tickets</div>
                    </div>
                    <div class="stat-item checked-in">
                        <div class="stat-value" id="checkedInTickets">0</div>
                        <div class="stat-label">Checked In</div>
                    </div>
                    <div class="stat-item remaining">
                        <div class="stat-value" id="remainingTickets">0</div>
                        <div class="stat-label">Remaining</div>
                    </div>
                </div>

                <div class="progress-bar">
                    <div id="checkinProgress" class="progress-fill" style="width: 0%"></div>
                </div>
                <p class="progress-text" id="progressText">0% checked in</p>
            </div>

            <!-- Active Orders -->
            <div class="active-orders-card">
                <h3>Active Orders</h3>
                <div id="activeOrders" class="active-orders">
                    <div class="empty-state">
                        <p>No orders being processed</p>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="quick-stats">
                <div class="quick-stat">
                    <span class="label">Average Check-in Time:</span>
                    <span class="value" id="avgTime">—</span>
                </div>
                <div class="quick-stat">
                    <span class="label">Last Scan:</span>
                    <span class="value" id="lastScan">—</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Camera Input (for mobile scanning) -->
<input type="file" id="cameraInput" accept="image/*" capture="environment" style="display: none;">

<style>
.admin-checkin-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.checkin-header {
    text-align: center;
    margin-bottom: 40px;
    border-bottom: 2px solid #0078d4;
    padding-bottom: 20px;
}

.checkin-header h1 {
    margin: 0 0 8px;
    color: #000;
    font-size: 32px;
}

.checkin-header .subtitle {
    color: #666;
    margin: 0;
    font-size: 14px;
}

.checkin-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 40px;
}

/* Scanner Section */
.checkin-scanner {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.scanner-card,
.history-card,
.stats-card,
.active-orders-card {
    background: white;
    border-radius: 8px;
    padding: 25px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border: 1px solid #e0e0e0;
}

.scanner-card h2,
.history-card h3,
.stats-card h2,
.active-orders-card h3 {
    margin: 0 0 20px;
    color: #000;
    display: flex;
    align-items: center;
    gap: 8px;
}

.qr-input-section {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 20px;
}

.qr-input-section label {
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

.input-group {
    display: flex;
    gap: 10px;
}

.qr-input {
    flex: 1;
    padding: 12px 16px;
    border: 2px solid #ddd;
    border-radius: 6px;
    font-size: 16px;
    font-family: 'Courier New', monospace;
    transition: border-color 0.2s;
}

.qr-input:focus {
    outline: none;
    border-color: #0078d4;
    box-shadow: 0 0 0 3px rgba(0, 120, 212, 0.1);
}

.btn-primary {
    padding: 12px 20px;
    background: #0078d4;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-primary:hover {
    background: #106ebe;
}

.hint {
    color: #999;
    font-size: 12px;
}

/* Verification Result */
.verification-result {
    margin-top: 20px;
}

.verification-result.hidden,
.loading-state.hidden,
.error-state.hidden {
    display: none;
}

.result-card {
    background: #f0f7ff;
    border: 2px solid #0078d4;
    border-radius: 8px;
    padding: 20px;
}

.result-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #ddd;
    font-size: 14px;
}

.result-item:last-child {
    border: none;
}

.result-label {
    color: #666;
    font-weight: 600;
}

.result-value {
    color: #000;
    font-weight: 500;
}

.check-in-button {
    width: 100%;
    padding: 14px;
    background: #107c10;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    margin-top: 15px;
    transition: background 0.2s;
}

.check-in-button:hover:not(:disabled) {
    background: #0d6e09;
}

.check-in-button:disabled {
    background: #ccc;
    cursor: not-allowed;
}

/* Loading State */
.loading-state {
    text-align: center;
    padding: 40px 20px;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #0078d4;
    border-top: 4px solid transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 15px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Error State */
.error-state {
    background: #fef0f0;
    border: 2px solid #d13438;
    border-radius: 8px;
    padding: 15px;
    margin-top: 20px;
}

.error-text {
    color: #d13438;
    margin: 0;
    font-size: 14px;
}

/* Scan History */
.history-card {
    flex: 1;
}

.scan-history {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.history-item {
    padding: 12px;
    background: #f5f5f5;
    border-radius: 6px;
    font-size: 13px;
    border-left: 4px solid #107c10;
}

.history-time {
    color: #999;
    font-size: 12px;
}

.history-customer {
    color: #000;
    font-weight: 600;
    margin: 4px 0;
}

.history-order {
    color: #666;
    font-size: 12px;
}

.empty-state {
    text-align: center;
    color: #999;
    padding: 30px 20px;
    font-size: 14px;
}

/* Statistics Section */
.checkin-stats {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.stats-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 25px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.stat-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-bottom: 25px;
}

.stat-item {
    text-align: center;
    padding: 15px;
    background: #f5f5f5;
    border-radius: 6px;
    border-top: 3px solid #ddd;
}

.stat-item.checked-in {
    background: #f0fff4;
    border-top-color: #107c10;
}

.stat-item.remaining {
    background: #fff9e6;
    border-top-color: #ffc646;
}

.stat-value {
    font-size: 28px;
    font-weight: bold;
    color: #000;
    margin-bottom: 5px;
}

.stat-item.checked-in .stat-value {
    color: #107c10;
}

.stat-item.remaining .stat-value {
    color: #ccaa00;
}

.stat-label {
    font-size: 12px;
    color: #666;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 8px;
}

.progress-fill {
    height: 100%;
    background: #107c10;
    transition: width 0.3s ease;
}

.progress-text {
    font-size: 12px;
    color: #666;
    margin: 0;
    text-align: right;
}

/* Active Orders */
.active-orders-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 25px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.active-order-item {
    padding: 12px;
    background: #f9f9f9;
    border-radius: 6px;
    margin-bottom: 10px;
    font-size: 13px;
}

.order-header {
    font-weight: 600;
    color: #000;
    margin-bottom: 4px;
}

.order-details {
    color: #666;
    font-size: 12px;
    line-height: 1.4;
}

/* Quick Stats */
.quick-stats {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.quick-stat {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
    font-size: 13px;
}

.quick-stat:last-child {
    border: none;
}

.quick-stat .label {
    color: #666;
    font-weight: 600;
}

.quick-stat .value {
    color: #000;
    font-weight: 500;
}

/* Responsive */
@media (max-width: 1024px) {
    .checkin-grid {
        grid-template-columns: 1fr;
    }

    .stat-row {
        grid-template-columns: 1fr;
    }

    .stat-value {
        font-size: 24px;
    }
}

@media (max-width: 768px) {
    .checkin-header h1 {
        font-size: 24px;
    }

    .input-group {
        flex-direction: column;
    }

    .btn-primary {
        width: 100%;
    }

    .stat-item {
        padding: 12px;
    }

    .stat-value {
        font-size: 20px;
    }
}

/* Success Animation */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.result-card {
    animation: slideIn 0.3s ease;
}

.history-item {
    animation: slideIn 0.3s ease;
}
</style>

<script>
const qrInput = document.getElementById('qrInput');
const scanBtn = document.getElementById('scanBtn');
const verificationResult = document.getElementById('verificationResult');
const resultContent = document.getElementById('resultContent');
const loadingState = document.getElementById('loadingState');
const errorState = document.getElementById('errorState');
const errorMessage = document.getElementById('errorMessage');
const scanHistory = document.getElementById('scanHistory');
const historyItems = [];
const cameraInput = document.getElementById('cameraInput');

let lastOrderData = null;

// Listen for QR code input
qrInput.addEventListener('keydown', async (e) => {
    if (e.key === 'Enter') {
        const qrCode = qrInput.value.trim();
        if (qrCode) {
            await verifyTicket(qrCode);
        }
    }
});

// Camera button (optional for mobile)
scanBtn.addEventListener('click', () => {
    cameraInput.click();
});

cameraInput.addEventListener('change', async (e) => {
    // For now, just focus the input field
    qrInput.focus();
});

async function verifyTicket(qrCode) {
    // Clear previous states
    verificationResult.classList.add('hidden');
    errorState.classList.add('hidden');
    loadingState.classList.remove('hidden');

    try {
        const response = await fetch('/ticket/verify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ qr_code: qrCode })
        });

        const data = await response.json();
        loadingState.classList.add('hidden');

        if (!response.ok || data.error) {
            showError(data.error || 'Invalid QR code');
            return;
        }

        lastOrderData = data;
        displayVerificationResult(data);
        addToHistory(data);

    } catch (error) {
        loadingState.classList.add('hidden');
        showError('Network error: ' + error.message);
    }
}

function displayVerificationResult(data) {
    let html = `
        <div class="result-item">
            <span class="result-label">Order #</span>
            <span class="result-value">${data.order_id}</span>
        </div>
        <div class="result-item">
            <span class="result-label">Customer</span>
            <span class="result-value">${data.customer_name}</span>
        </div>
        <div class="result-item">
            <span class="result-label">Email</span>
            <span class="result-value">${data.customer_email}</span>
        </div>
        <div class="result-item">
            <span class="result-label">Tickets Available</span>
            <span class="result-value">${data.unscanned_count} / ${data.total_count}</span>
        </div>
        <div class="result-item">
            <span class="result-label">Event</span>
            <span class="result-value">${data.event_name}</span>
        </div>
        <div class="result-item">
            <span class="result-label">Event Date</span>
            <span class="result-value">${data.event_date}</span>
        </div>
    `;

    if (data.unscanned_count > 0) {
        html += `
            <button class="check-in-button" onclick="markTicketAsUsed()">
                ✓ Check In ${data.unscanned_count} Ticket${data.unscanned_count > 1 ? 's' : ''}
            </button>
        `;
    } else {
        html += `
            <div style="padding: 12px; background: #ffe6e6; color: #d13438; border-radius: 4px; text-align: center; margin-top: 15px;">
                All tickets already checked in
            </div>
        `;
    }

    resultContent.innerHTML = html;
    verificationResult.classList.remove('hidden');
    
    // Clear input after successful verification
    qrInput.value = '';
    qrInput.focus();
}

async function markTicketAsUsed() {
    if (!lastOrderData) return;

    loadingState.classList.remove('hidden');
    verificationResult.classList.add('hidden');

    try {
        const response = await fetch('/ticket/mark-used', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                qr_code: 'ORDER-' + lastOrderData.order_id
            })
        });

        const data = await response.json();
        loadingState.classList.add('hidden');

        if (!response.ok || data.error) {
            showError(data.error || 'Failed to check in ticket');
            return;
        }

        // Show success message
        resultContent.innerHTML = `
            <div style="text-align: center; padding: 20px; background: #f0fff4; border: 2px solid #107c10; border-radius: 8px;">
                <div style="font-size: 32px; margin-bottom: 10px;">✓</div>
                <div style="font-size: 16px; font-weight: 600; color: #107c10; margin-bottom: 8px;">Ticket Checked In Successfully!</div>
                <div style="font-size: 13px; color: #666;">
                    ${lastOrderData.customer_name}<br>
                    Order #${lastOrderData.order_id}
                </div>
            </div>
        `;
        verificationResult.classList.remove('hidden');

        // Refresh statistics
        updateStatistics();

        lastOrderData = null;
    } catch (error) {
        loadingState.classList.add('hidden');
        showError('Network error: ' + error.message);
    }
}

function showError(message) {
    errorMessage.textContent = message;
    errorState.classList.remove('hidden');
    verificationResult.classList.add('hidden');
}

function addToHistory(data) {
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit',
        second: '2-digit'
    });

    const item = document.createElement('div');
    item.className = 'history-item';
    item.innerHTML = `
        <div class="history-time">${timeString}</div>
        <div class="history-customer">${data.customer_name}</div>
        <div class="history-order">Order #${data.order_id} • ${data.unscanned_count} tickets available</div>
    `;

    if (scanHistory.querySelector('.empty-state')) {
        scanHistory.innerHTML = '';
    }

    scanHistory.insertBefore(item, scanHistory.firstChild);
    if (scanHistory.children.length > 10) {
        scanHistory.removeChild(scanHistory.lastChild);
    }
}

async function updateStatistics() {
    // Update stat cards
    document.getElementById('totalTickets').textContent = '0';
    document.getElementById('checkedInTickets').textContent = '0';
    document.getElementById('remainingTickets').textContent = '0';
    document.getElementById('checkinProgress').style.width = '0%';
    document.getElementById('progressText').textContent = '0% checked in';
}

// Initialize
updateStatistics();
</script>
