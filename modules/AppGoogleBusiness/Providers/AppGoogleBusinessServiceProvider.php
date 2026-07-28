<?php

namespace Modules\AppGoogleBusiness\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Modules\AdminCrons\Support\SystemCronRegistry;

class AppGoogleBusinessServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.appgooglebusiness');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appgooglebusiness');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->commands([
            \Modules\AppGoogleBusiness\Console\PublishScheduledGoogleBusinessPostsCommand::class,
            \Modules\AppGoogleBusiness\Console\SyncGoogleBusinessReviewsCommand::class,
        ]);

        if ($this->app->runningInConsole()) {
            $this->app->booted(function (): void {
                $this->app->make(Schedule::class)
                    ->command('google-business:sync-reviews')
                    ->everyFifteenMinutes()
                    ->withoutOverlapping();

                $this->app->make(Schedule::class)
                    ->command('google-business:publish-scheduled-posts')
                    ->everyFiveMinutes()
                    ->withoutOverlapping();
            });
        }

        $this->registerAdminIntegrationItem();
        $this->registerCronTask();

        register_plan_permission([
            'key' => 'google_business',
            'label' => __('Google Business Profile'),
            'type' => 'config',
            'toggleable' => true,
            'order' => 149,
            'default' => false,
            'fields' => [
                ['key' => 'max_google_business_connections', 'label' => __('Google connections (Free plan only)'), 'type' => 'number', 'default' => 0, 'description' => __('Paid plans use the Businesses limit instead.')],
                ['key' => 'max_google_business_locations', 'label' => __('Google locations (Free plan only)'), 'type' => 'number', 'default' => 0, 'description' => __('Paid plans use the Businesses limit instead.')],
                ['key' => 'google_review_sync', 'label' => __('Google review sync'), 'type' => 'boolean', 'default' => false],
                ['key' => 'google_review_reply', 'label' => __('Publish review replies'), 'type' => 'boolean', 'default' => false],
                ['key' => 'google_business_insights', 'label' => __('Google insights'), 'type' => 'boolean', 'default' => false],
                ['key' => 'google_business_posts', 'label' => __('Google posts'), 'type' => 'boolean', 'default' => false],
            ],
        ]);

        register_user_sidebar_section('google-business', __('Google Business'), 500);
        $googleBusinessVisible = fn (): bool => (bool) auth()->user()?->canUsePlanFeature('google_business');
        $googleBusinessPath = trim((string) config('modules.appgooglebusiness.route_prefix', 'portal/integrations/google-business'), '/');

        foreach ([
            ['label' => 'Overview', 'tab' => 'overview', 'icon' => 'fa-light fa-grid-2', 'order' => 10],
            ['label' => 'Locations', 'tab' => 'locations', 'icon' => 'fa-light fa-location-dot', 'order' => 20],
            ['label' => 'Reviews', 'tab' => 'reviews', 'icon' => 'fa-light fa-star', 'order' => 30],
            ['label' => 'Posts', 'tab' => 'posts', 'icon' => 'fa-light fa-bullhorn', 'order' => 40],
            ['label' => 'Analytics', 'tab' => 'analytics', 'icon' => 'fa-light fa-chart-line', 'order' => 50],
            ['label' => 'Auto Reply', 'tab' => 'auto_reply', 'icon' => 'fa-light fa-wand-magic-sparkles', 'order' => 60],
        ] as $item) {
            register_user_sidebar_item('google-business', [
                'label' => $item['label'],
                'route' => url($googleBusinessPath).'?tab='.$item['tab'],
                'active' => fn (): bool => request()->routeIs('portal.google-business') && request()->query('tab', 'overview') === $item['tab'],
                'icon' => $item['icon'],
                'order' => $item['order'],
                'visible' => $googleBusinessVisible,
            ]);
        }

        $this->app->booted(function (): void {
            \Pricing::addSubFeatures([
                'sort' => 250,
                'parent' => 'features',
                'tab_id' => 'marketing',
                'tab_name' => __('Marketing'),
                'key' => 'google_business',
                'label' => __('Google Business Profile'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
            ]);

            \Pricing::add([
                'sort' => 658,
                'key' => 'google_business',
                'label' => __('Google Business Profile'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
                'subfeatures' => [
                    ['key' => 'max_google_business_connections', 'label' => __('Google connections'), 'check' => true, 'type' => 'number', 'raw' => 0],
                    ['key' => 'max_google_business_locations', 'label' => __('Google locations'), 'check' => true, 'type' => 'number', 'raw' => 0],
                    ['key' => 'google_review_sync', 'label' => __('Google review sync'), 'check' => true, 'type' => 'boolean', 'raw' => 0],
                    ['key' => 'google_review_reply', 'label' => __('Publish review replies'), 'check' => true, 'type' => 'boolean', 'raw' => 0],
                    ['key' => 'google_business_insights', 'label' => __('Google insights'), 'check' => true, 'type' => 'boolean', 'raw' => 0],
                    ['key' => 'google_business_posts', 'label' => __('Google posts'), 'check' => true, 'type' => 'boolean', 'raw' => 0],
                ],
            ]);
        });
    }

    protected function registerAdminIntegrationItem(): void
    {
        if (! function_exists('register_integration_item')) {
            return;
        }

        register_integration_item('google_business_profile', [
            'label' => 'Google Business Profile',
            'description' => 'OAuth credentials for Google Business Profile API: locations, reviews, replies, and insights.',
            'icon' => 'fa-brands fa-google',
            'color' => '#0f766e',
            'order' => 120,
            'required_fields' => ['client_id', 'client_secret'],
            'fields' => [
                [
                    'key' => 'status',
                    'label' => 'Status',
                    'type' => 'toggle',
                    'default' => '0',
                ],
                [
                    'key' => 'client_id',
                    'label' => 'Google OAuth Client ID',
                    'type' => 'text',
                    'default' => '',
                ],
                [
                    'key' => 'client_secret',
                    'label' => 'Google OAuth Client Secret',
                    'type' => 'secret',
                    'default' => '',
                ],
                [
                    'key' => 'callback_url',
                    'label' => 'Authorized redirect URI',
                    'type' => 'text',
                    'default' => url(trim((string) config('modules.appgooglebusiness.route_prefix', 'portal/integrations/google-business'), '/').'/callback'),
                    'readonly' => true,
                ],
                [
                    'key' => 'scope',
                    'label' => 'OAuth scope',
                    'type' => 'text',
                    'default' => 'https://www.googleapis.com/auth/business.manage',
                    'readonly' => true,
                ],
            ],
        ]);
    }

    protected function registerCronTask(): void
    {
        if (! class_exists(SystemCronRegistry::class)) {
            return;
        }

        $this->app->afterResolving(SystemCronRegistry::class, function (SystemCronRegistry $registry): void {
            $registry->register([
                'key' => 'google-business-sync-reviews',
                'name' => __('Google Business Review Sync'),
                'icon' => 'fa-brands fa-google',
                'description' => __('Sync reviews for managed Google Business locations and run configured auto-reply rules.'),
                'command' => 'google-business:sync-reviews',
                'recommended' => false,
                'cron_expression' => '*/15 * * * *',
            ]);

            $registry->register([
                'key' => 'google-business-publish-scheduled-posts',
                'name' => __('Google Business Scheduled Posts'),
                'icon' => 'fa-light fa-bullhorn',
                'description' => __('Publish scheduled Google Business posts when their publish time arrives.'),
                'command' => 'google-business:publish-scheduled-posts',
                'recommended' => false,
                'cron_expression' => '*/5 * * * *',
            ]);
        });
    }
}
