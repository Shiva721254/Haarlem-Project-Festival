<div class="invoice-container">
    <div class="invoice-header">
        <div class="invoice-title">
            <h1>INVOICE</h1>
            <p>Order #<?= h($order['id']) ?></p>
        </div>
        <div class="invoice-company">
            <h3>Haarlem Festival</h3>
            <p>Your premiere event destination</p>
        </div>
    </div>

    <div class="invoice-details">
        <div class="invoice-section">
            <h4>Bill To:</h4>
            <p class="customer-name"><?= h($order['customer_name'] ?? 'Customer') ?></p>
            <p><?= h($order['customer_email']) ?></p>
        </div>

        <div class="invoice-section text-right">
            <table class="details-table">
                <tr>
                    <td><strong>Order Date:</strong></td>
                    <td>
                        <?php 
                            $date = new DateTime($order['created_at']);
                            echo h($date->format('M d, Y'));
                        ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>Order ID:</strong></td>
                    <td><?= h($order['id']) ?></td>
                </tr>
                <tr>
                    <td><strong>Status:</strong></td>
                    <td>
                        <?php
                            $status = $order['status'];
                            $badgeClass = 'badge-secondary';
                            if ($status === 'completed') {
                                $badgeClass = 'badge-success';
                            } elseif ($status === 'pending') {
                                $badgeClass = 'badge-warning';
                            } elseif ($status === 'failed' || $status === 'cancelled') {
                                $badgeClass = 'badge-danger';
                            }
                        ?>
                        <span class="badge <?= h($badgeClass) ?>">
                            <?= h(ucfirst($status)) ?>
                        </span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="invoice-items">
        <table class="items-table">
            <thead>
                <tr>
                    <th class="text-left">Event</th>
                    <th class="text-center">Type</th>
                    <th class="text-center">Date</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <?php
                        $unitPrice = (float)$item['price_at_purchase'];
                        $totalPrice = $unitPrice * $item['quantity'];
                    ?>
                    <tr>
                        <td class="text-left"><?= h($item['event_title']) ?></td>
                        <td class="text-center"><?= h($item['ticket_type']) ?></td>
                        <td class="text-center">
                            <?php 
                                $eventDate = new DateTime($item['event_date']);
                                echo h($eventDate->format('M d'));
                            ?>
                        </td>
                        <td class="text-center"><?= h($item['quantity']) ?></td>
                        <td class="text-right">&euro;<?= h(number_format($unitPrice, 2)) ?></td>
                        <td class="text-right">&euro;<?= h(number_format($totalPrice, 2)) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="invoice-totals-section">
        <div class="qr-section">
            <div class="qr-box">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=ORDER-<?= h($order['id']) ?>" 
                     alt="Order QR Code" 
                     class="qr-code"
                     title="Scan at entrance">
                <p class="qr-label">Scan at Entrance</p>
            </div>
        </div>

        <div class="totals-box">
            <table class="totals-table">
                <tr>
                    <td class="label">Subtotal:</td>
                    <td class="amount">&euro;<?= h(number_format((float)$order['total_amount'], 2)) ?></td>
                </tr>
                <tr>
                    <td class="label">Tax:</td>
                    <td class="amount">&euro;0.00</td>
                </tr>
                <tr class="total-row">
                    <td class="label"><strong>Total Due:</strong></td>
                    <td class="amount"><strong>&euro;<?= h(number_format((float)$order['total_amount'], 2)) ?></strong></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="invoice-footer">
        <div class="payment-info">
            <strong>Payment Status:</strong> Completed
            <br>
            <strong>Order Reference:</strong> #<?= h($order['id']) ?>
        </div>
    </div>

    <div class="invoice-next-steps">
        <strong>⚡ Next Steps:</strong>
        <ol>
            <li>Save this invoice or take a screenshot</li>
            <li>Scan the QR code at the event entrance</li>
            <li>Or show your Order ID: <code>#<?= h($order['id']) ?></code></li>
        </ol>
    </div>

    <div class="invoice-actions">
        <a href="/profile/orders" class="btn btn-secondary">← Back to Orders</a>
        <a href="/schedule" class="btn btn-primary">Browse More Events →</a>
    </div>
</div>

<style>
    /* Invoice Container */
    .invoice-container {
        max-width: 850px;
        margin: 2rem auto;
        background: white;
        padding: 2.5rem;
        border: 1px solid #d0d0d0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    /* Invoice Header */
    .invoice-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 2px solid #0078d4;
    }

    .invoice-title h1 {
        margin: 0;
        font-size: 1.75rem;
        color: #0078d4;
        font-weight: 600;
        letter-spacing: 1px;
    }

    .invoice-title p {
        margin: 0.5rem 0 0 0;
        font-size: 0.95rem;
        color: #666;
    }

    .invoice-company h3 {
        margin: 0;
        font-size: 1.1rem;
        color: #323232;
        font-weight: 600;
    }

    .invoice-company p {
        margin: 0.25rem 0 0 0;
        font-size: 0.85rem;
        color: #999;
    }

    /* Invoice Details - Bill To & Order Info */
    .invoice-details {
        display: flex;
        justify-content: space-between;
        margin-bottom: 2rem;
        gap: 2rem;
    }

    .invoice-section {
        flex: 1;
    }

    .invoice-section h4 {
        margin: 0 0 0.5rem 0;
        font-size: 0.9rem;
        color: #0078d4;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .invoice-section p {
        margin: 0.25rem 0;
        font-size: 0.95rem;
        color: #505050;
    }

    .customer-name {
        font-weight: 600;
        font-size: 1rem;
        color: #323232 !important;
    }

    .invoice-section.text-right {
        text-align: right;
    }

    .details-table {
        width: 100%;
        border-collapse: collapse;
    }

    .details-table td {
        padding: 0.25rem 0;
        font-size: 0.9rem;
    }

    .details-table td:first-child {
        text-align: right;
        padding-right: 1rem;
        color: #666;
    }

    .details-table td:last-child {
        text-align: right;
        color: #323232;
    }

    /* Items Table */
    .invoice-items {
        margin: 2rem 0;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        border-top: 2px solid #0078d4;
        border-bottom: 2px solid #0078d4;
    }

    .items-table thead {
        background-color: #f5f5f5;
    }

    .items-table th {
        padding: 0.75rem;
        font-size: 0.85rem;
        font-weight: 600;
        color: #323232;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border-bottom: 1px solid #d0d0d0;
    }

    .items-table td {
        padding: 0.75rem;
        font-size: 0.9rem;
        color: #505050;
        border-bottom: 1px solid #e8e8e8;
    }

    .items-table tbody tr:last-child td {
        border-bottom: 2px solid #0078d4;
    }

    .text-left {
        text-align: left;
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    /* Totals and QR Section - Side by Side */
    .invoice-totals-section {
        display: flex;
        gap: 2rem;
        margin: 2rem 0;
        align-items: stretch;
    }

    .totals-box {
        flex: 1;
        width: 50%;
    }

    .totals-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #d0d0d0;
    }

    .totals-table tr {
        border-bottom: 1px solid #e8e8e8;
    }

    .totals-table td {
        padding: 0.6rem 0.75rem;
        font-size: 0.9rem;
    }

    .totals-table .label {
        text-align: left;
        color: #666;
    }

    .totals-table .amount {
        text-align: right;
        color: #323232;
        font-weight: 600;
        width: 40%;
    }

    .totals-table .total-row {
        background-color: #f5f5f5;
        border-bottom: none;
        border-top: 2px solid #0078d4;
    }

    .totals-table .total-row .label {
        color: #0078d4;
        font-weight: 700;
    }

    .totals-table .total-row .amount {
        color: #0078d4;
        font-size: 1.05rem;
    }

    .qr-section {
        flex: 1;
        width: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .qr-box {
        background: white;
        border: 2px solid #d0d0d0;
        padding: 1rem;
        border-radius: 4px;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .qr-code {
        display: block;
        width: 120px;
        height: 120px;
        margin: 0;
    }

    .qr-label {
        margin: 0.75rem 0 0 0;
        font-size: 0.85rem;
        font-weight: 600;
        color: #0078d4;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    /* Footer Sections */
    .invoice-footer {
        margin: 2rem 0;
        padding: 1.5rem;
        background-color: #f9f9f9;
        border-left: 3px solid #0078d4;
    }

    .payment-info {
        font-size: 0.95rem;
        color: #505050;
        line-height: 1.6;
        margin-bottom: 1rem;
    }

    .payment-info code {
        background-color: #e8f4f8;
        padding: 0.2rem 0.5rem;
        border-radius: 2px;
        color: #0078d4;
        font-weight: 600;
    }

    .invoice-next-steps {
        margin: 0;
        padding: 1rem;
        background-color: #f0f6ff;
        border-left: 3px solid #0078d4;
        font-size: 0.95rem;
        color: #505050;
    }

    .invoice-next-steps strong {
        color: #0078d4;
        font-weight: 700;
    }

    .invoice-next-steps ol {
        margin: 0.5rem 0 0 1.5rem;
        padding: 0;
    }

    .invoice-next-steps li {
        margin-bottom: 0.35rem;
        font-size: 0.9rem;
    }

    .invoice-next-steps code {
        background-color: white;
        padding: 0.3rem 0.6rem;
        border-radius: 2px;
        color: #0078d4;
        font-weight: 600;
    }

    /* Action Buttons */
    .invoice-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e0e0e0;
    }

    .btn {
        padding: 0.6rem 1.5rem;
        border: none;
        border-radius: 2px;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .btn-primary {
        background-color: #0078d4;
        color: white;
    }

    .btn-primary:hover {
        background-color: #006cbe;
        color: white;
    }

    .btn-secondary {
        background-color: #f5f5f5;
        color: #323232;
        border: 1px solid #d0d0d0;
    }

    .btn-secondary:hover {
        background-color: #e8e8e8;
        color: #323232;
    }

    /* Badges */
    .badge {
        padding: 0.3rem 0.7rem;
        font-size: 0.8rem;
        border-radius: 2px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.2px;
    }

    .badge-success {
        background-color: #107c10;
        color: white;
    }

    .badge-warning {
        background-color: #ffd21e;
        color: #323232;
    }

    .badge-danger {
        background-color: #d83b01;
        color: white;
    }

    .badge-secondary {
        background-color: #999;
        color: white;
    }

    /* Print Styles */
    @media print {
        .invoice-container {
            max-width: 100%;
            box-shadow: none;
            border: none;
        }

        .invoice-actions {
            display: none;
        }

        body {
            background: white;
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .invoice-container {
            padding: 1.5rem;
            margin: 1rem;
        }

        .invoice-header {
            flex-direction: column;
            gap: 1rem;
        }

        .invoice-details {
            flex-direction: column;
            gap: 1rem;
        }

        .invoice-section.text-right {
            text-align: left;
        }

        .details-table td:first-child {
            text-align: left;
            padding-right: 0.5rem;
        }

        .invoice-totals-section {
            flex-direction: column;
            gap: 1rem;
        }

        .totals-box {
            width: 100%;
        }

        .qr-section {
            display: flex;
            justify-content: center;
        }

        .qr-box {
            height: auto;
        }

        .qr-code {
            width: 150px;
            height: 150px;
        }

        .invoice-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            text-align: center;
        }

        .items-table {
            font-size: 0.85rem;
        }

        .items-table th,
        .items-table td {
            padding: 0.5rem;
        }

        .invoice-footer {
            margin-top: 1rem;
        }

        .invoice-next-steps {
            margin: 1rem 0 0 0;
        }
    }
</style>
