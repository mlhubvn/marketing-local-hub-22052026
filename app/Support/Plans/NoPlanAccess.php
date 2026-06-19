<?php

namespace App\Support\Plans;

class NoPlanAccess
{
    public static function enabled(): bool
    {
        return (bool) config('mlhub.no_plan_access.enabled', true);
    }

    public static function label(): string
    {
        return (string) config('mlhub.no_plan_access.label', 'MLHUB Free');
    }

    /**
     * @return array<string, mixed>
     */
    public static function permissions(): array
    {
        $permissions = config('mlhub.no_plan_access.permissions', []);

        return is_array($permissions) ? $permissions : [];
    }

    /**
     * Ma trận quyền user plan_id null — đọc từ env MLHUB_NO_PLAN_* (Coolify).
     *
     * @return array<string, mixed>
     */
    public static function permissionsFromEnv(): array
    {
        return [
            'credits_usage' => self::envBool('MLHUB_NO_PLAN_CREDITS_USAGE', true),
            'credits_usage_limit' => self::envInt('MLHUB_NO_PLAN_CREDITS_LIMIT', 100),

            'mlhub' => self::envBool('MLHUB_NO_PLAN_MLHUB', self::envBool('MLHUB_NO_PLAN_LOCALBOOST', true)),
            'max_businesses' => self::envInt('MLHUB_NO_PLAN_MAX_BUSINESSES', 1),
            'max_campaigns' => self::envInt('MLHUB_NO_PLAN_MAX_CAMPAIGNS', 5),
            'max_landing_pages' => self::envInt('MLHUB_NO_PLAN_MAX_LANDING_PAGES', 5),
            'max_qr_codes' => self::envInt('MLHUB_NO_PLAN_MAX_QR_CODES', 15),
            'max_templates' => self::envInt('MLHUB_NO_PLAN_MAX_TEMPLATES', 5),
            'remove_branding' => self::envBool('MLHUB_NO_PLAN_REMOVE_BRANDING', false),

            'files' => self::envBool('MLHUB_NO_PLAN_FILES', true),
            'file_google_drive' => self::envBool('MLHUB_NO_PLAN_FILE_GOOGLE_DRIVE', true),
            'file_dropbox' => self::envBool('MLHUB_NO_PLAN_FILE_DROPBOX', true),
            'file_onedrive' => self::envBool('MLHUB_NO_PLAN_FILE_ONEDRIVE', true),
            'image_editor' => self::envBool('MLHUB_NO_PLAN_IMAGE_EDITOR', true),
            'search_media_online' => self::envBool('MLHUB_NO_PLAN_SEARCH_MEDIA_ONLINE', true),
            'max_storage_size_mb' => self::envInt('MLHUB_NO_PLAN_MAX_STORAGE_MB', 512),
            'max_file_size_mb' => self::envInt('MLHUB_NO_PLAN_MAX_FILE_SIZE_MB', 32),

            'support' => self::envBool('MLHUB_NO_PLAN_SUPPORT', true),

            'ai_studio' => self::envBool('MLHUB_NO_PLAN_AI_STUDIO', true),
            'ai_studio_caption_generator' => self::envBool('MLHUB_NO_PLAN_AI_CAPTION', true),
            'ai_studio_content_planner' => self::envBool('MLHUB_NO_PLAN_AI_CONTENT_PLANNER', true),
            'ai_studio_repurpose' => self::envBool('MLHUB_NO_PLAN_AI_REPURPOSE', true),
            'ai_studio_image' => self::envBool('MLHUB_NO_PLAN_AI_IMAGE', true),

            'teams' => self::envBool('MLHUB_NO_PLAN_TEAMS', true),
            'max_team_members' => self::envInt('MLHUB_NO_PLAN_MAX_TEAM_MEMBERS', 2),

            'affiliate' => self::envBool('MLHUB_NO_PLAN_AFFILIATE', true),

            'advanced_crm' => self::envBool('MLHUB_NO_PLAN_ADVANCED_CRM', true),
            'customer_tags' => self::envInt('MLHUB_NO_PLAN_CRM_TAGS', 10),
            'customer_segments' => self::envInt('MLHUB_NO_PLAN_CRM_SEGMENTS', 3),
            'customer_tasks' => self::envInt('MLHUB_NO_PLAN_CRM_TASKS', 20),
            'crm_automations' => self::envInt('MLHUB_NO_PLAN_CRM_AUTOMATIONS', 2),
            'crm_activity_retention_days' => self::envInt('MLHUB_NO_PLAN_CRM_RETENTION_DAYS', 90),

            'google_business' => self::envBool('MLHUB_NO_PLAN_GOOGLE_BUSINESS', true),
            'max_google_business_connections' => self::envInt('MLHUB_NO_PLAN_GOOGLE_CONNECTIONS', 1),
            'max_google_business_locations' => self::envInt('MLHUB_NO_PLAN_GOOGLE_LOCATIONS', 1),
            'google_review_sync' => self::envBool('MLHUB_NO_PLAN_GOOGLE_REVIEW_SYNC', true),
            'google_review_reply' => self::envBool('MLHUB_NO_PLAN_GOOGLE_REVIEW_REPLY', true),
            'google_business_insights' => self::envBool('MLHUB_NO_PLAN_GOOGLE_INSIGHTS', true),
            'google_business_posts' => self::envBool('MLHUB_NO_PLAN_GOOGLE_POSTS', true),

            'email_automation' => self::envBool('MLHUB_NO_PLAN_EMAIL_AUTOMATION', true),
            'max_email_automations' => self::envInt('MLHUB_NO_PLAN_MAX_EMAIL_AUTOMATIONS', 2),
            'max_email_templates' => self::envInt('MLHUB_NO_PLAN_MAX_EMAIL_TEMPLATES', 3),
            'emails_per_month' => self::envInt('MLHUB_NO_PLAN_EMAILS_PER_MONTH', 200),
            'automation_delay' => self::envBool('MLHUB_NO_PLAN_EMAIL_DELAY', true),
            'automation_conditions' => self::envBool('MLHUB_NO_PLAN_EMAIL_CONDITIONS', true),

            'whatsapp_notification' => self::envBool('MLHUB_NO_PLAN_WHATSAPP', true),
            'max_whatsapp_notifications' => self::envInt('MLHUB_NO_PLAN_MAX_WHATSAPP_RULES', 2),
            'max_whatsapp_templates' => self::envInt('MLHUB_NO_PLAN_MAX_WHATSAPP_TEMPLATES', 2),
            'whatsapp_messages_per_month' => self::envInt('MLHUB_NO_PLAN_WHATSAPP_MESSAGES_PER_MONTH', 50),
            'whatsapp_cloud_api' => self::envBool('MLHUB_NO_PLAN_WHATSAPP_CLOUD_API', true),
            'whatsapp_template_messages' => self::envBool('MLHUB_NO_PLAN_WHATSAPP_TEMPLATE_MSG', true),

            'webhook_automation' => self::envBool('MLHUB_NO_PLAN_WEBHOOK', true),
            'max_webhook_automations' => self::envInt('MLHUB_NO_PLAN_MAX_WEBHOOK_RULES', 2),
            'webhooks_per_month' => self::envInt('MLHUB_NO_PLAN_WEBHOOKS_PER_MONTH', 300),
            'webhook_custom_headers' => self::envBool('MLHUB_NO_PLAN_WEBHOOK_HEADERS', true),
            'webhook_retry' => self::envBool('MLHUB_NO_PLAN_WEBHOOK_RETRY', true),

            'loyalty_stamp_cards' => self::envBool('MLHUB_NO_PLAN_LOYALTY', true),
            'max_loyalty_cards' => self::envInt('MLHUB_NO_PLAN_MAX_LOYALTY_CARDS', 1),
            'max_loyalty_customers' => self::envInt('MLHUB_NO_PLAN_MAX_LOYALTY_CUSTOMERS', 100),
            'max_referral_campaigns' => self::envInt('MLHUB_NO_PLAN_MAX_REFERRAL_CAMPAIGNS', 1),
            'loyalty_rewards' => self::envBool('MLHUB_NO_PLAN_LOYALTY_REWARDS', true),
            'loyalty_staff_redeem' => self::envBool('MLHUB_NO_PLAN_LOYALTY_STAFF_REDEEM', true),

            'qr_custom_domains' => self::envBool('MLHUB_NO_PLAN_CUSTOM_DOMAINS', true),
            'max_custom_domains' => self::envInt('MLHUB_NO_PLAN_MAX_CUSTOM_DOMAINS', 1),
        ];
    }

    protected static function envBool(string $key, bool $default): bool
    {
        $raw = env($key, $default ? 'true' : 'false');

        return filter_var($raw, FILTER_VALIDATE_BOOL);
    }

    protected static function envInt(string $key, int $default): int
    {
        $raw = env($key, (string) $default);

        return is_numeric($raw) ? (int) $raw : $default;
    }
}
