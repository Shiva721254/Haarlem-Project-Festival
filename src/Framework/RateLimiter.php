<?php
declare(strict_types=1);

namespace App\Framework;

final class RateLimiter
{
    /**
     * Maximum login attempts
     */
    private const MAX_ATTEMPTS = 5;
    
    /**
     * Lockout duration in seconds (15 minutes)
     */
    private const LOCKOUT_TIME = 900;
    
    /**
     * Get the cache key for an identifier
     */
    private static function getCacheKey(string $identifier): string
    {
        return 'rate_limit_' . hash('sha256', $identifier);
    }
    
    /**
     * Check if an identifier is rate limited
     */
    public static function isLimited(string $identifier): bool
    {
        $cacheKey = self::getCacheKey($identifier);
        $attempts = $_SESSION[$cacheKey] ?? null;
        
        if ($attempts === null) {
            return false;
        }
        
        // Check if lockout has expired
        if ($attempts['locked_until'] < time()) {
            unset($_SESSION[$cacheKey]);
            return false;
        }
        
        return true;
    }
    
    /**
     * Record a failed attempt
     */
    public static function recordAttempt(string $identifier): void
    {
        $cacheKey = self::getCacheKey($identifier);
        $attempts = $_SESSION[$cacheKey] ?? ['count' => 0, 'first_at' => time(), 'locked_until' => 0];
        
        $attempts['count']++;
        
        // Lock after max attempts
        if ($attempts['count'] >= self::MAX_ATTEMPTS) {
            $attempts['locked_until'] = time() + self::LOCKOUT_TIME;
        }
        
        $_SESSION[$cacheKey] = $attempts;
    }
    
    /**
     * Clear attempts for an identifier
     */
    public static function clearAttempts(string $identifier): void
    {
        $cacheKey = self::getCacheKey($identifier);
        unset($_SESSION[$cacheKey]);
    }
    
    /**
     * Get remaining time for lockout in seconds
     */
    public static function getRemainingLockoutTime(string $identifier): int
    {
        $cacheKey = self::getCacheKey($identifier);
        $attempts = $_SESSION[$cacheKey] ?? null;
        
        if ($attempts === null || $attempts['locked_until'] < time()) {
            return 0;
        }
        
        return $attempts['locked_until'] - time();
    }
}
