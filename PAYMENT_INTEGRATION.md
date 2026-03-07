# Payment Processing Implementation Guide

## 💳 Payment Integration - Stripe

The rubric requires: **Shopping cart functionality + checkout with integration with a real payment provider (development mode).**

---

## Overview

We'll integrate **Stripe** because:
- ✅ Test mode available (no real charges)
- ✅ PCI DSS compliant (never store CC numbers)
- ✅ Easy webhook handling
- ✅ Industry standard
- ✅ Great documentation

---

## PHASE 1: Initial Setup (30 min)

### Step 1: Create Stripe Account
1. Go to **https://stripe.com**
2. Sign up for free account
3. Go to Dashboard
4. Activate test mode (toggle in top right)
5. Copy your **Test API Keys**:
   - Publishable Key (starts with `pk_test_`)
   - Secret Key (starts with `sk_test_`)

### Step 2: Install Stripe PHP Library
```bash
cd c:\Users\shiva\Desktop\Haarlem-Project-Festival
composer require stripe/stripe-php
```

### Step 3: Store API Keys (Securely)
```
# .env file
STRIPE_PUBLIC_KEY=pk_test_YOUR_PUBLIC_KEY_HERE
STRIPE_SECRET_KEY=sk_test_YOUR_SECRET_KEY_HERE
ENVIRONMENT=test
```

**Create .env loader**:
```php
// src/Config/Environment.php

<?php
declare(strict_types=1);

namespace App\Config;

final class Environment
{
    public static function get(string $key, string $default = ''): string
    {
        $envFile = __DIR__ . '/../../.env';
        
        if (!file_exists($envFile)) {
            return $default;
        }
        
        $lines = file($envFile, FILE_IGNORE_NEW_LINES);
        foreach ($lines as $line) {
            if (empty($line) || str_starts_with($line, '#')) continue;
            [$envKey, $value] = explode('=', $line, 2);
            if (trim($envKey) === $key) {
                return trim($value);
            }
        }
        
        return $default;
    }
}
```

---

## PHASE 2: Database Setup

### Add Payment Tables
```sql
-- database/schema.sql

-- Payments table
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    amount INT NOT NULL,
    currency VARCHAR(3) DEFAULT 'EUR',
    stripe_payment_intent_id VARCHAR(255),
    stripe_charge_id VARCHAR(255),
    status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- Update orders table
ALTER TABLE orders ADD COLUMN stripe_session_id VARCHAR(255);
ALTER TABLE orders ADD COLUMN payment_status VARCHAR(50) DEFAULT 'pending';
```

---

## PHASE 3: Payment Service Implementation

### Create PaymentService

```php
// src/Services/PaymentService.php

<?php
declare(strict_types=1);

namespace App\Services;

use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use App\Config\Environment;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentRepository;

final class PaymentService
{
    private StripeClient $stripe;

    public function __construct()
    {
        Stripe::setApiKey(Environment::get('STRIPE_SECRET_KEY'));
        $this->stripe = new StripeClient(Environment::get('STRIPE_SECRET_KEY'));
    }

    /**
     * Create Stripe Checkout Session
     * 
     * @param int $orderId - Order to pay for
     * @param array $items - [['price' => '100', 'quantity' => 2], ...]
     * @param string $successUrl - Redirect after payment
     * @param string $cancelUrl - Redirect if cancelled
     */
    public function createCheckoutSession(
        int $orderId,
        array $items,
        string $successUrl,
        string $cancelUrl
    ): string {
        try {
            $session = $this->stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => $items,
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'client_reference_id' => (string)$orderId,
                'customer_email' => 'customer@example.com', // Get from order
            ]);

            // Store session ID in database
            $orderRepo = new OrderRepository();
            $orderRepo->updateStripeSession($orderId, $session->id);

            return $session->url;
        } catch (ApiErrorException $e) {
            throw new Exception('Payment session creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Retrieve session details
     */
    public function getSession(string $sessionId): ?array
    {
        try {
            $session = $this->stripe->checkout->sessions->retrieve($sessionId);
            
            return [
                'id' => $session->id,
                'status' => $session->status,
                'payment_status' => $session->payment_status,
                'customer_email' => $session->customer_email,
                'amount_total' => $session->amount_total,
                'currency' => $session->currency,
            ];
        } catch (ApiErrorException $e) {
            throw new Exception('Failed to retrieve session: ' . $e->getMessage());
        }
    }

    /**
     * Verify payment via webhook
     */
    public function verifyWebhookSignature(string $body, string $signature): bool
    {
        try {
            $event = \Stripe\Webhook::constructEvent(
                $body,
                $signature,
                Environment::get('STRIPE_WEBHOOK_SECRET')
            );
            return true;
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return false;
        }
    }

    /**
     * Handle successful payment
     */
    public function handlePaymentSuccess(string $sessionId): void
    {
        $session = $this->getSession($sessionId);
        $orderId = (int)$session['client_reference_id'] ?? 0;

        if ($orderId <= 0) {
            throw new Exception('Invalid order ID in session');
        }

        $orderRepo = new OrderRepository();
        $paymentRepo = new PaymentRepository();

        // Update order status
        $orderRepo->updateStatus($orderId, 'completed');

        // Record payment
        $paymentRepo->create([
            'order_id' => $orderId,
            'amount' => $session['amount_total'],
            'currency' => $session['currency'],
            'stripe_session_id' => $sessionId,
            'status' => 'completed',
        ]);
    }
}
```

---

## PHASE 4: Checkout Flow Controller

### Create CheckoutController

```php
// src/Controllers/CheckoutController.php

<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Framework\Auth;
use App\Framework\Response;
use App\Framework\Flash;
use App\Services\PaymentService;
use App\Repositories\OrderRepository;
use App\Repositories\CartRepository;

final class CheckoutController
{
    /**
     * Display checkout page (review order)
     */
    public function show(): Response
    {
        if (!Auth::isLoggedIn()) {
            Flash::set('error', 'Please log in to checkout');
            return Response::redirect('/login');
        }

        $userId = Auth::userId();

        // Get cart items
        $cart = new CartRepository();
        $items = $cart->getItems($userId);

        if (empty($items)) {
            Flash::set('error', 'Your cart is empty');
            return Response::redirect('/');
        }

        // Calculate total
        $total = 0;
        foreach ($items as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return Response::html(view('checkout/review', [
            'items' => $items,
            'total' => $total,
            'userId' => $userId,
        ]));
    }

    /**
     * Process checkout - create Stripe session
     */
    public function process(): Response
    {
        if (!Auth::isLoggedIn()) {
            return Response::json(['error' => 'Not authenticated'], 401);
        }

        $userId = Auth::userId();

        try {
            // Get cart items
            $cart = new CartRepository();
            $items = $cart->getItems($userId);

            if (empty($items)) {
                return Response::json(['error' => 'Cart is empty'], 400);
            }

            // Create order in database
            $orderRepo = new OrderRepository();
            $orderId = $orderRepo->create([
                'user_id' => $userId,
                'total_amount' => $this->calculateTotal($items),
                'status' => 'pending',
            ]);

            // Create line items for Stripe
            $stripeItems = array_map(function ($item) {
                return [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $item['title'],
                        ],
                        'unit_amount' => (int)($item['price'] * 100), // Stripe uses cents
                    ],
                    'quantity' => $item['quantity'],
                ];
            }, $items);

            // Create Stripe checkout session
            $paymentService = new PaymentService();
            $checkoutUrl = $paymentService->createCheckoutSession(
                $orderId,
                $stripeItems,
                'https://yourdomain.com/payment/success',
                'https://yourdomain.com/payment/cancel'
            );

            return Response::json(['checkout_url' => $checkoutUrl]);
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Success callback (user redirected here after Stripe payment)
     */
    public function success(): Response
    {
        $sessionId = $_GET['session_id'] ?? null;

        if (!$sessionId) {
            Flash::set('error', 'Invalid payment sessionession');
            return Response::redirect('/');
        }

        try {
            $paymentService = new PaymentService();
            $paymentService->handlePaymentSuccess($sessionId);

            Flash::set('success', 'Payment received! Your order is confirmed.');
            return Response::redirect('/orders');
        } catch (Exception $e) {
            Flash::set('error', 'Payment verification failed: ' . $e->getMessage());
            return Response::redirect('/');
        }
    }

    /**
     * Cancel callback (user cancelled Stripe payment)
     */
    public function cancel(): Response
    {
        Flash::set('error', 'Payment cancelled. Your order was not completed.');
        return Response::redirect('/checkout');
    }

    private function calculateTotal(array $items): int
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }
}
```

---

## PHASE 5: Frontend Integration

### Add Checkout Route
```php
// routes/web.php

use App\Controllers\CheckoutController;

return [
    // ... existing routes
    ['GET', '/checkout', [CheckoutController::class, 'show']],
    ['POST', '/checkout/process', [CheckoutController::class, 'process']],
    ['GET', '/payment/success', [CheckoutController::class, 'success']],
    ['GET', '/payment/cancel', [CheckoutController::class, 'cancel']],
];
```

### Create Checkout View

```php
// resources/views/checkout/review.php

<?php
declare(strict_types=1);

/** @var array $items */
/** @var int $total */
?>

<h1>Order Review</h1>

<table>
    <thead>
        <tr>
            <th>Item</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $item): ?>
        <tr>
            <td><?= h($item['title']) ?></td>
            <td>€<?= number_format($item['price'] / 100, 2) ?></td>
            <td><?= $item['quantity'] ?></td>
            <td>€<?= number_format(($item['price'] * $item['quantity']) / 100, 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h2>Total: €<?= number_format($total / 100, 2) ?></h2>

<button id="checkout-btn">Proceed to Payment</button>

<script>
document.getElementById('checkout-btn').addEventListener('click', async () => {
    const response = await fetch('/checkout/process', { method: 'POST' });
    const data = await response.json();
    
    if (data.error) {
        alert('Error: ' + data.error);
        return;
    }
    
    // Redirect to Stripe checkout
    window.location.href = data.checkout_url;
});
</script>
```

---

## PHASE 6: Webhook Handling (For Stripe Events)

### Create Webhook Endpoint
```php
// src/Controllers/WebhookController.php

<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Framework\Response;
use App\Services\PaymentService;

final class WebhookController
{
    public function stripeHandle(): Response
    {
        $payload = file_get_contents('php://input');
        $sig = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        $paymentService = new PaymentService();
        
        if (!$paymentService->verifyWebhookSignature($payload, $sig)) {
            return Response::json(['error' => 'Invalid signature'], 403);
        }

        $event = json_decode($payload, true);

        switch ($event['type']) {
            case 'checkout.session.completed':
                $session = $event['data']['object'];
                $paymentService->handlePaymentSuccess($session['id']);
                break;

            case 'payment_intent.payment_failed':
                // Handle failed payment
                break;
        }

        return Response::json(['received' => true]);
    }
}
```

### Add Webhook Route
```php
// routes/web.php - No auth needed for webhook!

['POST', '/webhooks/stripe', [WebhookController::class, 'stripeHandle']],
```

---

## Testing Payment Flow (IMPORTANT!)

### Test Card Numbers (Stripe Provides)
```
Successful payment:
4242 4242 4242 4242

Requires authentication:
4000 0000 0000 3220

Declined:
4000 0000 0000 0002

Expired:
4000 0000 0000 0069
```

### Testing Workflow:
1. Add items to cart
2. Go to checkout
3. Click "Proceed to Payment"
4. Use test card: `4242 4242 4242 4242`
5. Enter any future date, any CVC
6. Complete purchase
7. Should redirect to success page

---

## Test vs. Live Mode

### Test Mode (Development)
- ✅ No real charges
- ✅ Test card numbers work
- ✅ Instant webhooks
- ✅ Perfect for development

### Live Mode (Production - When Done)
- Use real publishable/secret keys
- Real customers charged
- Requires SSL/HTTPS
- Update .env for production keys

---

## Security Checklist for Payments

- [ ] Never store full credit card numbers
- [ ] Always use HTTPS (Stripe requires)
- [ ] Validate webhook signatures
- [ ] Verify order belongs to user before charging
- [ ] Store payment receipts
- [ ] Handle failed payments gracefully
- [ ] Rate limit checkout endpoint
- [ ] Log all payment attempts
- [ ] Test with Stripe's test cards

---

## Common Issues & Solutions

### Issue: "Invalid API Key"
**Solution**: Check .env file has correct STRIPE_SECRET_KEY

### Issue: "Session not found"
**Solution**: Make sure session.url exists and `created_at` is recent

### Issue: "Webhook failing"
**Solution**: 
1. Get webhook signing secret from Stripe dashboard
2. Add to .env: `STRIPE_WEBHOOK_SECRET=whsec_...`
3. Use correct endpoint: POST /webhooks/stripe

### Issue: "CORS errors"
**Solution**: This endpoint should NOT require CORS (same-origin)

---

## Email & Invoice Delivery (Implemented)

### Environment configuration
```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=your_gmail@gmail.com
MAIL_PASSWORD=your_16_char_google_app_password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=your_gmail@gmail.com
MAIL_FROM_NAME=Haarlem Festival
```

### Invoice capabilities
- Branded PDF invoice (logo at top)
- QR code generated from order identifier data
- Print-oriented totals block (subtotal/tax/total)
- Attached automatically to order confirmation email

### Validation command
```powershell
docker compose exec php php -r "require '/app/bootstrap/app.php'; var_export(\App\Services\EmailService::sendOrderConfirmation(8));"
```

## Next Steps After Payment Works

1. ✅ Generate invoices
2. ✅ Send confirmation emails
3. ⏳ Implement refunds
4. ⏳ Add subscription support (stretch goal)
5. ⏳ Multiple payment methods (Apple Pay, Google Pay)

---

## Resources

- **Stripe Docs**: https://stripe.com/docs
- **Test Data**: https://stripe.com/docs/testing
- **Webhooks**: https://stripe.com/docs/webhooks
- **PHP SDK**: https://github.com/stripe/stripe-php

