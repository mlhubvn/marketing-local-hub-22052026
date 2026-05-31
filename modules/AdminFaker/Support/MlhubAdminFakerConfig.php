<?php

namespace Modules\AdminFaker\Support;

/**
 * Loads Admin Faker demo dataset (Đà Nẵng SOHO investor demo).
 */
final class MlhubAdminFakerConfig
{
    public const DATA_FILENAME = 'mlhub_adminfaker_dn_soho.php';

    public static function load(): array
    {
        static $config;

        return $config ??= require database_path('seeders/data/'.self::DATA_FILENAME);
    }

    /**
     * @return list<string>
     */
    public static function businessKeys(): array
    {
        return array_keys(self::load()['businesses'] ?? []);
    }

    /**
     * @return list<string>
     */
    public static function businessNames(): array
    {
        return array_values(array_map(
            static fn (array $business): string => (string) $business['name'],
            self::load()['businesses'] ?? [],
        ));
    }

    /**
     * @return list<string>
     */
    public static function campaignSlugs(): array
    {
        return array_values(array_map(
            static fn (array $campaign): string => (string) $campaign['slug'],
            self::load()['campaigns'] ?? [],
        ));
    }

    /**
     * @return list<string>
     */
    public static function standaloneLandingSlugs(): array
    {
        return array_values(array_map(
            static fn (array $page): string => (string) $page['slug'],
            self::load()['standalone_landing_pages'] ?? [],
        ));
    }

    /**
     * @return array{visits: int, conversions: int}
     */
    public static function metricsForSlug(string $slug): array
    {
        $configured = self::load()['campaign_metrics'][$slug] ?? null;

        if (is_array($configured)) {
            return [
                'visits' => (int) ($configured['visits'] ?? 2000),
                'conversions' => (int) ($configured['conversions'] ?? (int) round(($configured['visits'] ?? 2000) * 0.09)),
            ];
        }

        $visits = 1400 + (abs(crc32($slug)) % 3201);

        return [
            'visits' => $visits,
            'conversions' => (int) round($visits * (0.07 + (abs(crc32($slug.'-c')) % 4) / 100)),
        ];
    }
}
