<?php
declare(strict_types=1);

namespace App\Framework;

final class Flash
{
    public static function set(string $key, string $message): void
    {
        $_SESSION['flash'][$key] = $message;
    }

    public static function get(string $key): ?string
    {
        $msg = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);

        return is_string($msg) ? $msg : null;
    }
}
