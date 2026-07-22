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
use Modules\APIPartnerFizaHUB\Http\Middleware\HandlePartnerRequest;
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
