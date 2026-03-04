<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Framework\Auth;
use App\Framework\Response;
use App\Repositories\OrderRepository;
use PDO;

final class TicketVerificationController
{
    public function __construct(
        private PDO $pdo = new PDO(
            'mysql:host=mysql:3306;dbname=developmentdb',
            'developer',
            'secret123'
        )
    ) {}

    /**
     * Verify a ticket using QR code data
     * QR code contains: ORDER-{order_id}
     */
    public function verify(): Response
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return Response::json(['error' => 'POST required'], 405);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $qrData = $data['qr_code'] ?? '';

        if (!$qrData) {
            return Response::json(['error' => 'QR code required'], 400);
        }

        // Parse QR code: ORDER-123
        if (!preg_match('/^ORDER-(\d+)$/', $qrData, $matches)) {
            return Response::json(['error' => 'Invalid QR code format'], 400);
        }

        $orderId = (int)$matches[1];

        try {
            $repo = new OrderRepository();
            $order = $repo->findById($orderId);

            if (!$order) {
                return Response::json(['error' => 'Order not found'], 404);
            }

            // Get unscanned items
            $stmt = $this->pdo->prepare('
                SELECT oi.*, e.title as event_title, e.event_date
                FROM order_items oi
                JOIN events e ON oi.event_id = e.id
                WHERE oi.order_id = ? AND oi.is_used = FALSE
            ');
            $stmt->execute([$orderId]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($items)) {
                return Response::json([
                    'status' => 'already_used',
                    'message' => 'All tickets for this order have been scanned',
                    'order_id' => $orderId,
                ], 200);
            }

            // Verify order status
            if ($order['status'] !== 'completed') {
                return Response::json([
                    'status' => 'invalid',
                    'message' => 'Order status is not completed',
                    'order_id' => $orderId,
                ], 400);
            }

            // Check event date
            foreach ($items as $item) {
                $eventDate = new \DateTime($item['event_date']);
                $now = new \DateTime();
                
                if ($eventDate < $now) {
                    return Response::json([
                        'status' => 'expired',
                        'message' => 'Event date has passed',
                        'order_id' => $orderId,
                    ], 400);
                }
            }

            // Get total count (including already used)
            $stmtTotal = $this->pdo->prepare('
                SELECT COUNT(*) as total_count
                FROM order_items
                WHERE order_id = ?
            ');
            $stmtTotal->execute([$orderId]);
            $totalResult = $stmtTotal->fetch(PDO::FETCH_ASSOC);
            $totalCount = (int)$totalResult['total_count'];
            $unscannedCount = count($items);

            // Get first event name and date for display
            $firstEvent = $items[0] ?? null;
            $eventName = $firstEvent['event_title'] ?? 'Event';
            $eventDate = $firstEvent['event_date'] ?? date('Y-m-d');

            return Response::json([
                'order_id' => $orderId,
                'customer_name' => $order['customer_name'] ?? 'Guest',
                'customer_email' => $order['customer_email'] ?? '',
                'unscanned_count' => $unscannedCount,
                'total_count' => $totalCount,
                'event_name' => $eventName,
                'event_date' => $eventDate,
            ], 200);

        } catch (\Exception $e) {
            return Response::json(['error' => 'Verification failed'], 500);
        }
    }

    /**
     * Mark ticket as used/scanned
     */
    public function markUsed(): Response
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return Response::json(['error' => 'POST required'], 405);
        }

        // Require admin auth
        if (!Auth::check() || !Auth::isAdmin()) {
            return Response::json(['error' => 'Unauthorized'], 403);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $qrData = $data['qr_code'] ?? '';

        if (!$qrData) {
            return Response::json(['error' => 'QR code required'], 400);
        }

        // Parse QR code
        if (!preg_match('/^ORDER-(\d+)$/', $qrData, $matches)) {
            return Response::json(['error' => 'Invalid QR code format'], 400);
        }

        $orderId = (int)$matches[1];
        $userId = Auth::user()['id'];

        try {
            // Get the first unscanned item
            $stmt = $this->pdo->prepare('
                SELECT id FROM order_items 
                WHERE order_id = ? AND is_used = FALSE
                LIMIT 1
            ');
            $stmt->execute([$orderId]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                return Response::json([
                    'status' => 'no_tickets',
                    'error' => 'No unscanned tickets available for this order'
                ], 400);
            }

            $itemId = (int)$item['id'];

            // Mark as used
            $stmt = $this->pdo->prepare('
                UPDATE order_items 
                SET is_used = TRUE, used_at = NOW(), checked_by_user_id = ?
                WHERE id = ?
            ');
            $stmt->execute([$userId, $itemId]);

            // Log the scan
            $stmt = $this->pdo->prepare('
                INSERT INTO ticket_scans (order_item_id, user_id, ip_address, user_agent)
                VALUES (?, ?, ?, ?)
            ');
            $stmt->execute([
                $itemId,
                $userId,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);

            return Response::json([
                'status' => 'success',
                'message' => 'Ticket marked as used',
                'order_id' => $orderId,
            ], 200);

        } catch (\Exception $e) {
            return Response::json(['error' => 'Failed to mark ticket: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get ticket status
     */
    public function getStatus(): Response
    {
        $orderId = (int)($_GET['order_id'] ?? 0);

        if (!$orderId) {
            return Response::json(['error' => 'Order ID required'], 400);
        }

        try {
            $stmt = $this->pdo->prepare('
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_used = TRUE THEN 1 ELSE 0 END) as used,
                    SUM(CASE WHEN is_used = FALSE THEN 1 ELSE 0 END) as unused
                FROM order_items 
                WHERE order_id = ?
            ');
            $stmt->execute([$orderId]);
            $status = $stmt->fetch(PDO::FETCH_ASSOC);

            return Response::json([
                'order_id' => $orderId,
                'total_tickets' => (int)$status['total'],
                'used_tickets' => (int)$status['used'],
                'unused_tickets' => (int)$status['unused'],
                'status' => $status['unused'] == 0 ? 'complete' : 'partial',
            ], 200);

        } catch (\Exception $e) {
            return Response::json(['error' => 'Failed to get status'], 500);
        }
    }
}
