<?php

namespace Modules\AppWebhookAutomation\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Modules\AppWebhookAutomation\Support\WebhookAutomationService;

class AppWebhookAutomationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appwebhookautomation');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        register_user_sidebar_section('automation', __('Automation'), 550);
        register_user_sidebar_item('automation', [
            'label' => 'Webhook Automations',
            'route_name' => 'portal.webhook-automations',
            'active_when' => ['portal.webhook-automations'],
            'icon' => 'fa-light fa-webhook',
            'order' => 70,
            'visible' => fn (): bool => auth()->user()?->canUsePlanFeature('webhook_automation') ?? true,
        ]);
        register_user_sidebar_item('automation', [
            'label' => 'Webhook Logs',
            'route_name' => 'portal.webhook-logs',
            'active_when' => ['portal.webhook-logs'],
            'icon' => 'fa-light fa-list-timeline',
            'order' => 80,
            'visible' => fn (): bool => auth()->user()?->canUsePlanFeature('webhook_automation') ?? true,
        ]);

        $this->registerPlanPricing();

        $this->app->booted(fn () => $this->registerModelTriggers());
    }

    protected function registerPlanPricing(): void
    {
        register_plan_permission([
            'key' => 'webhook_automation',
            'label' => __('Webhook & Zapier Automation'),
            'type' => 'config',
            'toggleable' => true,
            'order' => 150,
            'default' => false,
            'fields' => [
                ['key' => 'max_webhook_automations', 'label' => __('Webhook automation rules'), 'type' => 'number', 'default' => 0],
                ['key' => 'webhooks_per_month', 'label' => __('Webhook sends per month'), 'type' => 'number', 'default' => 0],
                ['key' => 'webhook_custom_headers', 'label' => __('Custom headers'), 'type' => 'boolean', 'default' => false],
                ['key' => 'webhook_retry', 'label' => __('Retry on failure'), 'type' => 'boolean', 'default' => false],
            ],
        ]);

        $this->app->booted(function (): void {
            \Pricing::addSubFeatures(['sort' => 252, 'parent' => 'features', 'tab_id' => 'marketing', 'tab_name' => __('Marketing'), 'key' => 'webhook_automation', 'label' => __('Webhook & Zapier Automation'), 'check' => true, 'type' => 'boolean', 'raw' => 0]);
            \Pricing::addSubFeatures(['sort' => 253, 'parent' => 'features', 'tab_id' => 'marketing', 'tab_name' => __('Marketing'), 'key' => 'webhooks_per_month', 'label' => __('Webhook sends per month'), 'check' => true, 'type' => 'number', 'raw' => 0]);
            \Pricing::add([
                'sort' => 659,
                'key' => 'webhook_automation',
                'label' => __('Webhook & Zapier Automation Addon'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
                'subfeatures' => [
                    ['key' => 'max_webhook_automations', 'label' => __('Webhook automation rules'), 'check' => true, 'type' => 'number', 'raw' => 0],
                    ['key' => 'webhooks_per_month', 'label' => __('Webhook sends per month'), 'check' => true, 'type' => 'number', 'raw' => 0],
                    ['key' => 'webhook_custom_headers', 'label' => __('Custom headers'), 'check' => true, 'type' => 'boolean', 'raw' => 0],
                    ['key' => 'webhook_retry', 'label' => __('Retry on failure'), 'check' => true, 'type' => 'boolean', 'raw' => 0],
                ],
            ]);
        });
    }

    protected function registerModelTriggers(): void
    {
        $map = [
            'Modules\\AppLeadForms\\Models\\LeadSubmission' => ['created' => 'lead.submitted'],
            'Modules\\AppCustomers\\Models\\Customer' => ['created' => 'customer.created'],
        ];

        foreach ($map as $class => $events) {
            if (class_exists($class) && is_subclass_of($class, Model::class)) {
                $class::created(fn (Model $model) => app(WebhookAutomationService::class)->handle($events['created'], $model));
            }
        }

        if (class_exists('Modules\\AppCouponCampaigns\\Models\\CouponRedemption')) {
            $coupon = 'Modules\\AppCouponCampaigns\\Models\\CouponRedemption';
            $coupon::created(fn (Model $model) => app(WebhookAutomationService::class)->handle('coupon.claimed', $model));
            $coupon::updated(function (Model $model): void {
                if ($model->wasChanged('status') && (string) $model->status === 'used') {
                    app(WebhookAutomationService::class)->handle('coupon.used', $model);
                }
            });
        }

        if (class_exists('Modules\\AppFeedbackForms\\Models\\FeedbackResponse')) {
            $feedback = 'Modules\\AppFeedbackForms\\Models\\FeedbackResponse';
            $feedback::created(function (Model $model): void {
                app(WebhookAutomationService::class)->handle('feedback.submitted', $model);

                if ((int) ($model->rating ?? 0) > 0 && (int) $model->rating <= 3) {
                    app(WebhookAutomationService::class)->handle('feedback.low_score', $model);
                }
            });
        }

        if (class_exists('Modules\\AppBookingPages\\Models\\Booking')) {
            $booking = 'Modules\\AppBookingPages\\Models\\Booking';
            $booking::created(fn (Model $model) => app(WebhookAutomationService::class)->handle('booking.submitted', $model));
            $booking::updated(function (Model $model): void {
                if (! $model->wasChanged('status')) {
                    return;
                }

                $event = match ((string) $model->status) {
                    'confirmed' => 'booking.confirmed',
                    'cancelled' => 'booking.cancelled',
                    'completed' => 'booking.completed',
                    default => null,
                };

                if ($event) {
                    app(WebhookAutomationService::class)->handle($event, $model);
                }
            });
        }

        if (class_exists('Modules\\AppReviewBooster\\Models\\ReviewFeedback')) {
            $review = 'Modules\\AppReviewBooster\\Models\\ReviewFeedback';
            $review::created(fn (Model $model) => app(WebhookAutomationService::class)->handle(((int) $model->rating) >= 4 ? 'review.positive' : 'review.low_score', $model));
        }
    }
}
