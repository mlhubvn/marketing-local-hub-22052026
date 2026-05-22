<?php

namespace Modules\AppBusinessProfiles\Support;

class BusinessQrStyleCatalog
{
    public static function all(): array
    {
        return [
            'clean_card' => [
                'label' => __('Clean card'),
                'pattern' => 'square',
                'foreground' => '#0f172a',
                'background' => '#ffffff',
                'accent' => '#0f766e',
                'surface' => '#f8fafc',
                'shape' => 'square',
            ],
            'rounded_gradient' => [
                'label' => __('Rounded gradient'),
                'pattern' => 'gradient_rounded',
                'foreground' => '#0f766e',
                'background' => '#ffffff',
                'accent' => '#14b8a6',
                'surface' => '#ecfeff',
                'shape' => 'rounded',
            ],
            'emerald_ring' => [
                'label' => __('Emerald ring'),
                'pattern' => 'square',
                'foreground' => '#0f7a32',
                'background' => '#ffffff',
                'accent' => '#16a34a',
                'surface' => '#ecfdf5',
                'shape' => 'square',
            ],
            'navy_label' => [
                'label' => __('Navy label'),
                'pattern' => 'square',
                'foreground' => '#0f4f87',
                'background' => '#ffffff',
                'accent' => '#f97316',
                'surface' => '#eff6ff',
                'shape' => 'square',
            ],
            'forest_mosaic' => [
                'label' => __('Forest mosaic'),
                'pattern' => 'mosaic',
                'foreground' => '#15803d',
                'background' => '#f0fdf4',
                'accent' => '#65a30d',
                'surface' => '#dcfce7',
                'shape' => 'circle',
            ],
            'carbon_mosaic' => [
                'label' => __('Carbon mosaic'),
                'pattern' => 'mosaic',
                'foreground' => '#334155',
                'background' => '#f8fafc',
                'accent' => '#64748b',
                'surface' => '#e2e8f0',
                'shape' => 'rounded',
            ],
        ];
    }

    public static function default(): array
    {
        return [
            'template' => 'rounded_gradient',
            'foreground_color' => '#0f766e',
            'background_color' => '#ffffff',
            'accent_color' => '#14b8a6',
            'surface_color' => '#ecfeff',
            'shape' => 'rounded',
            'frame_style' => 'card',
            'label' => __('Scan to visit'),
            'show_business_name' => true,
            'show_address' => true,
            'logo_path' => null,
            'logo_enabled' => true,
            'logo_data_uri' => null,
        ];
    }

    public static function designFor(string $template): array
    {
        $templateData = self::all()[$template] ?? self::all()['rounded_gradient'];

        return array_merge(self::default(), [
            'template' => $template,
            'foreground_color' => $templateData['foreground'],
            'background_color' => $templateData['background'],
            'accent_color' => $templateData['accent'],
            'surface_color' => $templateData['surface'],
            'shape' => $templateData['shape'],
            'pattern' => $templateData['pattern'],
        ]);
    }

    public static function normalize(array $design): array
    {
        $template = (string) ($design['template'] ?? 'rounded_gradient');
        $base = self::designFor($template);
        $frameStyle = (string) ($design['frame_style'] ?? 'card');

        return array_merge($base, [
            'foreground_color' => self::hex((string) ($design['foreground_color'] ?? $base['foreground_color']), $base['foreground_color']),
            'background_color' => self::hex((string) ($design['background_color'] ?? $base['background_color']), $base['background_color']),
            'accent_color' => self::hex((string) ($design['accent_color'] ?? $base['accent_color']), $base['accent_color']),
            'surface_color' => self::hex((string) ($design['surface_color'] ?? $base['surface_color']), $base['surface_color']),
            'frame_style' => in_array($frameStyle, ['card', 'label', 'minimal'], true) ? $frameStyle : 'card',
            'label' => trim((string) ($design['label'] ?? $base['label'])) ?: $base['label'],
            'show_business_name' => (bool) ($design['show_business_name'] ?? true),
            'show_address' => (bool) ($design['show_address'] ?? true),
            'logo_path' => filled($design['logo_path'] ?? null) ? (string) $design['logo_path'] : null,
            'logo_enabled' => (bool) ($design['logo_enabled'] ?? true),
            'logo_data_uri' => self::dataUri((string) ($design['logo_data_uri'] ?? '')),
        ]);
    }

    private static function hex(string $hex, string $fallback): string
    {
        $hex = strtolower(trim($hex));
        $hex = str_starts_with($hex, '#') ? $hex : '#'.$hex;

        return preg_match('/^#[0-9a-f]{6}$/', $hex) ? $hex : $fallback;
    }

    private static function dataUri(string $value): ?string
    {
        $value = trim($value);

        return str_starts_with($value, 'data:image/') ? $value : null;
    }
}
