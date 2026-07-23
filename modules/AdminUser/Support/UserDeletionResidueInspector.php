<?php

namespace Modules\AdminUser\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class UserDeletionResidueInspector
{
    /**
     * @param  array{
     *   user_id:int,
     *   team_ids:list<int>,
     *   business_ids:list<int>,
     *   campaign_ids:list<int>,
     *   customer_ids:list<int>,
     *   external_business_ids:list<string>,
     *   request_ids:list<string>,
     *   storage_assets:list<array{disk:string,path:string,directory?:bool}>
     * }  $context
     * @return array{clean:bool,database:array<string,int>,json:array<string,int>,storage:list<string>}
     */
    public function inspect(array $context): array
    {
        $database = [];
        $json = [];
        $storage = [];

        $this->inspectDirectUserReferences((int) $context['user_id'], $database);
        $this->inspectOwnedScopes($context, $database);
        $this->inspectPartnerPayloads($context, $json);

        foreach ($context['storage_assets'] as $asset) {
            $disk = trim((string) ($asset['disk'] ?? ''));
            $path = trim((string) ($asset['path'] ?? ''));

            if ($disk === '' || $path === '') {
                continue;
            }

            try {
                if (Storage::disk($disk)->exists($path)) {
                    $storage[] = $disk.':'.$path;
                }
            } catch (Throwable) {
                $storage[] = $disk.':'.$path.':unreadable';
            }
        }

        ksort($database);
        ksort($json);
        sort($storage);

        return [
            'clean' => $database === [] && $json === [] && $storage === [],
            'database' => $database,
            'json' => $json,
            'storage' => $storage,
        ];
    }

    /**
     * @param  array<string, int>  $residue
     */
    private function inspectDirectUserReferences(int $userId, array &$residue): void
    {
        $matrix = array_merge(UserDeletionDataMatrix::HARD_DELETE_OWNED, [
            'lb_businesses' => ['user_id'],
            'lb_campaigns' => ['user_id'],
            'lb_customers' => ['user_id'],
            'lb_customer_notes' => ['user_id'],
            'lb_email_templates' => ['user_id'],
            'lb_email_automations' => ['user_id'],
            'lb_email_automation_logs' => ['user_id'],
            'lb_google_business_connections' => ['user_id'],
            'lb_loyalty_cards' => ['user_id'],
            'lb_referral_campaigns' => ['user_id'],
            'lb_template_ratings' => ['user_id'],
            'lb_template_imports' => ['user_id'],
            'lb_template_packs' => ['created_by_user_id'],
            'lb_webhook_automations' => ['user_id'],
            'lb_webhook_automation_logs' => ['user_id'],
            'lb_whatsapp_templates' => ['user_id'],
            'lb_whatsapp_notifications' => ['user_id'],
            'lb_whatsapp_notification_logs' => ['user_id'],
            'partner_one_time_logins' => ['user_id'],
            'partner_integrations' => ['mlhub_user_id'],
            'partner_onboarding_requests' => ['mlhub_user_id'],
            'posts' => ['user_id'],
            'support_comments' => ['user_id'],
            'support_tickets' => ['uid', 'open_by'],
            'team_conversation_participants' => ['user_id'],
            'team_user' => ['user_id'],
        ]);

        foreach ($matrix as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                $count = DB::table($table)->where($column, $userId)->count();

                if ($count > 0) {
                    $residue[$table.'.'.$column] = $count;
                }
            }
        }
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<string, int>  $residue
     */
    private function inspectOwnedScopes(array $context, array &$residue): void
    {
        $scopes = [
            'team_id' => $context['team_ids'],
            'mlhub_workspace_id' => $context['team_ids'],
            'business_id' => $context['business_ids'],
            'mlhub_business_id' => $context['business_ids'],
            'campaign_id' => $context['campaign_ids'],
            'customer_id' => $context['customer_ids'],
            'primary_customer_id' => $context['customer_ids'],
            'merged_customer_id' => $context['customer_ids'],
        ];

        $tables = [
            'ai_content_plans',
            'ai_image_jobs',
            'ai_prompt_histories',
            'ai_publishing_runs',
            'ai_studio_workspace_settings',
            'ai_video_jobs',
            'custom_domains',
            'files',
            'lb_booking_services',
            'lb_bookings',
            'lb_coupon_redemptions',
            'lb_crm_automation_jobs',
            'lb_crm_automation_logs',
            'lb_crm_automations',
            'lb_customer_activities',
            'lb_customer_merge_logs',
            'lb_customer_notes',
            'lb_customer_score_logs',
            'lb_customer_segments',
            'lb_customer_tag_maps',
            'lb_customer_tags',
            'lb_customer_tasks',
            'lb_email_automation_logs',
            'lb_email_automations',
            'lb_email_templates',
            'lb_feedback_responses',
            'lb_google_auto_reply_logs',
            'lb_google_auto_reply_rules',
            'lb_google_business_locations',
            'lb_google_business_post_logs',
            'lb_google_business_posts',
            'lb_google_reviews',
            'lb_landing_pages',
            'lb_lead_submissions',
            'lb_locations',
            'lb_loyalty_cards',
            'lb_marketing_templates',
            'lb_qr_scans',
            'lb_referral_campaigns',
            'lb_review_feedbacks',
            'lb_template_imports',
            'lb_template_packs',
            'lb_template_usages',
            'lb_webhook_automation_logs',
            'lb_webhook_automations',
            'lb_whatsapp_notification_logs',
            'lb_whatsapp_notifications',
            'lb_whatsapp_templates',
            'partner_integrations',
            'partner_onboarding_requests',
            'posts',
            'rss_schedules',
            'support_tickets',
            'team_activity_logs',
            'team_conversations',
            'team_invitations',
            'team_post_comments',
            'team_post_reviews',
            'teams',
        ];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($scopes as $column => $ids) {
                if ($ids === [] || ! Schema::hasColumn($table, $column)) {
                    continue;
                }

                $count = DB::table($table)->whereIn($column, $ids)->count();

                if ($count > 0) {
                    $residue[$table.'.'.$column] = $count;
                }
            }
        }
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<string, int>  $residue
     */
    private function inspectPartnerPayloads(array $context, array &$residue): void
    {
        foreach ($context['external_business_ids'] as $externalBusinessId) {
            if (Schema::hasTable('partner_api_logs')) {
                $count = DB::table('partner_api_logs')
                    ->where(function ($query) use ($externalBusinessId): void {
                        $query->where('request_payload->external_business_id', $externalBusinessId)
                            ->orWhere('response_payload->data->external_business_id', $externalBusinessId);
                    })
                    ->count();

                if ($count > 0) {
                    $residue['partner_api_logs.external_business_id'] =
                        ($residue['partner_api_logs.external_business_id'] ?? 0) + $count;
                }
            }

            if (Schema::hasTable('partner_webhook_outbox')) {
                $count = DB::table('partner_webhook_outbox')
                    ->where('payload->external_business_id', $externalBusinessId)
                    ->count();

                if ($count > 0) {
                    $residue['partner_webhook_outbox.external_business_id'] =
                        ($residue['partner_webhook_outbox.external_business_id'] ?? 0) + $count;
                }
            }
        }

        foreach ($context['request_ids'] as $requestId) {
            if (Schema::hasTable('partner_api_logs') && Schema::hasColumn('partner_api_logs', 'request_id')) {
                $count = DB::table('partner_api_logs')->where('request_id', $requestId)->count();

                if ($count > 0) {
                    $residue['partner_api_logs.request_id'] =
                        ($residue['partner_api_logs.request_id'] ?? 0) + $count;
                }
            }

            if (Schema::hasTable('partner_webhook_outbox') && Schema::hasColumn('partner_webhook_outbox', 'dedupe_key')) {
                $count = DB::table('partner_webhook_outbox')
                    ->where('dedupe_key', 'like', $requestId.'%')
                    ->count();

                if ($count > 0) {
                    $residue['partner_webhook_outbox.request_id'] =
                        ($residue['partner_webhook_outbox.request_id'] ?? 0) + $count;
                }
            }
        }
    }
}
