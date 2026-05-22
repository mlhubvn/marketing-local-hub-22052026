<?php

namespace Modules\AppLandingPages\Support;

use Illuminate\Support\Str;
use Modules\AppLandingPages\Models\LandingPage;
use Modules\AppQRCampaigns\Models\QrCampaign;

class LandingPageFactory
{
    public function syncFromCampaign(QrCampaign $campaign): LandingPage
    {
        $type = $this->normalizeType((string) $campaign->type);
        $settings = $campaign->settings ?: [];

        $page = LandingPage::query()->firstOrNew([
            'campaign_id' => $campaign->id,
        ]);

        if (! $page->exists) {
            $page->slug = $this->uniqueSlug($campaign->name);
            $page->visits_count = 0;
            $page->conversions_count = 0;
        }

        $page->forceFill([
            'user_id' => $campaign->user_id,
            'business_id' => $campaign->business_id,
            'type' => $type,
            'template' => $this->templateFor($type, $settings),
            'title' => $campaign->name,
            'status' => $campaign->published_at ? 'published' : 'draft',
            'content' => $this->contentFor($campaign, $type, $settings),
            'settings' => $this->settingsFor($type, $settings),
            'published_at' => $campaign->published_at,
        ])->save();

        return $page;
    }

    protected function normalizeType(string $type): string
    {
        return in_array($type, ['review', 'booking', 'coupon', 'feedback', 'lead'], true) ? $type : 'lead';
    }

    protected function templateFor(string $type, array $settings = []): string
    {
        $template = (string) data_get($settings, 'landing_template', '');

        if ($template !== '' && array_key_exists($template, PageTemplateCatalog::forType($type))) {
            return $template;
        }

        return PageTemplateCatalog::defaultForType($type);
    }

    protected function contentFor(QrCampaign $campaign, string $type, array $settings): array
    {
        return match ($type) {
            'review' => [
                'headline' => $campaign->name ?: 'How was your visit?',
                'subheadline' => 'Choose a rating. Happy customers continue to a public review, while private feedback goes to the team.',
                'description' => data_get($settings, 'thank_you_message', ''),
                'cta' => 'Continue',
                'benefits' => ['Fast rating flow', 'Private feedback for low scores', 'Public review click tracking'],
                'thank_you_message' => data_get($settings, 'thank_you_message', 'Thank you for your feedback.'),
            ],
            'booking' => [
                'headline' => data_get($settings, 'headline', 'Book an appointment'),
                'subheadline' => 'Choose a service, pick a time, and send your booking request.',
                'description' => '',
                'cta' => 'Request booking',
                'benefits' => ['Choose a service', 'Pick an available slot', 'Get confirmation from the team'],
                'thank_you_message' => 'Thanks. We received your booking request.',
            ],
            'coupon' => [
                'headline' => $campaign->name,
                'subheadline' => 'Claim this local offer and show your code when you visit.',
                'description' => data_get($settings, 'terms', ''),
                'cta' => 'Claim coupon',
                'benefits' => ['Limited-time offer', 'Instant claim code', 'Redeem with staff'],
                'thank_you_message' => 'Your coupon has been sent.',
            ],
            'feedback' => [
                'headline' => data_get($settings, 'headline', 'Tell us about your experience'),
                'subheadline' => 'Send private feedback so the team can improve the next visit.',
                'description' => '',
                'cta' => 'Send feedback',
                'benefits' => ['Private feedback', 'Optional rating', 'Team follow-up'],
                'thank_you_message' => data_get($settings, 'thank_you_message', 'Thanks. Your feedback helps us improve.'),
            ],
            default => [
                'headline' => data_get($settings, 'headline', $campaign->name),
                'subheadline' => 'Leave your details and our local team will follow up.',
                'description' => '',
                'cta' => 'Send request',
                'benefits' => ['Fast response', 'Friendly local team', 'Simple next step'],
                'thank_you_message' => 'Thank you. We have received your request.',
            ],
        };
    }

    protected function settingsFor(string $type, array $settings): array
    {
        $template = $this->templateFor($type, $settings);
        $design = array_merge(
            PageTemplateCatalog::designFor($template),
            (array) data_get($settings, 'design', [])
        );
        $design['template'] = $template;

        return match ($type) {
            'review' => [
                'review_url' => data_get($settings, data_get($settings, 'preferred_destination') === 'facebook' ? 'facebook_review_url' : 'google_review_url'),
                'design' => $design,
                'blocks' => PageTemplateCatalog::blocksFor('review'),
            ],
            'booking' => [
                'service' => data_get($settings, 'service_name', 'Appointment'),
                'duration' => data_get($settings, 'duration_minutes') ? data_get($settings, 'duration_minutes').' minutes' : '',
                'price' => data_get($settings, 'price', ''),
                'available_slots' => ['09:00', '10:00', '14:00', '15:00'],
                'design' => $design,
                'blocks' => PageTemplateCatalog::blocksFor('booking'),
            ],
            'coupon' => [
                'coupon_title' => data_get($settings, 'coupon_code', ''),
                'discount' => trim(data_get($settings, 'discount_value', '').' '.str_replace('_', ' ', data_get($settings, 'discount_type', ''))),
                'expiry' => data_get($settings, 'expiry_date', ''),
                'terms' => data_get($settings, 'terms', ''),
                'design' => $design,
                'blocks' => PageTemplateCatalog::blocksFor('coupon'),
            ],
            'feedback' => [
                'design' => $design,
                'blocks' => PageTemplateCatalog::blocksFor('feedback'),
            ],
            default => [
                'design' => $design,
                'blocks' => PageTemplateCatalog::blocksFor('lead'),
            ],
        };
    }

    protected function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'landing-page';
        $slug = $base;
        $counter = 2;

        while (LandingPage::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
