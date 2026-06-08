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
            $raw = $this->envValue((string) $mapping['env']);

            if ($raw === null) {
                continue;
            }

            $value = $this->transform($raw, $mapping['transform'] ?? null);
            $options->set((string) $mapping['option'], $value);
            $applied++;
        }

        $this->syncLicenseMeta($options);

        return $applied;
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
        $purchaseCode = self::envValue('MLHUB_LICENSE_PURCHASE_CODE');

        if ($purchaseCode === null) {
            return;
        }

        $license = (array) config('mlhub.license', []);
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
