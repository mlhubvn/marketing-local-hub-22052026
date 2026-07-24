<?php

namespace Modules\AdminUser\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Modules\AdminUser\Models\User;
use Throwable;

class UserDeletionResidueInspector
{
    /**
     * @param  array{
     *   user_id:int,
     *   user_email?:string,
     *   user_username?:string,
     *   user_phone?:string,
     *   team_ids:list<int>,
     *   business_ids:list<int>,
     *   campaign_ids:list<int>,
     *   customer_ids:list<int>,
     *   external_business_ids:list<string>,
     *   request_ids:list<string>,
     *   storage_assets:list<array{disk:string,path:string,directory?:bool}>
     * }  $context
     * @return array{
     *   clean:bool,
     *   database:array<string,int>,
     *   json:array<string,int>,
     *   storage:list<string>,
     *   records:list<array{table:string,column:string,record_id:int|string|null,ownership_reason:string,remaining_reference:string}>
     * }
     */
    public function inspect(array $context): array
    {
        $database = [];
        $json = [];
        $storage = [];
        $records = [];

        $this->inspectDirectUserReferences((int) $context['user_id'], $database, $records);
        $this->inspectAuthenticationResidue($context, $database, $records);
        $this->inspectOwnedScopes($context, $database, $records);
        $this->inspectPartnerPayloads($context, $json, $records);
        $this->inspectRetainedPii($context, $json, $records);

        foreach ($context['storage_assets'] as $asset) {
            $disk = trim((string) ($asset['disk'] ?? ''));
            $path = trim((string) ($asset['path'] ?? ''));

            if ($disk === '' || $path === '') {
                continue;
            }

            try {
                if (Storage::disk($disk)->exists($path)) {
                    $storage[] = $disk.':'.$path;
                    $records[] = $this->finding(
                        'storage',
                        'path',
                        $disk.':'.$path,
                        'owned_storage_asset',
                        $disk.':'.$path
                    );
                }
            } catch (Throwable) {
                $storage[] = $disk.':'.$path.':unreadable';
                $records[] = $this->finding(
                    'storage',
                    'path',
                    $disk.':'.$path,
                    'owned_storage_asset_unreadable',
                    $disk.':'.$path
                );
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
            'records' => $records,
        ];
    }

    /**
     * @param  array<string, int>  $residue
     */
    private function inspectDirectUserReferences(int $userId, array &$residue, array &$records): void
    {
        $matrix = array_merge(
            UserDeletionDataMatrix::HARD_DELETE_OWNED,
            UserDeletionDataMatrix::DETACH_SHARED,
            [
                'lb_businesses' => ['user_id'],
                'lb_campaigns' => ['user_id'],
                'lb_customers' => ['user_id'],
                'lb_customer_notes' => ['user_id'],
                'lb_email_templates' => ['user_id'],
                'lb_email_automations' => ['user_id'],
                'lb_email_automation_logs' => ['user_id'],
                'lb_google_business_connections' => ['user_id'],
                'lb_loyalty_cards' => ['user_id'],
                'lb_loyalty_stamps' => ['staff_id'],
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
            ]
        );

        foreach ($matrix as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                $this->recordQueryFindings(
                    $table,
                    $column,
                    DB::table($table)->where($column, $userId),
                    'direct_user_reference',
                    (string) $userId,
                    $residue,
                    $records
                );
            }
        }

        if (Schema::hasTable('audit_logs')
            && Schema::hasColumns('audit_logs', ['causer_user_id', 'subject_type', 'subject_id'])) {
            $this->recordQueryFindings(
                'audit_logs',
                'causer_user_id/subject_type+subject_id',
                DB::table('audit_logs')->where(function ($query) use ($userId): void {
                    $query->where('causer_user_id', $userId)
                        ->orWhere(function ($subject) use ($userId): void {
                            $subject->where('subject_type', User::class)
                                ->where('subject_id', $userId);
                        });
                }),
                'retained_user_reference',
                (string) $userId,
                $residue,
                $records
            );
        }
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<string, int>  $residue
     */
    private function inspectAuthenticationResidue(array $context, array &$residue, array &$records): void
    {
        $userId = (int) $context['user_id'];

        if (Schema::hasTable('personal_access_tokens')
            && Schema::hasColumns('personal_access_tokens', ['tokenable_type', 'tokenable_id'])) {
            $this->recordQueryFindings(
                'personal_access_tokens',
                'tokenable_type+tokenable_id',
                DB::table('personal_access_tokens')
                    ->where('tokenable_type', User::class)
                    ->where('tokenable_id', $userId),
                'authentication_reference',
                (string) $userId,
                $residue,
                $records
            );
        }

        foreach (['oauth_access_tokens', 'oauth_auth_codes'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'user_id')) {
                continue;
            }

            $this->recordQueryFindings(
                $table,
                'user_id',
                DB::table($table)->where('user_id', $userId),
                'authentication_reference',
                (string) $userId,
                $residue,
                $records
            );
        }

        $email = trim((string) ($context['user_email'] ?? ''));

        if ($email !== '' && Schema::hasTable('password_reset_tokens')
            && Schema::hasColumn('password_reset_tokens', 'email')) {
            $this->recordQueryFindings(
                'password_reset_tokens',
                'email',
                DB::table('password_reset_tokens')->where('email', $email),
                'authentication_email_snapshot',
                $email,
                $residue,
                $records
            );
        }
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<string, int>  $residue
     */
    private function inspectOwnedScopes(array $context, array &$residue, array &$records): void
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
                if ($column === 'team_id'
                    && in_array($table, UserDeletionDataMatrix::USER_OWNED_CRM, true)) {
                    continue;
                }

                if ($ids === [] || ! Schema::hasColumn($table, $column)) {
                    continue;
                }

                $this->recordQueryFindings(
                    $table,
                    $column,
                    DB::table($table)->whereIn($column, $ids),
                    'deleted_owned_scope_reference',
                    implode(',', array_map('strval', $ids)),
                    $residue,
                    $records
                );
            }
        }
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<string, int>  $residue
     */
    private function inspectPartnerPayloads(array $context, array &$residue, array &$records): void
    {
        foreach (($context['external_business_ids'] ?? []) as $externalBusinessId) {
            if (Schema::hasTable('partner_api_logs')) {
                $query = DB::table('partner_api_logs')
                    ->where(function ($builder) use ($externalBusinessId): void {
                        $builder->where('request_payload->external_business_id', $externalBusinessId)
                            ->orWhere('response_payload->data->external_business_id', $externalBusinessId);
                    });
                $this->recordQueryFindings(
                    'partner_api_logs',
                    'request_payload/response_payload',
                    $query,
                    'external_business_id_in_json',
                    $externalBusinessId,
                    $residue,
                    $records
                );
            }

            if (Schema::hasTable('partner_webhook_outbox')) {
                $this->recordQueryFindings(
                    'partner_webhook_outbox',
                    'payload',
                    DB::table('partner_webhook_outbox')
                        ->where('payload->external_business_id', $externalBusinessId),
                    'external_business_id_in_json',
                    $externalBusinessId,
                    $residue,
                    $records
                );
            }
        }

        foreach (($context['request_ids'] ?? []) as $requestId) {
            if (Schema::hasTable('partner_api_logs') && Schema::hasColumn('partner_api_logs', 'request_id')) {
                $this->recordQueryFindings(
                    'partner_api_logs',
                    'request_id',
                    DB::table('partner_api_logs')->where('request_id', $requestId),
                    'deleted_onboarding_request_reference',
                    $requestId,
                    $residue,
                    $records
                );
            }

            if (Schema::hasTable('partner_webhook_outbox') && Schema::hasColumn('partner_webhook_outbox', 'dedupe_key')) {
                $this->recordQueryFindings(
                    'partner_webhook_outbox',
                    'dedupe_key',
                    DB::table('partner_webhook_outbox')
                        ->where('dedupe_key', 'like', $requestId.'%'),
                    'deleted_onboarding_request_reference',
                    $requestId,
                    $residue,
                    $records
                );
            }
        }
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<string, int>  $residue
     */
    private function inspectRetainedPii(array $context, array &$residue, array &$records): void
    {
        $needles = array_values(array_unique(array_filter([
            trim((string) ($context['user_email'] ?? '')),
            trim((string) ($context['user_username'] ?? '')),
            trim((string) ($context['user_phone'] ?? '')),
        ], fn (string $value): bool => $value !== '')));

        if ($needles === []) {
            return;
        }

        $columnsByTable = [
            'audit_logs' => ['description', 'metadata', 'ip_address', 'user_agent'],
            'credit_usage_logs' => ['metadata'],
            'payment_history' => ['meta'],
            'payment_manual' => ['payment_info', 'notes'],
            'payment_subscriptions' => ['subscription_id', 'customer_id'],
        ];

        foreach ($columnsByTable as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                foreach ($needles as $needle) {
                    $this->recordQueryFindings(
                        $table,
                        $column,
                        DB::table($table)->where($column, 'like', '%'.$needle.'%'),
                        'retained_personal_data',
                        $needle,
                        $residue,
                        $records
                    );
                }
            }
        }
    }

    /**
     * @param  array<string, int>  $residue
     * @param  list<array<string, mixed>>  $records
     */
    private function recordQueryFindings(
        string $table,
        string $column,
        $query,
        string $reason,
        string $reference,
        array &$residue,
        array &$records
    ): void {
        $count = (clone $query)->count();

        if ($count < 1) {
            return;
        }

        $residue[$table.'.'.$column] =
            ($residue[$table.'.'.$column] ?? 0) + $count;
        $idColumn = Schema::hasColumn($table, 'id') ? 'id' : $column;

        foreach ((clone $query)->pluck($idColumn) as $recordId) {
            $records[] = $this->finding(
                $table,
                $column,
                is_numeric($recordId) ? (int) $recordId : (string) $recordId,
                $reason,
                $reference
            );
        }
    }

    /**
     * @return array{table:string,column:string,record_id:int|string|null,ownership_reason:string,remaining_reference:string}
     */
    private function finding(
        string $table,
        string $column,
        int|string|null $recordId,
        string $reason,
        string $reference
    ): array {
        return [
            'table' => $table,
            'column' => $column,
            'record_id' => $recordId,
            'ownership_reason' => $reason,
            'remaining_reference' => $reference,
        ];
    }
}
