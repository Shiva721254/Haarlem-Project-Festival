<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\OrderRepository;
use Dompdf\Dompdf;
use Dompdf\Options;

final class EmailService
{
    /**
     * Send order confirmation email with invoice PDF attachment.
     */
    public static function sendOrderConfirmation(int $orderId): bool
    {
        $repo = new OrderRepository();
        $order = $repo->findById($orderId);

        if (!$order) {
            return false;
        }

        $to = (string)$order['customer_email'];
        $subject = "Your Haarlem Festival Order Confirmation - Order #{$order['id']}";
        $html = self::buildOrderConfirmationEmail($order);

        $attachments = [];
        $pdfBinary = self::buildInvoicePdf($order);
        if ($pdfBinary !== null) {
            $attachments[] = [
                'filename' => 'Invoice_Order_' . $order['id'] . '.pdf',
                'content' => $pdfBinary,
                'mime' => 'application/pdf',
            ];
        }

        return self::send($to, $subject, $html, $attachments);
    }

    private static function buildOrderConfirmationEmail(array $order): string
    {
        $orderDate = new \DateTime((string)$order['created_at']);
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
                $lineTotal = $unitPrice * (int)$item['quantity'];

                $itemsHtml .= '<tr style="border-bottom:1px solid #dee2e6;">';
                $itemsHtml .= '<td style="padding:12px;"><strong>' . htmlspecialchars((string)$item['event_title'], ENT_QUOTES, 'UTF-8') . '</strong></td>';
                $itemsHtml .= '<td style="padding:12px;">' . htmlspecialchars((string)$item['ticket_type'], ENT_QUOTES, 'UTF-8') . '</td>';
                $itemsHtml .= '<td style="padding:12px; text-align:center;">' . (int)$item['quantity'] . '</td>';
                $itemsHtml .= '<td style="padding:12px; text-align:right;">EUR ' . number_format($unitPrice, 2) . '</td>';
                $itemsHtml .= '<td style="padding:12px; text-align:right;"><strong>EUR ' . number_format($lineTotal, 2) . '</strong></td>';
                $itemsHtml .= '</tr>';
            }

            $itemsHtml .= '</table>';
        }

        $customerName = htmlspecialchars((string)($order['customer_name'] ?? 'Valued Customer'), ENT_QUOTES, 'UTF-8');
        $orderId = (int)$order['id'];

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #ffffff; }
        .header { background-color: #007bff; color: #ffffff; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .header h1 { margin: 0; font-size: 28px; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .order-info { background-color: #ffffff; padding: 15px; margin: 15px 0; border-left: 4px solid #007bff; }
        .footer { background-color: #333; color: #ffffff; padding: 20px; text-align: center; border-radius: 0 0 5px 5px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Payment Successful</h1>
        </div>

        <div class="content">
            <p>Dear {$customerName},</p>

            <p>Thank you for your purchase. Your order has been confirmed and payment processed successfully.</p>

            <div class="order-info">
                <h3 style="margin-top:0; color:#007bff;">Order Details</h3>
                <p><strong>Order ID:</strong> #{$orderId}</p>
                <p><strong>Order Date:</strong> {$formattedDate}</p>
                <p><strong>Total Amount:</strong> <span style="font-size:18px; color:#28a745;">EUR {$totalAmount}</span></p>
            </div>

            <h3 style="color:#007bff;">Your Tickets</h3>
            {$itemsHtml}

            <div class="order-info">
                <h3 style="margin-top:0; color:#007bff;">What Is Next?</h3>
                <ol>
                    <li>Save your Order ID: <strong>#{$orderId}</strong></li>
                    <li>Your invoice PDF is attached to this email</li>
                    <li>Present your tickets at the event entrance</li>
                    <li>Enjoy the Haarlem Festival</li>
                </ol>
            </div>

            <div class="order-info">
                <h3 style="margin-top:0; color:#007bff;">Need Help?</h3>
                <p>If you have any questions, contact support@haarlemfestival.nl</p>
            </div>
        </div>

        <div class="footer">
            <p>&copy; 2026 Haarlem Festival. All rights reserved.</p>
            <p>This is an automated email.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * @param array<int, array{filename:string,content:string,mime:string}> $attachments
     */
    private static function send(string $to, string $subject, string $html, array $attachments = []): bool
    {
        $headers = "From: noreply@haarlemfestival.nl\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

        $body = $html;

        if ($attachments !== []) {
            $boundary = 'mixed-' . bin2hex(random_bytes(12));
            $headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";

            $body = "--{$boundary}\r\n";
            $body .= "Content-Type: text/html; charset=UTF-8\r\n";
            $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $body .= $html . "\r\n";

            foreach ($attachments as $attachment) {
                $filename = str_replace(["\r", "\n", '"'], '', $attachment['filename']);
                $mime = $attachment['mime'];
                $encoded = chunk_split(base64_encode($attachment['content']));

                $body .= "--{$boundary}\r\n";
                $body .= "Content-Type: {$mime}; name=\"{$filename}\"\r\n";
                $body .= "Content-Transfer-Encoding: base64\r\n";
                $body .= "Content-Disposition: attachment; filename=\"{$filename}\"\r\n\r\n";
                $body .= $encoded . "\r\n";
            }

            $body .= "--{$boundary}--\r\n";
        } else {
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        }

        try {
            $result = mail($to, $subject, $body, $headers);

            if ($result) {
                error_log("Email sent to {$to}: {$subject}");
            } else {
                error_log("Failed to send email to {$to}: {$subject}");
            }

            return $result;
        } catch (\Throwable $e) {
            error_log('Email error: ' . $e->getMessage());
            return false;
        }
    }

    private static function buildInvoicePdf(array $order): ?string
    {
        if (!class_exists(Dompdf::class)) {
            error_log('PDF attachment skipped: dompdf not available');
            return null;
        }

        try {
            $options = new Options();
            $options->set('isRemoteEnabled', false);
            $options->set('defaultFont', 'DejaVu Sans');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml(self::buildInvoicePdfHtml($order));
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return $dompdf->output();
        } catch (\Throwable $e) {
            error_log('Invoice PDF generation failed: ' . $e->getMessage());
            return null;
        }
    }

    private static function buildInvoicePdfHtml(array $order): string
    {
        $orderDate = new \DateTime((string)$order['created_at']);
        $customerName = htmlspecialchars((string)($order['customer_name'] ?? 'Customer'), ENT_QUOTES, 'UTF-8');
        $customerEmail = htmlspecialchars((string)($order['customer_email'] ?? ''), ENT_QUOTES, 'UTF-8');
        $orderId = (int)$order['id'];
        $totalAmount = number_format((float)$order['total_amount'], 2);

        $rows = '';
        foreach (($order['items'] ?? []) as $item) {
            $eventTitle = htmlspecialchars((string)($item['event_title'] ?? ''), ENT_QUOTES, 'UTF-8');
            $ticketType = htmlspecialchars((string)($item['ticket_type'] ?? ''), ENT_QUOTES, 'UTF-8');
            $quantity = (int)($item['quantity'] ?? 0);
            $unitPrice = (float)($item['price_at_purchase'] ?? 0);
            $lineTotal = $quantity * $unitPrice;
            $eventDate = new \DateTime((string)($item['event_date'] ?? date('Y-m-d')));

            $rows .= '<tr>';
            $rows .= '<td>' . $eventTitle . '</td>';
            $rows .= '<td>' . $ticketType . '</td>';
            $rows .= '<td style="text-align:center;">' . $eventDate->format('Y-m-d') . '</td>';
            $rows .= '<td style="text-align:center;">' . $quantity . '</td>';
            $rows .= '<td style="text-align:right;">EUR ' . number_format($unitPrice, 2) . '</td>';
            $rows .= '<td style="text-align:right;">EUR ' . number_format($lineTotal, 2) . '</td>';
            $rows .= '</tr>';
        }

        return '<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Invoice</title>
  <style>
    body { font-family: DejaVu Sans, Arial, sans-serif; color: #1f2937; font-size: 12px; }
    .header { margin-bottom: 20px; }
    .title { font-size: 26px; color: #0f5ea7; margin: 0; }
    .meta { margin-top: 4px; color: #4b5563; }
    .section { margin: 14px 0; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #f3f4f6; text-align: left; border: 1px solid #d1d5db; padding: 8px; }
    td { border: 1px solid #d1d5db; padding: 8px; }
    .totals { margin-top: 16px; width: 45%; margin-left: auto; }
    .totals td { border: none; padding: 4px 0; }
    .totals .label { text-align: left; color: #4b5563; }
    .totals .value { text-align: right; }
    .totals .grand { border-top: 1px solid #9ca3af; font-weight: bold; padding-top: 8px; }
  </style>
</head>
<body>
  <div class="header">
    <h1 class="title">Invoice</h1>
    <div class="meta">Order #' . $orderId . ' | Date: ' . $orderDate->format('Y-m-d') . '</div>
  </div>

  <div class="section">
    <strong>Bill To</strong><br>
    ' . $customerName . '<br>
    ' . $customerEmail . '
  </div>

  <table>
    <thead>
      <tr>
        <th>Event</th>
        <th>Ticket</th>
        <th style="text-align:center;">Date</th>
        <th style="text-align:center;">Qty</th>
        <th style="text-align:right;">Unit Price</th>
        <th style="text-align:right;">Amount</th>
      </tr>
    </thead>
    <tbody>
      ' . $rows . '
    </tbody>
  </table>

  <table class="totals">
    <tr><td class="label">Subtotal</td><td class="value">EUR ' . $totalAmount . '</td></tr>
    <tr><td class="label">Tax</td><td class="value">EUR 0.00</td></tr>
    <tr><td class="label grand">Total</td><td class="value grand">EUR ' . $totalAmount . '</td></tr>
  </table>
</body>
</html>';
    }
}
