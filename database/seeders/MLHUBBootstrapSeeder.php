<?php

namespace Database\Seeders;

use Database\Support\IdSequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Modules\AdminSettings\Support\OptionStore;
use Modules\CustomMLHUB\Support\MLHUBEnvOptionsSync;

class MLHUBBootstrapSeeder extends Seeder
{
    /** @var list<string> */
    protected array $secretOptionKeys = [
        'smtp_password',
        'auth_google_recaptcha_secret_key',
        'auth_cloudflare_turnstile_secret_key',
        'paypal_client_secret',
        'stripe_secret_key',
        'stripe_webhook_secret',
        '2checkout_secret_key',
        'auth_facebook_login_app_secret',
        'auth_google_login_client_secret',
        'auth_x_login_client_secret',
    ];

    public function run(): void
    {
        if (! IdSequence::isUpdateMode()) {
            $this->seedSiteOptions();
        }
    }

    protected function seedSiteOptions(): void
    {
        if (! class_exists(OptionStore::class)) {
            return;
        }

        $site = (array) config('mlhub.site', []);
        /** @var OptionStore $options */
        $options = app(OptionStore::class);

        $title = (string) ($site['title'] ?? 'MKT');
        $guestTheme = (string) ($site['guest_theme'] ?? 'mlhubfrontend');
        $backendTheme = (string) ($site['backend_theme'] ?? 'mlhubbackend');

        $options->set('website_title', $title);
        $options->set('website_description', (string) ($site['description'] ?? ''));
        $options->set('website_keyword', (string) ($site['keywords'] ?? ''));
        $options->set('website_favicon', (string) config('mlhub.site.favicon', 'img/favicon.svg'));
        $options->set('website_logo_dark', (string) config('mlhub.site.logo_dark', 'img/logo-dark.svg'));
        $options->set('website_logo_light', (string) config('mlhub.site.logo_light', 'img/logo-light.svg'));
        $options->set('website_logo_brand_dark', (string) config('mlhub.site.brand_logo_dark', 'img/logo-brand-dark.svg'));
        $options->set('website_logo_brand_light', (string) config('mlhub.site.brand_logo_light', 'img/logo-brand-light.svg'));
        $options->set('contact_company_name', $title);
        $contactEmail = trim((string) config('mlhub.contact_email', ''));
        if ($contactEmail === '') {
            $contactEmail = trim((string) config('custommlhub.first_user.email', ''));
        }
        $options->set('contact_email', $contactEmail);
        $options->set('app_timezone', (string) config('mlhub.timezone', 'Asia/Ho_Chi_Minh'));
        $options->set('default_locale', (string) config('mlhub.locale', 'vi'));
        $options->set(config('themes.areas.guest.option_key', 'frontend_theme'), $guestTheme);
        $options->set(config('themes.areas.app.option_key', 'backend_theme'), $backendTheme);
        $options->set('theme_settings.guest.'.$guestTheme, (array) config('mlhub.guest_theme_settings', []));
        $options->set('theme_settings.app.'.$backendTheme, (array) config('mlhub.backend_theme_settings', []));
        $options->set('installer_completed_at', Carbon::now()->toIso8601String());

        $cronKey = MLHUBEnvOptionsSync::envValue('MLHUB_SYSTEM_CRON_SECURE_KEY');

        $options->set('system_cron_secure_key', $cronKey ?? Str::random(16));

        $path = (string) config('custommlhub.site_options_file', database_path('seeders/data/mlhub_site_options.php'));

        if (is_file($path)) {
            foreach ((array) require $path as $name => $value) {
                if (in_array($name, $this->secretOptionKeys, true)) {
                    continue;
                }

                $options->set($name, $value);
            }
        }

    }
}

