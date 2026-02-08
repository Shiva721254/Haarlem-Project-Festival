<?php
declare(strict_types=1);

namespace App\Framework;

final class Auth
{
    public static function user(): ?array
    {
        $u = $_SESSION['user'] ?? null;
        return is_array($u) ? $u : null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function isAdmin(): bool
    {
        $u = self::user();
        return $u !== null && (($u['role'] ?? '') === 'admin');
    }
}
