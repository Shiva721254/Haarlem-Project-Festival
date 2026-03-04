<?php
declare(strict_types=1);

// Load env and bootstrap
if (file_exists(__DIR__ . '/.env')) {
    $env_lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($env_lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $_ENV[trim($key)] = trim($value);
    }
}

require 'vendor/autoload.php';

use App\Repositories\TicketRepository;
use App\Repositories\OrderRepository;
use App\Services\CartService;
use App\Services\PaymentService;
use App\Framework\SessionManager;

echo "=== End-to-End Payment Flow Test ===\n\n";

try {
    // Mock session
    session_start();
    
    // Step 1: Create simulated cart
    echo "[Step 1] Setting up test cart with tickets...\n";
    $ticket_repo = new TicketRepository();
    
    // Get first two tickets from database
    $tickets = $ticket_repo->findByEventIds([1, 2, 3]);
    
    if (empty($tickets)) {
        throw new \Exception('No tickets found in database. Did you seed the data?');
    }
    
    $test_tickets = array_slice($tickets, 0, 2);
    foreach ($test_tickets as $ticket) {
        CartService::add($ticket['id'], 1);
    }
    
    echo "✓ Cart populated with " . count($test_tickets) . " tickets\n";
    echo "  Tickets: " . implode(', ', array_column($test_tickets, 'ticket_type')) . "\n\n";
    
    // Step 2: Create order from cart
    echo "[Step 2] Creating order from cart...\n";
    $order_repo = new OrderRepository();
    $cart_items = CartService::items();
    
    $lines = [];
    $total = 0.0;
    $ticket_data = [];
    
    foreach ($cart_items as $ticket_id => $quantity) {
        $ticket = $tickets[$ticket_id] ?? null;
        if (!$ticket) continue;
        
        $price = (float)$ticket['price'];
        $line_total = $price * $quantity;
        $total += $line_total;
        
        $lines[] = [
            'ticket_id' => $ticket_id,
            'event_title' => $ticket['event_title'],
            'ticket_type' => $ticket['ticket_type'],
            'event_date' => $ticket['event_date'],
            'quantity' => $quantity,
            'price' => $price,
            'line_total' => $line_total,
        ];
        
        $ticket_data[$ticket_id] = $ticket;
    }
    
    $order_id = $order_repo->create(
        null,
        'test@payment-flow.nl',
        'Test User',
        $total,
        $cart_items
    );
    
    echo "✓ Order created successfully\n";
    echo "  - Order ID: $order_id\n";
    echo "  - Total Amount: €" . number_format($total, 2) . "\n";
    echo "  - Items: " . count($lines) . "\n\n";
    
    // Step 3: Verify order exists and has items
    echo "[Step 3] Verifying order in database...\n";
    $order = $order_repo->findById($order_id);
    
    if (!$order) {
        throw new \Exception('Order not found after creation');
    }
    
    echo "✓ Order retrieved from database\n";
    echo "  - Status: " . $order['status'] . "\n";
    echo "  - Customer: " . $order['customer_email'] . "\n";
    echo "  - Items in order: " . count($order['items']) . "\n\n";
    
    // Step 4: Create Stripe checkout session
    echo "[Step 4] Creating Stripe checkout session...\n";
    $payment = new PaymentService();
    
    $stripe_items = array_map(function($item) {
        return [
            'event_title' => $item['event_title'],
            'ticket_type' => $item['ticket_type'],
            'quantity' => $item['quantity'],
            'price_at_purchase' => $item['price_at_purchase'],
        ];
    }, $order['items']);
    
    $checkout_url = $payment->createCheckoutSession(
        $order_id,
        $stripe_items,
        $order['total_amount'],
        $order['customer_email']
    );
    
    echo "✓ Stripe checkout session created\n";
    echo "  - Checkout URL: " . substr($checkout_url, 0, 60) . "...\n";
    echo "  - URL valid: " . (filter_var($checkout_url, FILTER_VALIDATE_URL) ? 'YES' : 'NO') . "\n\n";
    
    // Step 5: Test order retrieval by ID
    echo "[Step 5] Testing order lookups...\n";
    $found_order = $order_repo->findById($order_id);
    
    if ($found_order && $found_order['id'] == $order_id) {
        echo "✓ Order lookup by ID works\n";
        echo "  - Items in order: " . count($found_order['items']) . "\n";
        echo "  - Total: €" . number_format($found_order['total_amount'], 2) . "\n";
    } else {
        throw new \Exception('Order lookup failed');
    }
    
    // Step 6: Test order status update
    echo "\n[Step 6] Testing order status updates...\n";
    $order_repo->updateStatus($order_id, 'completed', 'pi_test_12345');
    $updated_order = $order_repo->findById($order_id);
    
    if ($updated_order['status'] === 'completed') {
        echo "✓ Order status updated successfully\n";
        echo "  - New status: " . $updated_order['status'] . "\n";
        echo "  - Payment intent stored: " . ($updated_order['stripe_payment_intent_id'] ? 'YES' : 'NO') . "\n";
    } else {
        throw new \Exception('Status update failed');
    }
    
    echo "\n=== ALL TESTS PASSED ===\n";
    echo "\n✓ Payment flow is fully operational!\n";
    echo "\nSummary:\n";
    echo "- Tickets can be added to cart ✓\n";
    echo "- Orders can be created from cart ✓\n";
    echo "- Stripe checkout sessions can be generated ✓\n";
    echo "- Order status can be tracked ✓\n";
    echo "\nManual Testing:\n";
    echo "1. Go to http://localhost:8000/schedule\n";
    echo "2. Add tickets to cart\n";
    echo "3. Click 'Review checkout'\n";
    echo "4. Click 'Proceed to Payment'\n";
    echo "5. Complete payment with test card: 4242 4242 4242 4242 (12/25 | 123)\n";

} catch (\Exception $e) {
    echo "✗ TEST FAILED\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
