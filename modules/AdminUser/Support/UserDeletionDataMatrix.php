<?php

namespace Modules\AdminUser\Support;

/**
 * Explicit ownership matrix used by DeleteUser and residue tests.
 *
 * Tables are intentionally listed rather than discovered from column names. A
 * schema scan at runtime could delete shared records merely because they expose
 * a user_id/created_by column.
 */
class UserDeletionDataMatrix
{
    /** @var array<string, list<string>> */
    public const HARD_DELETE_OWNED = [
        'affiliate_commissions' => ['affiliate_user_id'],
        'affiliate_profiles' => ['user_id'],
        'affiliate_withdrawals' => ['affiliate_user_id'],
        'ai_content_plans' => ['owner_user_id', 'requested_by_user_id'],
        'ai_image_jobs' => ['owner_user_id', 'requested_by_user_id'],
        'ai_prompt_histories' => ['owner_user_id', 'requested_by_user_id'],
        'ai_publishing_prompts' => ['owner_user_id'],
        'ai_publishing_runs' => ['owner_user_id', 'workspace_owner_user_id'],
        'ai_studio_user_settings' => ['user_id'],
        'ai_studio_workspace_settings' => ['owner_user_id'],
        'ai_usage_logs' => ['user_id'],
        'ai_video_jobs' => ['owner_user_id', 'requested_by_user_id'],
        'credit_topup_ledgers' => ['user_id'],
        'custom_domains' => ['owner_user_id'],
        'files' => ['owner_user_id'],
        'lb_template_imports' => ['user_id'],
        'lb_template_packs' => ['created_by_user_id'],
        'notifications' => ['user_id'],
        'notification_manual_states' => ['user_id'],
        'rss_schedules' => ['user_id'],
        'sessions' => ['user_id'],
    ];

    /** @var array<string, list<string>> */
    public const DELETE_BY_OWNED_SCOPE = [
        'business' => ['business_id', 'mlhub_business_id'],
        'campaign' => ['campaign_id'],
        'customer' => ['customer_id', 'primary_customer_id', 'merged_customer_id'],
        'team' => ['team_id', 'mlhub_workspace_id'],
    ];

    /** @var array<string, list<string>> */
    public const DETACH_SHARED = [
        'notification_manual' => ['created_by'],
        'social_accounts' => ['created_by_user_id'],
        'team_activity_logs' => ['owner_user_id', 'actor_user_id'],
        'team_conversations' => ['created_by_user_id'],
        'team_invitations' => ['invited_by_user_id', 'accepted_by_user_id'],
        'team_messages' => ['user_id'],
        'team_post_comments' => ['user_id'],
        'team_post_reviews' => ['submitted_by_user_id', 'decided_by_user_id'],
    ];

    /** @var array<string, list<string>> */
    public const RETAIN_ANONYMIZED = [
        'affiliate_commissions' => ['referred_user_id', 'meta'],
        'audit_logs' => ['causer_user_id', 'subject_id', 'metadata'],
        'credit_usage_logs' => ['user_id', 'metadata'],
        'payment_history' => ['uid', 'meta'],
        'payment_manual' => ['uid', 'payment_info', 'notes'],
        'payment_subscriptions' => ['uid', 'subscription_id', 'customer_id'],
    ];

    /** @var list<string> */
    public const STORAGE_ASSET_SOURCES = [
        'users.avatar_path',
        'files.path',
        'lb_businesses.qr_design.logo_path',
        'lb_locations.qr_design.logo_path',
        'lb_customers.avatar',
        'social_accounts.avatar_path',
        'partner_support_attachments.path',
        'lb_marketing_templates.preview_image',
        'lb_template_packs.preview_image',
        'team_messages.attachments',
    ];
}
