<?php

namespace Database\Seeders;

use Database\Support\IdSequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminPlans\Support\PlanPermissionSchema;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            AdminPlan::query()
                ->where('default_signup_plan', true)
                ->update(['default_signup_plan' => false]);

            foreach ($this->plans() as $index => $plan) {
                $slug = (string) $plan['slug'];
                $plan['permissions'] = $this->normalizePermissions($plan['permissions']);

                if (
                    ! AdminPlan::query()->where('slug', $slug)->exists()
                    && ($id = IdSequence::idForNewSeed($index)) !== null
                    && ! AdminPlan::query()->whereKey($id)->exists()
                ) {
                    $plan['id'] = $id;
                }

                AdminPlan::unguarded(fn () => AdminPlan::query()->updateOrCreate(
                    ['slug' => $slug],
                    $plan,
                ));
            }
        });
    }

    protected function plans(): array
    {
        return [
            $this->plan(
                name: 'MLHUB Free Da Nang',
                slug: 'mlhub-free-da-nang',
                price: 0,
                type: 3,
                featured: true,
                freePlan: true,
                defaultSignupPlan: true,
                trialDay: 0,
                position: 0,
                desc: 'The default free plan for Da Nang household businesses during the pilot phase, with business profiles, QR codes, coupons, landing pages, customer contacts, and a basic dashboard.',
                permissions: $this->freePermissions(),
            ),
            $this->plan('MLHUB Starter Monthly', 'mlhub-starter-monthly', 199000, 1, false, false, false, 7, 10, 'Essential MLHUB tools for household businesses starting local digital marketing.', $this->starterPermissions(300)),
            $this->plan('MLHUB Starter Yearly', 'mlhub-starter-yearly', 1990000, 2, false, false, false, 14, 11, 'Essential MLHUB tools for household businesses starting local digital marketing.', $this->starterPermissions(3600)),
            $this->plan('MLHUB Starter Lifetime', 'mlhub-starter-lifetime', 5990000, 3, false, false, false, 0, 12, 'Essential MLHUB tools for household businesses starting local digital marketing.', $this->starterPermissions(300)),
            $this->plan('MLHUB Growth Monthly', 'mlhub-growth-monthly', 349000, 1, true, false, false, 10, 20, 'Growth tools, automation, and higher limits for expanding local businesses.', $this->growthPermissions(1000)),
            $this->plan('MLHUB Growth Yearly', 'mlhub-growth-yearly', 3490000, 2, false, false, false, 21, 21, 'Growth tools, automation, and higher limits for expanding local businesses.', $this->growthPermissions(12000)),
            $this->plan('MLHUB Growth Lifetime', 'mlhub-growth-lifetime', 9990000, 3, false, false, false, 0, 22, 'Growth tools, automation, and higher limits for expanding local businesses.', $this->growthPermissions(1000)),
            $this->plan('MLHUB Pro Monthly', 'mlhub-pro-monthly', 749000, 1, true, false, false, 14, 30, 'Advanced automation, AI, CRM, and branding controls for professional teams.', $this->proPermissions(5000)),
            $this->plan('MLHUB Pro Yearly', 'mlhub-pro-yearly', 7490000, 2, false, false, false, 30, 31, 'Advanced automation, AI, CRM, and branding controls for professional teams.', $this->proPermissions(60000)),
            $this->plan('MLHUB Pro Lifetime', 'mlhub-pro-lifetime', 21990000, 3, false, false, false, 0, 32, 'Advanced automation, AI, CRM, and branding controls for professional teams.', $this->proPermissions(5000)),
            $this->plan('MLHUB Partner Monthly', 'mlhub-partner-monthly', 1249000, 1, false, false, false, 14, 40, 'High-capacity MLHUB operations for partners managing many businesses and customers.', $this->partnerPermissions(20000)),
            $this->plan('MLHUB Partner Yearly', 'mlhub-partner-yearly', 12490000, 2, false, false, false, 30, 41, 'High-capacity MLHUB operations for partners managing many businesses and customers.', $this->partnerPermissions(240000)),
            $this->plan('MLHUB Partner Lifetime', 'mlhub-partner-lifetime', 36990000, 3, false, false, false, 0, 42, 'High-capacity MLHUB operations for partners managing many businesses and customers.', $this->partnerPermissions(20000)),
        ];
    }

    protected function plan(
        string $name,
        string $slug,
        int $price,
        int $type,
        bool $featured,
        bool $freePlan,
        bool $defaultSignupPlan,
        int $trialDay,
        int $position,
        string $desc,
        array $permissions,
    ): array {
        return [
            'name' => $name,
            'slug' => $slug,
            'status' => true,
            'featured' => $featured,
            'currency' => 'VND',
            'price' => $price,
            'type' => $type,
            'free_plan' => $freePlan,
            'default_signup_plan' => $defaultSignupPlan,
            'trial_day' => $trialDay,
            'position' => $position,
            'desc' => $desc,
            'permissions' => $permissions,
        ];
    }

    protected function freePermissions(): array
    {
        return $this->permissions([
            'credits_usage_limit' => 100,
            'max_businesses' => 1,
            'max_campaigns' => 5,
            'max_landing_pages' => 5,
            'max_qr_codes' => 15,
            'max_templates' => 5,
            'max_storage_size_mb' => 512,
            'max_file_size_mb' => 32,
            'max_team_members' => 1,
            'ai_studio_repurpose' => false,
            'ai_studio_content_planner' => false,
            'ai_studio_image' => false,
            'email_automation' => true,
            'max_email_automations' => 1,
            'max_email_templates' => 3,
            'emails_per_month' => 200,
            'automation_delay' => true,
            'loyalty_stamp_cards' => true,
            'max_loyalty_cards' => 1,
            'max_loyalty_customers' => 100,
            'crm_activity_retention_days' => 180,
        ]);
    }

    protected function starterPermissions(int $credits): array
    {
        return $this->permissions([
            'credits_usage_limit' => $credits,
            'max_businesses' => 1,
            'max_campaigns' => 20,
            'max_landing_pages' => 10,
            'max_qr_codes' => 50,
            'max_templates' => 20,
            'max_storage_size_mb' => 512,
            'max_file_size_mb' => 50,
            'max_team_members' => 2,
            'ai_studio_repurpose' => false,
            'ai_studio_content_planner' => false,
            'ai_studio_image' => false,
            'email_automation' => true,
            'max_email_automations' => 2,
            'max_email_templates' => 5,
            'emails_per_month' => 500,
            'automation_delay' => true,
            'google_business' => true,
            'max_google_business_connections' => 1,
            'max_google_business_locations' => 1,
            'google_review_sync' => true,
            'google_business_insights' => true,
            'loyalty_stamp_cards' => true,
            'max_loyalty_cards' => 1,
            'max_loyalty_customers' => 200,
            'advanced_crm' => true,
            'customer_tags' => 10,
            'customer_segments' => 3,
            'customer_tasks' => 20,
            'crm_activity_retention_days' => 365,
        ]);
    }

    protected function growthPermissions(int $credits): array
    {
        return $this->permissions([
            'credits_usage_limit' => $credits,
            'max_businesses' => 3,
            'max_campaigns' => 75,
            'max_landing_pages' => 50,
            'max_qr_codes' => 200,
            'max_templates' => 100,
            'max_storage_size_mb' => 2048,
            'max_file_size_mb' => 128,
            'max_team_members' => 5,
            'ai_studio_image' => false,
            'email_automation' => true,
            'max_email_automations' => 10,
            'max_email_templates' => 20,
            'emails_per_month' => 3000,
            'automation_delay' => true,
            'automation_conditions' => true,
            'google_business' => true,
            'max_google_business_connections' => 2,
            'max_google_business_locations' => 3,
            'google_review_sync' => true,
            'google_review_reply' => true,
            'google_business_insights' => true,
            'google_business_posts' => true,
            'whatsapp_notification' => true,
            'max_whatsapp_notifications' => 3,
            'max_whatsapp_templates' => 10,
            'whatsapp_messages_per_month' => 1000,
            'whatsapp_cloud_api' => true,
            'whatsapp_template_messages' => true,
            'webhook_automation' => true,
            'max_webhook_automations' => 5,
            'webhooks_per_month' => 5000,
            'webhook_retry' => true,
            'loyalty_stamp_cards' => true,
            'max_loyalty_cards' => 5,
            'max_loyalty_customers' => 1000,
            'max_referral_campaigns' => 3,
            'loyalty_rewards' => true,
            'advanced_crm' => true,
            'customer_tags' => 50,
            'customer_segments' => 20,
            'customer_tasks' => 200,
            'crm_automations' => 10,
            'crm_activity_retention_days' => 365,
            'qr_custom_domains' => true,
            'max_custom_domains' => 1,
            'affiliate' => true,
        ]);
    }

    protected function proPermissions(int $credits): array
    {
        return $this->permissions([
            'credits_usage_limit' => $credits,
            'max_businesses' => 10,
            'max_campaigns' => 300,
            'max_landing_pages' => 200,
            'max_qr_codes' => 1000,
            'max_templates' => 300,
            'max_storage_size_mb' => 10240,
            'max_file_size_mb' => 512,
            'max_team_members' => 20,
            'remove_branding' => true,
            'email_automation' => true,
            'max_email_automations' => 50,
            'max_email_templates' => 200,
            'emails_per_month' => 20000,
            'automation_delay' => true,
            'automation_conditions' => true,
            'google_business' => true,
            'max_google_business_connections' => 5,
            'max_google_business_locations' => 20,
            'google_review_sync' => true,
            'google_review_reply' => true,
            'google_business_insights' => true,
            'google_business_posts' => true,
            'whatsapp_notification' => true,
            'max_whatsapp_notifications' => 30,
            'max_whatsapp_templates' => 100,
            'whatsapp_messages_per_month' => 10000,
            'whatsapp_cloud_api' => true,
            'whatsapp_template_messages' => true,
            'webhook_automation' => true,
            'max_webhook_automations' => 30,
            'webhooks_per_month' => 50000,
            'webhook_custom_headers' => true,
            'webhook_retry' => true,
            'loyalty_stamp_cards' => true,
            'max_loyalty_cards' => 30,
            'max_loyalty_customers' => 10000,
            'max_referral_campaigns' => 20,
            'loyalty_rewards' => true,
            'loyalty_staff_redeem' => true,
            'advanced_crm' => true,
            'customer_tags' => 500,
            'customer_segments' => 200,
            'customer_tasks' => 2000,
            'crm_automations' => 100,
            'crm_activity_retention_days' => 730,
            'qr_custom_domains' => true,
            'max_custom_domains' => 5,
            'affiliate' => true,
        ]);
    }

    protected function partnerPermissions(int $credits): array
    {
        return $this->permissions([
            'credits_usage_limit' => $credits,
            'max_businesses' => 100,
            'max_campaigns' => 2000,
            'max_landing_pages' => 1000,
            'max_qr_codes' => 5000,
            'max_templates' => 2000,
            'max_storage_size_mb' => 51200,
            'max_file_size_mb' => 1024,
            'max_team_members' => 100,
            'remove_branding' => true,
            'email_automation' => true,
            'max_email_automations' => 300,
            'max_email_templates' => 1000,
            'emails_per_month' => 200000,
            'automation_delay' => true,
            'automation_conditions' => true,
            'google_business' => true,
            'max_google_business_connections' => 30,
            'max_google_business_locations' => 150,
            'google_review_sync' => true,
            'google_review_reply' => true,
            'google_business_insights' => true,
            'google_business_posts' => true,
            'whatsapp_notification' => true,
            'max_whatsapp_notifications' => 200,
            'max_whatsapp_templates' => 500,
            'whatsapp_messages_per_month' => 100000,
            'whatsapp_cloud_api' => true,
            'whatsapp_template_messages' => true,
            'webhook_automation' => true,
            'max_webhook_automations' => 300,
            'webhooks_per_month' => 500000,
            'webhook_custom_headers' => true,
            'webhook_retry' => true,
            'loyalty_stamp_cards' => true,
            'max_loyalty_cards' => 300,
            'max_loyalty_customers' => 100000,
            'max_referral_campaigns' => 300,
            'loyalty_rewards' => true,
            'loyalty_staff_redeem' => true,
            'advanced_crm' => true,
            'customer_tags' => 5000,
            'customer_segments' => 2000,
            'customer_tasks' => 50000,
            'crm_automations' => 500,
            'crm_activity_retention_days' => 1095,
            'qr_custom_domains' => true,
            'max_custom_domains' => 50,
            'affiliate' => true,
        ]);
    }

    protected function permissions(array $overrides): array
    {
        return array_replace([
            'credits_usage' => true,
            'credits_usage_limit' => 0,
            'credit_cost_ai_studio_generate_captions' => 1,
            'credit_cost_ai_studio_repurpose_content' => 2,
            'credit_cost_ai_studio_plan_calendar' => 2,
            'credit_cost_ai_studio_review_reply' => 1,
            'credit_cost_ai_studio_generate_image' => 10,
            'mlhub' => true,
            'max_businesses' => 0,
            'max_campaigns' => 0,
            'max_landing_pages' => 0,
            'max_qr_codes' => 0,
            'max_templates' => 0,
            'remove_branding' => false,
            'files' => true,
            'file_google_drive' => false,
            'file_dropbox' => false,
            'file_onedrive' => false,
            'image_editor' => true,
            'search_media_online' => true,
            'max_storage_size_mb' => 0,
            'max_file_size_mb' => 0,
            'support' => true,
            'ai_studio' => true,
            'ai_studio_caption_generator' => true,
            'ai_studio_repurpose' => true,
            'ai_studio_content_planner' => true,
            'ai_studio_image' => true,
            'teams' => true,
            'max_team_members' => 0,
            'affiliate' => false,
            'advanced_crm' => false,
            'customer_tags' => 0,
            'customer_segments' => 0,
            'customer_tasks' => 0,
            'crm_automations' => 0,
            'crm_activity_retention_days' => 0,
            'google_business' => false,
            'max_google_business_connections' => 0,
            'max_google_business_locations' => 0,
            'google_review_sync' => false,
            'google_review_reply' => false,
            'google_business_insights' => false,
            'google_business_posts' => false,
            'email_automation' => false,
            'max_email_automations' => 0,
            'max_email_templates' => 0,
            'emails_per_month' => 0,
            'automation_delay' => false,
            'automation_conditions' => false,
            'whatsapp_notification' => false,
            'max_whatsapp_notifications' => 0,
            'max_whatsapp_templates' => 0,
            'whatsapp_messages_per_month' => 0,
            'whatsapp_cloud_api' => false,
            'whatsapp_template_messages' => false,
            'webhook_automation' => false,
            'max_webhook_automations' => 0,
            'webhooks_per_month' => 0,
            'webhook_custom_headers' => false,
            'webhook_retry' => false,
            'loyalty_stamp_cards' => false,
            'max_loyalty_cards' => 0,
            'max_loyalty_customers' => 0,
            'max_referral_campaigns' => 0,
            'loyalty_rewards' => false,
            'loyalty_staff_redeem' => false,
            'qr_custom_domains' => false,
            'max_custom_domains' => 0,
            'channels' => false,
            'max_channels' => 0,
            'channel_count_mode' => 'entire_social_network',
            'channel_facebook_pages' => false,
            'channel_instagram_profiles' => false,
            'publishing' => false,
            'max_posts_per_month' => 0,
            'facebook_page' => false,
            'instagram_profile' => false,
            'campaign_publishing' => false,
            'label_publishing' => false,
            'bulk_posts' => false,
            'max_bulk_posts_per_csv' => 0,
            'groups' => false,
            'ai_publishing' => false,
            'max_ai_publishing_posts_per_month' => 0,
            'ai_publishing_generate_content' => 0,
            'ai_publishing_generate_image' => 0,
            'ai_publishing_generate_images' => false,
            'ai_publishing_user_schedule_time' => false,
            'rss_schedules' => false,
            'watermark' => false,
        ], $overrides);
    }

    protected function normalizePermissions(array $permissions): array
    {
        /** @var PlanPermissionSchema $schema */
        $schema = app(PlanPermissionSchema::class);
        $defaults = $schema->normalize($schema->mapToState([]));

        return array_replace($defaults, $permissions);
    }
}
