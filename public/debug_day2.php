<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Framework\SessionManager;
use App\Framework\RateLimiter;

SessionManager::start();

echo "<h1>Day 2 Feature Check</h1>";
echo "<style>
    body { font-family: Arial; padding: 20px; }
    .check { color: green; font-weight: bold; }
    .section { margin: 20px 0; padding: 15px; background: #f5f5f5; border-radius: 5px; }
    .code { background: #e0e0e0; padding: 10px; margin: 10px 0; border-radius: 3px; }
</style>";

echo "<div class='section'>";
echo "<h2>✅ 1. Session Security Configuration</h2>";
echo "<div class='code'>";
echo "Session Save Path: " . ini_get('session.save_path') . "<br>";
echo "Strict Mode: " . ini_get('session.use_strict_mode') . "<br>";
echo "Use Only Cookies: " . ini_get('session.use_only_cookies') . "<br>";
echo "HTTPOnly: " . ini_get('session.cookie_httponly') . "<br>";
echo "Secure: " . ini_get('session.cookie_secure') . "<br>";
echo "SameSite: " . ini_get('session.cookie_samesite') . "<br>";
echo "GC Max Lifetime: " . ini_get('session.gc_maxlifetime') . " seconds (should be 3600)<br>";
echo "</div>";
$sessionOk = ini_get('session.use_strict_mode') == '1' && 
             ini_get('session.use_only_cookies') == '1' &&
             ini_get('session.cookie_httponly') == '1';
echo $sessionOk ? "<span class='check'>✓ Session security configured</span>" : "<span style='color:red'>✗ Session config needs review</span>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>✅ 2. Session Timeout</h2>";
$remaining = SessionManager::getRemainingTime();
$hours = intdiv($remaining, 3600);
$minutes = intdiv($remaining % 3600, 60);
$seconds = $remaining % 60;
echo "<div class='code'>";
echo "Timeout: 3600 seconds (1 hour)<br>";
echo "Remaining: " . sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds) . "<br>";
echo "Last Activity: " . date('Y-m-d H:i:s', $_SESSION['last_activity'] ?? time()) . "<br>";
echo "</div>";
echo "<span class='check'>✓ Session timeout tracking active</span>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>✅ 3. Rate Limiting</h2>";
$testEmail = 'test@example.com';
$isLimited = RateLimiter::isLimited($testEmail);
$lockoutTime = RateLimiter::getRemainingLockoutTime($testEmail);
echo "<div class='code'>";
echo "Max Attempts: 5<br>";
echo "Lockout Duration: 900 seconds (15 minutes)<br>";
echo "Test Email Status: " . ($isLimited ? "LOCKED (${lockoutTime}s remaining)" : "Not locked") . "<br>";
echo "</div>";
echo "<span class='check'>✓ Rate limiter class loaded</span><br>";
echo "<small>To test: Try logging in with wrong password 5+ times</small>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>✅ 4. Security Logging</h2>";
$logFile = __DIR__ . '/../storage/logs/security.log';
$logExists = file_exists($logFile);
echo "<div class='code'>";
echo "Log File: storage/logs/security.log<br>";
echo "Status: " . ($logExists ? "EXISTS" : "Not created yet") . "<br>";
if ($logExists) {
    $logSize = filesize($logFile);
    $lines = count(file($logFile));
    echo "Size: " . $logSize . " bytes<br>";
    echo "Events: ~" . $lines . " entries<br>";
    echo "<hr>";
    echo "<strong>Last 5 events:</strong><br><pre style='font-size:11px;'>";
    $logLines = file($logFile);
    echo htmlspecialchars(implode('', array_slice($logLines, -5)), ENT_QUOTES, 'UTF-8');
    echo "</pre>";
}
echo "</div>";
echo $logExists ? "<span class='check'>✓ Security logging active</span>" : "<span style='color:orange'>⚠ No events logged yet (trigger by logging in)</span>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>✅ 5. Profile Page</h2>";
echo "Route: <code>/profile</code><br>";
echo "Controller: <code>AuthController::showProfile()</code><br>";
echo "View: <code>resources/views/auth/profile.php</code><br>";
echo "<a href='/profile' style='display:inline-block; margin-top:10px; padding:8px 16px; background:#0066cc; color:white; text-decoration:none; border-radius:4px;'>
    Visit Profile Page
</a><br>";
echo "<small>(Must be logged in)</small>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>✅ 6. Flash Messages</h2>";
$testFlash = $_SESSION['flash'] ?? [];
echo "<div class='code'>";
echo "Current Flash Data: " . (empty($testFlash) ? "None" : json_encode($testFlash, JSON_PRETTY_PRINT)) . "<br>";
echo "</div>";
echo "<span class='check'>✓ Flash system loaded</span><br>";
echo "<small>Flash messages appear after login/logout/register</small>";
echo "</div>";

echo "<hr style='margin:30px 0;'>";
echo "<h2>Manual Testing Checklist</h2>";
echo "<ol>";
echo "<li><strong>Session Timeout:</strong> Login, wait 1 hour, try to access /admin/events → Should redirect to login with 'Session expired' message</li>";
echo "<li><strong>Rate Limiting:</strong> Try logging in with wrong password 5 times → Should see lockout message</li>";
echo "<li><strong>Security Logging:</strong> Check <code>storage/logs/security.log</code> after failed login</li>";
echo "<li><strong>Profile Page:</strong> Login and visit <a href='/profile'>/profile</a> → Should show your email, role, session timer</li>";
echo "<li><strong>CSRF Protection:</strong> Try submitting login form without CSRF token → Should be blocked</li>";
echo "<li><strong>Password Validation:</strong> Try registering with weak password → Should show validation errors</li>";
echo "</ol>";

echo "<hr style='margin:30px 0;'>";
echo "<h2>Quick Test Actions</h2>";
echo "<div style='margin:10px 0;'>";
echo "<a href='/login' style='display:inline-block; margin:5px; padding:10px 20px; background:#28a745; color:white; text-decoration:none; border-radius:4px;'>Test Login</a>";
echo "<a href='/register' style='display:inline-block; margin:5px; padding:10px 20px; background:#17a2b8; color:white; text-decoration:none; border-radius:4px;'>Test Registration</a>";
echo "<a href='/profile' style='display:inline-block; margin:5px; padding:10px 20px; background:#6c757d; color:white; text-decoration:none; border-radius:4px;'>Test Profile</a>";
echo "<a href='/debug_ratelimit.php' style='display:inline-block; margin:5px; padding:10px 20px; background:#ffc107; color:black; text-decoration:none; border-radius:4px;'>Rate Limit Tester</a>";
echo "</div>";
