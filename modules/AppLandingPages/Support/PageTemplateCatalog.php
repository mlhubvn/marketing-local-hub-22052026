<?php

namespace Modules\AppLandingPages\Support;

use Illuminate\Support\Str;

class PageTemplateCatalog
{
    public static function all(): array
    {
        static $catalog = null;

        if ($catalog !== null) {
            return $catalog;
        }

        $catalog = [];
        $palettes = self::palettes();

        foreach (self::templateGroups() as $type => $templates) {
            foreach ($templates as $index => $template) {
                $palette = $palettes[$index % count($palettes)];
                $style = self::styleVariants()[$index % count(self::styleVariants())];
                $key = $template['key'] ?? $type.'_'.Str::slug($template['name'], '_');

                $catalog[$key] = [
                    'type' => $type,
                    'name' => __($template['name']),
                    'primary' => $template['primary'] ?? $palette['primary'],
                    'background' => $template['background'] ?? $palette['background'],
                    'accent' => $template['accent'] ?? $palette['accent'],
                    'layout' => $template['layout'] ?? $style['layout'],
                    'background_type' => $template['background_type'] ?? $style['background_type'],
                    'font_style' => $template['font_style'] ?? $style['font_style'],
                    'button_style' => $template['button_style'] ?? $style['button_style'],
                    'card_style' => $template['card_style'] ?? $style['card_style'],
                    'logo_shape' => $template['logo_shape'] ?? $style['logo_shape'],
                ];
            }
        }

        return $catalog;
    }

    public static function options(): array
    {
        return collect(self::all())->mapWithKeys(fn (array $preset, string $key) => [$key => $preset['name']])->all();
    }

    public static function forType(string $type): array
    {
        return collect(self::all())
            ->filter(fn (array $preset) => $preset['type'] === $type)
            ->mapWithKeys(fn (array $preset, string $key) => [$key => $preset['name']])
            ->all();
    }

    public static function defaultForType(string $type): string
    {
        return array_key_first(self::forType($type)) ?: 'lead_free_consultation';
    }

    public static function designFor(string $template): array
    {
        $preset = self::all()[$template] ?? self::all()['lead_free_consultation'];

        return [
            'template' => $template,
            'layout_style' => $preset['layout'] ?? self::layoutStyleFor($template),
            'primary_color' => $preset['primary'],
            'background_color' => $preset['background'],
            'accent_color' => $preset['accent'],
            'background_type' => $preset['background_type'] ?? 'gradient',
            'font_style' => $preset['font_style'] ?? 'modern',
            'button_style' => $preset['button_style'] ?? 'pill',
            'card_style' => $preset['card_style'] ?? 'soft',
            'logo_url' => '',
            'logo_shape' => $preset['logo_shape'] ?? 'circle',
            'cover_image' => '',
            'show_logo' => true,
            'show_business_info' => true,
            'show_social_links' => true,
            'show_benefits' => true,
            'show_terms' => true,
            'show_faq' => true,
        ];
    }

    public static function publicViewFor(string $template, string $type): string
    {
        if (in_array($type, ['coupon', 'promotion'], true)) {
            return match ($template) {
                'coupon_weekend_deal' => 'applandingpages::public.templates.coupon-weekend-deal',
                'coupon_comeback' => 'applandingpages::public.templates.coupon-comeback',
                'coupon_birthday' => 'applandingpages::public.templates.coupon-birthday',
                'coupon_restaurant' => 'applandingpages::public.templates.coupon-restaurant',
                'coupon_retail' => 'applandingpages::public.templates.coupon-retail',
                default => 'applandingpages::public.templates.coupon-20-off',
            };
        }

        if ($type === 'feedback') {
            return match ($template) {
                'feedback_recovery' => 'applandingpages::public.templates.feedback-recovery',
                'feedback_post_visit' => 'applandingpages::public.templates.feedback-post-visit',
                'feedback_quality' => 'applandingpages::public.templates.feedback-quality',
                'feedback_quick_rating' => 'applandingpages::public.templates.feedback-quick-rating',
                'feedback_satisfaction' => 'applandingpages::public.templates.feedback-satisfaction',
                'feedback_one_minute_survey' => 'applandingpages::public.templates.feedback-one-minute',
                'feedback_experience_check' => 'applandingpages::public.templates.feedback-experience-check',
                'feedback_manager_feedback' => 'applandingpages::public.templates.feedback-manager',
                'feedback_anonymous_feedback' => 'applandingpages::public.templates.feedback-anonymous',
                'feedback_nps_feedback' => 'applandingpages::public.templates.feedback-nps',
                default => 'applandingpages::public.templates.feedback-private',
            };
        }

        if ($type === 'booking') {
            return match ($template) {
                'booking_salon' => 'applandingpages::public.templates.booking-salon',
                'booking_clinic' => 'applandingpages::public.templates.booking-clinic',
                'booking_gym' => 'applandingpages::public.templates.booking-gym',
                'booking_restaurant' => 'applandingpages::public.templates.booking-restaurant',
                'booking_minimal' => 'applandingpages::public.templates.booking-minimal',
                default => 'applandingpages::public.templates.booking-spa',
            };
        }

        if ($type === 'lead') {
            return match ($template) {
                'lead_quote_request' => 'applandingpages::public.templates.lead-quote-request',
                'lead_new_customer' => 'applandingpages::public.templates.lead-new-customer',
                'lead_event_capture' => 'applandingpages::public.templates.lead-event-capture',
                'lead_local_service' => 'applandingpages::public.templates.lead-local-service',
                'lead_agency_client' => 'applandingpages::public.templates.lead-agency-client',
                default => 'applandingpages::public.templates.lead-free-consultation',
            };
        }

        if ($type === 'custom') {
            return match ($template) {
                'custom_announcement_page' => 'applandingpages::public.templates.custom-announcement',
                'custom_simple_cta_page' => 'applandingpages::public.templates.custom-simple-cta',
                'custom_qr_poster_page' => 'applandingpages::public.templates.custom-qr-poster',
                'custom_local_event_page' => 'applandingpages::public.templates.custom-local-event',
                'custom_product_spotlight' => 'applandingpages::public.templates.custom-product-spotlight',
                default => 'applandingpages::public.templates.custom-blank-campaign',
            };
        }

        if ($type !== 'review') {
            return 'applandingpages::public.show';
        }

        return match ($template) {
            'review_google_focus' => 'applandingpages::public.templates.review-google-focus',
            'review_friendly_feedback' => 'applandingpages::public.templates.review-friendly-feedback',
            'review_luxury' => 'applandingpages::public.templates.review-luxury',
            'review_restaurant' => 'applandingpages::public.templates.review-restaurant',
            'review_clinic' => 'applandingpages::public.templates.review-clinic',
            'review_star_rating_invite' => 'applandingpages::public.templates.review-star-rating-invite',
            'review_neighborhood_review' => 'applandingpages::public.templates.review-neighborhood',
            'review_service_follow_up' => 'applandingpages::public.templates.review-service-follow-up',
            'review_happy_customer_prompt' => 'applandingpages::public.templates.review-happy-customer',
            'review_minimal_review_link' => 'applandingpages::public.templates.review-minimal-link',
            'review_warm_thank_you_review' => 'applandingpages::public.templates.review-warm-thank-you',
            'review_premium_reputation' => 'applandingpages::public.templates.review-premium-reputation',
            'review_recovery_first_review' => 'applandingpages::public.templates.review-recovery-first',
            default => 'applandingpages::public.templates.review-clean-request',
        };
    }

    public static function blocksFor(string $type): array
    {
        return match ($type) {
            'review' => ['hero', 'review_rating', 'benefits', 'business_info', 'thank_you'],
            'booking' => ['hero', 'booking_service', 'benefits', 'form', 'business_info', 'thank_you'],
            'coupon', 'promotion' => ['hero', 'offer', 'benefits', 'form', 'business_info', 'thank_you'],
            'feedback' => ['hero', 'feedback_question', 'benefits', 'form', 'business_info', 'thank_you'],
            'loyalty' => ['hero', 'offer', 'benefits', 'form', 'business_info', 'thank_you'],
            default => ['hero', 'benefits', 'form', 'business_info', 'thank_you'],
        };
    }

    private static function layoutStyleFor(string $template): string
    {
        $layouts = ['split', 'centered', 'poster', 'sidebar', 'stacked', 'editorial'];
        $keys = array_keys(self::all());
        $index = array_search($template, $keys, true);

        return $layouts[$index === false ? 0 : $index % count($layouts)];
    }

    private static function palettes(): array
    {
        return [
            ['primary' => '#0f766e', 'background' => '#f4fbf8', 'accent' => '#ccfbf1'],
            ['primary' => '#2563eb', 'background' => '#f8fafc', 'accent' => '#bfdbfe'],
            ['primary' => '#ea580c', 'background' => '#fff7ed', 'accent' => '#fed7aa'],
            ['primary' => '#111827', 'background' => '#f8f5ef', 'accent' => '#fde68a'],
            ['primary' => '#b45309', 'background' => '#fffbeb', 'accent' => '#fed7aa'],
            ['primary' => '#0369a1', 'background' => '#f0f9ff', 'accent' => '#bae6fd'],
            ['primary' => '#be185d', 'background' => '#fdf2f8', 'accent' => '#fbcfe8'],
            ['primary' => '#16a34a', 'background' => '#f7fee7', 'accent' => '#bbf7d0'],
            ['primary' => '#7c3aed', 'background' => '#f5f3ff', 'accent' => '#ddd6fe'],
            ['primary' => '#0891b2', 'background' => '#ecfeff', 'accent' => '#cffafe'],
            ['primary' => '#db2777', 'background' => '#fdf2f8', 'accent' => '#fce7f3'],
            ['primary' => '#4f46e5', 'background' => '#eef2ff', 'accent' => '#c7d2fe'],
            ['primary' => '#dc2626', 'background' => '#fef2f2', 'accent' => '#fecaca'],
            ['primary' => '#7c2d12', 'background' => '#fff7ed', 'accent' => '#fed7aa'],
            ['primary' => '#ca8a04', 'background' => '#fefce8', 'accent' => '#fef08a'],
            ['primary' => '#0e7490', 'background' => '#ecfeff', 'accent' => '#a5f3fc'],
            ['primary' => '#1d4ed8', 'background' => '#eff6ff', 'accent' => '#bfdbfe'],
            ['primary' => '#c2410c', 'background' => '#fff7ed', 'accent' => '#fed7aa'],
            ['primary' => '#334155', 'background' => '#f8fafc', 'accent' => '#e2e8f0'],
            ['primary' => '#047857', 'background' => '#f0fdf4', 'accent' => '#bbf7d0'],
        ];
    }

    private static function styleVariants(): array
    {
        return [
            ['layout' => 'split', 'background_type' => 'gradient', 'font_style' => 'modern', 'button_style' => 'pill', 'card_style' => 'soft', 'logo_shape' => 'circle'],
            ['layout' => 'centered', 'background_type' => 'solid', 'font_style' => 'friendly', 'button_style' => 'rounded', 'card_style' => 'bordered', 'logo_shape' => 'square'],
            ['layout' => 'poster', 'background_type' => 'gradient', 'font_style' => 'elegant', 'button_style' => 'square', 'card_style' => 'flat', 'logo_shape' => 'circle'],
            ['layout' => 'sidebar', 'background_type' => 'solid', 'font_style' => 'classic', 'button_style' => 'rounded', 'card_style' => 'soft', 'logo_shape' => 'square'],
            ['layout' => 'stacked', 'background_type' => 'gradient', 'font_style' => 'modern', 'button_style' => 'square', 'card_style' => 'bordered', 'logo_shape' => 'circle'],
            ['layout' => 'editorial', 'background_type' => 'solid', 'font_style' => 'elegant', 'button_style' => 'pill', 'card_style' => 'flat', 'logo_shape' => 'square'],
            ['layout' => 'split', 'background_type' => 'solid', 'font_style' => 'friendly', 'button_style' => 'square', 'card_style' => 'bordered', 'logo_shape' => 'circle'],
            ['layout' => 'centered', 'background_type' => 'gradient', 'font_style' => 'classic', 'button_style' => 'pill', 'card_style' => 'soft', 'logo_shape' => 'square'],
        ];
    }

    private static function templateGroups(): array
    {
        return [
            'review' => [
                ['key' => 'review_clean_request', 'name' => 'Clean Review Request'],
                ['key' => 'review_google_focus', 'name' => 'Google Review Focus'],
                ['key' => 'review_friendly_feedback', 'name' => 'Friendly Feedback'],
                ['key' => 'review_luxury', 'name' => 'Luxury Review'],
                ['key' => 'review_restaurant', 'name' => 'Restaurant Review'],
                ['key' => 'review_clinic', 'name' => 'Clinic Review'],
                ['name' => 'Star Rating Invite'],
                ['name' => 'Neighborhood Review'],
                ['name' => 'Service Follow-up'],
                ['name' => 'Happy Customer Prompt'],
                ['name' => 'Minimal Review Link'],
                ['name' => 'Warm Thank You Review'],
                ['name' => 'Premium Reputation'],
                ['name' => 'Google First Routing'],
                ['name' => 'Facebook Review Ask'],
                ['name' => 'Post-Visit Review'],
                ['name' => 'Local Trust Builder'],
                ['name' => 'Quick Star Feedback'],
                ['name' => 'Recovery First Review'],
                ['name' => 'Modern Review Card'],
            ],
            'booking' => [
                ['key' => 'booking_spa', 'name' => 'Spa Booking'],
                ['key' => 'booking_salon', 'name' => 'Salon Appointment'],
                ['key' => 'booking_clinic', 'name' => 'Clinic Consultation'],
                ['key' => 'booking_gym', 'name' => 'Gym Trial Booking'],
                ['key' => 'booking_restaurant', 'name' => 'Restaurant Reservation'],
                ['key' => 'booking_minimal', 'name' => 'Minimal Service Booking'],
                ['name' => 'Dental Appointment'],
                ['name' => 'Auto Service Booking'],
                ['name' => 'Home Service Visit'],
                ['name' => 'Beauty Treatment'],
                ['name' => 'Consultation Call'],
                ['name' => 'Event Reservation'],
                ['name' => 'Private Session'],
                ['name' => 'Class Booking'],
                ['name' => 'Repair Appointment'],
                ['name' => 'Wellness Session'],
                ['name' => 'Table Booking'],
                ['name' => 'Tour Booking'],
                ['name' => 'Intro Appointment'],
                ['name' => 'Priority Booking'],
            ],
            'coupon' => [
                ['key' => 'coupon_20_off', 'name' => '20% Off Offer'],
                ['key' => 'coupon_weekend_deal', 'name' => 'Weekend Deal'],
                ['key' => 'coupon_comeback', 'name' => 'Come Back Coupon'],
                ['key' => 'coupon_birthday', 'name' => 'Birthday Offer'],
                ['key' => 'coupon_restaurant', 'name' => 'Restaurant Discount'],
                ['key' => 'coupon_retail', 'name' => 'Retail Promo'],
                ['name' => 'First Visit Discount'],
                ['name' => 'Limited Time Deal'],
                ['name' => 'Loyalty Coupon'],
                ['name' => 'Holiday Offer'],
                ['name' => 'Flash Sale Claim'],
                ['name' => 'Free Add-on Offer'],
                ['name' => 'Bundle Discount'],
                ['name' => 'Referral Coupon'],
                ['name' => 'Lunch Special'],
                ['name' => 'Service Upgrade'],
                ['name' => 'VIP Coupon'],
                ['name' => 'New Customer Promo'],
                ['name' => 'Invoice Coupon'],
                ['name' => 'QR Counter Offer'],
            ],
            'promotion' => [
                ['name' => 'Seasonal Promotion'],
                ['name' => 'Grand Opening Promo'],
                ['name' => 'Local Store Launch'],
                ['name' => 'Service Spotlight'],
                ['name' => 'Weekend Campaign'],
                ['name' => 'Holiday Campaign'],
                ['name' => 'New Menu Promo'],
                ['name' => 'Member Exclusive'],
                ['name' => 'Community Offer'],
                ['name' => 'Social Promo'],
                ['name' => 'Limited Run Campaign'],
                ['name' => 'Event Booth Promo'],
                ['name' => 'Back to School'],
                ['name' => 'Summer Special'],
                ['name' => 'Winter Special'],
                ['name' => 'Premium Upgrade'],
                ['name' => 'Local Giveaway'],
                ['name' => 'QR Flyer Promo'],
                ['name' => 'Storefront Offer'],
                ['name' => 'Reactivation Campaign'],
            ],
            'feedback' => [
                ['key' => 'feedback_private', 'name' => 'Private Feedback'],
                ['key' => 'feedback_recovery', 'name' => 'Complaint Recovery'],
                ['key' => 'feedback_post_visit', 'name' => 'Post-Visit Survey'],
                ['key' => 'feedback_quality', 'name' => 'Service Quality Check'],
                ['key' => 'feedback_quick_rating', 'name' => 'Quick Rating'],
                ['key' => 'feedback_satisfaction', 'name' => 'Customer Satisfaction'],
                ['name' => 'One-Minute Survey'],
                ['name' => 'Experience Check'],
                ['name' => 'Manager Feedback'],
                ['name' => 'Table Feedback'],
                ['name' => 'Clinic Feedback'],
                ['name' => 'Delivery Feedback'],
                ['name' => 'Support Follow-up'],
                ['name' => 'Service Recovery'],
                ['name' => 'Anonymous Feedback'],
                ['name' => 'Staff Feedback'],
                ['name' => 'Visit Improvement'],
                ['name' => 'NPS Feedback'],
                ['name' => 'Quick Comment Box'],
                ['name' => 'Detailed Feedback Form'],
            ],
            'lead' => [
                ['key' => 'lead_free_consultation', 'name' => 'Free Consultation'],
                ['key' => 'lead_quote_request', 'name' => 'Quote Request'],
                ['key' => 'lead_new_customer', 'name' => 'New Customer Inquiry'],
                ['key' => 'lead_event_capture', 'name' => 'Event Lead Capture'],
                ['key' => 'lead_local_service', 'name' => 'Local Service Lead'],
                ['key' => 'lead_agency_client', 'name' => 'Agency Client Lead'],
                ['name' => 'Free Estimate'],
                ['name' => 'Callback Request'],
                ['name' => 'Brochure Lead'],
                ['name' => 'Demo Request'],
                ['name' => 'Waitlist Signup'],
                ['name' => 'Newsletter Capture'],
                ['name' => 'Property Inquiry'],
                ['name' => 'Healthcare Inquiry'],
                ['name' => 'Fitness Trial Lead'],
                ['name' => 'Home Service Quote'],
                ['name' => 'Wedding Inquiry'],
                ['name' => 'Event Vendor Lead'],
                ['name' => 'Local Contractor Lead'],
                ['name' => 'Professional Service Lead'],
            ],
            'loyalty' => [
                ['key' => 'loyalty_coffee_stamp', 'name' => 'Coffee Stamp Card'],
                ['key' => 'loyalty_visit_reward', 'name' => 'Visit Reward Card'],
                ['key' => 'loyalty_restaurant_return', 'name' => 'Restaurant Return Offer'],
                ['key' => 'loyalty_spa_rewards', 'name' => 'Spa Visit Rewards'],
                ['key' => 'loyalty_gym_sessions', 'name' => 'Gym Session Rewards'],
                ['key' => 'loyalty_salon_card', 'name' => 'Salon Loyalty Card'],
                ['name' => 'Buy More Get Reward'],
                ['name' => 'Repeat Customer Card'],
                ['name' => 'VIP Stamp Card'],
                ['name' => 'Monthly Visit Challenge'],
                ['name' => 'Dessert Reward Card'],
                ['name' => 'Free Service Trial'],
                ['name' => 'Counter QR Stamp Card'],
                ['name' => 'Local Rewards Program'],
                ['name' => 'Member Return Offer'],
                ['name' => 'Punch Card Reward'],
                ['name' => 'Free Item Unlock'],
                ['name' => 'Customer Comeback Card'],
                ['name' => 'In-Store Stamp Program'],
                ['name' => 'Digital Coupon Unlock'],
            ],
            'custom' => [
                ['name' => 'Blank Local Campaign'],
                ['name' => 'Announcement Page'],
                ['name' => 'Simple CTA Page'],
                ['name' => 'QR Poster Page'],
                ['name' => 'Local Event Page'],
                ['name' => 'Product Spotlight'],
                ['name' => 'Service Menu Page'],
                ['name' => 'Bio Link Landing'],
                ['name' => 'Community Update'],
                ['name' => 'Micro Landing Page'],
                ['name' => 'Store Info Page'],
                ['name' => 'Custom URL Campaign'],
                ['name' => 'Waitlist Campaign'],
                ['name' => 'Offline Flyer Page'],
                ['name' => 'Business Card QR'],
                ['name' => 'Launch Notice'],
                ['name' => 'Local Guide Page'],
                ['name' => 'Mini Site Page'],
                ['name' => 'Custom Form Page'],
                ['name' => 'General Campaign Page'],
            ],
        ];
    }
}
