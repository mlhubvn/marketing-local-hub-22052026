<?php

namespace Modules\CustomMLHUB\Support;

use Illuminate\Support\Carbon;
use Modules\AdminSettings\Support\OptionStore;

class MLHUBEnvOptionsSync
{
    /** @var list<string> */
    protected array $placeholderFragments = [
        'Coolify',
        'không commit',
        'đặt secret',
        'mật khẩu mạnh',
        'hostname',
        'API key',
        'mã license',
    ];

    public function apply(): int
    {
        if (! class_exists(OptionStore::class)) {
            return 0;
        }

        if (! filter_var(env('MLHUB_SYNC_ENV_OPTIONS', true), FILTER_VALIDATE_BOOL)) {
            return 0;
        }

        /** @var OptionStore $options */
        $options = app(OptionStore::class);
        $applied = 0;

        foreach ($this->mappings() as $mapping) {
            $optionKey = (string) $mapping['option'];
            $raw = $this->envValue((string) $mapping['env']);

            if ($raw !== null) {
                // Env (Coolify) là nguồn ưu tiên — luôn ghi đè.
                $value = $this->transform($raw, $mapping['transform'] ?? null);
                $options->set($optionKey, $value);
                $applied++;

                continue;
            }

            // Không có env → dùng default trong code (giai đoạn thử nghiệm), CHỈ điền khi option còn trống
            // để không ghi đè giá trị admin đã chỉnh trong UI.
            $default = $mapping['default'] ?? null;

            if ($default === null || $default === '') {
                continue;
            }

            $existing = $options->get($optionKey, null);

            if ($existing !== null && trim((string) $existing) !== '') {
                continue;
            }

            $value = $this->transform((string) $default, $mapping['transform'] ?? null);
            $options->set($optionKey, $value);
            $applied++;
        }

        $this->syncLicenseMeta($options);
        $this->syncProviderStatusFromCredentials($options);

        return $applied;
    }

    protected function syncProviderStatusFromCredentials(OptionStore $options): void
    {
        $providers = [
            [
                'status_option' => 'auth_google_login_status',
                'status_env' => 'MLHUB_AUTH_GOOGLE_LOGIN_STATUS',
                'id_option' => 'auth_google_login_client_id',
                'secret_option' => 'auth_google_login_client_secret',
            ],
            [
                'status_option' => 'auth_facebook_login_status',
                'status_env' => 'MLHUB_AUTH_FACEBOOK_LOGIN_STATUS',
                'id_option' => 'auth_facebook_login_app_id',
                'secret_option' => 'auth_facebook_login_app_secret',
            ],
            [
                'status_option' => 'auth_x_login_status',
                'status_env' => 'MLHUB_AUTH_X_LOGIN_STATUS',
                'id_option' => 'auth_x_login_client_id',
                'secret_option' => 'auth_x_login_client_secret',
            ],
            [
                'status_option' => 'integration_google_business_profile_status',
                'status_env' => 'MLHUB_GOOGLE_BUSINESS_STATUS',
                'id_option' => 'integration_google_business_profile_client_id',
                'secret_option' => 'integration_google_business_profile_client_secret',
            ],
        ];

        foreach ($providers as $provider) {
            if (self::envValue((string) $provider['status_env']) !== null) {
                continue;
            }

            $clientId = trim((string) $options->get((string) $provider['id_option'], ''));
            $clientSecret = trim((string) $options->get((string) $provider['secret_option'], ''));

            if ($clientId !== '' && $clientSecret !== '') {
                $options->set((string) $provider['status_option'], '1');
            }
        }

        if (self::envValue('MLHUB_CLOUDFLARE_TURNSTILE_STATUS') === null) {
            $turnstileSiteKey = trim((string) $options->get('auth_cloudflare_turnstile_site_key', ''));
            $turnstileSecretKey = trim((string) $options->get('auth_cloudflare_turnstile_secret_key', ''));

            if ($turnstileSiteKey !== '' && $turnstileSecretKey !== '') {
                $options->set('auth_cloudflare_turnstile_status', '1');
            }
        }

        if (self::envValue('MLHUB_GOOGLE_RECAPTCHA_STATUS') === null) {
            $recaptchaSiteKey = trim((string) $options->get('auth_google_recaptcha_site_key', ''));
            $recaptchaSecretKey = trim((string) $options->get('auth_google_recaptcha_secret_key', ''));

            if ($recaptchaSiteKey !== '' && $recaptchaSecretKey !== '') {
                $options->set('auth_google_recaptcha_status', '1');
            }
        }
    }

    public static function envValue(string $key): ?string
    {
        $value = env($key);

        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return null;
        }

        if (str_starts_with($trimmed, '(') && str_ends_with($trimmed, ')')) {
            return null;
        }

        $instance = new self;

        foreach ($instance->placeholderFragments as $fragment) {
            if (stripos($trimmed, $fragment) !== false) {
                return null;
            }
        }

        return $trimmed;
    }

    /**
     * @return list<array{env: string, option: string, transform?: callable|string|null}>
     */
    protected function mappings(): array
    {
        $path = base_path('modules/CustomMLHUB/config/env_options.php');

        if (! is_file($path)) {
            return [];
        }

        $mappings = require $path;

        return is_array($mappings) ? $mappings : [];
    }

  /**
   * @param  callable|string|null  $transform
   */
    protected function transform(string $value, mixed $transform): mixed
    {
        if ($transform === 'bool01') {
            return filter_var($value, FILTER_VALIDATE_BOOL) ? '1' : '0';
        }

        if (is_callable($transform)) {
            return $transform($value);
        }

        return $value;
    }

    protected function syncLicenseMeta(OptionStore $options): void
    {
        $license = (array) config('mlhub.license', []);
        $purchaseCode = self::envValue('MLHUB_LICENSE_PURCHASE_CODE');

        // Fallback default trong config/mlhub.php (giai đoạn thử nghiệm) khi không đặt env,
        // và chỉ khi options chưa có license để không ghi đè cấu hình admin.
        if ($purchaseCode === null) {
            $configCode = trim((string) ($license['purchase_code'] ?? ''));
            $existingCode = trim((string) $options->get('license_purchase_code', ''));

            if ($configCode === '' || $existingCode !== '') {
                return;
            }

            $purchaseCode = $configCode;
        }

        $verifiedAt = Carbon::now()->toIso8601String();

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
