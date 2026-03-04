<?php
declare(strict_types=1);

namespace App\Services;

final class CartService
{
    private const SESSION_KEY = 'cart_items';

    public static function items(): array
    {
        $items = $_SESSION[self::SESSION_KEY] ?? [];
        if (!is_array($items)) {
            return [];
        }

        $normalized = [];
        foreach ($items as $ticketId => $quantity) {
            $ticketIdInt = (int)$ticketId;
            $quantityInt = (int)$quantity;
            if ($ticketIdInt > 0 && $quantityInt > 0) {
                $normalized[$ticketIdInt] = $quantityInt;
            }
        }

        return $normalized;
    }

    public static function add(int $ticketId, int $quantity = 1): void
    {
        if ($ticketId <= 0 || $quantity <= 0) {
            return;
        }

        $items = self::items();
        $items[$ticketId] = ($items[$ticketId] ?? 0) + $quantity;
        $_SESSION[self::SESSION_KEY] = $items;
    }

    public static function remove(int $ticketId): void
    {
        if ($ticketId <= 0) {
            return;
        }

        $items = self::items();
        unset($items[$ticketId]);
        $_SESSION[self::SESSION_KEY] = $items;
    }

    public static function update(int $ticketId, int $quantity): void
    {
        if ($ticketId <= 0) {
            return;
        }

        $items = self::items();

        if ($quantity <= 0) {
            unset($items[$ticketId]);
        } else {
            $items[$ticketId] = $quantity;
        }

        $_SESSION[self::SESSION_KEY] = $items;
    }

    public static function clear(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
    }

    public static function count(): int
    {
        $items = self::items();
        return array_sum($items);
    }
}
