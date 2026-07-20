<?php

namespace Modules\APIPartnerFizaHUB\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\APIPartnerFizaHUB\Services\PartnerMappingService;
use Throwable;

/**
 * Shared readiness logic used by both `GET /health` (PartnerApiResponse for FizaHUB) and
 * `php artisan fizahub:doctor` (human-readable PASS/FAIL for MLHUB ops), so the two never
 * drift apart on what "ready" means for this module.
 */
class PartnerReadinessChecker
{
    /**
     * FizaHUB's own tables from both onboarding migrations.
     *
     * @var list<string>
     */
    private const PARTNER_TABLES = [
        'partner_integrations',
        'partner_onboarding_requests',
        'partner_api_logs',
        'partner_one_time_logins',
        'partner_onboarding_status_histories',
        'partner_package_assignments',
        'partner_support_presets',
        'partner_support_ticket_contexts',
        'partner_webhook_outbox',
        'partner_support_attachments',
    ];

    /**
     * Columns added by the 2026_07_17 extension migration; missing these means only the
     * first migration ran, which is exactly the deploy state that caused the onboarding 500.
     */
    private const PARTNER_ONBOARDING_COLUMNS = [
        'requested_package_code',
        'approved_package_code',
        'admin_status',
    ];

    /** @var list<string> */
    private const CRM_LOGIN_COLUMNS = [
        'idempotency_key',
        'token_ciphertext',
    ];

    /**
     * AdminSupport tables the support-ticket bridge writes to; missing these breaks every
     * support endpoint even when the FizaHUB-owned schema is fine.
     *
     * @var list<string>
     */
    private const SUPPORT_TABLES = [
        'support_tickets',
        'support_comments',
    ];

    public function __construct(
        private readonly PartnerMappingService $mapping
    ) {}

    /**
     * @return array{status: 'ok'|'degraded', checks: array<string, array{status: 'ok'|'degraded', message: string}>}
     */
    public function check(): array
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'partner_schema' => $this->checkPartnerSchema(),
            'default_plan' => $this->checkDefaultPlan(),
            'support_tables' => $this->checkSupportTables(),
        ];

        $status = collect($checks)->contains(fn (array $check): bool => $check['status'] !== 'ok')
            ? 'degraded'
            : 'ok';

        return ['status' => $status, 'checks' => $checks];
    }

    /**
     * @return array{status: 'ok'|'degraded', message: string}
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return ['status' => 'ok', 'message' => 'Database connection is reachable.'];
        } catch (Throwable) {
            return ['status' => 'degraded', 'message' => 'Cannot reach the database connection.'];
        }
    }

    /**
     * @return array{status: 'ok'|'degraded', message: string}
     */
    private function checkPartnerSchema(): array
    {
        try {
            $missingTables = array_values(array_filter(
                self::PARTNER_TABLES,
                fn (string $table): bool => ! Schema::hasTable($table)
            ));

            $missingColumns = [];

            if (Schema::hasTable('partner_onboarding_requests')) {
                foreach (self::PARTNER_ONBOARDING_COLUMNS as $column) {
                    if (! Schema::hasColumn('partner_onboarding_requests', $column)) {
                        $missingColumns[] = "partner_onboarding_requests.{$column}";
                    }
                }
            }

            if (Schema::hasTable('partner_one_time_logins')) {
                foreach (self::CRM_LOGIN_COLUMNS as $column) {
                    if (! Schema::hasColumn('partner_one_time_logins', $column)) {
                        $missingColumns[] = "partner_one_time_logins.{$column}";
                    }
                }
            }

            $missing = array_merge($missingTables, $missingColumns);

            if ($missing !== []) {
                return [
                    'status' => 'degraded',
                    'message' => 'Missing FizaHUB schema: '.implode(', ', $missing).'. Run pending module migrations.',
                ];
            }

            return ['status' => 'ok', 'message' => 'All FizaHUB partner tables and columns are present.'];
        } catch (Throwable) {
            return ['status' => 'degraded', 'message' => 'Unable to inspect the FizaHUB partner schema.'];
        }
    }

    /**
     * @return array{status: 'ok'|'degraded', message: string}
     */
    private function checkDefaultPlan(): array
    {
        try {
            $defaultPackage = (string) config('modules.apipartnerfizahub.default_package', 'free');
            $plan = $this->mapping->resolvePlan($defaultPackage);

            if ($plan === null) {
                $slug = $this->mapping->resolvePlanSlug($defaultPackage) ?? '(unmapped package code)';

                return [
                    'status' => 'degraded',
                    'message' => "Default plan '{$slug}' for package '{$defaultPackage}' is missing or inactive. Run db:seed / php artisan mlhub:update.",
                ];
            }

            return ['status' => 'ok', 'message' => "Default plan '{$plan->slug}' is active."];
        } catch (Throwable) {
            return ['status' => 'degraded', 'message' => 'Unable to resolve the default plan.'];
        }
    }

    /**
     * @return array{status: 'ok'|'degraded', message: string}
     */
    private function checkSupportTables(): array
    {
        try {
            $missing = array_values(array_filter(
                self::SUPPORT_TABLES,
                fn (string $table): bool => ! Schema::hasTable($table)
            ));

            if ($missing !== []) {
                return [
                    'status' => 'degraded',
                    'message' => 'Missing AdminSupport tables: '.implode(', ', $missing).'.',
                ];
            }

            return ['status' => 'ok', 'message' => 'AdminSupport ticket tables are present.'];
        } catch (Throwable) {
            return ['status' => 'degraded', 'message' => 'Unable to inspect AdminSupport tables.'];
        }
    }
}
