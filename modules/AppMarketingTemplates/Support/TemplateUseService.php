<?php

namespace Modules\AppMarketingTemplates\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\AppBookingPages\Models\BookingService;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppEmailAutomation\Models\EmailTemplate;
use Modules\AppLandingPages\Models\LandingPage;
use Modules\AppLandingPages\Support\LandingPageFactory;
use Modules\AppMarketingTemplates\Models\MarketingTemplate;
use Modules\AppQRCampaigns\Models\QrCampaign;
use Modules\AppWebhookAutomation\Models\WebhookAutomation;
use Modules\AppWhatsAppNotification\Models\WhatsAppTemplate;

class TemplateUseService
{
    /**
     * @param  array<string, mixed>  $useForm
     */
    public function createLandingPage(MarketingTemplate $template, LocalBusiness $business, array $useForm, int $userId): LandingPage
    {
        $campaignType = $this->campaignTypeFor($template);

        return LandingPage::query()->create([
            'user_id' => $userId,
            'business_id' => $business->id,
            'campaign_id' => null,
            'slug' => $this->uniqueLandingPageSlug((string) $useForm['campaign_name']),
            'type' => $campaignType,
            'template' => match ($campaignType) {
                'review' => 'review_request',
                'booking' => 'appointment',
                'coupon' => 'offer',
                'feedback' => 'feedback',
                default => 'local_campaign',
            },
            'title' => (string) $useForm['campaign_name'],
            'status' => 'draft',
            'content' => [
                'headline' => $useForm['headline'],
                'subheadline' => '',
                'description' => $useForm['description'],
                'cta' => $useForm['cta'],
                'benefits' => (array) data_get($template->content ?: [], 'benefits', []),
                'thank_you_message' => $useForm['thank_you_message'],
            ],
            'settings' => $this->campaignSettings($template, $template->settings ?: [], $useForm),
            'published_at' => null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $useForm
     * @return array{campaign:QrCampaign, landing_page:mixed}
     */
    public function createCampaign(MarketingTemplate $template, LocalBusiness $business, array $useForm, int $userId): array
    {
        $content = $template->content ?: [];
        $settings = $template->settings ?: [];
        $campaignType = $this->campaignTypeFor($template);

        if ($campaignType === 'booking') {
            BookingService::query()->create([
                'user_id' => $userId,
                'business_id' => $business->id,
                'name' => (string) ($useForm['service_name'] ?: data_get($content, 'campaign_name', $template->name)),
                'duration_minutes' => (int) ($useForm['duration_minutes'] ?: 60),
                'price' => $useForm['price'] !== '' ? $useForm['price'] : null,
                'description' => (string) ($useForm['description'] ?: data_get($content, 'description', '')),
                'available_days' => ['mon', 'tue', 'wed', 'thu', 'fri', 'sat'],
                'time_slots' => ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00'],
            ]);
        }

        $campaign = QrCampaign::query()->create([
            'user_id' => $userId,
            'business_id' => $business->id,
            'slug' => $this->uniqueCampaignSlug((string) $useForm['campaign_name']),
            'name' => (string) $useForm['campaign_name'],
            'type' => $campaignType,
            'settings' => $this->campaignSettings($template, $settings, $useForm),
            'published_at' => now(),
        ]);

        return [
            'campaign' => $campaign,
            'landing_page' => app(LandingPageFactory::class)->syncFromCampaign($campaign),
        ];
    }

    public function createEmailTemplate(MarketingTemplate $template, LocalBusiness $business, int $userId): EmailTemplate
    {
        $content = $template->content ?: [];
        $email = (array) data_get($content, 'email_content', []);

        return EmailTemplate::query()->create([
            'user_id' => $userId,
            'business_id' => $business->id,
            'name' => (string) data_get($content, 'campaign_name', $template->name),
            'type' => (string) ($template->goal ?: 'general'),
            'subject' => (string) ($email['subject'] ?? data_get($content, 'headline', $template->name)),
            'preheader' => (string) data_get($content, 'description', $template->description),
            'body' => (string) ($email['body'] ?? data_get($content, 'prompt_template', data_get($content, 'description', $template->description))),
            'language' => (string) data_get($content, 'language', 'en'),
            'is_system' => false,
            'status' => 'active',
        ]);
    }

    public function createWhatsAppTemplate(MarketingTemplate $template, LocalBusiness $business, int $userId): WhatsAppTemplate
    {
        $content = $template->content ?: [];
        $whatsapp = (array) data_get($content, 'whatsapp_content', []);

        return WhatsAppTemplate::query()->create([
            'user_id' => $userId,
            'business_id' => $business->id,
            'name' => (string) data_get($content, 'campaign_name', $template->name),
            'type' => (string) ($template->goal ?: 'general'),
            'template_name' => Str::slug((string) $template->name, '_'),
            'language' => (string) data_get($content, 'language', 'en_US'),
            'body' => (string) ($whatsapp['message'] ?? data_get($content, 'prompt_template', data_get($content, 'description', $template->description))),
            'is_system' => false,
            'status' => 'active',
        ]);
    }

    public function createWebhookAutomation(MarketingTemplate $template, LocalBusiness $business, int $userId): WebhookAutomation
    {
        $settings = $template->settings ?: [];
        $content = $template->content ?: [];

        return WebhookAutomation::query()->create([
            'user_id' => $userId,
            'business_id' => $business->id,
            'name' => (string) data_get($content, 'campaign_name', $template->name),
            'trigger_event' => (string) data_get($settings, 'trigger_event', $template->goal === 'feedback' ? 'feedback.created' : 'lead.created'),
            'status' => (string) data_get($settings, 'default_status', 'draft'),
            'delay_type' => (string) data_get($settings, 'delay_type', 'immediate'),
            'delay_value' => (int) data_get($settings, 'delay_value', 0),
            'delay_unit' => (string) data_get($settings, 'delay_unit', 'minutes'),
            'condition_json' => (array) data_get($settings, 'conditions', []),
            'webhook_url' => (string) data_get($settings, 'webhook_url', url('/webhooks/mlhub-template')),
            'method' => (string) data_get($settings, 'method', 'POST'),
            'headers_json' => (array) data_get($settings, 'headers', ['Content-Type' => 'application/json']),
            'secret_token' => null,
            'retry_on_failure' => true,
            'created_by' => $userId,
        ]);
    }

    public function recordUsage(MarketingTemplate $template, LocalBusiness $business, string $objectType, ?int $objectId, ?int $userId): void
    {
        DB::table('lb_template_usages')->insert([
            'team_id' => $template->team_id,
            'template_id' => $template->id,
            'business_id' => $business->id,
            'created_object_type' => $objectType,
            'created_object_id' => $objectId,
            'user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function campaignTypeFor(MarketingTemplate $template): string
    {
        $type = (string) data_get($template->settings, 'campaign_type', $template->goal);

        return match ($type) {
            'reviews', 'review' => 'review',
            'bookings', 'booking' => 'booking',
            'coupons', 'coupon', 'retention' => 'coupon',
            'feedback' => 'feedback',
            'leads', 'lead' => 'lead',
            default => in_array($template->goal, ['review', 'booking', 'coupon', 'feedback', 'lead'], true) ? $template->goal : 'lead',
        };
    }

    /**
     * @param  array<string, mixed>  $settings
     * @param  array<string, mixed>  $useForm
     * @return array<string, mixed>
     */
    public function campaignSettings(MarketingTemplate $template, array $settings, array $useForm): array
    {
        $campaignType = $this->campaignTypeFor($template);
        $base = [
            'headline' => $useForm['headline'],
            'description' => $useForm['description'],
            'cta' => $useForm['cta'],
            'thank_you_message' => $useForm['thank_you_message'],
            'template_id' => $template->id,
            'template_slug' => $template->slug,
            'tracking_goal' => $settings['tracking_goal'] ?? $campaignType,
        ];

        return match ($campaignType) {
            'review' => [
                ...$base,
                'google_review_url' => $useForm['google_review_url'],
                'facebook_review_url' => $useForm['facebook_review_url'],
                'negative_feedback_message' => $useForm['negative_feedback_message'],
                'positive_threshold' => (int) $useForm['positive_threshold'],
                'preferred_destination' => filled($useForm['facebook_review_url']) ? 'facebook' : 'google',
            ],
            'booking' => [
                ...$base,
                'service_name' => $useForm['service_name'],
                'duration_minutes' => (int) $useForm['duration_minutes'],
                'price' => $useForm['price'],
            ],
            'coupon' => [
                ...$base,
                'discount_type' => $useForm['discount_type'],
                'discount_value' => $useForm['discount_value'],
                'coupon_code' => $useForm['coupon_code'],
                'expiry_date' => $useForm['expiry_date'],
                'usage_limit' => $useForm['usage_limit'],
                'terms' => $useForm['terms'],
                'status' => 'active',
            ],
            'feedback' => [
                ...$base,
                'headline' => $useForm['headline'],
                'rating_required' => (bool) $useForm['rating_required'],
                'contact_required' => (bool) $useForm['contact_required'],
            ],
            default => [
                ...$base,
                'headline' => $useForm['headline'],
            ],
        };
    }

    protected function uniqueCampaignSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'campaign';
        $slug = $base;
        $counter = 2;

        while (QrCampaign::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    protected function uniqueLandingPageSlug(string $title): string
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
