<?php

namespace Modules\APIPartnerFizaHUB\Support;

class PartnerPayloadRedactor
{
    private const MAX_BYTES = 65536;

    /** @var list<string> */
    private const REDACTED_KEYS = [
        'authorization',
        'token',
        'password',
        'cccd',
        'identity_document',
        'business_license_file',
        'url',
    ];

    public static function redact(mixed $payload): mixed
    {
        return self::walk($payload);
    }

    public static function cap(mixed $payload): mixed
    {
        $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (! is_string($encoded)) {
            return ['truncated' => true, 'sha256' => hash('sha256', '')];
        }

        if (strlen($encoded) <= self::MAX_BYTES) {
            return $payload;
        }

        return [
            'truncated' => true,
            'sha256' => hash('sha256', $encoded),
        ];
    }

    public static function redactAndCap(mixed $payload): mixed
    {
        return self::cap(self::redact($payload));
    }

    private static function walk(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        $result = [];

        foreach ($value as $key => $child) {
            $keyString = is_string($key) ? $key : (string) $key;

            if (self::shouldRedactKey($keyString)) {
                $result[$key] = '[REDACTED]';

                continue;
            }

            $result[$key] = self::walk($child);
        }

        return $result;
    }

    private static function shouldRedactKey(string $key): bool
    {
        $normalized = strtolower($key);

        foreach (self::REDACTED_KEYS as $needle) {
            if ($normalized === $needle || str_contains($normalized, $needle)) {
                return true;
            }
        }

        return false;
    }
}
