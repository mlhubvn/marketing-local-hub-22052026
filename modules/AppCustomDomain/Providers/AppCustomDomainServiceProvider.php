<?php

namespace Modules\AppCustomDomain\Providers;

use Illuminate\Support\ServiceProvider;

class AppCustomDomainServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appcustomdomain');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        register_plan_permission([
            'key' => 'qr_custom_domains',
            'label' => __('Custom Domains'),
            'type' => 'config',
            'toggleable' => true,
            'order' => 147,
            'default' => false,
            'fields' => [
                [
                    'key' => 'max_custom_domains',
                    'label' => __('Custom domains'),
                    'type' => 'number',
                    'default' => 0,
                    'description' => __('Maximum verified branded domains. Use -1 for unlimited.'),
                ],
            ],
        ]);

        if (class_exists('Modules\\AppBrandKit\\Support\\BrandSidebarIntegrator')) {
            \Modules\AppBrandKit\Support\BrandSidebarIntegrator::registerSharedItem([
                'label' => 'Custom Domains',
                'route_name' => 'portal.brand.domains',
                'active_when' => ['portal.brand.domains', 'portal.qr-codes.domains'],
                'icon' => 'fa-light fa-globe-pointer',
                'order' => 35,
                'visible' => fn () => ! auth()->user()?->plan || (auth()->user()?->canUsePlanFeature('qr_custom_domains') ?? false),
            ], 70);
        } else {
            register_user_sidebar_item('marketing-assets', [
                'label' => 'Custom Domains',
                'route_name' => 'portal.brand.domains',
                'icon' => 'fa-light fa-globe-pointer',
                'active_when' => ['portal.brand.domains', 'portal.qr-codes.domains'],
                'order' => 35,
                'visible' => fn () => ! auth()->user()?->plan || (auth()->user()?->canUsePlanFeature('qr_custom_domains') ?? false),
            ]);
        }

        $this->app->booted(function (): void {
            if (class_exists('Modules\\AppBrandKit\\Support\\BrandSidebarIntegrator')) {
                \Modules\AppBrandKit\Support\BrandSidebarIntegrator::sync();
            }

            \Pricing::addSubFeatures([
                'sort' => 246,
                'parent' => 'features',
                'tab_id' => 'marketing',
                'tab_name' => __('Marketing'),
                'key' => 'qr_custom_domains',
                'label' => __('Custom Domains'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
            ]);

            \Pricing::addSubFeatures([
                'sort' => 247,
                'parent' => 'features',
                'tab_id' => 'marketing',
                'tab_name' => __('Marketing'),
                'key' => 'max_custom_domains',
                'label' => __('Custom domain limit'),
                'check' => true,
                'type' => 'number',
                'raw' => 0,
            ]);

            \Pricing::add([
                'sort' => 656,
                'key' => 'qr_custom_domains',
                'label' => __('Custom Domains'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
                'subfeatures' => [
                    [
                        'key' => 'max_custom_domains',
                        'label' => __('Custom domain limit'),
                        'check' => true,
                        'type' => 'number',
                        'raw' => 0,
                    ],
                ],
            ]);
        });
    }
}
