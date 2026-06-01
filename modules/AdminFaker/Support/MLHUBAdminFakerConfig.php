<?php

namespace Modules\AdminFaker\Support;

/**
 * Loads Admin Faker demo dataset (Đà Nẵng SOHO investor demo).
 */
final class MLHUBAdminFakerConfig
{
    public const DATA_FILENAME = 'mlhub_adminfaker_dn_soho.php';

    public static function load(): array
    {
        static $config;

        if ($config !== null) {
            return $config;
        }

        $raw = require database_path('seeders/data/'.self::DATA_FILENAME);

        return $config = MLHUBEnterpriseDemoExpander::expand($raw);
    }

    /**
     * @return array<string, mixed>
     */
    public static function extensions(): array
    {
        static $extensions;

        if (isset($extensions)) {
            return $extensions;
        }

        $path = database_path('seeders/data/mlhub_adminfaker_extensions.php');

        return $extensions = is_file($path) ? require $path : [];
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

    public static function metricsMultiplierMax(): int
    {
        return max(1, (int) (self::load()['meta']['metrics_multiplier_max'] ?? 1500));
    }

    public static function targetQrVisits(): int
    {
        return max(0, (int) (self::load()['meta']['target_qr_visits'] ?? 0));
    }

    public static function baselineQrVisitsTotal(): int
    {
        $total = 0;

        foreach (self::load()['campaign_metrics'] ?? [] as $metric) {
            if (! is_array($metric)) {
                continue;
            }

            $total += (int) ($metric['visits'] ?? 0);
        }

        return max(0, $total);
    }

    public static function metricsMultiplier(): int
    {
        $meta = self::load()['meta'] ?? [];
        $max = self::metricsMultiplierMax();
        $target = self::targetQrVisits();

        if ($target > 0) {
            $baseline = self::baselineQrVisitsTotal();

            if ($baseline > 0) {
                return max(1, min($max, (int) round($target / $baseline)));
            }
        }

        $multiplier = (int) ($meta['metrics_multiplier'] ?? 5);

        return max(1, min($max, $multiplier));
    }

    public static function volumeScale(): int
    {
        $scale = (int) (self::load()['meta']['volume_scale'] ?? 1);

        return max(1, min(50, $scale));
    }

    public static function customerTarget(): int
    {
        $target = (int) (self::load()['meta']['customer_target'] ?? 120);

        return max(1, min(20000, $target));
    }

    public static function engagementMaxDaysAgo(): int
    {
        $days = (int) (self::load()['engagement_max_days_ago'] ?? 365);

        return max(30, min(730, $days));
    }

    public static function siteCount(): int
    {
        $configured = (int) (self::load()['meta']['site_count'] ?? 0);

        if ($configured > 0) {
            return $configured;
        }

        return count(self::load()['businesses'] ?? []);
    }

    public static function marketingFaqTarget(): int
    {
        $target = (int) (self::load()['meta']['marketing_faq_target'] ?? 250);

        return max(50, min(500, $target));
    }

    public static function marketingBlogTarget(): int
    {
        $target = (int) (self::load()['meta']['marketing_blog_target'] ?? 250);

        return max(50, min(500, $target));
    }

    public static function engagementInsertCap(): int
    {
        $cap = (int) (self::load()['meta']['engagement_insert_cap'] ?? 4000);

        return max(200, min(50000, $cap));
    }

    public static function cappedConversions(int $conversions): int
    {
        if ($conversions <= 0) {
            return 0;
        }

        return min($conversions, self::engagementInsertCap());
    }

    /**
     * @return array{visits: int, conversions: int}
     */
    public static function metricsForSlug(string $slug): array
    {
        $multiplier = self::metricsMultiplier();
        $configured = self::load()['campaign_metrics'][$slug] ?? null;

        if (is_array($configured)) {
            $visits = (int) ($configured['visits'] ?? 2000);
            $conversions = (int) ($configured['conversions'] ?? (int) round($visits * 0.09));

            return self::scaleMetrics($visits, $conversions, $multiplier);
        }

        $visits = 1400 + (abs(crc32($slug)) % 3201);
        $conversions = (int) round($visits * (0.07 + (abs(crc32($slug.'-c')) % 4) / 100));

        return self::scaleMetrics($visits, $conversions, $multiplier);
    }

    /**
     * @return array{visits: int, conversions: int}
     */
    public static function scaleMetrics(int $visits, int $conversions, ?int $multiplier = null): array
    {
        $multiplier ??= self::metricsMultiplier();

        return [
            'visits' => max(0, (int) round($visits * $multiplier)),
            'conversions' => max(0, (int) round($conversions * $multiplier)),
        ];
    }
}
