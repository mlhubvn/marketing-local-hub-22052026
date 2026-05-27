<?php

namespace Modules\AppEmailAutomation\Support;

class EmailAutomationCatalog
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
        ];
    }

    public static function templateSeeds(): array
    {
        return [
            ['name' => 'Booking Request Received', 'type' => 'booking', 'subject' => 'Your booking request has been received', 'body' => "Hi {customer_name},\n\nThank you for booking {booking_service} with {business_name}.\nWe received your request for {booking_date} at {booking_time}.\n\nOur team will confirm your appointment soon."],
            ['name' => 'Booking Confirmed', 'type' => 'booking', 'subject' => 'Your booking is confirmed', 'body' => "Hi {customer_name},\n\nYour appointment with {business_name} is confirmed for {booking_date} at {booking_time}.\n\nSee you soon."],
            ['name' => 'Booking Reminder', 'type' => 'booking', 'subject' => 'Reminder: your appointment is coming up', 'body' => "Hi {customer_name},\n\nThis is a reminder for your appointment with {business_name} on {booking_date} at {booking_time}."],
            ['name' => 'Booking Cancelled', 'type' => 'booking', 'subject' => 'Your booking was cancelled', 'body' => "Hi {customer_name},\n\nYour booking with {business_name} was cancelled. Contact us if you want to reschedule."],
            ['name' => 'Booking Completed Review Request', 'type' => 'review', 'subject' => 'How was your visit?', 'body' => "Hi {customer_name},\n\nThanks for visiting {business_name}. We would appreciate your feedback:\n{public_page_url}"],
            ['name' => 'Coupon Claimed', 'type' => 'coupon', 'subject' => 'Your coupon code is ready', 'body' => "Hi {customer_name},\n\nHere is your coupon for {business_name}: {coupon_code}\n\nUse it before {coupon_expiry}."],
            ['name' => 'Coupon Used Thank You', 'type' => 'coupon', 'subject' => 'Thanks for using your coupon', 'body' => "Hi {customer_name},\n\nThanks for visiting {business_name}. We hope to see you again soon."],
            ['name' => 'Lead Received', 'type' => 'lead', 'subject' => 'We received your request', 'body' => "Hi {customer_name},\n\nThanks for contacting {business_name}. Our team will follow up soon."],
            ['name' => 'Lead Follow-up', 'type' => 'lead', 'subject' => 'Following up on your request', 'body' => "Hi {customer_name},\n\nJust checking in after your request to {business_name}. Reply to this email if you have any questions."],
            ['name' => 'Feedback Received', 'type' => 'feedback', 'subject' => 'Thank you for your feedback', 'body' => "Hi {customer_name},\n\nThank you for sharing your feedback with {business_name}. We appreciate it."],
            ['name' => 'Low-score Recovery', 'type' => 'feedback', 'subject' => 'We want to make this right', 'body' => "Hi {customer_name},\n\nWe are sorry your experience with {business_name} was not perfect. Please reply and tell us how we can make it right."],
            ['name' => 'Positive Review Thank You', 'type' => 'review', 'subject' => 'Thank you for your review', 'body' => "Hi {customer_name},\n\nThank you for supporting {business_name}. Your feedback means a lot to us."],
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
        ];
    }
}
