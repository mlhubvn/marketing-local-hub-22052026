<?php

use Modules\AdminSettings\Support\OptionStore;
use Modules\AdminSettings\Support\SettingsPageRegistry;

if (! function_exists('get_option')) {
    function get_option(string $name, mixed $default = null): mixed
    {
        return app(OptionStore::class)->get($name, $default);
    }
}

if (! function_exists('settings_page_registry')) {
    function settings_page_registry(): SettingsPageRegistry
    {
        return app(SettingsPageRegistry::class);
    }
}

if (! function_exists('register_setting_section')) {
    function register_setting_section(string $key, ?string $label = null, int $order = 100): SettingsPageRegistry
    {
        return settings_page_registry()->section($key, $label, $order);
    }
}

if (! function_exists('register_settings_section')) {
    function register_settings_section(string $key, ?string $label = null, int $order = 100): SettingsPageRegistry
    {
        return register_setting_section($key, $label, $order);
    }
}

if (! function_exists('register_setting_item')) {
    function register_setting_item(string $sectionKey, array $item): SettingsPageRegistry
    {
        return settings_page_registry()->register($sectionKey, $item);
    }
}

if (! function_exists('register_settings_item')) {
    function register_settings_item(string $sectionKey, array $item): SettingsPageRegistry
    {
        return register_setting_item($sectionKey, $item);
    }
}

if (! function_exists('settings_navigation_sections')) {
    function settings_navigation_sections(): array
    {
        return settings_page_registry()->sections();
    }
}

if (! function_exists('settings_navigation_items')) {
    function settings_navigation_items(): array
    {
        return settings_page_registry()->items();
    }
}

if (! function_exists('settings_default_url')) {
    function settings_default_url(): string
    {
        return settings_page_registry()->defaultUrl();
    }
}

if (! function_exists('site_brand_title')) {
    /**
     * Brand label for guest <title> / chrome. On a host different from APP_URL
     * (white-label / partner domain), use the request host so e.g. fzh.vmo.com.vn
     * shows "Đăng nhập - fzh.vmo.com.vn" instead of the primary site title.
     */
    function site_brand_title(?string $fallback = null): string
    {
        $configured = trim((string) get_option('website_title', ''));
        if ($configured === '') {
            $configured = trim((string) ($fallback
                ?? config('mlhub.site.title')
                ?? config('site.title')
                ?? config('app.name')
                ?? 'MKT'));
        }
        if ($configured === '') {
            $configured = 'MKT';
        }

        if (! app()->bound('request') || ! request()) {
            return $configured;
        }

        $normalize = static function (string $host): string {
            $host = strtolower(trim($host));
            $host = preg_replace('/:\d+$/', '', $host) ?: '';
            $host = preg_replace('/^www\./', '', $host) ?: '';

            return $host;
        };

        $requestHost = $normalize((string) request()->getHost());
        $primaryHost = $normalize((string) (parse_url((string) config('app.url'), PHP_URL_HOST) ?: ''));

        if ($requestHost !== '' && $primaryHost !== '' && $requestHost !== $primaryHost) {
            return $requestHost;
        }

        return $configured;
    }
}
