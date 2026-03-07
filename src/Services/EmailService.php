<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\OrderRepository;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

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
        try {
            if (self::shouldUseSmtp()) {
                $result = self::sendViaSmtp($to, $subject, $html, $attachments);
            } else {
                $result = self::sendViaMail($to, $subject, $html, $attachments);
            }

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

    private static function shouldUseSmtp(): bool
    {
        $mailer = strtolower(trim((string)($_ENV['MAIL_MAILER'] ?? 'smtp')));
        if ($mailer !== 'smtp') {
            return false;
        }

        return trim((string)($_ENV['MAIL_HOST'] ?? '')) !== ''
            && trim((string)($_ENV['MAIL_USERNAME'] ?? '')) !== ''
            && trim((string)($_ENV['MAIL_PASSWORD'] ?? '')) !== '';
    }

    /**
     * @param array<int, array{filename:string,content:string,mime:string}> $attachments
     */
    private static function sendViaSmtp(string $to, string $subject, string $html, array $attachments): bool
    {
        if (!class_exists(PHPMailer::class)) {
            error_log('SMTP mailer unavailable: PHPMailer not installed');
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = (string)($_ENV['MAIL_HOST'] ?? 'smtp.gmail.com');
            $mail->Port = (int)($_ENV['MAIL_PORT'] ?? 587);
            $mail->SMTPAuth = true;
            $mail->Username = (string)($_ENV['MAIL_USERNAME'] ?? '');
            $mail->Password = (string)($_ENV['MAIL_PASSWORD'] ?? '');

            $encryption = strtolower(trim((string)($_ENV['MAIL_ENCRYPTION'] ?? 'tls')));
            if ($encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($encryption === 'tls' || $encryption === 'starttls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = '';
                $mail->SMTPAutoTLS = false;
            }

            $fromAddress = trim((string)($_ENV['MAIL_FROM_ADDRESS'] ?? $_ENV['MAIL_USERNAME'] ?? 'noreply@haarlemfestival.nl'));
            $fromName = trim((string)($_ENV['MAIL_FROM_NAME'] ?? 'Haarlem Festival'));

            $mail->setFrom($fromAddress, $fromName);
            $mail->addAddress($to);
            $mail->CharSet = 'UTF-8';
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $html;
            $mail->AltBody = self::toPlainText($html);

            foreach ($attachments as $attachment) {
                $mail->addStringAttachment(
                    $attachment['content'],
                    $attachment['filename'],
                    PHPMailer::ENCODING_BASE64,
                    $attachment['mime']
                );
            }

            return $mail->send();
        } catch (PHPMailerException $e) {
            error_log('SMTP email error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @param array<int, array{filename:string,content:string,mime:string}> $attachments
     */
    private static function sendViaMail(string $to, string $subject, string $html, array $attachments): bool
    {
        $fromAddress = trim((string)($_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@haarlemfestival.nl'));
        $headers = "From: {$fromAddress}\r\n";
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

        return mail($to, $subject, $body, $headers);
    }

    private static function toPlainText(string $html): string
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text) ?? '';
        return trim($text);
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

        // Load and encode logo
        $logoPath = __DIR__ . '/../../public/assets/images/logo_haarlem_festival.png';
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        // Generate QR Code PNG
        $qrData = "Order #" . $orderId . " | " . htmlspecialchars_decode($customerEmail);
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'imageBase64' => false,
        ]);
        $qrCode = new QRCode($options);
        $qrPng = $qrCode->render($qrData);
        $qrBase64 = 'data:image/png;base64,' . base64_encode($qrPng);

        $rows = '';
        foreach (($order['items'] ?? []) as $item) {
            $eventTitle = htmlspecialchars((string)($item['event_title'] ?? ''), ENT_QUOTES, 'UTF-8');
            $ticketType = htmlspecialchars((string)($item['ticket_type'] ?? ''), ENT_QUOTES, 'UTF-8');
            $quantity = (int)($item['quantity'] ?? 0);
            $unitPrice = (float)($item['price_at_purchase'] ?? 0);
            $lineTotal = $quantity * $unitPrice;

            $itemDescription = $eventTitle . ' - ' . $ticketType;
            
            $rows .= '<tr>';
            $rows .= '<td>' . $itemDescription . '</td>';
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
    body { 
      font-family: DejaVu Sans, Arial, sans-serif; 
      color: #2d2d2d; 
      font-size: 11px; 
      margin: 0; 
      padding: 30px; 
      background-color: #f5f3f0;
    }
    .invoice-box { 
      background: white; 
      padding: 40px; 
      max-width: 800px; 
      margin: 0 auto; 
    }
    .top-section { 
      display: table; 
      width: 100%; 
      margin-bottom: 40px; 
    }
    .logo-cell { 
      display: table-cell; 
      width: 50%; 
      vertical-align: middle; 
    }
    .logo-cell img { 
      height: 80px; 
    }
    .invoice-title { 
      display: table-cell; 
      width: 50%; 
      text-align: right; 
      vertical-align: middle; 
    }
    .invoice-title h1 { 
      font-size: 42px; 
      margin: 0; 
      font-weight: 400; 
      letter-spacing: 2px; 
    }
    .info-section { 
      display: table; 
      width: 100%; 
      margin-bottom: 30px; 
      border-bottom: 1px solid #ddd; 
      padding-bottom: 20px; 
    }
    .bill-to { 
      display: table-cell; 
      width: 50%; 
      vertical-align: top; 
    }
    .bill-to-label { 
      font-weight: bold; 
      font-size: 12px; 
      margin-bottom: 8px; 
    }
    .bill-to-details { 
      line-height: 1.6; 
    }
    .invoice-details { 
      display: table-cell; 
      width: 50%; 
      text-align: right; 
      vertical-align: top; 
    }
    .invoice-details div { 
      line-height: 1.6; 
    }
    table.items { 
      width: 100%; 
      border-collapse: collapse; 
      margin-bottom: 20px; 
    }
    table.items thead th { 
      background: #f8f8f8; 
      text-align: left; 
      padding: 12px; 
      border-bottom: 2px solid #ddd; 
      font-weight: 600; 
      font-size: 11px; 
    }
    table.items tbody td { 
      padding: 12px; 
      border-bottom: 1px solid #eee; 
    }
    table.items thead th:last-child,
    table.items tbody td:last-child { 
      text-align: right; 
    }
    .totals-section { 
      margin-top: 30px; 
      display: table; 
      width: 100%; 
    }
    .qr-side { 
      display: table-cell; 
      width: 50%; 
      vertical-align: top; 
    }
    .qr-side img { 
      width: 100px; 
      height: 100px; 
      border: 1px solid #ddd; 
    }
    .totals-side { 
      display: table-cell; 
      width: 50%; 
      text-align: right; 
      vertical-align: top; 
    }
    .totals-table { 
      display: inline-block; 
      min-width: 250px; 
      text-align: right; 
    }
    .totals-table .row { 
      padding: 8px 0; 
      display: table; 
      width: 100%; 
    }
    .totals-table .row.total { 
      border-top: 2px solid #333; 
      font-weight: bold; 
      font-size: 16px; 
      margin-top: 8px; 
      padding-top: 12px; 
    }
    .totals-table .label { 
      display: table-cell; 
      text-align: left; 
      padding-right: 30px; 
    }
    .totals-table .value { 
      display: table-cell; 
      text-align: right; 
    }
    .thank-you { 
      margin: 40px 0 30px 0; 
      font-size: 18px; 
      font-weight: 500; 
    }
    .footer { 
      margin-top: 50px; 
      padding-top: 20px; 
      border-top: 1px solid #eee; 
      display: table; 
      width: 100%; 
      font-size: 10px; 
      color: #666; 
    }
    .footer-left { 
      display: table-cell; 
      width: 50%; 
    }
    .footer-right { 
      display: table-cell; 
      width: 50%; 
      text-align: right; 
    }
  </style>
</head>
<body>
  <div class="invoice-box">
    <div class="top-section">
      <div class="logo-cell">
        ' . ($logoBase64 ? '<img src="' . $logoBase64 . '" alt="Haarlem Festival">' : '') . '
      </div>
      <div class="invoice-title">
        <h1>INVOICE</h1>
      </div>
    </div>

    <div class="info-section">
      <div class="bill-to">
        <div class="bill-to-label">BILLED TO:</div>
        <div class="bill-to-details">
          ' . $customerName . '<br>
          ' . $customerEmail . '
        </div>
      </div>
      <div class="invoice-details">
        <div><strong>Invoice No.</strong> ' . $orderId . '</div>
        <div>' . $orderDate->format('d F Y') . '</div>
      </div>
    </div>

    <table class="items">
      <thead>
        <tr>
          <th>Item</th>
          <th style="text-align:center;">Quantity</th>
          <th style="text-align:right;">Unit Price</th>
          <th style="text-align:right;">Total</th>
        </tr>
      </thead>
      <tbody>
        ' . $rows . '
      </tbody>
    </table>

    <div class="totals-section">
      <div class="qr-side">
        <img src="' . $qrBase64 . '" alt="Order QR Code">
      </div>
      <div class="totals-side">
        <div class="totals-table">
          <div class="row">
            <div class="label">Subtotal</div>
            <div class="value">EUR ' . $totalAmount . '</div>
          </div>
          <div class="row">
            <div class="label">Tax (0%)</div>
            <div class="value">EUR 0.00</div>
          </div>
          <div class="row total">
            <div class="label">Total</div>
            <div class="value">EUR ' . $totalAmount . '</div>
          </div>
        </div>
      </div>
    </div>

    <div class="thank-you">Thank you!</div>

    <div class="footer">
      <div class="footer-left">
        <strong>PAYMENT INFORMATION</strong><br>
        Payment processed via Stripe<br>
        Order confirmation sent to ' . $customerEmail . '
      </div>
      <div class="footer-right">
        <strong>Haarlem Festival</strong><br>
        festivalhaarlem44@gmail.com<br>
        www.haarlemfestival.nl
      </div>
    </div>
  </div>
</body>
</html>';
    }
}
