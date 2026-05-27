<?php

namespace Modules\AppAdvancedCustomerCrm\Support;

use Modules\AppAdvancedCustomerCrm\Models\CustomerActivity;
use Modules\AppCustomers\Models\Customer;

class CustomerActivityService
{
    public function record(Customer $customer, string $type, string $title, array $data = []): CustomerActivity
    {
        $activity = CustomerActivity::query()->create([
            'team_id' => data_get($data, 'team_id', $customer->team_id),
            'business_id' => data_get($data, 'business_id', $customer->business_id),
            'customer_id' => $customer->id,
            'type' => $type,
            'title' => $title,
            'description' => data_get($data, 'description'),
            'related_type' => data_get($data, 'related_type'),
            'related_id' => data_get($data, 'related_id'),
            'source_module' => data_get($data, 'source_module', 'advanced_crm'),
            'icon' => data_get($data, 'icon', $this->iconFor($type)),
            'color' => data_get($data, 'color', '#0f766e'),
            'metadata' => data_get($data, 'metadata', []),
            'created_by' => data_get($data, 'created_by', auth()->id()),
            'occurred_at' => data_get($data, 'occurred_at', now()),
        ]);

        $customer->forceFill([
            'last_activity_at' => $activity->occurred_at,
            'first_seen_at' => $customer->first_seen_at ?: $activity->occurred_at,
        ])->save();

        return $activity;
    }

    protected function iconFor(string $type): string
    {
        return match ($type) {
            'lead_submitted' => 'fa-light fa-inbox',
            'booking_submitted', 'booking_confirmed', 'booking_completed', 'booking_cancelled' => 'fa-light fa-calendar-check',
            'coupon_claimed', 'coupon_used' => 'fa-light fa-ticket',
            'feedback_submitted', 'low_score_feedback_submitted' => 'fa-light fa-message-lines',
            'review_rating_submitted', 'google_review_synced' => 'fa-light fa-star',
            'email_sent' => 'fa-light fa-envelope',
            'whatsapp_sent' => 'fa-brands fa-whatsapp',
            'webhook_sent' => 'fa-light fa-webhook',
            'loyalty_stamp_added', 'reward_unlocked' => 'fa-light fa-stamp',
            'referral_converted' => 'fa-light fa-share-nodes',
            'note_created', 'note_updated', 'note_deleted', 'note_pinned', 'note_unpinned' => 'fa-light fa-note-sticky',
            'task_created', 'task_updated', 'task_completed', 'task_rescheduled', 'task_deleted' => 'fa-light fa-list-check',
            'tag_added', 'tag_removed' => 'fa-light fa-tag',
            'status_changed' => 'fa-light fa-toggle-on',
            'score_changed' => 'fa-light fa-gauge-high',
            default => 'fa-light fa-timeline',
        };
    }
}
