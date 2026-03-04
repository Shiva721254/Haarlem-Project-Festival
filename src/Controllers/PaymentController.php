<?php

namespace App\Controllers;

use App\Framework\Response;
use App\Framework\SessionManager;
use App\Framework\Flash;
use App\Services\CartService;
use App\Services\PaymentService;
use App\Repositories\OrderRepository;

class PaymentController
{
    private OrderRepository $order_repo;
    private PaymentService $payment_service;

    public function __construct()
    {
        $this->order_repo = new OrderRepository();

        try {
            $this->payment_service = new PaymentService();
        } catch (\Exception $e) {
            // PaymentService will throw if env vars not set
            error_log('PaymentService init error: ' . $e->getMessage());
        }
    }

    /**
     * Initiate Stripe payment for an order
     * POST /payment/checkout?order_id=N
     */
    public function checkout()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return Response::redirect('/checkout');
        }

        $order_id = (int)($_GET['order_id'] ?? $_POST['order_id'] ?? 0);

        if (!$order_id) {
            Flash::set('error', 'Invalid order ID.');
            return Response::redirect('/checkout');
        }

        $order = $this->order_repo->findById($order_id);
        if (!$order) {
            Flash::set('error', 'Order not found.');
            return Response::redirect('/checkout');
        }

        if (!$this->payment_service) {
            Flash::set('error', 'Payment service not configured. Please try again later.');
            return Response::redirect('/checkout');
        }

        try {
            // Format items for Stripe
            $stripe_items = array_map(function ($item) {
                return [
                    'event_title' => $item['event_title'],
                    'ticket_type' => $item['ticket_type'],
                    'quantity' => $item['quantity'],
                    'price_at_purchase' => $item['price_at_purchase'],
                ];
            }, $order['items']);

            // Create Stripe checkout session
            $stripe_url = $this->payment_service->createCheckoutSession(
                $order_id,
                $stripe_items,
                $order['total_amount'],
                $order['customer_email']
            );

            // Redirect to Stripe payment page
            return Response::redirect($stripe_url);
        } catch (\Exception $e) {
            error_log('Stripe checkout error: ' . $e->getMessage());
            Flash::set('error', 'Failed to initiate payment. Please try again.');
            return Response::redirect('/checkout');
        }
    }

    /**
     * Handle Stripe checkout success callback
     * GET /order/success?session_id=...&order_id=N
     */
    public function success()
    {
        $session_id = $_GET['session_id'] ?? '';
        $order_id = (int)($_GET['order_id'] ?? 0);

        if (!$session_id || !$order_id) {
            Flash::set('error', 'Missing callback parameters.');
            return Response::redirect('/');
        }

        $order = $this->order_repo->findById($order_id);
        if (!$order) {
            Flash::set('error', 'Order not found.');
            return Response::redirect('/');
        }

        try {
            // Verify Stripe session
            $session = $this->payment_service->verifyCheckoutSession($session_id);

            if ($session['payment_status'] !== 'paid') {
                throw new \Exception('Payment not completed. Status: ' . $session['payment_status']);
            }

            // Update order status to 'completed'
            $intent_id = $session['payment_intent'] ?? null;
            $this->order_repo->updateStatus($order_id, 'completed', $intent_id);

            // Clear cart session
            CartService::clear();

            // Redirect to confirmation page
            Flash::set('success', 'Payment successful! Your order has been confirmed.');
            return Response::redirect('/order/confirmation?order_id=' . $order_id);
        } catch (\Exception $e) {
            error_log('Payment success verification error: ' . $e->getMessage());
            Flash::set('error', 'Payment verification failed. Please contact support.');
            return Response::redirect('/');
        }
    }

    /**
     * Show order confirmation page
     * GET /order/confirmation?order_id=N
     */
    public function confirmation()
    {
        $order_id = (int)($_GET['order_id'] ?? 0);

        if (!$order_id) {
            Flash::set('error', 'Invalid order ID.');
            return Response::redirect('/');
        }

        $order = $this->order_repo->findById($order_id);
        if (!$order) {
            Flash::set('error', 'Order not found.');
            return Response::redirect('/');
        }

        // Log the page view for analytics
        error_log('Order confirmation viewed: Order ID ' . $order_id . ' by ' . $order['customer_email']);

        return Response::html(view('order/confirmation', [
            'order' => $order,
        ]));
    }

    /**
     * Handle payment cancellation (user goes back from Stripe)
     * GET /order/cancel?order_id=N (if needed; currently cart handles this)
     */
    public function cancel()
    {
        $order_id = (int)($_GET['order_id'] ?? 0);

        if ($order_id) {
            // Mark order as 'cancelled' in DB
            try {
                $this->order_repo->updateStatus($order_id, 'cancelled');
            } catch (\Exception $e) {
                error_log('Order cancellation error: ' . $e->getMessage());
            }
        }

        Flash::set('info', 'Payment cancelled. Your cart has been preserved.');
        return Response::redirect('/cart');
    }
}
