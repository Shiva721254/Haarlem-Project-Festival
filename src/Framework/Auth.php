<?php
declare(strict_types=1);

namespace App\Framework;

final class Auth
{
    public static function isAdmin(): bool
    {
        $user = $_SESSION['user'] ?? null;
        if (!is_array($user)) return false;

        return (($user['role'] ?? '') === 'admin');
    }
}
