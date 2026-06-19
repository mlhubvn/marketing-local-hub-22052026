<?php

namespace Modules\AdminPlans\Support;

use Illuminate\Support\Collection;

class PlanFeatureOrder
{
    public const MLHUB_AI_KEY = 'mlhub';

    /**
     * @return array<string, int>
     */
    public static function priorities(): array
    {
        return [
            self::MLHUB_AI_KEY => 1,
            'max_businesses' => 12,
            'max_campaigns' => 13,
            'max_landing_pages' => 14,
            'max_qr_codes' => 15,
            'max_templates' => 16,
            'remove_branding' => 17,
            'teams' => 20,
            'max_team_members' => 21,
            'max_bio_pages' => 22,
            'link_bio_templates' => 23,
            'ai_bio_assistant' => 24,
            'link_bio_analytics' => 25,
            'link_bio_ab_testing' => 26,
            'remove_link_bio_branding' => 27,
            'advanced_qr_codes' => 30,
            'monthly_qr_scans' => 32,
            'dynamic_rules_per_qr' => 33,
            'qr_bulk_generation' => 34,
            'max_bulk_rows' => 35,
            'pre_printed_qr' => 36,
            'ai_qr_codes' => 37,
            'credits_per_ai_qr_request' => 38,
            'short_links' => 40,
            'short_link_quota' => 41,
            'monthly_clicks' => 42,
            'advanced_routing' => 43,
            'ab_destination_testing' => 44,
            'bulk_import' => 45,
            'bulk_rows_per_import' => 46,
            'api_and_webhooks' => 47,
            'brand_kit' => 50,
            'custom_domains' => 51,
            'tracking_pixels' => 52,
            'utm_presets' => 53,
            'ai_studio' => 60,
            'url_shortener' => 61,
            'credits_usage_limit' => 80,
            'premium_support' => 90,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function promotedKeys(): array
    {
        return [
            self::MLHUB_AI_KEY,
            'max_businesses',
            'max_campaigns',
            'max_landing_pages',
            'max_qr_codes',
            'max_templates',
            'remove_branding',
            'credits_usage_limit',
            'max_team_members',
            'max_bio_pages',
            'remove_link_bio_branding',
            'monthly_qr_scans',
            'dynamic_rules_per_qr',
            'qr_bulk_generation',
            'max_bulk_rows',
            'short_link_quota',
            'monthly_clicks',
            'bulk_import',
            'bulk_rows_per_import',
            'analytics_retention',
            'custom_short_codes',
            'password_links',
            'expiration_and_click_caps',
            'advanced_routing',
            'api_and_webhooks',
            'api_keys',
            'webhooks',
        ];
    }

    public static function priority(?string $key): int
    {
        if (! is_string($key) || $key === '') {
            return 80;
        }

        return self::priorities()[$key] ?? 80;
    }

    public static function isMlhubAiFeature(array $item): bool
    {
        return ($item['key'] ?? null) === self::MLHUB_AI_KEY;
    }

    /**
     * @param  array<int, array<string, mixed>>  $features
     * @return array<int, string>
     */
    public static function outerFeatureKeys(array $features): array
    {
        return collect($features)
            ->map(fn ($item) => (string) ($item['key'] ?? ''))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $features
     * @param  array<int, string>  $outerFeatureKeys
     */
    public static function promotedSubFeatures(array $features, array $outerFeatureKeys): Collection
    {
        return collect($features)
            ->flatMap(fn ($item) => collect($item['subfeature'] ?? [])->flatMap(fn ($group) => $group['items'] ?? []))
            ->filter(function ($sub) use ($outerFeatureKeys) {
                $key = (string) ($sub['key'] ?? '');

                return $key !== ''
                    && in_array($key, self::promotedKeys(), true)
                    && ! in_array($key, $outerFeatureKeys, true);
            })
            ->values();
    }

    /**
     * @param  array<int, array<string, mixed>>  $features
     * @param  Collection<int, array<string, mixed>>  $promotedSubFeatures
     * @return array<int, string>
     */
    public static function visibleFeatureKeys(array $features, Collection $promotedSubFeatures): array
    {
        return array_values(array_unique(array_merge(
            self::outerFeatureKeys($features),
            $promotedSubFeatures
                ->map(fn ($item) => (string) ($item['key'] ?? ''))
                ->filter()
                ->all()
        )));
    }

    /**
     * @param  array<int, string>  $visibleFeatureKeys
     */
    public static function visibleSubFeatureCountResolver(array $visibleFeatureKeys): \Closure
    {
        return function (array $item) use ($visibleFeatureKeys): int {
            return collect($item['subfeature'] ?? [])
                ->flatMap(fn ($group) => $group['items'] ?? [])
                ->filter(function ($sub) use ($visibleFeatureKeys) {
                    $key = (string) ($sub['key'] ?? '');
                    $label = strtolower(trim((string) ($sub['label'] ?? '')));

                    return $key !== ''
                        && ! in_array($key, $visibleFeatureKeys, true)
                        && stripos($label, 'approval') === false
                        && stripos($label, 'approvals') === false;
                })
                ->count();
        };
    }

    /**
     * @param  array<int, array<string, mixed>>  $features
     * @param  Collection<int, array<string, mixed>>  $promotedSubFeatures
     */
    public static function orderedPublicFeatures(
        array $features,
        Collection $promotedSubFeatures,
        \Closure $visibleSubFeatureCount,
        bool $hideUrlShortener = false,
    ): Collection {
        return collect($features)
            ->merge($promotedSubFeatures)
            ->filter(function ($item) use ($visibleSubFeatureCount, $hideUrlShortener) {
                $label = strtolower(trim((string) ($item['label'] ?? $item)));
                $key = (string) ($item['key'] ?? '');

                if ($label === '' || stripos($label, 'approval') !== false || stripos($label, 'approvals') !== false) {
                    return false;
                }

                if (($item['key'] ?? null) === 'access_feature' || $label === 'access features') {
                    return false;
                }

                if ($hideUrlShortener && ($key === 'url_shortener' || $label === 'url shortener')) {
                    return false;
                }

                if (($item['key'] ?? null) === 'access_feature' && $visibleSubFeatureCount($item) === 0) {
                    return false;
                }

                if (($item['type'] ?? null) === 'group' && $visibleSubFeatureCount($item) === 0) {
                    return false;
                }

                return true;
            })
            ->sortBy(fn ($item) => self::priority($item['key'] ?? null))
            ->values();
    }

}
