<?php

namespace Modules\AdminUser\Actions;

use App\Support\Plans\PlanLimitGuard;
use App\Support\Portal\PortalGrowthDashboardMetrics;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\AdminUser\Data\UserDeletionResult;
use Modules\AdminUser\Exceptions\LastSuperAdminDeletionException;
use Modules\AdminUser\Exceptions\SharedTeamOwnershipException;
use Modules\AdminUser\Models\User;
use Modules\AdminUser\Support\UserDeletionDataMatrix;
use Modules\AdminUser\Support\UserDeletionResidueInspector;
use Modules\AdminUser\Support\UserDeletionStorageCleanup;
use Modules\APIPartnerFizaHUB\Services\OnboardingAdminService;
use Throwable;

class DeleteUser
{
    /** @var array<string, list<string>> */
    private array $columnCache = [];

    public function __construct(
        protected OnboardingAdminService $onboarding,
        protected UserDeletionStorageCleanup $storageCleanup,
        protected UserDeletionResidueInspector $residueInspector,
    ) {}

    public function execute(User $user, ?int $deletedByUserId = null): UserDeletionResult
    {
        $result = new UserDeletionResult((int) $user->getKey());
        $result->status = 'deleting_database';

        return DB::transaction(function () use ($user, $deletedByUserId, $result): UserDeletionResult {
            $lockedUser = User::query()->lockForUpdate()->find($user->getKey());

            if (! $lockedUser) {
                $result->status = 'already_deleted';

                return $result;
            }

            $this->assertNotLastSuperAdmin($lockedUser);
            $context = $this->collectContext($lockedUser);
            $result->deletedBusinessIds = $context['business_ids'];
            $result->deletedTeamIds = $context['personal_team_ids'];
            $result->detachedSharedTeamIds = $context['shared_member_team_ids'];

            if ($context['shared_owned_team_ids'] !== []) {
                throw new SharedTeamOwnershipException($context['shared_owned_team_ids']);
            }

            $assets = $this->collectStorageAssets($lockedUser, $context);
            $result->storageAssetsScheduled = count($assets);

            $partner = $this->onboarding->adminPurgeForUser((int) $lockedUser->id, $deletedByUserId);
            $result->onboardingBusinessesPurged = (int) ($partner['businesses_purged'] ?? 0);
            $result->partnerTicketsDeleted = (int) ($partner['tickets_deleted'] ?? 0);
            $context['external_business_ids'] = array_values($partner['external_business_ids'] ?? []);
            $context['request_ids'] = array_values($partner['request_ids'] ?? []);

            $this->deleteSupportData($context, $result);
            $this->deleteScopedProductData($context, $result);
            $this->deleteTeamAndSocialData($lockedUser, $context, $result);
            $this->deleteDirectOwnedData($context, $result);
            $this->deleteAuthenticationState($lockedUser, $result);
            $this->deleteCoreGrowthData($context, $result);
            $this->anonymizeRetainedData($lockedUser, $context, $result);
            $this->deletePersonalTeams($context, $result);

            $result->deleted = (bool) $lockedUser->delete();

            if ($result->deleted) {
                $result->status = 'deleting_storage';
                $auditId = $this->writeDeletionAudit($deletedByUserId, $result);
                $this->runAfterCommit($assets, $context, $result, $auditId);
            }

            return $result;
        });
    }

    /**
     * @return array{
     *   user_id:int,
     *   user_email:string,
     *   user_username:string,
     *   user_phone:string,
     *   locale:string,
     *   business_ids:list<int>,
     *   campaign_ids:list<int>,
     *   customer_ids:list<int>,
     *   owned_team_ids:list<int>,
     *   personal_team_ids:list<int>,
     *   shared_owned_team_ids:list<int>,
     *   member_team_ids:list<int>,
     *   shared_member_team_ids:list<int>,
     *   owned_social_account_ids:list<int>,
     *   shared_social_account_ids:list<int>,
     *   deletable_social_account_ids:list<int>
     * }
     */
    private function collectContext(User $user): array
    {
        $userId = (int) $user->id;
        $businessIds = $this->pluckIds('lb_businesses', ['user_id' => $userId]);
        $ownedTeamIds = $this->pluckIds('teams', ['owner_user_id' => $userId]);
        $memberTeamIds = $this->pluckIds('team_user', ['user_id' => $userId], 'team_id');
        $sharedOwnedTeamIds = [];

        foreach ($ownedTeamIds as $teamId) {
            if (! $this->hasTableAndColumns('team_user', ['team_id', 'user_id'])) {
                continue;
            }

            $hasOtherMembers = DB::table('team_user')
                ->where('team_id', $teamId)
                ->where('user_id', '!=', $userId)
                ->exists();

            if ($hasOtherMembers) {
                $sharedOwnedTeamIds[] = $teamId;
            }
        }

        $personalTeamIds = array_values(array_diff($ownedTeamIds, $sharedOwnedTeamIds));
        $sharedMemberTeamIds = array_values(array_diff($memberTeamIds, $personalTeamIds));
        $campaignIds = $this->pluckIds('lb_campaigns', [
            'user_id' => $userId,
            'business_id' => $businessIds,
        ]);
        $customerIds = $this->pluckIds('lb_customers', [
            'user_id' => $userId,
            'business_id' => $businessIds,
        ]);
        $ownedSocialAccountIds = $this->pluckIds('social_accounts', ['created_by_user_id' => $userId]);
        $sharedSocialAccountIds = $this->sharedSocialAccountIds($userId, $ownedSocialAccountIds);

        return [
            'user_id' => $userId,
            'email' => (string) $user->email,
            'user_email' => (string) $user->email,
            'user_username' => (string) ($user->username ?? ''),
            'user_phone' => (string) ($user->phone ?? ''),
            'locale' => (string) ($user->locale ?: app()->getLocale()),
            'business_ids' => $businessIds,
            'campaign_ids' => $campaignIds,
            'customer_ids' => $customerIds,
            'owned_team_ids' => $ownedTeamIds,
            'personal_team_ids' => $personalTeamIds,
            'shared_owned_team_ids' => $sharedOwnedTeamIds,
            'member_team_ids' => $memberTeamIds,
            'shared_member_team_ids' => $sharedMemberTeamIds,
            'owned_social_account_ids' => $ownedSocialAccountIds,
            'shared_social_account_ids' => $sharedSocialAccountIds,
            'deletable_social_account_ids' => array_values(array_diff(
                $ownedSocialAccountIds,
                $sharedSocialAccountIds
            )),
        ];
    }

    private function assertNotLastSuperAdmin(User $user): void
    {
        if (! $this->columns('users') || ! in_array('is_super_admin', $this->columns('users'), true)) {
            return;
        }

        $isSuperAdmin = (bool) $user->is_super_admin;

        if (! $isSuperAdmin && $this->hasTableAndColumns('admin_roles', ['id', 'slug'])
            && in_array('role_id', $this->columns('users'), true)) {
            $isSuperAdmin = DB::table('admin_roles')
                ->where('id', $user->role_id)
                ->where('slug', 'super-admin')
                ->exists();
        }

        if (! $isSuperAdmin) {
            return;
        }

        $query = DB::table('users')->where('id', '!=', $user->id);
        $query->where(function (Builder $builder): void {
            $builder->where('is_super_admin', true);

            if ($this->hasTableAndColumns('admin_roles', ['id', 'slug'])
                && in_array('role_id', $this->columns('users'), true)) {
                $roleIds = DB::table('admin_roles')->where('slug', 'super-admin')->pluck('id')->all();

                if ($roleIds !== []) {
                    $builder->orWhereIn('role_id', $roleIds);
                }
            }
        });

        if (! $query->exists()) {
            throw new LastSuperAdminDeletionException;
        }
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function deleteSupportData(array $context, UserDeletionResult $result): void
    {
        $ticketIds = $this->pluckIds('support_tickets', [
            'uid' => $context['user_id'],
            'open_by' => $context['user_id'],
            'team_id' => $context['personal_team_ids'],
        ]);

        if ($ticketIds === []) {
            return;
        }

        $this->deleteWhereAny('partner_support_attachments', ['support_ticket_id' => $ticketIds], $result);
        $this->deleteWhereAny('partner_support_ticket_contexts', ['support_ticket_id' => $ticketIds], $result);
        $this->deleteWhereAny('support_comments', ['ticket_id' => $ticketIds], $result);
        $this->deleteWhereAny('support_map_labels', ['ticket_id' => $ticketIds], $result);
        $this->deleteWhereAny('support_tickets', ['id' => $ticketIds], $result);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function deleteScopedProductData(array $context, UserDeletionResult $result): void
    {
        $userScope = [
            'user_id' => $context['user_id'],
            'owner_user_id' => $context['user_id'],
            'business_id' => $context['business_ids'],
            'campaign_id' => $context['campaign_ids'],
            'customer_id' => $context['customer_ids'],
            'primary_customer_id' => $context['customer_ids'],
            'merged_customer_id' => $context['customer_ids'],
            'team_id' => $context['personal_team_ids'],
        ];
        $crmUserScope = [
            'owner_user_id' => $context['user_id'],
            'business_id' => $context['business_ids'],
            'customer_id' => $context['customer_ids'],
            'primary_customer_id' => $context['customer_ids'],
            'merged_customer_id' => $context['customer_ids'],
        ];

        $crmAutomationIds = $this->pluckIds('lb_crm_automations', $crmUserScope);
        $googleConnectionIds = $this->pluckIds('lb_google_business_connections', [
            'user_id' => $context['user_id'],
            // This module's historical schema names a users.id foreign key team_id.
            'team_id' => $context['user_id'],
        ]);
        $googleLocationIds = $this->pluckIds('lb_google_business_locations', array_merge($userScope, [
            'team_id' => $context['user_id'],
            'connection_id' => $googleConnectionIds,
        ]));
        $googleReviewIds = $this->pluckIds('lb_google_reviews', array_merge($userScope, [
            'team_id' => $context['user_id'],
            'google_business_location_id' => $googleLocationIds,
        ]));
        $googleRuleIds = $this->pluckIds('lb_google_auto_reply_rules', array_merge($userScope, [
            'team_id' => $context['user_id'],
            'google_business_location_id' => $googleLocationIds,
        ]));
        $googlePostIds = $this->pluckIds('lb_google_business_posts', array_merge($userScope, [
            'team_id' => $context['user_id'],
            'google_business_location_id' => $googleLocationIds,
        ]));
        $loyaltyCardIds = $this->pluckIds('lb_loyalty_cards', $userScope);
        $referralCampaignIds = $this->pluckIds('lb_referral_campaigns', $userScope);
        $referralLinkIds = $this->pluckIds('lb_referral_links', [
            'campaign_id' => $referralCampaignIds,
            'customer_id' => $context['customer_ids'],
        ]);
        $emailTemplateIds = $this->pluckIds('lb_email_templates', $userScope);
        $emailAutomationIds = $this->pluckIds('lb_email_automations', $userScope);
        $whatsAppTemplateIds = $this->pluckIds('lb_whatsapp_templates', $userScope);
        $whatsAppNotificationIds = $this->pluckIds('lb_whatsapp_notifications', $userScope);
        $webhookAutomationIds = $this->pluckIds('lb_webhook_automations', $userScope);
        $marketingTemplateIds = $this->pluckIds('lb_marketing_templates', [
            'user_id' => $context['user_id'],
            'team_id' => $context['personal_team_ids'],
        ]);

        $rules = [
            'lb_google_business_post_logs' => ['team_id' => $context['user_id'], 'post_id' => $googlePostIds],
            'lb_google_auto_reply_logs' => ['team_id' => $context['user_id'], 'rule_id' => $googleRuleIds, 'review_id' => $googleReviewIds],
            'lb_google_business_posts' => array_merge($userScope, ['team_id' => $context['user_id'], 'id' => $googlePostIds]),
            'lb_google_reviews' => array_merge($userScope, ['team_id' => $context['user_id'], 'id' => $googleReviewIds]),
            'lb_google_auto_reply_rules' => array_merge($userScope, ['team_id' => $context['user_id'], 'id' => $googleRuleIds]),
            'lb_google_business_locations' => array_merge($userScope, ['team_id' => $context['user_id'], 'id' => $googleLocationIds]),
            'lb_google_business_connections' => ['user_id' => $context['user_id'], 'team_id' => $context['user_id'], 'id' => $googleConnectionIds],
            'lb_crm_automation_jobs' => array_merge($crmUserScope, ['automation_id' => $crmAutomationIds]),
            'lb_crm_automation_logs' => array_merge($crmUserScope, ['automation_id' => $crmAutomationIds]),
            'lb_customer_merge_logs' => $crmUserScope,
            'lb_customer_score_logs' => $crmUserScope,
            'lb_customer_tag_maps' => $crmUserScope,
            'lb_customer_activities' => $crmUserScope,
            'lb_customer_notes' => $crmUserScope,
            'lb_customer_tasks' => $crmUserScope,
            'lb_customer_segments' => $crmUserScope,
            'lb_customer_tags' => $crmUserScope,
            'lb_crm_automations' => array_merge($crmUserScope, ['id' => $crmAutomationIds]),
            'lb_email_automation_logs' => array_merge($userScope, ['email_template_id' => $emailTemplateIds, 'automation_id' => $emailAutomationIds]),
            'lb_email_automations' => array_merge($userScope, ['id' => $emailAutomationIds, 'email_template_id' => $emailTemplateIds]),
            'lb_email_templates' => array_merge($userScope, ['id' => $emailTemplateIds]),
            'lb_whatsapp_notification_logs' => array_merge($userScope, ['whatsapp_template_id' => $whatsAppTemplateIds, 'notification_id' => $whatsAppNotificationIds]),
            'lb_whatsapp_notifications' => array_merge($userScope, ['id' => $whatsAppNotificationIds, 'whatsapp_template_id' => $whatsAppTemplateIds]),
            'lb_whatsapp_templates' => array_merge($userScope, ['id' => $whatsAppTemplateIds]),
            'lb_webhook_automation_logs' => array_merge($userScope, ['automation_id' => $webhookAutomationIds]),
            'lb_webhook_automations' => array_merge($userScope, ['id' => $webhookAutomationIds]),
            'lb_loyalty_rewards' => ['card_id' => $loyaltyCardIds, 'customer_id' => $context['customer_ids']],
            'lb_loyalty_stamps' => ['card_id' => $loyaltyCardIds, 'customer_id' => $context['customer_ids'], 'staff_id' => $context['user_id']],
            'lb_loyalty_customers' => ['card_id' => $loyaltyCardIds, 'customer_id' => $context['customer_ids']],
            'lb_loyalty_cards' => array_merge($userScope, ['id' => $loyaltyCardIds]),
            'lb_referral_rewards' => ['campaign_id' => $referralCampaignIds, 'customer_id' => $context['customer_ids']],
            'lb_referrals' => ['campaign_id' => $referralCampaignIds, 'referral_link_id' => $referralLinkIds, 'referrer_customer_id' => $context['customer_ids'], 'referred_customer_id' => $context['customer_ids']],
            'lb_referral_links' => ['id' => $referralLinkIds, 'campaign_id' => $referralCampaignIds, 'customer_id' => $context['customer_ids']],
            'lb_referral_campaigns' => array_merge($userScope, ['id' => $referralCampaignIds]),
            'lb_template_ratings' => ['user_id' => $context['user_id'], 'template_id' => $marketingTemplateIds],
            'lb_template_usages' => array_merge($userScope, ['template_id' => $marketingTemplateIds]),
            'lb_template_pack_items' => ['template_id' => $marketingTemplateIds],
            'lb_marketing_templates' => ['id' => $marketingTemplateIds, 'user_id' => $context['user_id'], 'team_id' => $context['personal_team_ids']],
            'lb_template_imports' => ['user_id' => $context['user_id'], 'team_id' => $context['personal_team_ids']],
            'lb_template_packs' => ['created_by_user_id' => $context['user_id'], 'team_id' => $context['personal_team_ids']],
        ];

        foreach ($rules as $table => $conditions) {
            $this->deleteWhereAny($table, $conditions, $result);
        }

        foreach (['lb_email_automations', 'lb_whatsapp_notifications', 'lb_webhook_automations'] as $table) {
            $this->anonymizeCreatorReferencesOutsideOwnedScope($table, $context['user_id']);
        }

        $this->anonymizeCreatorReferencesOutsideOwnedScope('lb_marketing_templates', $context['user_id']);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function deleteTeamAndSocialData(User $user, array $context, UserDeletionResult $result): void
    {
        $personalConversationIds = $this->pluckIds('team_conversations', [
            'team_id' => $context['personal_team_ids'],
        ]);
        $sharedAuthoredMessageIds = [];

        if ($this->hasTableAndColumns('team_messages', ['id', 'user_id', 'conversation_id'])
            && $context['shared_member_team_ids'] !== []
            && $this->hasTableAndColumns('team_conversations', ['id', 'team_id'])) {
            $sharedConversationIds = DB::table('team_conversations')
                ->whereIn('team_id', $context['shared_member_team_ids'])
                ->pluck('id')
                ->all();

            if ($sharedConversationIds !== []) {
                $sharedAuthoredMessageIds = DB::table('team_messages')
                    ->where('user_id', $context['user_id'])
                    ->whereIn('conversation_id', $sharedConversationIds)
                    ->pluck('id')
                    ->all();
            }
        }

        $this->deleteWhereAny('team_conversation_participants', [
            'conversation_id' => $personalConversationIds,
            'user_id' => $context['user_id'],
        ], $result);
        $this->deleteWhereAny('team_messages', ['conversation_id' => $personalConversationIds], $result);
        $this->deleteWhereAny('team_conversations', ['id' => $personalConversationIds], $result);

        if ($sharedAuthoredMessageIds !== []) {
            $payload = $this->existingPayload('team_messages', [
                'user_id' => null,
                'body' => '[Deleted user content removed]',
                'attachments' => null,
                'metadata' => null,
            ]);

            if ($payload !== []) {
                DB::table('team_messages')->whereIn('id', $sharedAuthoredMessageIds)->update($payload);
            }
        }

        $postIds = $this->pluckIds('posts', [
            'user_id' => $context['user_id'],
            'team_id' => $context['personal_team_ids'],
            'account_id' => $context['deletable_social_account_ids'],
        ]);
        $this->deleteWhereAny('team_post_comments', [
            'post_id' => $postIds,
            'team_id' => $context['personal_team_ids'],
            'user_id' => $context['user_id'],
        ], $result);
        $this->deleteWhereAny('team_post_reviews', [
            'post_id' => $postIds,
            'team_id' => $context['personal_team_ids'],
            'submitted_by_user_id' => $context['user_id'],
            'decided_by_user_id' => $context['user_id'],
        ], $result);
        $this->deleteWhereAny('posts', [
            'id' => $postIds,
            'user_id' => $context['user_id'],
            'team_id' => $context['personal_team_ids'],
            'account_id' => $context['deletable_social_account_ids'],
        ], $result);

        $this->deleteWhereAny('team_invitations', [
            'team_id' => $context['personal_team_ids'],
            'accepted_by_user_id' => $context['user_id'],
            'email' => (string) $user->email,
        ], $result);
        $this->updateWhereAny('team_invitations', ['invited_by_user_id' => $context['user_id']], [
            'invited_by_user_id' => null,
        ]);
        $this->deleteWhereAny('team_activity_logs', ['team_id' => $context['personal_team_ids']], $result);
        $this->updateWhereAny('team_activity_logs', [
            'owner_user_id' => $context['user_id'],
            'actor_user_id' => $context['user_id'],
        ], [
            'owner_user_id' => null,
            'actor_user_id' => null,
            'metadata' => null,
        ]);

        if ($context['shared_social_account_ids'] !== []) {
            $payload = $this->existingPayload('social_accounts', ['created_by_user_id' => null]);

            if ($payload !== []) {
                DB::table('social_accounts')
                    ->whereIn('id', $context['shared_social_account_ids'])
                    ->update($payload);
            }
        }

        $this->deleteWhereAny('social_accounts', [
            'id' => $context['deletable_social_account_ids'],
        ], $result);
        $this->deleteWhereAny('team_user', ['user_id' => $context['user_id']], $result);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function deleteDirectOwnedData(array $context, UserDeletionResult $result): void
    {
        foreach (UserDeletionDataMatrix::HARD_DELETE_OWNED as $table => $columns) {
            $conditions = [];

            foreach ($columns as $column) {
                $conditions[$column] = $context['user_id'];
            }

            if (! in_array($table, UserDeletionDataMatrix::USER_OWNED_CRM, true)
                && in_array('team_id', $this->columns($table), true)) {
                $conditions['team_id'] = $context['personal_team_ids'];
            }

            $this->deleteWhereAny($table, $conditions, $result);
        }

        $this->deleteWhereAny('support_comments', ['user_id' => $context['user_id']], $result);
        $this->deleteWhereAny('lb_customer_notes', ['user_id' => $context['user_id']], $result);
        $this->deleteWhereAny('lb_template_ratings', ['user_id' => $context['user_id']], $result);
        $this->deleteWhereAny('partner_one_time_logins', ['user_id' => $context['user_id']], $result);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function deleteAuthenticationState(User $user, UserDeletionResult $result): void
    {
        $this->deleteWhereAny('sessions', ['user_id' => $user->id], $result);
        $this->deleteWhereAny('password_reset_tokens', ['email' => (string) $user->email], $result);
        $this->deleteWhereAll('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
        ], $result);
        $this->deleteWhereAny('oauth_access_tokens', ['user_id' => $user->id], $result);
        $this->deleteWhereAny('oauth_auth_codes', ['user_id' => $user->id], $result);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function deleteCoreGrowthData(array $context, UserDeletionResult $result): void
    {
        $scope = [
            'user_id' => $context['user_id'],
            'business_id' => $context['business_ids'],
            'campaign_id' => $context['campaign_ids'],
            'customer_id' => $context['customer_ids'],
            'team_id' => $context['personal_team_ids'],
        ];

        foreach ([
            'lb_qr_scans',
            'lb_review_feedbacks',
            'lb_bookings',
            'lb_coupon_redemptions',
            'lb_feedback_responses',
            'lb_lead_submissions',
            'lb_booking_services',
            'lb_locations',
            'lb_landing_pages',
        ] as $table) {
            $this->deleteWhereAny($table, $scope, $result);
        }

        $result->customersDeleted = $this->deleteWhereAny('lb_customers', [
            'id' => $context['customer_ids'],
            'user_id' => $context['user_id'],
            'business_id' => $context['business_ids'],
        ], $result);
        $result->campaignsDeleted = $this->deleteWhereAny('lb_campaigns', [
            'id' => $context['campaign_ids'],
            'user_id' => $context['user_id'],
            'business_id' => $context['business_ids'],
        ], $result);
        $result->businessesDeleted = $this->deleteWhereAny('lb_businesses', [
            'id' => $context['business_ids'],
            'user_id' => $context['user_id'],
        ], $result);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function anonymizeRetainedData(
        User $user,
        array $context,
        UserDeletionResult $result
    ): void {
        $result->addAnonymized('affiliate_commissions', $this->updateWhereAny('affiliate_commissions', ['referred_user_id' => $user->id], [
            'referred_user_id' => null,
            'meta' => null,
        ]));
        $result->addAnonymized('credit_usage_logs', $this->updateWhereAny('credit_usage_logs', ['user_id' => $user->id], [
            'user_id' => null,
            'metadata' => null,
        ]));
        $result->addAnonymized('payment_history', $this->updateWhereAny('payment_history', ['uid' => $user->id], [
            'uid' => null,
            'meta' => null,
        ]));
        $result->addAnonymized('payment_manual', $this->updateWhereAny('payment_manual', ['uid' => $user->id], [
            'uid' => null,
            'payment_info' => null,
            'notes' => null,
        ]));
        $result->addAnonymized('payment_subscriptions', $this->updateWhereAny('payment_subscriptions', ['uid' => $user->id], [
            'uid' => null,
            'subscription_id' => null,
            'customer_id' => null,
            'status' => 0,
        ]));
        $auditQuery = null;

        if ($this->hasTableAndColumns('audit_logs', ['causer_user_id', 'subject_type', 'subject_id'])) {
            $auditQuery = DB::table('audit_logs')->where(function (Builder $query) use ($user): void {
                $query->where('causer_user_id', $user->id)
                    ->orWhere(function (Builder $subject) use ($user): void {
                        $subject->where('subject_type', User::class)
                            ->where('subject_id', $user->id);
                    });
            });
        }

        $result->addAnonymized('audit_logs', $this->updateQuery($auditQuery, 'audit_logs', [
            'causer_user_id' => null,
            'description' => 'Retained anonymized audit event.',
            'subject_id' => null,
            'ip_address' => null,
            'user_agent' => null,
            'metadata' => null,
        ]));
        $result->addAnonymized('notification_manual', $this->updateWhereAny('notification_manual', ['created_by' => $user->id], [
            'created_by' => null,
            'title' => 'Notification retained after account deletion',
            'message' => 'Content removed during user deletion.',
            'url' => null,
        ]));

        foreach ([
            'lb_customer_activities',
            'lb_customer_tag_maps',
            'lb_customer_tasks',
            'lb_customer_segments',
            'lb_crm_automations',
            'lb_customer_merge_logs',
        ] as $table) {
            foreach (['created_by', 'assigned_to', 'merged_by'] as $column) {
                $result->addAnonymized(
                    $table,
                    $this->updateWhereAny($table, [$column => $user->id], [$column => null])
                );
            }
        }
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function deletePersonalTeams(array $context, UserDeletionResult $result): void
    {
        if ($context['personal_team_ids'] === []) {
            return;
        }

        $result->personalTeamsDeleted = $this->deleteWhereAny('teams', [
            'id' => $context['personal_team_ids'],
        ], $result);
        $result->sharedTeamsDetached = count(array_intersect(
            $context['member_team_ids'],
            $context['shared_member_team_ids']
        ));
    }

    /**
     * @param  list<array{disk:string,path:string,directory:bool,verified_user_owned:bool}>  $assets
     * @param  array<string, mixed>  $context
     */
    private function runAfterCommit(
        array $assets,
        array $context,
        UserDeletionResult $result,
        ?int $auditId
    ): void {
        DB::afterCommit(function () use ($assets, $context, $result, $auditId): void {
            try {
                foreach ($assets as $asset) {
                    $cleanup = $this->storageCleanup->delete((int) $context['user_id'], $asset);

                    if ($cleanup['status'] === 'deleted') {
                        $result->storageAssetsDeleted++;
                    } elseif ($cleanup['status'] === 'missing') {
                        $result->storageAssetsMissing++;
                    } else {
                        $result->storageFailureCount++;
                        $result->storageFailures[] = [
                            'asset_fingerprint' => $cleanup['fingerprint'],
                            'reason' => $cleanup['failure_reason'],
                            'retry_status' => $cleanup['retry_status'],
                        ];
                        $result->warnings[] = implode(':', [
                            'storage_delete_failed',
                            $cleanup['fingerprint'],
                            (string) $cleanup['failure_reason'],
                            $cleanup['retry_status'],
                        ]);
                    }
                }

                PortalGrowthDashboardMetrics::forget((int) $context['user_id']);
                PlanLimitGuard::forgetPlanUsageCache((int) $context['user_id']);
                $this->forgetAiContextCache($context);

                $residue = $this->residueInspector->inspect([
                    'user_id' => (int) $context['user_id'],
                    'user_email' => (string) $context['user_email'],
                    'user_username' => (string) $context['user_username'],
                    'user_phone' => (string) $context['user_phone'],
                    'team_ids' => $context['personal_team_ids'],
                    'business_ids' => $context['business_ids'],
                    'campaign_ids' => $context['campaign_ids'],
                    'customer_ids' => $context['customer_ids'],
                    'external_business_ids' => $context['external_business_ids'] ?? [],
                    'request_ids' => $context['request_ids'] ?? [],
                    'storage_assets' => $assets,
                ]);
                $result->databaseResidueCount =
                    array_sum($residue['database']) + array_sum($residue['json']);
                $result->status = match (true) {
                    $result->databaseResidueCount > 0 => 'failed_verification',
                    $result->storageFailureCount > 0 => 'completed_with_warnings',
                    default => 'completed',
                };
            } catch (Throwable $exception) {
                $result->status = 'failed_verification';
                $result->warnings[] = 'post_commit_verification_failed:'.$exception::class;
            } finally {
                $this->updateDeletionAudit($auditId, $result);
            }
        });
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function forgetAiContextCache(array $context): void
    {
        $teamIds = array_values(array_unique(array_merge(
            [0],
            $context['owned_team_ids'],
            $context['member_team_ids'],
        )));
        $locales = array_values(array_unique(array_filter([
            'en',
            'vi',
            strtolower((string) $context['locale']),
            strtolower((string) app()->getLocale()),
        ])));

        foreach ($teamIds as $teamId) {
            foreach ($locales as $locale) {
                Cache::forget("mlhub_ai_context:{$context['user_id']}:team-{$teamId}:{$locale}");
            }
        }
    }

    /**
     * @param  array<string, mixed>  $context
     * @return list<array{disk:string,path:string,directory:bool,verified_user_owned:bool}>
     */
    private function collectStorageAssets(User $user, array $context): array
    {
        $assets = [];
        $this->addAsset($assets, (string) ($user->avatar_disk ?: 'public'), (string) $user->avatar_path);

        if ($this->hasTableAndColumns('files', ['owner_user_id', 'disk', 'path'])) {
            $select = ['disk', 'path'];

            if (in_array('is_folder', $this->columns('files'), true)) {
                $select[] = 'is_folder';
            }

            foreach (DB::table('files')->where('owner_user_id', $user->id)->whereNotNull('path')->get($select) as $file) {
                $this->addAsset(
                    $assets,
                    (string) ($file->disk ?: 'public'),
                    (string) $file->path,
                    (bool) ($file->is_folder ?? false),
                    true
                );
            }
        }

        if ($this->hasTableAndColumns('lb_businesses', ['id', 'qr_design'])
            && $context['business_ids'] !== []) {
            foreach (DB::table('lb_businesses')->whereIn('id', $context['business_ids'])->pluck('qr_design') as $design) {
                $this->addAsset($assets, 'public', (string) data_get($this->decodeJson($design), 'logo_path'));
            }
        }

        if ($this->hasTableAndColumns('lb_locations', ['business_id', 'qr_design'])
            && $context['business_ids'] !== []) {
            foreach (DB::table('lb_locations')->whereIn('business_id', $context['business_ids'])->pluck('qr_design') as $design) {
                $this->addAsset($assets, 'public', (string) data_get($this->decodeJson($design), 'logo_path'));
            }
        }

        if ($this->hasTableAndColumns('lb_customers', ['id', 'avatar'])
            && $context['customer_ids'] !== []) {
            foreach (DB::table('lb_customers')->whereIn('id', $context['customer_ids'])->pluck('avatar') as $avatar) {
                $this->addAsset($assets, 'public', (string) $avatar);
            }
        }

        if ($this->hasTableAndColumns('social_accounts', ['id', 'avatar_path'])
            && $context['deletable_social_account_ids'] !== []) {
            $select = ['avatar_path'];

            if (in_array('avatar_disk', $this->columns('social_accounts'), true)) {
                $select[] = 'avatar_disk';
            }

            foreach (DB::table('social_accounts')->whereIn('id', $context['deletable_social_account_ids'])->get($select) as $account) {
                $this->addAsset($assets, (string) ($account->avatar_disk ?? 'public'), (string) $account->avatar_path);
            }
        }

        foreach ([
            ['lb_marketing_templates', ['user_id' => $user->id, 'team_id' => $context['personal_team_ids']]],
            ['lb_template_packs', ['created_by_user_id' => $user->id, 'team_id' => $context['personal_team_ids']]],
        ] as [$table, $conditions]) {
            if (! $this->hasTableAndColumns($table, ['preview_image'])) {
                continue;
            }

            foreach ($this->queryWhereAny($table, $conditions)->pluck('preview_image') as $path) {
                $this->addAsset($assets, 'public', (string) $path);
            }
        }

        if ($this->hasTableAndColumns('team_messages', ['attachments', 'conversation_id'])) {
            $conversationIds = $this->pluckIds('team_conversations', [
                'team_id' => $context['personal_team_ids'],
            ]);
            $query = DB::table('team_messages')->where(function (Builder $builder) use ($conversationIds, $user): void {
                if ($conversationIds !== []) {
                    $builder->whereIn('conversation_id', $conversationIds);
                }

                if (in_array('user_id', $this->columns('team_messages'), true)) {
                    $conversationIds === []
                        ? $builder->where('user_id', $user->id)
                        : $builder->orWhere('user_id', $user->id);
                }
            });

            foreach ($query->pluck('attachments') as $attachments) {
                $this->addAssetsFromJson($assets, $this->decodeJson($attachments));
            }
        }

        return collect($assets)
            ->unique(fn (array $asset): string => $asset['disk'].'|'.$asset['path'].'|'.($asset['directory'] ? 'd' : 'f'))
            ->values()
            ->all();
    }

    /**
     * @param  list<array{disk:string,path:string,directory:bool,verified_user_owned:bool}>  $assets
     */
    private function addAsset(
        array &$assets,
        string $disk,
        string $path,
        bool $directory = false,
        bool $verifiedUserOwned = false
    ): void {
        $disk = trim($disk) ?: 'public';
        $path = trim(str_replace('\\', '/', $path));

        if ($path === '' || Str::startsWith(Str::lower($path), ['http://', 'https://', '//', 'data:'])) {
            return;
        }

        if (preg_match('/^[a-z]:\//i', $path) === 1) {
            return;
        }

        if (Str::startsWith($path, '/')) {
            if ($disk !== 'public'
                || ! Str::startsWith(Str::lower($path), ['/storage/', '/public/storage/'])) {
                return;
            }
        }

        $path = ltrim($path, '/');

        if ($disk === 'public') {
            $path = Str::replaceStart('public/storage/', '', $path);
            $path = Str::replaceStart('storage/', '', $path);
            $path = Str::replaceStart('public/', '', $path);
        }

        if ($path === '' || $path === '.' || Str::contains($path, ['../', '/..'])) {
            return;
        }

        $assets[] = [
            'disk' => $disk,
            'path' => $path,
            'directory' => $directory,
            'verified_user_owned' => $verifiedUserOwned,
        ];
    }

    /**
     * @param  list<array{disk:string,path:string,directory:bool,verified_user_owned:bool}>  $assets
     * @param  array<mixed>  $value
     */
    private function addAssetsFromJson(array &$assets, array $value): void
    {
        if (isset($value['path']) && is_scalar($value['path'])) {
            $this->addAsset(
                $assets,
                is_scalar($value['disk'] ?? null) ? (string) $value['disk'] : 'public',
                (string) $value['path'],
                (bool) ($value['is_folder'] ?? false)
            );
        }

        foreach ($value as $nested) {
            if (is_array($nested)) {
                $this->addAssetsFromJson($assets, $nested);
            }
        }
    }

    /**
     * @return array<mixed>
     */
    private function decodeJson(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param  array<string, int|list<int>|string>  $conditions
     */
    private function deleteWhereAny(string $table, array $conditions, UserDeletionResult $result): int
    {
        $query = $this->queryWhereAny($table, $conditions);

        return $this->deleteQuery($query, $table, $result);
    }

    /**
     * @param  array<string, int|list<int>|string>  $conditions
     */
    private function deleteWhereAll(string $table, array $conditions, UserDeletionResult $result): int
    {
        $query = $this->queryWhereAll($table, $conditions);

        return $this->deleteQuery($query, $table, $result);
    }

    private function deleteQuery(?Builder $query, string $table, UserDeletionResult $result): int
    {
        if (! $query) {
            return 0;
        }

        $count = $query->delete();
        $result->addDeleted($table, $count);

        return $count;
    }

    /**
     * @param  array<string, int|list<int>|string>  $conditions
     * @param  array<string, mixed>  $payload
     */
    private function updateWhereAny(string $table, array $conditions, array $payload): int
    {
        $query = $this->queryWhereAny($table, $conditions);

        return $this->updateQuery($query, $table, $payload);
    }

    /**
     * @param  array<string, int|list<int>|string>  $conditions
     * @param  array<string, mixed>  $payload
     */
    private function updateWhereAll(string $table, array $conditions, array $payload): int
    {
        $query = $this->queryWhereAll($table, $conditions);

        return $this->updateQuery($query, $table, $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function updateQuery(?Builder $query, string $table, array $payload): int
    {
        $payload = $this->existingPayload($table, $payload);

        if (! $query || $payload === []) {
            return 0;
        }

        return $query->update($payload);
    }

    /**
     * @param  array<string, int|list<int>|string>  $conditions
     */
    private function queryWhereAny(string $table, array $conditions): ?Builder
    {
        return $this->queryWhere($table, $conditions, false);
    }

    /**
     * @param  array<string, int|list<int>|string>  $conditions
     */
    private function queryWhereAll(string $table, array $conditions): ?Builder
    {
        return $this->queryWhere($table, $conditions, true);
    }

    /**
     * @param  array<string, int|list<int>|string>  $conditions
     */
    private function queryWhere(string $table, array $conditions, bool $requireAll): ?Builder
    {
        if (! Schema::hasTable($table)) {
            return null;
        }

        $valid = [];

        foreach ($conditions as $column => $value) {
            if (! in_array($column, $this->columns($table), true)) {
                continue;
            }

            if (is_array($value) && $value === []) {
                continue;
            }

            if (! is_array($value) && $value === '') {
                continue;
            }

            $valid[$column] = $value;
        }

        if ($valid === []) {
            return null;
        }

        return DB::table($table)->where(function (Builder $query) use ($valid, $requireAll): void {
            $first = true;

            foreach ($valid as $column => $value) {
                $method = $first || $requireAll ? 'where' : 'orWhere';

                if (is_array($value)) {
                    $query->{$method}(fn (Builder $nested) => $nested->whereIn($column, $value));
                } else {
                    $query->{$method}($column, $value);
                }

                $first = false;
            }
        });
    }

    /**
     * @param  array<string, int|list<int>|string>  $conditions
     * @return list<int>
     */
    private function pluckIds(string $table, array $conditions, string $idColumn = 'id'): array
    {
        if (! Schema::hasTable($table) || ! in_array($idColumn, $this->columns($table), true)) {
            return [];
        }

        $query = $this->queryWhereAny($table, $conditions);

        if (! $query) {
            return [];
        }

        return $query->pluck($idColumn)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function existingPayload(string $table, array $payload): array
    {
        $columns = $this->columns($table);

        return array_filter(
            $payload,
            fn (string $column): bool => in_array($column, $columns, true),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * @return list<string>
     */
    private function columns(string $table): array
    {
        if (! array_key_exists($table, $this->columnCache)) {
            $this->columnCache[$table] = Schema::hasTable($table)
                ? Schema::getColumnListing($table)
                : [];
        }

        return $this->columnCache[$table];
    }

    /**
     * @param  list<string>  $columns
     */
    private function hasTableAndColumns(string $table, array $columns): bool
    {
        $existing = $this->columns($table);

        return $existing !== [] && array_diff($columns, $existing) === [];
    }

    /**
     * @param  list<int>  $ownedAccountIds
     * @return list<int>
     */
    private function sharedSocialAccountIds(int $userId, array $ownedAccountIds): array
    {
        if ($ownedAccountIds === [] || ! $this->hasTableAndColumns('team_user', ['user_id', 'managed_account_ids'])) {
            return [];
        }

        $shared = [];

        foreach (DB::table('team_user')->where('user_id', '!=', $userId)->pluck('managed_account_ids') as $managedIds) {
            foreach ($this->decodeJson($managedIds) as $accountId) {
                $accountId = (int) $accountId;

                if (in_array($accountId, $ownedAccountIds, true)) {
                    $shared[] = $accountId;
                }
            }
        }

        return array_values(array_unique($shared));
    }

    private function anonymizeCreatorReferencesOutsideOwnedScope(string $table, int $userId): void
    {
        $this->updateWhereAny($table, ['created_by' => $userId], ['created_by' => null]);
    }

    private function writeDeletionAudit(?int $actorUserId, UserDeletionResult $result): ?int
    {
        if (! Schema::hasTable('audit_logs')) {
            return null;
        }

        $payload = $this->existingPayload('audit_logs', [
            'causer_user_id' => $actorUserId === $result->userId ? null : $actorUserId,
            'event' => 'admin.users.delete',
            'description' => 'Deleted a user and owned operational data.',
            'subject_type' => User::class,
            'subject_id' => null,
            'area' => 'admin',
            'ip_address' => null,
            'user_agent' => null,
            'metadata' => json_encode($result->auditMetadata(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (in_array('id', $this->columns('audit_logs'), true)) {
            return (int) DB::table('audit_logs')->insertGetId($payload);
        }

        DB::table('audit_logs')->insert($payload);

        return null;
    }

    private function updateDeletionAudit(?int $auditId, UserDeletionResult $result): void
    {
        if (! $auditId || ! $this->hasTableAndColumns('audit_logs', ['id', 'metadata'])) {
            return;
        }

        DB::table('audit_logs')
            ->where('id', $auditId)
            ->update($this->existingPayload('audit_logs', [
                'metadata' => json_encode(
                    $result->auditMetadata(),
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                ),
                'updated_at' => now(),
            ]));
    }
}
