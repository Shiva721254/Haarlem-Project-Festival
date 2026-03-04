<?php
declare(strict_types=1);

namespace App\Framework;

final class Validator
{
    /**
     * Validate email format
     */
    public static function email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate password strength
     * Requirements: 8+ chars, uppercase, lowercase, number, special char
     */
    public static function password(string $password): array
    {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }
        
        return $errors;
    }
    
    /**
     * Validate string length
     */
    public static function length(string $value, int $min, int $max): bool
    {
        $len = mb_strlen($value);
        return $len >= $min && $len <= $max;
    }
    
    /**
     * Validate required field
     */
    public static function required(mixed $value): bool
    {
        if (is_string($value)) {
            return trim($value) !== '';
        }
        return !empty($value);
    }
}
