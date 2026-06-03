<?php

namespace Modules\AdminPlans\Support;

use Illuminate\Support\Facades\Lang;

class CatalogLocalization
{
    protected static ?array $vietnameseToEnglishKey = null;

    public static function resolve(?string $text): string
    {
        $text = trim((string) $text);

        if ($text === '') {
            return '';
        }

        if (Lang::has($text, 'en')) {
            return __($text);
        }

        if (app()->getLocale() === 'vi') {
            return $text;
        }

        $englishKey = static::vietnameseToEnglishKey()[$text] ?? null;

        if ($englishKey !== null) {
            return __($englishKey);
        }

        return $text;
    }

    /**
     * @return array<string, string>
     */
    protected static function vietnameseToEnglishKey(): array
    {
        if (static::$vietnameseToEnglishKey !== null) {
            return static::$vietnameseToEnglishKey;
        }

        static::$vietnameseToEnglishKey = [];
        $path = lang_path('vi.json');

        if (! is_file($path)) {
            return static::$vietnameseToEnglishKey;
        }

        $lines = json_decode((string) file_get_contents($path), true);

        if (! is_array($lines)) {
            return static::$vietnameseToEnglishKey;
        }

        foreach ($lines as $english => $vietnamese) {
            if (! is_string($english) || ! is_string($vietnamese) || $vietnamese === '' || $vietnamese === $english) {
                continue;
            }

            static::$vietnameseToEnglishKey[$vietnamese] = $english;
        }

        return static::$vietnameseToEnglishKey;
    }
}
