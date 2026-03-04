<?php
declare(strict_types=1);

namespace App\Framework;

final class SessionManager
{
    /**
     * Session timeout in seconds (1 hour)
     */
    private const SESSION_TIMEOUT = 3600;
    
    /**
     * Start session with secure configuration
     */
    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            // Configure session security
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_secure', self::isSecure() ? '1' : '0');
            ini_set('session.cookie_samesite', 'Lax');
            ini_set('session.gc_maxlifetime', (string)self::SESSION_TIMEOUT);
            
            session_start();
        }
        
        self::checkTimeout();
    }
    
    /**
     * Check if session has timed out
     */
    private static function checkTimeout(): void
    {
        $now = time();
        $lastActivity = $_SESSION['last_activity'] ?? 0;
        
        // If no last activity, set it
        if ($lastActivity === 0) {
            $_SESSION['last_activity'] = $now;
            return;
        }
        
        // Check if session expired
        if (($now - $lastActivity) > self::SESSION_TIMEOUT) {
            self::destroy();
            Flash::set('error', 'Session expired. Please login again.');
        } else {
            // Update last activity
            $_SESSION['last_activity'] = $now;
        }
    }
    
    /**
     * Regenerate session ID for security
     */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Destroy session
     */
    public static function destroy(): void
    {
        $_SESSION = [];
        
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        session_destroy();
    }
    
    /**
     * Check if connection is secure (HTTPS)
     */
    private static function isSecure(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
               $_SERVER['SERVER_PORT'] == 443;
    }
    
    /**
     * Get remaining session time in seconds
     */
    public static function getRemainingTime(): int
    {
        $lastActivity = $_SESSION['last_activity'] ?? 0;
        if ($lastActivity === 0) {
            return self::SESSION_TIMEOUT;
        }
        
        $elapsed = time() - $lastActivity;
        $remaining = self::SESSION_TIMEOUT - $elapsed;
        
        return max(0, $remaining);
    }
}
