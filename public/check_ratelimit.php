<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Framework\SessionManager;
use App\Framework\RateLimiter;

SessionManager::start();

$email = $_GET['email'] ?? 'admin@haarlemfestival.nl';

echo "<h1>Rate Limit Checker</h1>";
echo "<style>body { font-family: Arial; padding: 20px; } .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0; } .locked { background: #ffebee; } code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; }</style>";

echo "<form method='GET'>";
echo "<label>Check Email: <input type='email' name='email' value='" . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . "' style='padding:5px; width:250px;'></label> ";
echo "<button type='submit' style='padding:5px 15px;'>Check</button>";
echo "</form>";

$cacheKey = 'rate_limit_' . hash('sha256', $email);
$attempts = $_SESSION[$cacheKey] ?? null;

echo "<div class='info" . (RateLimiter::isLimited($email) ? " locked" : "") . "'>";
echo "<h3>Status for: <code>" . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . "</code></h3>";

if ($attempts === null) {
    echo "<p><strong>✅ Clean</strong> - No failed attempts recorded</p>";
} else {
    echo "<p><strong>Failed Attempts:</strong> " . ($attempts['count'] ?? 0) . " / 5</p>";
    echo "<p><strong>First Attempt:</strong> " . date('Y-m-d H:i:s', $attempts['first_at'] ?? time()) . "</p>";
    
    if ($attempts['locked_until'] > 0) {
        $remaining = $attempts['locked_until'] - time();
        if ($remaining > 0) {
            $minutes = ceil($remaining / 60);
            echo "<p style='color:red; font-weight:bold;'>🔒 LOCKED for " . $remaining . " seconds (~{$minutes} minutes)</p>";
        } else {
            echo "<p style='color:orange;'>⏰ Lockout expired, will be cleared on next check</p>";
        }
    }
    
    echo "<hr>";
    echo "<pre>" . htmlspecialchars(json_encode($attempts, JSON_PRETTY_PRINT), ENT_QUOTES, 'UTF-8') . "</pre>";
}

echo "</div>";

echo "<hr>";
echo "<h3>Actions</h3>";
echo "<a href='?email=" . urlencode($email) . "&action=clear' style='display:inline-block; padding:8px 16px; background:#28a745; color:white; text-decoration:none; border-radius:4px; margin:5px;'>Clear This Email</a>";
echo "<a href='/login' style='display:inline-block; padding:8px 16px; background:#007bff; color:white; text-decoration:none; border-radius:4px; margin:5px;'>Go to Login</a>";

if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    unset($_SESSION[$cacheKey]);
    echo "<script>window.location.href='?email=" . urlencode($email) . "';</script>";
}

echo "<hr>";
echo "<h3>Test Instructions</h3>";
echo "<ol>";
echo "<li>Go to <a href='/login'>/login</a></li>";
echo "<li>Enter email: <code>admin@haarlemfestival.nl</code></li>";
echo "<li>Enter WRONG password 5 times</li>";
echo "<li>On attempt 6, you should see the lockout message</li>";
echo "<li>Check this page to see lockout status</li>";
echo "</ol>";

echo "<h3>All Session Rate Limit Keys</h3>";
echo "<pre>";
foreach ($_SESSION as $key => $value) {
    if (str_starts_with($key, 'rate_limit_')) {
        echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . ":\n";
        echo htmlspecialchars(json_encode($value, JSON_PRETTY_PRINT), ENT_QUOTES, 'UTF-8') . "\n\n";
    }
}
echo "</pre>";
