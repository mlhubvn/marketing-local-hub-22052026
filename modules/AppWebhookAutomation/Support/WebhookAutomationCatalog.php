<?php

namespace Modules\AppWebhookAutomation\Support;

class WebhookAutomationCatalog
{
    public static function triggers(): array
    {
        return [
            'lead.submitted' => __('Lead submitted'),
            'booking.submitted' => __('Booking submitted'),
            'booking.confirmed' => __('Booking confirmed'),
            'booking.completed' => __('Booking completed'),
            'booking.cancelled' => __('Booking cancelled'),
            'coupon.claimed' => __('Coupon claimed'),
            'coupon.used' => __('Coupon used'),
            'feedback.submitted' => __('Feedback submitted'),
            'feedback.low_score' => __('Low-score feedback submitted'),
            'review.positive' => __('Review rating submitted'),
            'review.low_score' => __('Low-score feedback submitted'),
            'customer.created' => __('Customer created'),
        ];
    }

    public static function conditionFields(): array
    {
        return [
            '' => __('No extra condition'),
            'business_id' => __('Business'),
            'campaign_type' => __('Campaign type'),
            'booking.status' => __('Booking status'),
            'coupon.status' => __('Coupon status'),
            'lead.status' => __('Lead status'),
            'review.rating' => __('Rating'),
            'customer.email' => __('Customer email'),
            'customer.phone' => __('Customer phone'),
        ];
    }

    public static function conditionValues(): array
    {
        return [
            'campaign_type' => ['lead' => __('Lead'), 'booking' => __('Booking'), 'coupon' => __('Coupon'), 'feedback' => __('Feedback'), 'review' => __('Review')],
            'booking.status' => ['pending' => __('Pending'), 'confirmed' => __('Confirmed'), 'cancelled' => __('Cancelled'), 'completed' => __('Completed')],
            'coupon.status' => ['claimed' => __('Claimed'), 'used' => __('Used'), 'expired' => __('Expired')],
            'lead.status' => ['new' => __('New'), 'contacted' => __('Contacted'), 'converted' => __('Converted'), 'lost' => __('Lost')],
            'review.rating' => ['1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5'],
        ];
    }
}
