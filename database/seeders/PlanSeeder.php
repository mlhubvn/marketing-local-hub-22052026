<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminPlans\Support\PlanPermissionSchema;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->plans() as $plan) {
            $plan['permissions'] = $this->normalizePermissions($plan['permissions'] ?? $this->fullPermissions());

            AdminPlan::query()->updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }

    protected function plans(): array
    {
        return [
            [
                'name' => 'Starter Monthly',
                'slug' => 'starter-monthly',
                'status' => true,
                'featured' => false,
                'currency' => '$',
                'price' => 19,
                'type' => 1,
                'free_plan' => false,
                'trial_day' => 7,
                'position' => 10,
                'desc' => 'For solo creators and small brands starting with consistent Facebook and Instagram publishing.',
                'permissions' => $this->starterPermissions(),
            ],
            [
                'name' => 'Growth Monthly',
                'slug' => 'growth-monthly',
                'status' => true,
                'featured' => true,
                'currency' => '$',
                'price' => 39,
                'type' => 1,
                'free_plan' => false,
                'trial_day' => 10,
                'position' => 20,
                'desc' => 'For teams running recurring campaigns, queues, bulk publishing, and smarter content workflows.',
                'permissions' => $this->growthPermissions(),
            ],
            [
                'name' => 'Agency Monthly',
                'slug' => 'agency-monthly',
                'status' => true,
                'featured' => false,
                'currency' => '$',
                'price' => 79,
                'type' => 1,
                'free_plan' => false,
                'trial_day' => 14,
                'position' => 30,
                'desc' => 'For agencies and high-volume operators managing multiple brands, workspaces, and publishing pipelines.',
                'permissions' => $this->agencyPermissions(),
            ],
            [
                'name' => 'Starter Yearly',
                'slug' => 'starter-yearly',
                'status' => true,
                'featured' => false,
                'currency' => '$',
                'price' => 190,
                'type' => 2,
                'free_plan' => false,
                'trial_day' => 14,
                'position' => 10,
                'desc' => 'Yearly starter access for predictable publishing with better value and room to grow.',
                'permissions' => $this->starterPermissions(),
            ],
            [
                'name' => 'Growth Yearly',
                'slug' => 'growth-yearly',
                'status' => true,
                'featured' => true,
                'currency' => '$',
                'price' => 390,
                'type' => 2,
                'free_plan' => false,
                'trial_day' => 21,
                'position' => 20,
                'desc' => 'Best for scaling teams that want yearly savings across automation, scheduling, and team workflows.',
                'permissions' => $this->growthPermissions(),
            ],
            [
                'name' => 'Agency Yearly',
                'slug' => 'agency-yearly',
                'status' => true,
                'featured' => false,
                'currency' => '$',
                'price' => 790,
                'type' => 2,
                'free_plan' => false,
                'trial_day' => 30,
                'position' => 30,
                'desc' => 'Full-year agency capacity for larger workspaces, more channels, and advanced StackPosts operations.',
                'permissions' => $this->agencyPermissions(),
            ],
            [
                'name' => 'Starter Lifetime',
                'slug' => 'starter-lifetime',
                'status' => true,
                'featured' => false,
                'currency' => '$',
                'price' => 149,
                'type' => 3,
                'free_plan' => false,
                'trial_day' => 30,
                'position' => 10,
                'desc' => 'One-time access for lean teams that want a durable StackPosts publishing setup.',
                'permissions' => $this->starterPermissions(),
            ],
            [
                'name' => 'Growth Lifetime',
                'slug' => 'growth-lifetime',
                'status' => true,
                'featured' => true,
                'currency' => '$',
                'price' => 299,
                'type' => 3,
                'free_plan' => false,
                'trial_day' => 45,
                'position' => 20,
                'desc' => 'One-time purchase for active teams that need automation, AI workflows, and stronger publishing throughput.',
                'permissions' => $this->growthPermissions(),
            ],
            [
                'name' => 'Agency Lifetime',
                'slug' => 'agency-lifetime',
                'status' => true,
                'featured' => false,
                'currency' => '$',
                'price' => 599,
                'type' => 3,
                'free_plan' => false,
                'trial_day' => 60,
                'position' => 30,
                'desc' => 'Lifetime agency tier for heavy publishing teams managing many clients, assets, and automations.',
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
