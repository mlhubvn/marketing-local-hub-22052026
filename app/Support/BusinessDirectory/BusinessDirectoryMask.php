<?php

namespace App\Support\BusinessDirectory;

class BusinessDirectoryMask
{
    public static function mask(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $length = mb_strlen($value);

        if ($length <= 3) {
            return '***';
        }

        if ($length <= 6) {
            return mb_substr($value, 0, 2).'***'.mb_substr($value, -1);
        }

        return mb_substr($value, 0, 3).'***'.mb_substr($value, -3);
    }
}
