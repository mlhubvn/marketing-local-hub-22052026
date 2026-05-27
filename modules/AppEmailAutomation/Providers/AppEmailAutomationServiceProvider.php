<?php

namespace Modules\AppEmailAutomation\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Modules\AppEmailAutomation\Support\EmailAutomationService;

class AppEmailAutomationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appemailautomation');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        register_user_sidebar_section('automation', __('Automation'), 550);
        register_user_sidebar_item('automation', [
            'label' => 'Email Automations',
            'route_name' => 'portal.email-automations',
            'active_when' => ['portal.email-automations'],
            'icon' => 'fa-light fa-bolt',
            'order' => 10,
            'visible' => fn (): bool => auth()->user()?->canUsePlanFeature('email_automation') ?? true,
        ]);
        register_user_sidebar_item('automation', [
            'label' => 'Email Templates',
            'route_name' => 'portal.email-templates',
            'active_when' => ['portal.email-templates'],
            'icon' => 'fa-light fa-envelope-open-text',
            'order' => 20,
            'visible' => fn (): bool => auth()->user()?->canUsePlanFeature('email_automation') ?? true,
        ]);
        register_user_sidebar_item('automation', [
            'label' => 'Email Logs',
            'route_name' => 'portal.email-logs',
            'active_when' => ['portal.email-logs'],
            'icon' => 'fa-light fa-list-check',
            'order' => 30,
            'visible' => fn (): bool => auth()->user()?->canUsePlanFeature('email_automation') ?? true,
        ]);

        $this->registerPlanPricing();

        $this->app->booted(fn () => $this->registerModelTriggers());
    }

    protected function registerPlanPricing(): void
    {
        register_plan_permission([
            'key' => 'email_automation',
            'label' => __('Email Automation'),
            'type' => 'config',
            'toggleable' => true,
            'order' => 148,
            'default' => false,
            'fields' => [
                ['key' => 'max_email_automations', 'label' => __('Email automations'), 'type' => 'number', 'default' => 0],
                ['key' => 'max_email_templates', 'label' => __('Email templates'), 'type' => 'number', 'default' => 0],
                ['key' => 'emails_per_month', 'label' => __('Emails per month'), 'type' => 'number', 'default' => 0],
                ['key' => 'automation_delay', 'label' => __('Delayed automations'), 'type' => 'boolean', 'default' => false],
                ['key' => 'automation_conditions', 'label' => __('Automation conditions'), 'type' => 'boolean', 'default' => false],
            ],
        ]);

        $this->app->booted(function (): void {
            \Pricing::addSubFeatures(['sort' => 248, 'parent' => 'features', 'tab_id' => 'marketing', 'tab_name' => __('Marketing'), 'key' => 'email_automation', 'label' => __('Email Automation'), 'check' => true, 'type' => 'boolean', 'raw' => 0]);
            \Pricing::addSubFeatures(['sort' => 249, 'parent' => 'features', 'tab_id' => 'marketing', 'tab_name' => __('Marketing'), 'key' => 'emails_per_month', 'label' => __('Emails per month'), 'check' => true, 'type' => 'number', 'raw' => 0]);
            \Pricing::add([
                'sort' => 657,
                'key' => 'email_automation',
                'label' => __('Email Automation'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
                'subfeatures' => [
                    ['key' => 'max_email_automations', 'label' => __('Email automations'), 'check' => true, 'type' => 'number', 'raw' => 0],
                    ['key' => 'max_email_templates', 'label' => __('Email templates'), 'check' => true, 'type' => 'number', 'raw' => 0],
                    ['key' => 'emails_per_month', 'label' => __('Emails per month'), 'check' => true, 'type' => 'number', 'raw' => 0],
                    ['key' => 'automation_delay', 'label' => __('Delayed automations'), 'check' => true, 'type' => 'boolean', 'raw' => 0],
                    ['key' => 'automation_conditions', 'label' => __('Automation conditions'), 'check' => true, 'type' => 'boolean', 'raw' => 0],
                ],
            ]);
        });
    }

    protected function registerModelTriggers(): void
    {
        $map = [
            'Modules\\AppLeadForms\\Models\\LeadSubmission' => ['created' => 'lead.submitted'],
            'Modules\\AppCouponCampaigns\\Models\\CouponRedemption' => ['created' => 'coupon.claimed'],
            'Modules\\AppFeedbackForms\\Models\\FeedbackResponse' => ['created' => 'feedback.submitted'],
            'Modules\\AppCustomers\\Models\\Customer' => ['created' => 'customer.created'],
        ];

        foreach ($map as $class => $events) {
            if (class_exists($class) && is_subclass_of($class, Model::class)) {
                $class::created(fn (Model $model) => app(EmailAutomationService::class)->handle($events['created'], $model));
            }
        }

        if (class_exists('Modules\\AppBookingPages\\Models\\Booking')) {
            $booking = 'Modules\\AppBookingPages\\Models\\Booking';
            $booking::created(fn (Model $model) => app(EmailAutomationService::class)->handle('booking.submitted', $model));
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
                    app(EmailAutomationService::class)->handle($event, $model);
                }
            });
        }

        if (class_exists('Modules\\AppReviewBooster\\Models\\ReviewFeedback')) {
            $review = 'Modules\\AppReviewBooster\\Models\\ReviewFeedback';
            $review::created(fn (Model $model) => app(EmailAutomationService::class)->handle(((int) $model->rating) >= 4 ? 'review.positive' : 'review.low_score', $model));
        }
    }
}
