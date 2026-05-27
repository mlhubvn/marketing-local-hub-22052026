<?php

namespace Modules\AppWhatsAppNotification\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Modules\AppWhatsAppNotification\Support\WhatsAppNotificationService;

class AppWhatsAppNotificationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appwhatsappnotification');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        register_user_sidebar_section('automation', __('Automation'), 550);
        register_user_sidebar_item('automation', [
            'label' => 'WhatsApp Notifications',
            'route_name' => 'portal.whatsapp-notifications',
            'active_when' => ['portal.whatsapp-notifications'],
            'icon' => 'fa-brands fa-whatsapp',
            'order' => 40,
            'visible' => fn (): bool => auth()->user()?->canUsePlanFeature('whatsapp_notification') ?? true,
        ]);
        register_user_sidebar_item('automation', [
            'label' => 'WhatsApp Templates',
            'route_name' => 'portal.whatsapp-templates',
            'active_when' => ['portal.whatsapp-templates'],
            'icon' => 'fa-light fa-message-lines',
            'order' => 50,
            'visible' => fn (): bool => auth()->user()?->canUsePlanFeature('whatsapp_notification') ?? true,
        ]);
        register_user_sidebar_item('automation', [
            'label' => 'WhatsApp Logs',
            'route_name' => 'portal.whatsapp-logs',
            'active_when' => ['portal.whatsapp-logs'],
            'icon' => 'fa-light fa-list-check',
            'order' => 60,
            'visible' => fn (): bool => auth()->user()?->canUsePlanFeature('whatsapp_notification') ?? true,
        ]);

        $this->registerPlanPricing();

        $this->app->booted(fn () => $this->registerModelTriggers());
    }

    protected function registerPlanPricing(): void
    {
        register_plan_permission([
            'key' => 'whatsapp_notification',
            'label' => __('WhatsApp Notification'),
            'type' => 'config',
            'toggleable' => true,
            'order' => 149,
            'default' => false,
            'fields' => [
                ['key' => 'max_whatsapp_notifications', 'label' => __('WhatsApp notification rules'), 'type' => 'number', 'default' => 0],
                ['key' => 'max_whatsapp_templates', 'label' => __('WhatsApp templates'), 'type' => 'number', 'default' => 0],
                ['key' => 'whatsapp_messages_per_month', 'label' => __('WhatsApp messages per month'), 'type' => 'number', 'default' => 0],
                ['key' => 'whatsapp_cloud_api', 'label' => __('WhatsApp Cloud API'), 'type' => 'boolean', 'default' => false],
                ['key' => 'whatsapp_template_messages', 'label' => __('Approved template messages'), 'type' => 'boolean', 'default' => false],
            ],
        ]);

        $this->app->booted(function (): void {
            \Pricing::addSubFeatures(['sort' => 250, 'parent' => 'features', 'tab_id' => 'marketing', 'tab_name' => __('Marketing'), 'key' => 'whatsapp_notification', 'label' => __('WhatsApp Notifications'), 'check' => true, 'type' => 'boolean', 'raw' => 0]);
            \Pricing::addSubFeatures(['sort' => 251, 'parent' => 'features', 'tab_id' => 'marketing', 'tab_name' => __('Marketing'), 'key' => 'whatsapp_messages_per_month', 'label' => __('WhatsApp messages per month'), 'check' => true, 'type' => 'number', 'raw' => 0]);
            \Pricing::add([
                'sort' => 658,
                'key' => 'whatsapp_notification',
                'label' => __('WhatsApp Notification Addon'),
                'check' => true,
                'type' => 'boolean',
                'raw' => 0,
                'subfeatures' => [
                    ['key' => 'max_whatsapp_notifications', 'label' => __('WhatsApp notification rules'), 'check' => true, 'type' => 'number', 'raw' => 0],
                    ['key' => 'max_whatsapp_templates', 'label' => __('WhatsApp templates'), 'check' => true, 'type' => 'number', 'raw' => 0],
                    ['key' => 'whatsapp_messages_per_month', 'label' => __('WhatsApp messages per month'), 'check' => true, 'type' => 'number', 'raw' => 0],
                    ['key' => 'whatsapp_cloud_api', 'label' => __('WhatsApp Cloud API'), 'check' => true, 'type' => 'boolean', 'raw' => 0],
                    ['key' => 'whatsapp_template_messages', 'label' => __('Approved template messages'), 'check' => true, 'type' => 'boolean', 'raw' => 0],
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
                $class::created(fn (Model $model) => app(WhatsAppNotificationService::class)->handle($events['created'], $model));
            }
        }

        if (class_exists('Modules\\AppBookingPages\\Models\\Booking')) {
            $booking = 'Modules\\AppBookingPages\\Models\\Booking';
            $booking::created(fn (Model $model) => app(WhatsAppNotificationService::class)->handle('booking.submitted', $model));
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
                    app(WhatsAppNotificationService::class)->handle($event, $model);
                }
            });
        }

        if (class_exists('Modules\\AppReviewBooster\\Models\\ReviewFeedback')) {
            $review = 'Modules\\AppReviewBooster\\Models\\ReviewFeedback';
            $review::created(fn (Model $model) => app(WhatsAppNotificationService::class)->handle(((int) $model->rating) >= 4 ? 'review.positive' : 'review.low_score', $model));
        }
    }
}
