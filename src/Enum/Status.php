<?php

namespace App\Enum;

enum Status: string
{
    case available = 'available';
    case borrowed = 'borrowed';

    public static function isValid(string $value): bool
    {
        return in_array($value, array_column(self::cases(), 'value'), true);
    }
}
