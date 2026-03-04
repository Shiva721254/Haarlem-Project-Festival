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
            'order' => $order,
            'items' => $order['items'] ?? [],
        ]));
    }
}
