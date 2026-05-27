<?php

namespace Modules\AppWhatsAppNotification\Support;

class WhatsAppNotificationCatalog
{
    public static function triggers(): array
    {
        return [
            'booking.submitted' => __('Booking submitted'),
            'booking.confirmed' => __('Booking confirmed'),
            'booking.cancelled' => __('Booking cancelled'),
            'booking.completed' => __('Booking completed'),
            'lead.submitted' => __('Lead submitted'),
            'coupon.claimed' => __('Coupon claimed'),
            'coupon.used' => __('Coupon used'),
            'feedback.submitted' => __('Feedback submitted'),
            'review.positive' => __('Positive review submitted'),
            'review.low_score' => __('Low-score review submitted'),
            'customer.created' => __('Customer created'),
            'loyalty.stamp_added' => __('Stamp submitted'),
        ];
    }

    public static function templateSeeds(): array
    {
        return [
            ['name' => 'Booking Request Received', 'type' => 'booking', 'body' => "Hi {customer_name}, your booking request for {booking_service} with {business_name} on {booking_date} at {booking_time} has been received."],
            ['name' => 'New Booking Alert', 'type' => 'booking', 'body' => "New booking from {customer_name}. Service: {booking_service}. Date: {booking_date} {booking_time}. Phone: {customer_phone}."],
            ['name' => 'Booking Reminder', 'type' => 'booking', 'body' => "Hi {customer_name}, reminder for your appointment with {business_name} on {booking_date} at {booking_time}."],
            ['name' => 'Booking Completed Review Request', 'type' => 'review', 'body' => "Hi {customer_name}, thanks for visiting {business_name}. Please share your feedback here: {public_page_url}"],
            ['name' => 'Coupon Claimed', 'type' => 'coupon', 'body' => "Hi {customer_name}, here is your coupon code: {coupon_code}. Show this code at checkout before {coupon_expiry}."],
            ['name' => 'Lead Owner Alert', 'type' => 'lead', 'body' => "New lead from {customer_name}. Phone: {customer_phone}. Message: {feedback_message}"],
            ['name' => 'Lead Thank You', 'type' => 'lead', 'body' => "Hi {customer_name}, thanks for contacting {business_name}. Our team will follow up soon."],
            ['name' => 'Low-score Feedback Alert', 'type' => 'feedback', 'body' => "New low-score feedback received from {customer_name}. Rating: {review_rating}. Message: {feedback_message}"],
            ['name' => 'Feedback Thank You', 'type' => 'feedback', 'body' => "Hi {customer_name}, thank you for sharing your feedback with {business_name}."],
            ['name' => 'Stamp Submitted Thank You', 'type' => 'loyalty', 'body' => "Hi {customer_name}, your stamp was added to {stamp_card_name}. Progress: {stamp_count}/{required_stamps}. Reward: {reward_title}."],
        ];
    }

    public static function variables(): array
    {
        return [
            '{business_name}', '{business_phone}', '{business_email}', '{business_website}', '{business_address}',
            '{customer_name}', '{customer_email}', '{customer_phone}',
            '{campaign_name}', '{campaign_type}', '{public_page_url}',
            '{booking_service}', '{booking_date}', '{booking_time}', '{booking_status}',
            '{coupon_code}', '{coupon_expiry}', '{review_rating}', '{feedback_message}', '{lead_status}',
            '{stamp_card_name}', '{stamp_count}', '{required_stamps}', '{reward_title}',
        ];
    }
}
