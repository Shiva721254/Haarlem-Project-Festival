<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Stripe\PaymentIntent;

class PaymentService
{
    private string $stripe_key;
    private string $app_url;

    public function __construct()
    {
        $this->stripe_key = $_ENV['STRIPE_SECRET_KEY'] ?? '';
        $this->app_url = $_ENV['APP_URL'] ?? 'http://localhost:8000';

        if (!$this->stripe_key) {
            throw new \Exception('STRIPE_SECRET_KEY not configured in environment');
        }

        Stripe::setApiKey($this->stripe_key);
    }

    /**
     * Create a Stripe checkout session for order
     *
     * @param int $order_id
     * @param array $order_lines [['event_title', 'ticket_type', 'quantity', 'price_at_purchase']]
     * @param float $total_amount
     * @param string $customer_email
     * @return string Stripe checkout URL
     */
    public function createCheckoutSession(int $order_id, array $order_lines, float $total_amount, string $customer_email): string
    {
        $line_items = [];

        foreach ($order_lines as $line) {
            $line_items[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $line['event_title'],
                        'description' => $line['ticket_type'],
                    ],
                    'unit_amount' => (int)($line['price_at_purchase'] * 100), // Convert to cents
                ],
                'quantity' => (int)$line['quantity'],
            ];
        }

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => $line_items,
            'mode' => 'payment',
            'customer_email' => $customer_email,
            'success_url' => $this->app_url . '/order/success?session_id={CHECKOUT_SESSION_ID}&order_id=' . $order_id,
            'cancel_url' => $this->app_url . '/checkout?order_id=' . $order_id . '&cancelled=1',
            'metadata' => [
                'order_id' => $order_id,
            ],
        ]);

        return $session->url;
    }

    /**
     * Verify and complete a Stripe checkout session
     *
     * @param string $session_id
     * @return array Session data with payment status and payment intent
     */
    public function verifyCheckoutSession(string $session_id): array
    {
        $session = StripeSession::retrieve($session_id);

        return [
            'payment_status' => $session->payment_status, // 'paid', 'unpaid', 'no_payment_required'
            'payment_intent' => $session->payment_intent,
            'customer_email' => $session->customer_email,
            'metadata' => $session->metadata,
        ];
    }

    /**
     * Retrieve payment intent details
     *
     * @param string $intent_id
     * @return array
     */
    public function getPaymentIntent(string $intent_id): array
    {
        $intent = PaymentIntent::retrieve($intent_id);

        return [
            'status' => $intent->status, // 'succeeded', 'processing', 'requires_payment_method', etc.
            'amount' => $intent->amount / 100, // Convert from cents to euros
            'currency' => $intent->currency,
            'charges' => $intent->charges->data,
        ];
    }

    /**
     * Refund a payment (in case of order cancellation)
     *
     * @param string $payment_intent_id
     * @return bool
     */
    public function refund(string $payment_intent_id): bool
    {
        try {
            $intent = PaymentIntent::retrieve($payment_intent_id);

            if ($intent->status === 'succeeded' && !empty($intent->charges->data)) {
                $charge_id = $intent->charges->data[0]->id;
                $refund = \Stripe\Refund::create(['charge' => $charge_id]);
                return $refund->status === 'succeeded';
            }

            return false;
        } catch (\Exception $e) {
            error_log('Refund error: ' . $e->getMessage());
            return false;
        }
    }
}
