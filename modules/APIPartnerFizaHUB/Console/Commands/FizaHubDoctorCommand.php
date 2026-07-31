<?php

namespace Modules\APIPartnerFizaHUB\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Modules\APIPartnerFizaHUB\Support\PartnerReadinessChecker;
use Throwable;

/**
 * Standalone deploy-readiness diagnostic for MLHUB ops: token, migrations, tables, columns,
 * default plan, and routes — the exact checklist from the FizaHUB onboarding 500 root-cause
 * investigation, so the next deploy gap is caught by running one command instead of grepping
 * production logs.
 */
class FizaHubDoctorCommand extends Command
{
    protected $signature = 'fizahub:doctor';

    protected $description = 'Diagnose FizaHUB partner API deploy readiness (token, migrations, tables, columns, plan, routes).';

    /** @var list<string> */
    private const REQUIRED_MIGRATIONS = [
        '2026_07_13_000000_create_fizahub_partner_api_tables',
        '2026_07_17_120000_extend_fizahub_partner_onboarding_tables',
        '2026_07_20_000000_add_crm_login_idempotency_to_partner_one_time_logins',
    ];

    /** @var list<string> */
    private const REQUIRED_ROUTES = [
        'partner.fizahub.health',
        'partner.fizahub.sso.verify',
        'partner.fizahub.marketing-catalog',
        'partner.fizahub.onboarding.store',
        'partner.fizahub.onboarding.show',
        'partner.fizahub.businesses.marketing-status',
        'partner.fizahub.businesses.profile.update',
        'partner.fizahub.businesses.marketing-preferences.update',
        'partner.fizahub.businesses.dashboard',
        'partner.fizahub.businesses.growth-insights',
        'partner.fizahub.businesses.campaigns.index',
        'partner.fizahub.businesses.campaigns.show',
        'partner.fizahub.businesses.campaigns.approval',
        'partner.fizahub.businesses.package',
        'partner.fizahub.businesses.support-presets',
        'partner.fizahub.businesses.support-tickets.store',
        'partner.fizahub.businesses.support-tickets.index',
        'partner.fizahub.businesses.support-tickets.show',
        'partner.fizahub.businesses.support-tickets.messages.store',
        'partner.fizahub.businesses.support-tickets.close',
        'partner.fizahub.businesses.support-tickets.reopen',
        'partner.fizahub.businesses.support-tickets.attachments.index',
        'partner.fizahub.businesses.support-tickets.attachments.store',
        'partner.fizahub.businesses.support-tickets.attachments.show',
        'partner.fizahub.businesses.crm-login-links.store',
    ];

    /** @var list<string> */
    private const FORBIDDEN_ROUTE_NAMES = [
        'partner.fizahub.packages.index',
        'partner.fizahub.onboarding.confirm',
        'partner.fizahub.onboarding.cancel',
        'partner.fizahub.businesses.integration-status',
        'partner.fizahub.businesses.one-time-login',
        'partner.fizahub.businesses.insights',
        'partner.fizahub.businesses.recommendations',
        'partner.fizahub.businesses.support-summary',
        'partner.fizahub.support-tickets.show',
        'partner.fizahub.support-tickets.messages.store',
        'partner.fizahub.support-tickets.attachments.store',
        'partner.fizahub.support-tickets.close',
        'partner.fizahub.support-tickets.reopen',
    ];

    public function handle(PartnerReadinessChecker $readiness): int
    {
        $this->info('FizaHUB Partner API — readiness doctor');
        $this->newLine();

        $tokenOk = $this->checkToken();
        $migrationsOk = $this->checkMigrations();

        $result = $readiness->check();

        $readinessOk = true;

        foreach (['database', 'partner_schema', 'default_plan', 'support_tables'] as $key) {
            $check = $result['checks'][$key];
            $this->printLine($key, $check['status'] === 'ok', $check['message']);
            $readinessOk = $readinessOk && $check['status'] === 'ok';
        }

        $routesOk = $this->checkRoutes();

        $allPassed = $tokenOk && $migrationsOk && $readinessOk && $routesOk;

        $this->newLine();

        if ($allPassed) {
            $this->info('OVERALL: PASS — FizaHUB partner API is ready.');

            return self::SUCCESS;
        }

        $this->error('OVERALL: FAIL — see FAIL lines above. Fix before pointing FizaHUB at this environment.');

        return self::FAILURE;
    }

    private function checkToken(): bool
    {
        $token = (string) config('modules.apipartnerfizahub.token', '');

        if ($token === '') {
            $this->printLine('token', false, 'FIZAHUB_PARTNER_TOKEN is empty — every partner request will be rejected with invalid_partner_token.');

            return false;
        }

        if ($token === 'fizahub') {
            $this->printLine('token', true, "Token is set (testing-phase default 'fizahub' — rotate to a strong secret before going live).");

            return true;
        }

        $this->printLine('token', true, 'Token is configured.');

        return true;
    }

    private function checkMigrations(): bool
    {
        try {
            if (! Schema::hasTable('migrations')) {
                $this->printLine('migrations', false, 'The migrations table does not exist — has `php artisan migrate` ever run on this database?');

                return false;
            }

            $ran = DB::table('migrations')->pluck('migration')->all();
            $missing = array_values(array_diff(self::REQUIRED_MIGRATIONS, $ran));

            if ($missing !== []) {
                $this->printLine('migrations', false, 'Missing FizaHUB migrations: '.implode(', ', $missing).'. Run `php artisan migrate --force`.');

                return false;
            }

            $this->printLine('migrations', true, 'All '.count(self::REQUIRED_MIGRATIONS).' FizaHUB module migrations have run.');

            return true;
        } catch (Throwable) {
            $this->printLine('migrations', false, 'Unable to inspect migration state because the database is unavailable.');

            return false;
        }
    }

    private function checkRoutes(): bool
    {
        $apiRoutes = collect(Route::getRoutes())
            ->filter(fn ($route): bool => str_starts_with($route->uri(), 'api/v1/partners/fizahub'));
        $actualNames = $apiRoutes->map(fn ($route): ?string => $route->getName())->filter()->values()->all();
        $missing = array_values(array_filter(
            self::REQUIRED_ROUTES,
            fn (string $name): bool => ! in_array($name, $actualNames, true)
        ));
        $unexpected = array_values(array_diff($actualNames, self::REQUIRED_ROUTES));
        $forbidden = array_values(array_filter(
            self::FORBIDDEN_ROUTE_NAMES,
            fn (string $name): bool => in_array($name, $actualNames, true)
        ));

        if ($missing !== [] || $unexpected !== [] || $forbidden !== [] || $apiRoutes->count() !== count(self::REQUIRED_ROUTES)) {
            $details = array_filter([
                $missing === [] ? null : 'missing: '.implode(', ', $missing),
                $unexpected === [] ? null : 'unexpected: '.implode(', ', $unexpected),
                $forbidden === [] ? null : 'forbidden: '.implode(', ', $forbidden),
                'count: '.$apiRoutes->count().'/'.count(self::REQUIRED_ROUTES),
            ]);

            $this->printLine('routes', false, 'Route contract mismatch ('.implode('; ', $details).').');

            return false;
        }

        $this->printLine('routes', true, 'All '.count(self::REQUIRED_ROUTES).' documented partner routes are registered.');

        return true;
    }

    private function printLine(string $check, bool $passed, string $message): void
    {
        $label = $passed ? '<fg=green>PASS</>' : '<fg=red>FAIL</>';

        $this->line(sprintf('[%s] %-16s %s', $label, $check, $message));
    }
}
