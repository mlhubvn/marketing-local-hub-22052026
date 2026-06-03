<?php

namespace Modules\AppBusinessProfiles\Providers;

use Illuminate\Support\ServiceProvider;

class AppBusinessProfilesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.appbusinessprofiles');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appbusinessprofiles');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        register_plan_permission([
            'key' => 'localboost',
            'label' => __('LocalBoost AI'),
            'type' => 'config',
            'order' => 60,
            'fields' => [
                ['key' => 'max_businesses', 'label' => __('Businesses limit'), 'type' => 'number', 'default' => 3, 'description' => __('Maximum local business profiles. Enter -1 for unlimited.')],
                ['key' => 'max_campaigns', 'label' => __('Campaigns limit'), 'type' => 'number', 'default' => 10, 'description' => __('Maximum LocalBoost campaign pages and growth tool campaigns. Enter -1 for unlimited.')],
                ['key' => 'max_landing_pages', 'label' => __('Landing pages limit'), 'type' => 'number', 'default' => 10, 'description' => __('Maximum public campaign landing pages. Enter -1 for unlimited.')],
                ['key' => 'max_qr_codes', 'label' => __('QR codes limit'), 'type' => 'number', 'default' => 25, 'description' => __('Maximum QR campaign links. Enter -1 for unlimited.')],
                ['key' => 'max_templates', 'label' => __('Templates limit'), 'type' => 'number', 'default' => 10, 'description' => __('Maximum custom marketing templates. System templates do not count. Enter -1 for unlimited.')],
                ['key' => 'remove_branding', 'label' => __('Remove branding'), 'type' => 'boolean', 'default' => false],
            ],
        ]);

        register_user_sidebar_section('local-businesses', 'Enterprise', 150);
        register_user_sidebar_section('growth-tools', __('Growth Tools'), 200);
        register_user_sidebar_section('ai-tools', __('AI Tools'), 300);
        register_user_sidebar_section('marketing-assets', __('Assets'), 400);
        register_user_sidebar_section('analytics', __('Reports'), 450);
        register_user_sidebar_section('team-billing', __('Account'), 600);

        register_user_sidebar_item('local-businesses', [
            'label' => 'Businesses',
            'route_name' => 'portal.businesses',
            'active_when' => ['portal.businesses'],
            'icon' => 'fa-light fa-store',
            'order' => 10,
            'visible' => fn (): bool => auth()->user()?->canUsePlanFeature('localboost') ?? true,
        ]);

        $this->app->booted(function (): void {
            \Pricing::add([
                [
                    'sort' => 110,
                    'key' => 'localboost',
                    'label' => __('LocalBoost AI'),
                    'check' => true,
                    'type' => 'boolean',
                    'raw' => 0,
                ],
                [
                    'sort' => 111,
                    'key' => 'max_businesses',
                    'label' => __('Businesses'),
                    'check' => true,
                    'type' => 'number',
                    'raw' => 0,
                ],
                [
                    'sort' => 112,
                    'key' => 'max_campaigns',
                    'label' => __('Campaigns'),
                    'check' => true,
                    'type' => 'number',
                    'raw' => 0,
                ],
                [
                    'sort' => 113,
                    'key' => 'max_landing_pages',
                    'label' => __('Landing Pages'),
                    'check' => true,
                    'type' => 'number',
                    'raw' => 0,
                ],
                [
                    'sort' => 114,
                    'key' => 'max_qr_codes',
                    'label' => __('QR Codes'),
                    'check' => true,
                    'type' => 'number',
                    'raw' => 0,
                ],
                [
                    'sort' => 115,
                    'key' => 'max_templates',
                    'label' => __('Templates'),
                    'check' => true,
                    'type' => 'number',
                    'raw' => 0,
                ],
                [
                    'sort' => 116,
                    'key' => 'remove_branding',
                    'label' => __('Remove Branding'),
                    'check' => true,
                    'type' => 'boolean',
                    'raw' => 0,
                ],
            ]);

            \Pricing::addSubFeatures([
                ['sort' => 110, 'parent' => 'features', 'tab_id' => 'localboost', 'tab_name' => __('LocalBoost AI'), 'key' => 'localboost', 'label' => __('LocalBoost AI'), 'check' => true, 'type' => 'boolean', 'raw' => 0],
                ['sort' => 111, 'parent' => 'features', 'tab_id' => 'localboost', 'tab_name' => __('LocalBoost AI'), 'key' => 'max_businesses', 'label' => __('Businesses'), 'check' => true, 'type' => 'number', 'raw' => 0],
                ['sort' => 112, 'parent' => 'features', 'tab_id' => 'localboost', 'tab_name' => __('LocalBoost AI'), 'key' => 'max_campaigns', 'label' => __('Campaigns'), 'check' => true, 'type' => 'number', 'raw' => 0],
                ['sort' => 113, 'parent' => 'features', 'tab_id' => 'localboost', 'tab_name' => __('LocalBoost AI'), 'key' => 'max_landing_pages', 'label' => __('Landing Pages'), 'check' => true, 'type' => 'number', 'raw' => 0],
                ['sort' => 114, 'parent' => 'features', 'tab_id' => 'localboost', 'tab_name' => __('LocalBoost AI'), 'key' => 'max_qr_codes', 'label' => __('QR Codes'), 'check' => true, 'type' => 'number', 'raw' => 0],
                ['sort' => 115, 'parent' => 'features', 'tab_id' => 'localboost', 'tab_name' => __('LocalBoost AI'), 'key' => 'max_templates', 'label' => __('Templates'), 'check' => true, 'type' => 'number', 'raw' => 0],
                ['sort' => 116, 'parent' => 'features', 'tab_id' => 'localboost', 'tab_name' => __('LocalBoost AI'), 'key' => 'remove_branding', 'label' => __('Remove Branding'), 'check' => true, 'type' => 'boolean', 'raw' => 0],
            ]);
        });

    }
}
