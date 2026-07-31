<?php

namespace Modules\APIPartnerFizaHUB\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Modules\APIPartnerFizaHUB\Console\Commands\FizaHubDoctorCommand;
use Modules\APIPartnerFizaHUB\Console\Commands\RetryPartnerWebhooksCommand;
use Modules\APIPartnerFizaHUB\Console\Commands\WarmPartnerDashboardsCommand;
use Modules\APIPartnerFizaHUB\Http\Middleware\EnsureFizaHubPartnerAccess;
use Modules\APIPartnerFizaHUB\Http\Middleware\HandlePartnerRequest;
use Modules\APIPartnerFizaHUB\Http\Middleware\RestrictFizaHubDomainHost;
use Modules\APIPartnerFizaHUB\Http\Middleware\VerifyPartnerToken;
use Modules\APIPartnerFizaHUB\Support\PartnerApiException;
use Modules\APIPartnerFizaHUB\Support\PartnerExceptionRenderer;
use Throwable;

class APIPartnerFizaHUBServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.apipartnerfizahub');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'apipartnerfizahub');

        RateLimiter::for('fizahub-partner', function (Request $request) {
            $maxAttempts = max(1, (int) config('modules.apipartnerfizahub.rate_limit_per_minute', 60));

            return Limit::perMinute($maxAttempts)->by('fizahub|'.$request->ip());
        });

        $this->app['router']->aliasMiddleware('partner.fizahub.token', VerifyPartnerToken::class);
        $this->app['router']->aliasMiddleware('partner.fizahub.request', HandlePartnerRequest::class);
        $this->app['router']->aliasMiddleware('partner.fizahub.reporting-access', EnsureFizaHubPartnerAccess::class);

        // Runs on EVERY web request app-wide (see class docblock) so the reporting domain
        // can only ever reach its own tiny allowlist of routes — registered here instead of
        // bootstrap/app.php to keep this entirely isolated inside the module.
        $this->app['router']->pushMiddlewareToGroup('web', RestrictFizaHubDomainHost::class);

        $this->configurePartnerReportingSessionIsolation();

        $this->app->make(ExceptionHandler::class)->renderable(
            function (Throwable $exception, Request $request) {
                return app(PartnerExceptionRenderer::class)->render($exception, $request);
            }
        );

        // Business 4xx PartnerApiException responses must not flood production.ERROR logs.
        $this->app->make(ExceptionHandler::class)->reportable(
            function (PartnerApiException $exception): false {
                return false;
            }
        );

        if ($this->app->runningInConsole()) {
            $this->commands([
                RetryPartnerWebhooksCommand::class,
                WarmPartnerDashboardsCommand::class,
                FizaHubDoctorCommand::class,
            ]);

            $this->app->booted(function (): void {
                $schedule = $this->app->make(Schedule::class);

                $schedule->command('fizahub:webhooks-retry')
                    ->everyFiveMinutes()
                    ->withoutOverlapping();

                $schedule->command('fizahub:dashboard-warm')
                    ->hourly()
                    ->withoutOverlapping();
            });
        }

        $this->registerSidebar();
    }

    /**
     * `mlhub.vn` and `FIZAHUB_DOMAIN` are two different registrable domains, so the
     * production session cookie (`domain => .mlhub.vn`, see config/session.php) is not a
     * valid `Set-Cookie` domain attribute for the reporting host — browsers silently drop
     * such a cookie, which would break login there entirely rather than "share" a session.
     *
     * This runs once per request during `BootProviders`, before the `web` middleware group
     * (and therefore before Laravel's `StartSession`) executes, so overriding config here
     * is read by `StartSession` for this request only. Only requests whose Host header
     * matches `FIZAHUB_DOMAIN` are affected; every other request (including all of
     * mlhub.vn) reads the unmodified config and keeps its current session behaviour.
     */
    private function configurePartnerReportingSessionIsolation(): void
    {
        if (! $this->app->bound('request')) {
            return;
        }

        $domain = strtolower(trim((string) config('modules.apipartnerfizahub.partner_reporting_domain', '')));

        if ($domain === '') {
            return;
        }

        /** @var Request $request */
        $request = $this->app->make('request');

        if (strtolower((string) $request->getHost()) !== $domain) {
            return;
        }

        // Host-only cookie (no `domain` attribute) scoped to FIZAHUB_DOMAIN only, and a
        // distinct cookie name so it can never collide with the mlhub.vn `mlhub_session`
        // cookie even if a browser somehow held both at once.
        config([
            'session.domain' => null,
            'session.cookie' => 'fizahub_partner_session',
        ]);
    }

    private function registerSidebar(): void
    {
        if (! function_exists('register_sidebar_section') || ! function_exists('register_sidebar_item')) {
            return;
        }

        register_sidebar_section('integrations', 'Integrations', 60);

        register_sidebar_item('integrations', [
            'label' => 'FizaHUB onboarding',
            'route_name' => 'admin-fizahub.onboarding',
            'active_when' => ['admin-fizahub.*'],
            'icon' => 'fa-light fa-handshake',
            'order' => 10,
        ]);
    }
}
