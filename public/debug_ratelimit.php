<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Framework\SessionManager;

SessionManager::start();

echo "<h1>Rate Limit Debug</h1>";
echo "<pre>";

echo "PHP Configuration:\n";
echo "- session.save_handler: " . ini_get('session.save_handler') . "\n";
echo "- session.save_path: " . ini_get('session.save_path') . "\n";
echo "- session.use_cookies: " . ini_get('session.use_cookies') . "\n";
echo "- session.use_only_cookies: " . ini_get('session.use_only_cookies') . "\n";

echo "\nSession Status: " . session_status() . "\n";
echo "- 0 = DISABLED\n";
echo "- 1 = NONE (enabled but not started)\n";
echo "- 2 = ACTIVE\n";

echo "\nSession ID: " . session_id() . "\n";
echo "Cookie set: " . (isset($_COOKIE[session_name()]) ? 'Yes' : 'No') . "\n";

echo "\n\nSession Data:\n";
var_dump($_SESSION);

// Test hash
$email = 'admin@haarlem.nl';
$hash = 'rate_limit_' . hash('sha256', $email);
echo "\n\nRate Limiter Test:\n";
echo "Email: $email\n";
echo "Cache Key: $hash\n";
echo "Stored Data: ";
var_dump($_SESSION[$hash] ?? 'NOT FOUND');

echo "\n\n<a href='/debug_ratelimit.php'>Refresh</a> | <a href='/login'>Go to Login</a>";
?>
