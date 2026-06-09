<?php

namespace Database\Seeders;

use Database\Support\IdSequence;
use Illuminate\Database\Seeder;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminPlans\Support\PlanPermissionSchema;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->plans() as $index => $plan) {
            $plan['permissions'] = $this->normalizePermissions($plan['permissions'] ?? $this->fullPermissions());

            $record = AdminPlan::query()->firstOrNew(['slug' => $plan['slug']]);

            if (! $record->exists && ($id = IdSequence::idForNewSeed($index)) !== null) {
                $record->id = $id;
            }

            $record->fill($plan)->save();
        }
    }

    protected function plans(): array
    {
        return [
            [
                'name' => 'Starter — Monthly',
                'slug' => 'starter-monthly',
                'status' => true,
                'featured' => false,
                'currency' => '₫',
                'price' => 490000,
                'type' => 1,
                'free_plan' => false,
                'trial_day' => 7,
                'position' => 10,
                'desc' => 'For new local businesses starting campaign pages, QR codes, and review growth.',
                'permissions' => $this->starterPermissions(),
            ],
            [
                'name' => 'Growth — Monthly',
                'slug' => 'growth-monthly',
                'status' => true,
                'featured' => true,
                'currency' => '₫',
                'price' => 990000,
                'type' => 1,
                'free_plan' => false,
                'trial_day' => 10,
                'position' => 20,
                'desc' => 'For growing local businesses running recurring campaigns, bookings, and reports.',
                'permissions' => $this->growthPermissions(),
            ],
            [
                'name' => 'Professional — Monthly',
                'slug' => 'agency-monthly',
                'status' => true,
                'featured' => false,
                'currency' => '₫',
                'price' => 1990000,
                'type' => 1,
                'free_plan' => false,
                'trial_day' => 14,
                'position' => 30,
                'desc' => 'For teams managing multiple brands, campaigns, and full marketing pipelines.',
                'permissions' => $this->agencyPermissions(),
            ],
            [
                'name' => 'Starter — Yearly',
                'slug' => 'starter-yearly',
                'status' => true,
                'featured' => false,
                'currency' => '₫',
                'price' => 4900000,
                'type' => 2,
                'free_plan' => false,
                'trial_day' => 14,
                'position' => 10,
                'desc' => 'Annual plan for local businesses that want stable costs and steady marketing growth.',
                'permissions' => $this->starterPermissions(),
            ],
            [
                'name' => 'Growth — Yearly',
                'slug' => 'growth-yearly',
                'status' => true,
                'featured' => true,
                'currency' => '₫',
                'price' => 9900000,
                'type' => 2,
                'free_plan' => false,
                'trial_day' => 21,
                'position' => 20,
                'desc' => 'Yearly plan for teams scaling campaigns, automation, and AI-driven local workflows.',
                'permissions' => $this->growthPermissions(),
            ],
            [
                'name' => 'Professional — Yearly',
                'slug' => 'agency-yearly',
                'status' => true,
                'featured' => false,
                'currency' => '₫',
                'price' => 19900000,
                'type' => 2,
                'free_plan' => false,
                'trial_day' => 30,
                'position' => 30,
                'desc' => 'Full-year plan for agencies running many workspaces and large-scale marketing ops.',
                'permissions' => $this->agencyPermissions(),
            ],
            [
                'name' => 'Starter — Lifetime',
                'slug' => 'starter-lifetime',
                'status' => true,
                'featured' => false,
                'currency' => '₫',
                'price' => 7900000,
                'type' => 3,
                'free_plan' => false,
                'trial_day' => 30,
                'position' => 10,
                'desc' => 'One-time payment for small shops building a long-term MLHUB local growth base.',
                'permissions' => $this->starterPermissions(),
            ],
            [
                'name' => 'Growth — Lifetime',
                'slug' => 'growth-lifetime',
                'status' => true,
                'featured' => true,
                'currency' => '₫',
                'price' => 14900000,
                'type' => 3,
                'free_plan' => false,
                'trial_day' => 45,
                'position' => 20,
                'desc' => 'Lifetime access for active businesses needing AI, automation, and higher volume.',
                'permissions' => $this->growthPermissions(),
            ],
            [
                'name' => 'Professional — Lifetime',
                'slug' => 'agency-lifetime',
                'status' => true,
                'featured' => false,
                'currency' => '₫',
                'price' => 29900000,
                'type' => 3,
                'free_plan' => false,
                'trial_day' => 60,
                'position' => 30,
                'desc' => 'Lifetime plan for operators managing many clients, assets, and automations.',
                'permissions' => $this->agencyPermissions(),
            ],
        ];
    }

    protected function starterPermissions(): array
    {
        return [
            'credits_usage' => true,
            'credits_usage_limit' => 300,
            'localboost' => true,
            'max_businesses' => 3,
            'max_campaigns' => 10,
            'max_landing_pages' => 10,
            'max_qr_codes' => 25,
            'max_templates' => 10,
            'qr_custom_domains' => false,
            'max_custom_domains' => 0,
            'remove_branding' => false,
            'ai_publishing_generate_content' => 1,
            'ai_publishing_generate_image' => 3,
            'channels' => true,
            'max_channels' => 6,
            'channel_count_mode' => 'entire_social_network',
            'channel_facebook_pages' => true,
            'channel_instagram_profiles' => true,
            'publishing' => true,
            'max_posts_per_month' => 120,
            'facebook_page' => true,
            'instagram_profile' => true,
            'campaign_publishing' => true,
            'label_publishing' => false,
            'files' => true,
            'file_google_drive' => false,
            'file_dropbox' => false,
            'file_onedrive' => false,
            'image_editor' => true,
            'max_storage_size_mb' => 2048,
            'max_file_size_mb' => 128,
            'bulk_posts' => false,
            'max_bulk_posts_per_csv' => 0,
            'groups' => false,
            'ai_publishing' => false,
            'max_ai_publishing_posts_per_month' => 0,
            'ai_publishing_generate_images' => false,
            'ai_publishing_user_schedule_time' => false,
            'support' => true,
            'ai_studio' => true,
            'ai_studio_caption_generator' => true,
            'ai_studio_content_planner' => true,
            'ai_studio_repurpose' => false,
            'ai_studio_image' => false,
            'email_automation' => false,
            'max_email_automations' => 0,
            'max_email_templates' => 0,
            'emails_per_month' => 0,
            'automation_delay' => false,
            'automation_conditions' => false,
            'google_business' => false,
            'max_google_business_connections' => 0,
            'max_google_business_locations' => 0,
            'google_review_sync' => false,
            'google_review_reply' => false,
            'google_business_insights' => false,
            'google_business_posts' => false,
            'advanced_crm' => false,
            'customer_tags' => 0,
            'customer_segments' => 0,
            'customer_tasks' => 0,
            'crm_automations' => 0,
            'crm_activity_retention_days' => 90,
            'teams' => true,
            'max_team_members' => 2,
            'affiliate' => false,
            'rss_schedules' => false,
            'watermark' => false,
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
        ];
    }

    protected function growthPermissions(): array
    {
        return [
            'credits_usage' => true,
            'credits_usage_limit' => 2000,
            'localboost' => true,
            'max_businesses' => 10,
            'max_campaigns' => 50,
            'max_landing_pages' => 50,
            'max_qr_codes' => 150,
            'max_templates' => 50,
            'qr_custom_domains' => true,
            'max_custom_domains' => 3,
            'remove_branding' => true,
            'ai_publishing_generate_content' => 1,
            'ai_publishing_generate_image' => 3,
            'channels' => true,
            'max_channels' => 20,
            'channel_count_mode' => 'entire_social_network',
            'channel_facebook_pages' => true,
            'channel_instagram_profiles' => true,
            'publishing' => true,
            'max_posts_per_month' => 600,
            'facebook_page' => true,
            'instagram_profile' => true,
            'campaign_publishing' => true,
            'label_publishing' => true,
            'files' => true,
            'file_google_drive' => true,
            'file_dropbox' => false,
            'file_onedrive' => false,
            'image_editor' => true,
            'max_storage_size_mb' => 10240,
            'max_file_size_mb' => 512,
            'bulk_posts' => true,
            'max_bulk_posts_per_csv' => 500,
            'groups' => true,
            'ai_publishing' => true,
            'max_ai_publishing_posts_per_month' => 80,
            'ai_publishing_generate_images' => true,
            'ai_publishing_user_schedule_time' => true,
            'support' => true,
            'ai_studio' => true,
            'ai_studio_caption_generator' => true,
            'ai_studio_content_planner' => true,
            'ai_studio_repurpose' => true,
            'ai_studio_image' => true,
            'email_automation' => true,
            'max_email_automations' => 5,
            'max_email_templates' => 10,
            'emails_per_month' => 3000,
            'automation_delay' => true,
            'automation_conditions' => false,
            'google_business' => true,
            'max_google_business_connections' => 2,
            'max_google_business_locations' => 5,
            'google_review_sync' => true,
            'google_review_reply' => true,
            'google_business_insights' => true,
            'google_business_posts' => false,
            'advanced_crm' => true,
            'customer_tags' => 25,
            'customer_segments' => 10,
            'customer_tasks' => 100,
            'crm_automations' => 5,
            'crm_activity_retention_days' => 365,
            'teams' => true,
            'max_team_members' => 5,
            'affiliate' => true,
            'rss_schedules' => true,
            'watermark' => true,
            'whatsapp_notification' => true,
            'max_whatsapp_notifications' => 10,
            'max_whatsapp_templates' => 20,
            'whatsapp_messages_per_month' => 1000,
            'whatsapp_cloud_api' => true,
            'whatsapp_template_messages' => true,
            'webhook_automation' => true,
            'max_webhook_automations' => 10,
            'webhooks_per_month' => 5000,
            'webhook_custom_headers' => true,
            'webhook_retry' => true,
            'loyalty_stamp_cards' => false,
            'max_loyalty_cards' => 5,
            'max_loyalty_customers' => 500,
            'max_referral_campaigns' => 3,
            'loyalty_rewards' => true,
            'loyalty_staff_redeem' => false,
        ];
    }

    protected function agencyPermissions(): array
    {
        return [
            'credits_usage' => true,
            'credits_usage_limit' => -1,
            'localboost' => true,
            'max_businesses' => -1,
            'max_campaigns' => -1,
            'max_landing_pages' => -1,
            'max_qr_codes' => -1,
            'max_templates' => -1,
            'qr_custom_domains' => true,
            'max_custom_domains' => -1,
            'remove_branding' => true,
            'ai_publishing_generate_content' => 1,
            'ai_publishing_generate_image' => 3,
            'channels' => true,
            'max_channels' => -1,
            'channel_count_mode' => 'entire_social_network',
            'channel_facebook_pages' => true,
            'channel_instagram_profiles' => true,
            'publishing' => true,
            'max_posts_per_month' => -1,
            'facebook_page' => true,
            'instagram_profile' => true,
            'campaign_publishing' => true,
            'label_publishing' => true,
            'files' => true,
            'file_google_drive' => true,
            'file_dropbox' => true,
            'file_onedrive' => true,
            'image_editor' => true,
            'max_storage_size_mb' => 51200,
            'max_file_size_mb' => 2048,
            'bulk_posts' => true,
            'max_bulk_posts_per_csv' => -1,
            'groups' => true,
            'ai_publishing' => true,
            'max_ai_publishing_posts_per_month' => -1,
            'ai_publishing_generate_images' => true,
            'ai_publishing_user_schedule_time' => true,
            'support' => true,
            'ai_studio' => true,
            'ai_studio_caption_generator' => true,
            'ai_studio_content_planner' => true,
            'ai_studio_repurpose' => true,
            'ai_studio_image' => true,
            'email_automation' => true,
            'max_email_automations' => -1,
            'max_email_templates' => -1,
            'emails_per_month' => -1,
            'automation_delay' => true,
            'automation_conditions' => true,
            'google_business' => true,
            'max_google_business_connections' => -1,
            'max_google_business_locations' => -1,
            'google_review_sync' => true,
            'google_review_reply' => true,
            'google_business_insights' => true,
            'google_business_posts' => true,
            'advanced_crm' => true,
            'customer_tags' => -1,
            'customer_segments' => -1,
            'customer_tasks' => -1,
            'crm_automations' => -1,
            'crm_activity_retention_days' => -1,
            'teams' => true,
            'max_team_members' => 15,
            'affiliate' => true,
            'rss_schedules' => true,
            'watermark' => true,
            'whatsapp_notification' => true,
            'max_whatsapp_notifications' => -1,
            'max_whatsapp_templates' => -1,
            'whatsapp_messages_per_month' => -1,
            'whatsapp_cloud_api' => true,
            'whatsapp_template_messages' => true,
            'webhook_automation' => true,
            'max_webhook_automations' => -1,
            'webhooks_per_month' => -1,
            'webhook_custom_headers' => true,
            'webhook_retry' => true,
            'loyalty_stamp_cards' => true,
            'max_loyalty_cards' => -1,
            'max_loyalty_customers' => -1,
            'max_referral_campaigns' => -1,
            'loyalty_rewards' => true,
            'loyalty_staff_redeem' => true,
        ];
    }

    protected function normalizePermissions(array $permissions): array
    {
        /** @var PlanPermissionSchema $schema */
        $schema = app(PlanPermissionSchema::class);
        $defaults = $schema->normalize($schema->mapToState([]));
        $normalized = array_replace($defaults, $permissions);

        $normalized = $this->synchronizeParentFlags($normalized);

        if (($normalized['max_channels'] ?? 0) === -1) {
            $normalized = $this->enableAllChannelCapabilities($normalized);
            $normalized = $this->enableAllPublishingCapabilities($normalized);
        }

        return $normalized;
    }

    protected function fullPermissions(): array
    {
        /** @var PlanPermissionSchema $schema */
        $schema = app(PlanPermissionSchema::class);

        $state = [];

        foreach ($schema->definitions() as $definition) {
            $key = (string) $definition['key'];
            $type = $definition['type'] ?? 'toggle';

            if ($type === 'toggle') {
                $state[$key] = '1';

                continue;
            }

            $state[$key] = [
                'enabled' => true,
            ];

            foreach ($definition['fields'] ?? [] as $field) {
                $fieldKey = (string) $field['key'];
                $fieldType = $field['type'] ?? 'boolean';

                if ($fieldType === 'checkbox_list') {
                    $state[$key][$fieldKey] = collect($field['options'] ?? [])
                        ->pluck('key')
                        ->map(fn ($value) => (string) $value)
                        ->values()
                        ->all();

                    continue;
                }

                if ($fieldType === 'number') {
                    $state[$key][$fieldKey] = -1;

                    continue;
                }

                if ($fieldType === 'choice') {
                    $state[$key][$fieldKey] = (string) ($field['default'] ?? '');

                    continue;
                }

                $state[$key][$fieldKey] = true;
            }
        }

        return $this->normalizePermissions($schema->normalize($state));
    }

    protected function synchronizeParentFlags(array $permissions): array
    {
        $permissions['channels'] = $this->truthy($permissions['channels'] ?? false)
            || $this->truthy($permissions['max_channels'] ?? 0)
            || $this->hasTruthyPrefixedKey($permissions, 'channel_');

        $permissions['publishing'] = $this->truthy($permissions['publishing'] ?? false)
            || $this->truthy($permissions['max_posts_per_month'] ?? 0)
            || $this->hasPublishingCapability($permissions)
            || $this->truthy($permissions['campaign_publishing'] ?? false)
            || $this->truthy($permissions['label_publishing'] ?? false);

        $permissions['ai_publishing'] = $this->truthy($permissions['ai_publishing'] ?? false)
            || $this->truthy($permissions['max_ai_publishing_posts_per_month'] ?? 0)
            || $this->truthy($permissions['ai_publishing_generate_images'] ?? false)
            || $this->truthy($permissions['ai_publishing_user_schedule_time'] ?? false);

        $permissions['ai_studio'] = $this->truthy($permissions['ai_studio'] ?? false)
            || $this->hasTruthyPrefixedKey($permissions, 'ai_studio_');

        $permissions['automation'] = $this->truthy($permissions['automation'] ?? false)
            || $this->truthy($permissions['max_automation_api_keys'] ?? 0)
            || $this->truthy($permissions['max_automation_webhooks'] ?? 0);

        $permissions['email_automation'] = $this->truthy($permissions['email_automation'] ?? false)
            || $this->truthy($permissions['max_email_automations'] ?? 0)
            || $this->truthy($permissions['emails_per_month'] ?? 0);

        $permissions['google_business'] = $this->truthy($permissions['google_business'] ?? false)
            || $this->truthy($permissions['max_google_business_connections'] ?? 0)
            || $this->truthy($permissions['max_google_business_locations'] ?? 0)
            || $this->truthy($permissions['max_businesses'] ?? 0);

        $permissions['advanced_crm'] = $this->truthy($permissions['advanced_crm'] ?? false)
            || $this->truthy($permissions['customer_tags'] ?? 0)
            || $this->truthy($permissions['customer_segments'] ?? 0)
            || $this->truthy($permissions['customer_tasks'] ?? 0)
            || $this->truthy($permissions['crm_automations'] ?? 0);

        $permissions['whatsapp_notification'] = $this->truthy($permissions['whatsapp_notification'] ?? false)
            || $this->truthy($permissions['max_whatsapp_notifications'] ?? 0)
            || $this->truthy($permissions['whatsapp_messages_per_month'] ?? 0);

        $permissions['webhook_automation'] = $this->truthy($permissions['webhook_automation'] ?? false)
            || $this->truthy($permissions['max_webhook_automations'] ?? 0)
            || $this->truthy($permissions['webhooks_per_month'] ?? 0);

        $permissions['qr_custom_domains'] = $this->truthy($permissions['qr_custom_domains'] ?? false)
            || $this->truthy($permissions['max_custom_domains'] ?? 0);

        $permissions['teams'] = $this->truthy($permissions['teams'] ?? false)
            || $this->truthy($permissions['max_team_members'] ?? 0);

        return $permissions;
    }

    protected function enableAllChannelCapabilities(array $permissions): array
    {
        foreach ($this->channelCapabilities() as $key => $capability) {
            $permissionKey = match ((string) $key) {
                'facebook_page' => 'channel_facebook_pages',
                'instagram_profile' => 'channel_instagram_profiles',
                default => 'channel_'.str_replace('-', '_', (string) $key),
            };

            $permissions[$permissionKey] = true;
        }

        return $permissions;
    }

    protected function enableAllPublishingCapabilities(array $permissions): array
    {
        foreach ($this->channelCapabilities() as $key => $capability) {
            if (! (bool) ($capability['supports_publishing'] ?? true)) {
                continue;
            }

            $permissions[(string) $key] = true;
        }

        return $permissions;
    }

    protected function hasTruthyPrefixedKey(array $permissions, string $prefix): bool
    {
        foreach ($permissions as $key => $value) {
            if (str_starts_with((string) $key, $prefix) && $this->truthy($value)) {
                return true;
            }
        }

        return false;
    }

    protected function hasPublishingCapability(array $permissions): bool
    {
        foreach (array_keys($this->channelCapabilities()) as $key) {
            if ($this->truthy($permissions[(string) $key] ?? false)) {
                return true;
            }
        }

        return false;
    }

    protected function channelCapabilities(): array
    {
        $catalogClass = 'Modules\\AppChannels\\Support\\ChannelCatalog';

        if (! class_exists($catalogClass)) {
            return [];
        }

        $catalog = app($catalogClass);

        if (! method_exists($catalog, 'capabilities')) {
            return [];
        }

        return (array) $catalog->capabilities();
    }

    protected function truthy(mixed $value): bool
    {
        if (is_numeric($value)) {
            return (float) $value !== 0.0;
        }

        return in_array($value, [true, 1, '1'], true);
    }
}
