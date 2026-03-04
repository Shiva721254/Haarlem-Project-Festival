<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\OrderRepository;

final class EmailService
{
    /**
     * Send order confirmation email
     * 
     * @param int $orderId Order ID
     * @return bool Success
     */
    public static function sendOrderConfirmation(int $orderId): bool
    {
        $repo = new OrderRepository();
        $order = $repo->findById($orderId);

        if (!$order) {
            return false;
        }

        // Build email content
        $to = $order['customer_email'];
        $subject = "Your Haarlem Festival Order Confirmation - Order #{$order['id']}";
        
        $html = self::buildOrderConfirmationEmail($order);
        
        // Send email
        return self::send($to, $subject, $html);
    }

    /**
     * Build HTML email body
     */
    private static function buildOrderConfirmationEmail(array $order): string
    {
        $orderDate = new \DateTime($order['created_at']);
        $formattedDate = $orderDate->format('F d, Y');
        $totalAmount = number_format((float)$order['total_amount'], 2);
        
        $itemsHtml = '';
        if (!empty($order['items'])) {
            $itemsHtml = '<table style="width:100%; border-collapse:collapse; margin:20px 0;">';
            $itemsHtml .= '<tr style="background-color:#f8f9fa; border-bottom:2px solid #dee2e6;">';
            $itemsHtml .= '<th style="padding:12px; text-align:left; font-weight:bold;">Event</th>';
            $itemsHtml .= '<th style="padding:12px; text-align:left; font-weight:bold;">Ticket Type</th>';
            $itemsHtml .= '<th style="padding:12px; text-align:center; font-weight:bold;">Qty</th>';
            $itemsHtml .= '<th style="padding:12px; text-align:right; font-weight:bold;">Price</th>';
            $itemsHtml .= '<th style="padding:12px; text-align:right; font-weight:bold;">Total</th>';
            $itemsHtml .= '</tr>';
            
            foreach ($order['items'] as $item) {
                $unitPrice = (float)$item['price_at_purchase'];
                $totalPrice = $unitPrice * $item['quantity'];
                $eventDate = new \DateTime($item['event_date']);
                
                $itemsHtml .= '<tr style="border-bottom:1px solid #dee2e6;">';
                $itemsHtml .= '<td style="padding:12px;"><strong>' . htmlspecialchars($item['event_title']) . '</strong></td>';
                $itemsHtml .= '<td style="padding:12px;">' . htmlspecialchars($item['ticket_type']) . '</td>';
                $itemsHtml .= '<td style="padding:12px; text-align:center;">' . $item['quantity'] . '</td>';
                $itemsHtml .= '<td style="padding:12px; text-align:right;">€' . number_format($unitPrice, 2) . '</td>';
                $itemsHtml .= '<td style="padding:12px; text-align:right;"><strong>€' . number_format($totalPrice, 2) . '</strong></td>';
                $itemsHtml .= '</tr>';
            }
            
            $itemsHtml .= '</table>';
        }

        $customerName = htmlspecialchars($order['customer_name'] ?? 'Valued Customer');
        $orderId = $order['id'];

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
        }
        .header {
            background-color: #007bff;
            color: #ffffff;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 20px;
            background-color: #f9f9f9;
        }
        .order-info {
            background-color: #ffffff;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #007bff;
        }
        .footer {
            background-color: #333;
            color: #ffffff;
            padding: 20px;
            text-align: center;
            border-radius: 0 0 5px 5px;
            font-size: 12px;
        }
        .success-badge {
            display: inline-block;
            background-color: #28a745;
            color: #ffffff;
            padding: 8px 16px;
            border-radius: 4px;
            margin: 10px 0;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✓ Payment Successful!</h1>
        </div>
        
        <div class="content">
            <p>Dear $customerName,</p>
            
            <p>Thank you for your purchase! Your order has been confirmed and payment processed successfully.</p>
            
            <div class="order-info">
                <h3 style="margin-top:0; color:#007bff;">Order Details</h3>
                <p><strong>Order ID:</strong> #$orderId</p>
                <p><strong>Order Date:</strong> $formattedDate</p>
                <p><strong>Total Amount:</strong> <span style="font-size:18px; color:#28a745;">€$totalAmount</span></p>
            </div>
            
            <h3 style="color:#007bff;">Your Tickets</h3>
            $itemsHtml
            
            <div class="order-info">
                <h3 style="margin-top:0; color:#007bff;">What's Next?</h3>
                <ol>
                    <li>Save your Order ID: <strong>#$orderId</strong></li>
                    <li>You'll receive your tickets via email shortly</li>
                    <li>Present your tickets at the event entrance</li>
                    <li>Enjoy the Haarlem Festival! 🎉</li>
                </ol>
            </div>
            
            <div class="order-info">
                <h3 style="margin-top:0; color:#007bff;">Need Help?</h3>
                <p>If you have any questions about your order, please reply to this email or contact our support team at support@haarlefestival.nl</p>
            </div>
        </div>
        
        <div class="footer">
            <p>&copy; 2026 Haarlem Festival. All rights reserved.</p>
            <p>This is an automated email. Please do not reply directly to this message.</p>
        </div>
    </div>
</body>
</html>
HTML;
        
        return $html;
    }

    /**
     * Send email using PHP mail()
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $html HTML body
     * @return bool Success
     */
    private static function send(string $to, string $subject, string $html): bool
    {
        // Set headers for HTML email
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: noreply@haarlefestival.nl\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        
        try {
            $result = mail($to, $subject, $html, $headers);
            
            if ($result) {
                error_log("Email sent to {$to}: {$subject}");
            } else {
                error_log("Failed to send email to {$to}: {$subject}");
            }
            
            return $result;
        } catch (\Exception $e) {
            error_log("Email error: " . $e->getMessage());
            return false;
        }
    }
}
