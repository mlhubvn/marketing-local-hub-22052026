<?php

namespace Modules\AppAdvancedCustomerCrm\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Modules\AppAdvancedCustomerCrm\Console\CleanupCrmActivitiesCommand;
use Modules\AppAdvancedCustomerCrm\Console\CrmLifecycleCommand;
use Modules\AppAdvancedCustomerCrm\Console\ProcessCrmAutomationsCommand;
use Modules\AppAdvancedCustomerCrm\Console\SeedAdvancedCrmDemoCommand;
use Modules\AppAdvancedCustomerCrm\Models\CustomerActivity;
use Modules\AppAdvancedCustomerCrm\Models\CustomerNote;
use Modules\AppAdvancedCustomerCrm\Models\CustomerTag;
use Modules\AppAdvancedCustomerCrm\Models\CustomerTask;
use Modules\AppAdvancedCustomerCrm\Support\CrmAutomationService;
use Modules\AppAdvancedCustomerCrm\Support\CrmCustomerResolver;
use Modules\AppAdvancedCustomerCrm\Support\CustomerActivityService;
use Modules\AppAdvancedCustomerCrm\Support\CustomerScoreService;
use Modules\AppCustomers\Models\Customer;
use Modules\AppQRCampaigns\Models\QrCampaign;

class AppAdvancedCustomerCrmServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.appadvancedcustomercrm');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appadvancedcustomercrm');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->registerCustomerExtensions();
        $this->registerExternalModuleHooks();
        if ($this->app->runningInConsole()) {
            $this->commands([
                CleanupCrmActivitiesCommand::class,
                CrmLifecycleCommand::class,
                ProcessCrmAutomationsCommand::class,
                SeedAdvancedCrmDemoCommand::class,
            ]);
        }

        register_plan_permission([
            'key' => 'advanced_crm',
            'label' => __('Advanced Customer CRM'),
            'type' => 'config',
            'toggleable' => true,
            'order' => 170,
            'default' => false,
            'fields' => [
                ['key' => 'customer_tags', 'label' => __('Customer tags'), 'type' => 'number', 'default' => 0, 'description' => __('Maximum CRM tags. Enter -1 for unlimited.')],
                ['key' => 'customer_segments', 'label' => __('Customer segments'), 'type' => 'number', 'default' => 0, 'description' => __('Maximum saved customer segments. Enter -1 for unlimited.')],
                ['key' => 'customer_tasks', 'label' => __('Customer tasks'), 'type' => 'number', 'default' => 0, 'description' => __('Maximum CRM follow-up tasks. Enter -1 for unlimited.')],
                ['key' => 'crm_automations', 'label' => __('CRM automations'), 'type' => 'number', 'default' => 0, 'description' => __('Maximum CRM automation rules. Enter -1 for unlimited.')],
                ['key' => 'crm_activity_retention_days', 'label' => __('Activity retention days'), 'type' => 'number', 'default' => 365],
            ],
        ]);

        register_user_sidebar_section('crm', __('CRM'), 225);

        register_user_sidebar_item('crm', [
            'label' => 'CRM Customers',
            'route_name' => 'portal.crm.customers',
            'active_when' => ['portal.crm.customers', 'portal.crm.customers.show'],
            'icon' => 'fa-light fa-users-viewfinder',
            'order' => 38,
            'visible' => fn (): bool => (bool) auth()->user()?->canUsePlanFeature('advanced_crm'),
        ]);

        register_user_sidebar_item('crm', [
            'label' => 'CRM Segments',
            'route_name' => 'portal.crm.segments',
            'active_when' => ['portal.crm.segments'],
            'icon' => 'fa-light fa-chart-pie-simple',
            'order' => 39,
            'visible' => fn (): bool => (bool) auth()->user()?->canUsePlanFeature('advanced_crm'),
        ]);

        register_user_sidebar_item('crm', [
            'label' => 'CRM Tags',
            'route_name' => 'portal.crm.tags',
            'active_when' => ['portal.crm.tags'],
            'icon' => 'fa-light fa-tags',
            'order' => 40,
            'visible' => fn (): bool => (bool) auth()->user()?->canUsePlanFeature('advanced_crm'),
        ]);

        register_user_sidebar_item('crm', [
            'label' => 'CRM Tasks',
            'route_name' => 'portal.crm.tasks',
            'active_when' => ['portal.crm.tasks'],
            'icon' => 'fa-light fa-list-check',
            'order' => 41,
            'visible' => fn (): bool => (bool) auth()->user()?->canUsePlanFeature('advanced_crm'),
        ]);

        register_user_sidebar_item('crm', [
            'label' => 'CRM Automations',
            'route_name' => 'portal.crm.automations',
            'active_when' => ['portal.crm.automations'],
            'icon' => 'fa-light fa-wand-magic-sparkles',
            'order' => 42,
            'visible' => fn (): bool => (bool) auth()->user()?->canUsePlanFeature('advanced_crm'),
        ]);

        register_user_sidebar_item('crm', [
            'label' => 'CRM Reports',
            'route_name' => 'portal.crm.reports',
            'active_when' => ['portal.crm.reports'],
            'icon' => 'fa-light fa-chart-line',
            'order' => 43,
            'visible' => fn (): bool => (bool) auth()->user()?->canUsePlanFeature('advanced_crm'),
        ]);

        $this->app->booted(function (): void {
            \Pricing::addSubFeatures([
                'sort' => 170,
                'parent' => 'features',
                'tab_id' => 'mlhub',
                'tab_name' => __('MLHUB AI'),
                'key' => 'advanced_crm',
                'label' => __('Advanced Customer CRM'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
            ]);

            \Pricing::add([
                'sort' => 670,
                'key' => 'advanced_crm',
                'label' => __('Advanced Customer CRM'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
                'subfeatures' => [
                    ['key' => 'customer_tags', 'label' => __('Customer tags'), 'check' => true, 'type' => 'number', 'raw' => 0],
                    ['key' => 'customer_segments', 'label' => __('Customer segments'), 'check' => true, 'type' => 'number', 'raw' => 0],
                    ['key' => 'customer_tasks', 'label' => __('Customer tasks'), 'check' => true, 'type' => 'number', 'raw' => 0],
                    ['key' => 'crm_automations', 'label' => __('CRM automations'), 'check' => true, 'type' => 'number', 'raw' => 0],
                    ['key' => 'crm_activity_retention_days', 'label' => __('Activity retention days'), 'check' => true, 'type' => 'number', 'raw' => 365],
                ],
            ]);
        });
    }

    protected function registerCustomerExtensions(): void
    {
        Customer::resolveRelationUsing('activities', fn (Customer $customer) => $customer->hasMany(CustomerActivity::class, 'customer_id'));
        Customer::resolveRelationUsing('notes', fn (Customer $customer) => $customer->hasMany(CustomerNote::class, 'customer_id'));
        Customer::resolveRelationUsing('tasks', fn (Customer $customer) => $customer->hasMany(CustomerTask::class, 'customer_id'));
        Customer::resolveRelationUsing('crmTags', fn (Customer $customer) => $customer
            ->belongsToMany(CustomerTag::class, 'lb_customer_tag_maps', 'customer_id', 'tag_id')
            ->withPivot(['owner_user_id', 'created_by', 'created_at']));

        Customer::created(function (Customer $customer): void {
            app(CustomerActivityService::class)->record($customer, 'customer_created', __('Customer created'), [
                'source_module' => 'AppCustomers',
            ]);

            app(CrmAutomationService::class)->handle('customer_created', $customer->refresh());
        });
    }

    protected function registerExternalModuleHooks(): void
    {
        $this->observeCreated('Modules\\AppBookingPages\\Models\\Booking', function ($booking): void {
            $this->recordModuleEvent($booking, 'booking_submitted', __('Booking submitted'), 'AppBookingPages', 'total_bookings', 3, [
                'booking.status' => (string) ($booking->status ?? 'pending'),
            ]);
        });

        $this->observeUpdated('Modules\\AppBookingPages\\Models\\Booking', function ($booking): void {
            if (! $booking->wasChanged('status')) {
                return;
            }

            $status = (string) $booking->status;
            if (in_array($status, ['confirmed', 'completed', 'cancelled'], true)) {
                $event = 'booking_'.$status;
                $points = $status === 'completed' ? 7 : ($status === 'cancelled' ? -5 : 2);
                $this->recordModuleEvent($booking, $event, __(str($event)->replace('_', ' ')->headline()->toString()), 'AppBookingPages', null, $points, [
                    'booking.status' => $status,
                ]);
            }
        });

        $this->observeCreated('Modules\\AppCouponCampaigns\\Models\\CouponRedemption', function ($claim): void {
            $this->recordModuleEvent($claim, 'coupon_claimed', __('Coupon claimed'), 'AppCouponCampaigns', 'total_coupon_claims', 3, [
                'coupon.status' => (string) ($claim->status ?? 'claimed'),
                'coupon_code' => (string) ($claim->code ?? ''),
            ]);
        });

        $this->observeUpdated('Modules\\AppCouponCampaigns\\Models\\CouponRedemption', function ($claim): void {
            if ($claim->wasChanged('status') && (string) $claim->status === 'used') {
                $this->recordModuleEvent($claim, 'coupon_used', __('Coupon used'), 'AppCouponCampaigns', 'total_coupon_used', 5, [
                    'coupon.status' => 'used',
                    'coupon_code' => (string) ($claim->code ?? ''),
                ]);
            }
        });

        $this->observeCreated('Modules\\AppFeedbackForms\\Models\\FeedbackResponse', function ($feedback): void {
            $rating = (int) ($feedback->rating ?? 0);
            $event = $rating > 0 && $rating <= 3 ? 'low_score_feedback_submitted' : 'feedback_submitted';
            $this->recordModuleEvent($feedback, $event, $rating <= 3 && $rating > 0 ? __('Low-score feedback submitted') : __('Feedback submitted'), 'AppFeedbackForms', 'total_feedback', $rating <= 3 && $rating > 0 ? -10 : 5, [
                'review.rating' => $rating,
                'feedback_message' => (string) ($feedback->message ?? ''),
            ]);
        });

        $this->observeCreated('Modules\\AppReviewBooster\\Models\\ReviewFeedback', function ($review): void {
            $rating = (int) ($review->rating ?? 0);
            $event = $rating > 0 && $rating <= 3 ? 'low_score_feedback_submitted' : 'review_rating_submitted';
            $this->recordModuleEvent($review, $event, $rating <= 3 && $rating > 0 ? __('Low-score review submitted') : __('Review rating submitted'), 'AppReviewBooster', 'total_reviews', $rating >= 5 ? 8 : ($rating <= 3 && $rating > 0 ? -10 : 3), [
                'review.rating' => $rating,
                'feedback_message' => (string) ($review->message ?? ''),
            ]);
        });

        $this->observeCreated('Modules\\AppGoogleBusiness\\Models\\GoogleReview', function ($review): void {
            $rating = (int) ($review->rating ?? 0);
            $this->recordModuleEvent($review, 'google_review_synced', __('Google review synced'), 'AppGoogleBusiness', 'total_reviews', $rating >= 5 ? 8 : ($rating <= 3 && $rating > 0 ? -10 : 3), [
                'review.rating' => $rating,
                'feedback_message' => (string) ($review->comment ?? ''),
            ]);
        });
    }

    protected function observeCreated(string $class, callable $callback): void
    {
        if (class_exists($class)) {
            $class::created($callback);
        }
    }

    protected function observeUpdated(string $class, callable $callback): void
    {
        if (class_exists($class)) {
            $class::updated($callback);
        }
    }

    protected function recordModuleEvent(object $related, string $event, string $title, string $sourceModule, ?string $counter = null, int $score = 0, array $payload = []): void
    {
        $campaign = null;
        if (isset($related->campaign_id) && class_exists('Modules\\AppQRCampaigns\\Models\\QrCampaign')) {
            $campaign = QrCampaign::query()->with('business')->find($related->campaign_id);
        }

        $business = method_exists($related, 'business') ? $related->business()->first() : ($campaign?->business);
        $customer = app(CrmCustomerResolver::class)->fromRelated($related, $campaign, $business, $sourceModule);

        if (! $customer) {
            return;
        }

        $payload = array_merge([
            'business_id' => $customer->business_id,
            'campaign_id' => $campaign?->id,
            'campaign_type' => (string) ($campaign?->type ?? ''),
            'related_type' => $related::class,
            'related_id' => method_exists($related, 'getKey') ? $related->getKey() : null,
        ], $payload);

        if ($counter && Schema::hasColumn('lb_customers', $counter)) {
            $customer->increment($counter);
            $customer->refresh();
        }

        app(CustomerActivityService::class)->record($customer, $event, $title, [
            'source_module' => $sourceModule,
            'related_type' => $related::class,
            'related_id' => method_exists($related, 'getKey') ? $related->getKey() : null,
            'metadata' => $payload,
        ]);

        if ($score !== 0) {
            app(CustomerScoreService::class)->add($customer->refresh(), $score, $title, [
                'related_type' => $related::class,
                'related_id' => method_exists($related, 'getKey') ? $related->getKey() : null,
            ]);
        }

        app(CrmAutomationService::class)->handle($event, $customer->refresh(), $payload);
    }
}
