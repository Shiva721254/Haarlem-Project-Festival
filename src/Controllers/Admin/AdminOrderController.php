<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Framework\Response;
use App\Repositories\OrderRepository;

final class AdminOrderController
{
    /**
     * List all orders
     */
    public function index(): Response
    {
        $repo = new OrderRepository();
        
        // Get all orders sorted by recent first
        $orders = $repo->findAll();

        return Response::html(view('admin/orders/index', [
            'title' => 'Order Management',
            'orders' => $orders,
            'orderCount' => count($orders),
            'totalRevenue' => array_sum(array_column($orders, 'total_amount')),
            'completedCount' => count(array_filter($orders, fn($o) => $o['status'] === 'completed')),
        ]));
    }

    /**
     * View order details
     */
    public function detail(): Response
    {
        $orderId = (int)($_GET['id'] ?? 0);

        if (!$orderId) {
            return Response::redirect('/admin/orders');
        }

        $repo = new OrderRepository();
        $order = $repo->findById($orderId);

        if (!$order) {
            return Response::redirect('/admin/orders');
        }

        return Response::html(view('admin/orders/detail', [
            'title' => 'Order #' . $order['id'],
            'order' => $order,
            'items' => $order['items'] ?? [],
        ]));
    }

    /**
     * Update order status
     */
    public function updateStatus(): Response
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return Response::redirect('/admin/orders');
        }

        $orderId = (int)($_POST['order_id'] ?? 0);
        $status = $_POST['status'] ?? null;

        if (!$orderId || !in_array($status, ['pending', 'completed', 'failed', 'cancelled'])) {
            return Response::redirect('/admin/orders');
        }

        $repo = new OrderRepository();
        $repo->updateStatus($orderId, $status);

        return Response::redirect("/admin/orders/detail?id=$orderId");
    }
}
