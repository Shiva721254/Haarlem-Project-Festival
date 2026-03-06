<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Framework\Auth;
use App\Framework\Response;
use App\Repositories\OrderRepository;

final class UserOrderController
{
    public function orders(): Response
    {
        // Require authentication
        if (!Auth::check()) {
            return Response::redirect('/login');
        }

        $userId = Auth::user()['id'];
        $repo = new OrderRepository();

        // Get all orders for this user
        $orders = $repo->findByUserId($userId);

        return Response::html(view('profile/orders', [
            'title' => 'My Orders',
            'orders' => $orders,
            'userName' => Auth::user()['first_name'] ?? Auth::user()['email'],
        ]));
    }

    public function orderDetail(): Response
    {
        // Require authentication
        if (!Auth::check()) {
            return Response::redirect('/login');
        }

        $userId = Auth::user()['id'];
        $orderId = (int)($_GET['id'] ?? 0);

        if (!$orderId) {
            return Response::redirect('/profile/orders');
        }

        $repo = new OrderRepository();

        // Get order details
        $order = $repo->findById($orderId);

        // Verify order belongs to current user
        if (!$order || $order['user_id'] !== $userId) {
            return Response::redirect('/profile/orders');
        }

        return Response::html(view('profile/order-detail', [
            'title' => 'Order #' . $order['id'],
            'order' => $order,
            'items' => $order['items'] ?? [],
        ]));
    }

    public function downloadInvoice(): Response
    {
        // Require authentication
        if (!Auth::check()) {
            return Response::redirect('/login');
        }

        $userId = Auth::user()['id'];
        
        $orderId = (int)($_GET['id'] ?? 0);

        // Backward-compatible fallback for /profile/orders/download/{id}
        if ($orderId === 0) {
            $pathParts = explode('/', trim((string)($_SERVER['REQUEST_URI'] ?? ''), '/'));
            foreach ($pathParts as $key => $part) {
                if ($part === 'download' && isset($pathParts[$key + 1])) {
                    $orderId = (int)$pathParts[$key + 1];
                    break;
                }
            }
        }

        if (!$orderId) {
            return Response::redirect('/profile/orders');
        }

        $repo = new OrderRepository();
        $order = $repo->findById($orderId);

        // Verify order belongs to current user
        if (!$order || $order['user_id'] !== $userId) {
            return Response::redirect('/profile/orders');
        }

        // Generate filename
        $filename = 'Invoice_Order_' . $order['id'] . '_' . date('Ymd') . '.html';

        // Get invoice HTML
        $invoiceHtml = view('profile/order-detail', [
            'title' => 'Order #' . $order['id'],
            'order' => $order,
            'items' => $order['items'] ?? [],
        ]);

        // Return with download headers
        return Response::html($invoiceHtml, 200)
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->withHeader('Pragma', 'no-cache')
            ->withHeader('Expires', '0');
    }
}
