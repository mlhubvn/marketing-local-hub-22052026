<?php

namespace Database\Seeders;

use Database\Support\MlhubDemoVolume;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Modules\AdminSettings\Support\OptionStore;

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
        'payu_salt',
        'razorpay_key_secret',
        'razorpay_webhook_secret',
        'paytm_merchant_key',
        'yoomoney_secret_key',
        'ccavenue_working_key',
        '2checkout_secret_key',
        'sslcommerz_store_password',
        'paystack_secret_key',
        'instamojo_client_secret',
        'instamojo_salt',
        'flutterwave_secret_key',
        'iyzico_secret_key',
        'paytr_merchant_key',
        'paytr_merchant_salt',
        'auth_facebook_login_app_secret',
        'auth_google_login_client_secret',
        'auth_x_login_client_secret',
    ];

    public function run(): void
    {
        $this->seedSiteOptions();
        $this->seedLicenseOptions();
    }

    protected function seedSiteOptions(): void
    {
        if (! class_exists(OptionStore::class)) {
            return;
        }

        $demo = MlhubDemoVolume::demoConfig();
        $site = (array) config('mlhub.site', []);
        /** @var OptionStore $options */
        $options = app(OptionStore::class);

        $title = (string) ($site['title'] ?? 'MLHUB');
        $guestTheme = (string) ($site['guest_theme'] ?? 'mlhubtheme');
        $backendTheme = (string) ($site['backend_theme'] ?? 'default');

        $options->set('website_title', $title);
        $options->set('website_description', (string) ($site['description'] ?? ''));
        $options->set('website_keyword', (string) ($site['keywords'] ?? ''));
        $options->set('website_favicon', '');
        $options->set('website_logo_dark', '');
        $options->set('website_logo_light', '');
        $options->set('website_logo_brand_dark', '');
        $options->set('website_logo_brand_light', '');
        $options->set('contact_company_name', $title);
        $options->set('contact_email', (string) config('mlhub.contact_email', $demo['user']['email'] ?? 'demo@mlhub.vn'));
        $options->set('app_timezone', (string) config('mlhub.timezone', 'Asia/Ho_Chi_Minh'));
        $options->set('default_locale', (string) config('mlhub.locale', 'vi'));
        $options->set(config('themes.areas.guest.option_key', 'frontend_theme'), $guestTheme);
        $options->set(config('themes.areas.app.option_key', 'backend_theme'), $backendTheme);
        $options->set('theme_settings.guest.'.$guestTheme, (array) config('mlhub.guest_theme_settings', []));
        $options->set('theme_settings.app.'.$backendTheme, (array) config('mlhub.backend_theme_settings', []));
        $options->set('installer_completed_at', Carbon::now()->toIso8601String());
        $options->set('system_cron_secure_key', Str::random(16));

        $path = database_path('seeders/data/mlhub_site_options.php');

        if (is_file($path)) {
            foreach ((array) require $path as $name => $value) {
                if (in_array($name, $this->secretOptionKeys, true)) {
                    continue;
                }

                $options->set($name, $value);
            }
        }

        if ($password = env('MAIL_PASSWORD')) {
            $options->set('smtp_password', $password);
        }
    }

    protected function seedLicenseOptions(): void
    {
        if (! class_exists(OptionStore::class)) {
            return;
        }

        $license = (array) config('mlhub.license', []);
        $purchaseCode = trim((string) ($license['purchase_code'] ?? ''));

        if ($purchaseCode === '') {
            return;
        }

        $verifiedAt = Carbon::now()->toIso8601String();
        /** @var OptionStore $options */
        $options = app(OptionStore::class);

        $options->set('license_purchase_code', $purchaseCode);
        $options->set('license_status', 'verified');
        $options->set('license_product_id', (string) ($license['product_id'] ?? 10252026));
        $options->set('license_version', (string) ($license['version'] ?? '1.0.1'));
        $options->set('license_install_path', (string) ($license['install_path'] ?? './'));
        $options->set('license_verified_at', $verifiedAt);
        $options->set('license_meta', [
            'status' => 1,
            'message' => 'Addon installation verified.',
            'module_name' => null,
            'slug' => 'localboost-ai-review-booster-booking-coupons-feedback-lead-generation-saas',
            'version' => (string) ($license['version'] ?? '1.0.1'),
            'license' => (string) ($license['license_type'] ?? 'Extended License'),
            'domain' => (string) ($license['domain'] ?? 'mlhub.vn'),
            'product_id' => (int) ($license['product_id'] ?? 10252026),
            'install_path' => (string) ($license['install_path'] ?? './'),
        ]);
    }
}
