<?php
declare(strict_types=1);

namespace App\Config;

final class EventCategories
{
    /** @return array<int, string> */
    public static function all(): array
    {
        return [
            'Dance',
            'Music',
            'Kids',
            'Museum',
            'Food',
            'Workshop',
            'Theatre',
            'Other',
        ];
    }
}
