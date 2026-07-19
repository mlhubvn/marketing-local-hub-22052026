<?php

namespace Modules\APIPartnerFizaHUB\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Modules\APIPartnerFizaHUB\Support\PartnerReadinessChecker;

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
    ];

    /** @var list<string> */
    private const REQUIRED_ROUTES = [
        'partner.fizahub.health',
        'partner.fizahub.sso.verify',
        'partner.fizahub.packages.index',
        'partner.fizahub.onboarding.store',
        'partner.fizahub.onboarding.show',
        'partner.fizahub.onboarding.confirm',
        'partner.fizahub.onboarding.cancel',
        'partner.fizahub.businesses.integration-status',
        'partner.fizahub.businesses.profile.update',
        'partner.fizahub.businesses.one-time-login',
        'partner.fizahub.businesses.package',
        'partner.fizahub.businesses.dashboard',
        'partner.fizahub.businesses.insights',
        'partner.fizahub.businesses.recommendations',
        'partner.fizahub.businesses.campaigns.index',
        'partner.fizahub.businesses.campaigns.show',
        'partner.fizahub.businesses.support-summary',
        'partner.fizahub.businesses.support-tickets.store',
        'partner.fizahub.businesses.support-tickets.index',
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

        $this->printLine('migrations', true, 'Both FizaHUB module migrations have run.');

        return true;
    }

    private function checkRoutes(): bool
    {
        $missing = array_values(array_filter(
            self::REQUIRED_ROUTES,
            fn (string $name): bool => ! Route::has($name)
        ));

        if ($missing !== []) {
            $this->printLine('routes', false, 'Missing routes: '.implode(', ', $missing).'.');

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
