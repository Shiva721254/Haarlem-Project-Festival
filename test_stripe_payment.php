<?php
declare(strict_types=1);

// Load env
if (file_exists(__DIR__ . '/.env')) {
    $env_lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($env_lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $_ENV[trim($key)] = trim($value);
    }
}

require 'vendor/autoload.php';

use App\Services\PaymentService;

echo "=== Testing Stripe Payment Integration ===\n\n";

try {
    // Test 1: Verify PaymentService can initialize with keys
    echo "[1] Initializing PaymentService...\n";
    $payment = new PaymentService();
    echo "✓ PaymentService initialized successfully\n";
    echo "  - Stripe API Key loaded: " . (strlen($_ENV['STRIPE_SECRET_KEY'] ?? '') > 0 ? 'YES' : 'NO') . "\n";
    echo "  - Public Key available: " . (strlen($_ENV['STRIPE_PUBLIC_KEY'] ?? '') > 0 ? 'YES' : 'NO') . "\n\n";

    // Test 2: Try creating a test checkout session
    echo "[2] Creating test Stripe checkout session...\n";
    
    $test_order_lines = [
        [
            'event_title' => 'Dance Night',
            'ticket_type' => 'Regular',
            'quantity' => 2,
            'price_at_purchase' => 15.00,
        ],
        [
            'event_title' => 'Jazz Festival',
            'ticket_type' => 'VIP',
            'quantity' => 1,
            'price_at_purchase' => 25.00,
        ],
    ];
    
    $checkout_url = $payment->createCheckoutSession(
        999, // test order ID
        $test_order_lines,
        55.00, // total
        'test@example.com'
    );
    
    echo "✓ Stripe checkout session created successfully\n";
    echo "  - Checkout URL: " . substr($checkout_url, 0, 80) . "...\n";
    echo "  - URL Type: " . (strpos($checkout_url, 'stripe.com') !== false ? 'Stripe Checkout' : 'Unknown') . "\n\n";

    echo "=== All tests PASSED ===\n";
    echo "\n✓ Payment integration is ready!\n";
    echo "\nNext steps:\n";
    echo "1. Go to http://localhost:8000/schedule\n";
    echo "2. Select a ticket and click 'Add to cart'\n";
    echo "3. Review cart at http://localhost:8000/cart\n";
    echo "4. Click 'Review checkout' button\n";
    echo "5. Click 'Proceed to Payment'\n";
    echo "6. You'll be redirected to Stripe (test mode)\n";
    echo "7. Use test card: 4242 4242 4242 4242 | 12/25 | 123\n";

} catch (\Exception $e) {
    echo "✗ Test FAILED: " . $e->getMessage() . "\n";
    exit(1);
}
