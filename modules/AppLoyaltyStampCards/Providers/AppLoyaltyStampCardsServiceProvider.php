<?php

namespace Modules\AppLoyaltyStampCards\Providers;

use Illuminate\Support\ServiceProvider;

class AppLoyaltyStampCardsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.apployaltystampcards');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'apployaltystampcards');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        register_plan_permission([
            'key' => 'loyalty_stamp_cards',
            'label' => __('Loyalty & Referral'),
            'type' => 'config',
            'toggleable' => true,
            'order' => 165,
            'default' => false,
            'fields' => [
                ['key' => 'max_loyalty_cards', 'label' => __('Loyalty cards'), 'type' => 'number', 'default' => 0, 'description' => __('Maximum active stamp card campaigns. Enter -1 for unlimited.')],
                ['key' => 'max_loyalty_customers', 'label' => __('Loyalty customers'), 'type' => 'number', 'default' => 0, 'description' => __('Maximum customers tracked across loyalty cards. Enter -1 for unlimited.')],
                ['key' => 'max_referral_campaigns', 'label' => __('Referral campaigns'), 'type' => 'number', 'default' => 0, 'description' => __('Maximum active invite friend campaigns. Enter -1 for unlimited.')],
                ['key' => 'loyalty_rewards', 'label' => __('Auto rewards'), 'type' => 'boolean', 'default' => false],
                ['key' => 'loyalty_staff_redeem', 'label' => __('Staff redemption'), 'type' => 'boolean', 'default' => false],
            ],
        ]);

        register_user_sidebar_item('growth-tools', [
            'label' => 'Loyalty & Referral',
            'route_name' => 'portal.loyalty-cards',
            'active_when' => ['portal.loyalty-cards'],
            'icon' => 'fa-light fa-stamp',
            'order' => 35,
            'visible' => fn (): bool => (bool) auth()->user()?->canUsePlanFeature('loyalty_stamp_cards'),
        ]);

        $this->app->booted(function (): void {
            \Pricing::addSubFeatures([
                'sort' => 166,
                'parent' => 'features',
                'tab_id' => 'mlhub',
                'tab_name' => __('MKT AI'),
                'key' => 'loyalty_stamp_cards',
                'label' => __('Loyalty & Referral'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
            ]);

            \Pricing::add([
                'sort' => 659,
                'key' => 'loyalty_stamp_cards',
                'label' => __('Loyalty & Referral'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
                'subfeatures' => [
                    ['key' => 'max_loyalty_cards', 'label' => __('Loyalty cards'), 'check' => true, 'type' => 'number', 'raw' => 0],
                    ['key' => 'max_loyalty_customers', 'label' => __('Loyalty customers'), 'check' => true, 'type' => 'number', 'raw' => 0],
                    ['key' => 'max_referral_campaigns', 'label' => __('Referral campaigns'), 'check' => true, 'type' => 'number', 'raw' => 0],
                    ['key' => 'loyalty_rewards', 'label' => __('Auto rewards'), 'check' => true, 'type' => 'boolean', 'raw' => 0],
                    ['key' => 'loyalty_staff_redeem', 'label' => __('Staff redemption'), 'check' => true, 'type' => 'boolean', 'raw' => 0],
                ],
            ]);
        });
    }
}
