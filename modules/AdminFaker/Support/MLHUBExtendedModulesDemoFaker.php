<?php

namespace Modules\AdminFaker\Support;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\AppAdvancedCustomerCrm\Models\CrmAutomation;
use Modules\AppAdvancedCustomerCrm\Models\CrmAutomationLog;
use Modules\AppAdvancedCustomerCrm\Models\CustomerActivity;
use Modules\AppAdvancedCustomerCrm\Models\CustomerNote;
use Modules\AppAdvancedCustomerCrm\Models\CustomerScoreLog;
use Modules\AppAdvancedCustomerCrm\Models\CustomerSegment;
use Modules\AppAdvancedCustomerCrm\Models\CustomerTask;
use Modules\AppAdvancedCustomerCrm\Support\CustomerScoreService;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppCustomers\Models\Customer;
use Modules\AppEmailAutomation\Models\EmailAutomation;
use Modules\AppEmailAutomation\Models\EmailAutomationLog;
use Modules\AppEmailAutomation\Models\EmailTemplate;
use Modules\AppEmailAutomation\Support\EmailAutomationService;
use Modules\AppLoyaltyStampCards\Models\LoyaltyCard;
use Modules\AppLoyaltyStampCards\Models\LoyaltyCustomer;
use Modules\AppLoyaltyStampCards\Models\LoyaltyStamp;
use Modules\AppLoyaltyStampCards\Models\Referral;
use Modules\AppLoyaltyStampCards\Models\ReferralCampaign;
use Modules\AppLoyaltyStampCards\Models\ReferralLink;
use Modules\AppMarketingTemplates\Support\TemplatePackService;

class MLHUBExtendedModulesDemoFaker
{
    public function seed(User $user, Team $team, array &$counts): void
    {
        $this->clear($user, $counts);

        $this->seedMarketingExtras();
        $this->seedEmailAutomation($user, $counts);
        $this->seedAdvancedCrm($user, $team, $counts);
        $this->seedLoyaltyAndReferral($user, $team, $counts);
    }

    public function clear(User $user, array &$deleted): void
    {
        if (class_exists(ReferralCampaign::class) && Schema::hasTable('lb_referral_campaigns')) {
            $campaignIds = ReferralCampaign::query()
                ->where('user_id', $user->id)
                ->where('slug', 'like', 'admin-faker-%')
                ->pluck('id');

            if ($campaignIds->isNotEmpty()) {
                $deleted['referral_records'] = ($deleted['referral_records'] ?? 0)
                    + Referral::query()->whereIn('campaign_id', $campaignIds)->delete();
                $deleted['referral_links'] = ($deleted['referral_links'] ?? 0)
                    + ReferralLink::query()->whereIn('campaign_id', $campaignIds)->delete();
                $deleted['referral_campaigns'] = ($deleted['referral_campaigns'] ?? 0)
                    + ReferralCampaign::query()->whereIn('id', $campaignIds)->delete();
            }
        }

        if (class_exists(LoyaltyCard::class) && Schema::hasTable('lb_loyalty_cards')) {
            $cardIds = LoyaltyCard::query()
                ->where('user_id', $user->id)
                ->where('slug', 'like', 'admin-faker-%')
                ->pluck('id');

            if ($cardIds->isNotEmpty()) {
                $deleted['loyalty_stamps'] = ($deleted['loyalty_stamps'] ?? 0)
                    + LoyaltyStamp::query()->whereIn('card_id', $cardIds)->delete();
                $deleted['loyalty_cards'] = ($deleted['loyalty_cards'] ?? 0)
                    + LoyaltyCard::query()->whereIn('id', $cardIds)->delete();
            }
        }

        if (class_exists(EmailAutomation::class) && Schema::hasTable('lb_email_automations')) {
            $automationIds = EmailAutomation::query()
                ->where('user_id', $user->id)
                ->where('name', 'like', '[DEMO]%')
                ->pluck('id');

            if ($automationIds->isNotEmpty()) {
                $deleted['email_automation_logs'] = ($deleted['email_automation_logs'] ?? 0)
                    + EmailAutomationLog::query()->whereIn('automation_id', $automationIds)->delete();
                $deleted['email_automations'] = ($deleted['email_automations'] ?? 0)
                    + EmailAutomation::query()->whereIn('id', $automationIds)->delete();
            }
        }

        $teamId = $this->teamId($user, $team);

        if (class_exists(CrmAutomation::class) && Schema::hasTable('lb_crm_automations')) {
            $crmAutomationIds = CrmAutomation::query()
                ->where('team_id', $teamId)
                ->where('name', 'like', '[DEMO]%')
                ->pluck('id');

            if ($crmAutomationIds->isNotEmpty()) {
                $deleted['crm_automation_logs'] = ($deleted['crm_automation_logs'] ?? 0)
                    + CrmAutomationLog::query()->whereIn('automation_id', $crmAutomationIds)->delete();
                $deleted['crm_automations'] = ($deleted['crm_automations'] ?? 0)
                    + CrmAutomation::query()->whereIn('id', $crmAutomationIds)->delete();
            }
        }

        if (class_exists(CustomerActivity::class) && Schema::hasTable('lb_customer_activities')) {
            $deleted['crm_activities'] = ($deleted['crm_activities'] ?? 0)
                + CustomerActivity::query()
                    ->where('team_id', $teamId)
                    ->where('metadata->source', DemoMarker::SOURCE)
                    ->delete();
        }

        if (class_exists(CustomerTask::class) && Schema::hasTable('lb_customer_tasks')) {
            $deleted['crm_tasks'] = ($deleted['crm_tasks'] ?? 0)
                + CustomerTask::query()
                    ->where('team_id', $teamId)
                    ->where('title', 'like', '[DEMO]%')
                    ->delete();
        }

        if (class_exists(CustomerNote::class) && Schema::hasTable('lb_customer_notes')) {
            $deleted['crm_notes'] = ($deleted['crm_notes'] ?? 0)
                + CustomerNote::query()
                    ->where('team_id', $teamId)
                    ->where('note', 'like', '[DEMO]%')
                    ->delete();
        }

        if (class_exists(CustomerSegment::class) && Schema::hasTable('lb_customer_segments')) {
            $deleted['crm_segments'] = ($deleted['crm_segments'] ?? 0)
                + CustomerSegment::query()
                    ->where('team_id', $teamId)
                    ->where('name', 'like', '[DEMO]%')
                    ->delete();
        }
    }

    protected function seedMarketingExtras(): void
    {
        if (class_exists(EmailAutomationService::class)) {
            app(EmailAutomationService::class)->ensureSystemTemplates();
        }

        if (class_exists(TemplatePackService::class)) {
            app(TemplatePackService::class)->ensureSystemPacks();
        }
    }

    protected function seedEmailAutomation(User $user, array &$counts): void
    {
        if (! class_exists(EmailAutomation::class) || ! Schema::hasTable('lb_email_automations')) {
            return;
        }

        $businesses = $this->businessMap($user);
        $customerLimit = min(MLHUBAdminFakerConfig::customerTarget(), 60 * MLHUBAdminFakerConfig::volumeScale());
        $customers = Customer::query()->where('user_id', $user->id)->orderBy('id')->limit($customerLimit)->get();

        foreach (MLHUBAdminFakerConfig::extensions()['email_automations'] ?? [] as $index => $row) {
            $template = EmailTemplate::query()
                ->where('is_system', true)
                ->where('name', $row['template'])
                ->first();

            if (! $template) {
                continue;
            }

            $businessId = isset($row['business_key'], $businesses[$row['business_key']])
                ? $businesses[$row['business_key']]->id
                : null;

            $automation = EmailAutomation::query()->updateOrCreate(
                ['user_id' => $user->id, 'name' => $row['name']],
                [
                    'business_id' => $businessId,
                    'email_template_id' => $template->id,
                    'trigger_event' => $row['trigger_event'],
                    'status' => 'active',
                    'delay_type' => 'immediate',
                    'delay_value' => 0,
                    'delay_unit' => 'minutes',
                    'condition_json' => ['rules' => []],
                    'action_json' => [],
                    'send_to' => 'customer',
                    'created_by' => $user->id,
                ],
            );

            $counts['email_automations'] = ($counts['email_automations'] ?? 0) + 1;

            $customer = $customers[$index % max(1, $customers->count())] ?? null;

            if (! $customer) {
                continue;
            }

            $statuses = ['sent', 'sent', 'sent', 'opened', 'queued'];
            $status = $statuses[$index % count($statuses)];
            $sentAt = $status === 'queued' ? null : now()->subDays($index + 2);

            EmailAutomationLog::query()->updateOrCreate(
                [
                    'automation_id' => $automation->id,
                    'recipient_email' => $customer->email ?: 'demo+'.$customer->id.'@mlhub.vn',
                    'trigger_event' => $row['trigger_event'],
                ],
                [
                    'user_id' => $user->id,
                    'email_template_id' => $template->id,
                    'business_id' => $customer->business_id,
                    'customer_id' => $customer->id,
                    'related_type' => Customer::class,
                    'related_id' => $customer->id,
                    'recipient_name' => $customer->name,
                    'subject' => $template->subject,
                    'body' => $template->body,
                    'status' => $status,
                    'queued_at' => now()->subDays($index + 3),
                    'sent_at' => $sentAt,
                    'opened_at' => $status === 'opened' ? now()->subDays($index + 1) : null,
                ],
            );

            $counts['email_automation_logs'] = ($counts['email_automation_logs'] ?? 0) + 1;
        }
    }

    protected function seedAdvancedCrm(User $user, Team $team, array &$counts): void
    {
        if (! class_exists(CustomerActivity::class) || ! Schema::hasTable('lb_customer_activities')) {
            return;
        }

        $teamId = $this->teamId($user, $team);
        $crmLimit = min(MLHUBAdminFakerConfig::customerTarget(), 150 * MLHUBAdminFakerConfig::volumeScale());
        $customers = Customer::query()
            ->where('user_id', $user->id)
            ->with('business')
            ->orderByDesc('last_activity_at')
            ->limit($crmLimit)
            ->get();

        if ($customers->isEmpty()) {
            return;
        }

        foreach (MLHUBAdminFakerConfig::extensions()['crm_segments'] ?? [] as $segmentRow) {
            CustomerSegment::query()->updateOrCreate(
                ['team_id' => $teamId, 'name' => '[DEMO] '.$segmentRow['name']],
                [
                    'business_id' => null,
                    'description' => __('Demo segment for investor presentation.'),
                    'filters' => $segmentRow['filters'],
                    'is_dynamic' => true,
                    'color' => $segmentRow['color'],
                    'created_by' => $user->id,
                ],
            );
            $counts['crm_segments'] = ($counts['crm_segments'] ?? 0) + 1;
        }

        foreach (MLHUBAdminFakerConfig::extensions()['crm_automations'] ?? [] as $automationRow) {
            $automation = CrmAutomation::query()->updateOrCreate(
                ['team_id' => $teamId, 'name' => $automationRow['name']],
                [
                    'business_id' => null,
                    'trigger_event' => $automationRow['trigger_event'],
                    'condition_json' => ['rules' => []],
                    'action_json' => [
                        'actions' => [[
                            'type' => $automationRow['action_type'],
                            'value' => $automationRow['action_value'] ?? '',
                            'title' => $automationRow['action_title'] ?? __('Follow up with customer'),
                            'priority' => 'medium',
                        ]],
                    ],
                    'delay_type' => 'immediate',
                    'status' => 'active',
                    'created_by' => $user->id,
                ],
            );

            $counts['crm_automations'] = ($counts['crm_automations'] ?? 0) + 1;

            CrmAutomationLog::query()->firstOrCreate(
                [
                    'automation_id' => $automation->id,
                    'status' => 'success',
                    'message' => __('Demo automation run recorded.'),
                ],
                [
                    'team_id' => $teamId,
                    'payload' => ['source' => DemoMarker::SOURCE, 'trigger' => $automationRow['trigger_event']],
                    'created_at' => now()->subDays(3),
                    'updated_at' => now()->subDays(2),
                ],
            );
        }

        $activityTypes = [
            ['type' => 'booking_completed', 'title' => 'Hoàn tất đặt lịch', 'module' => 'AppBookingPages', 'icon' => 'fa-light fa-calendar-check'],
            ['type' => 'coupon_claimed', 'title' => 'Đã nhận mã ưu đãi', 'module' => 'AppCouponCampaigns', 'icon' => 'fa-light fa-ticket'],
            ['type' => 'lead_submitted', 'title' => 'Gửi form lead', 'module' => 'AppLeadForms', 'icon' => 'fa-light fa-user-plus'],
            ['type' => 'feedback_submitted', 'title' => 'Gửi phản hồi', 'module' => 'AppFeedbackForms', 'icon' => 'fa-light fa-message-lines'],
            ['type' => 'email_sent', 'title' => 'Email tự động đã gửi', 'module' => 'AppEmailAutomation', 'icon' => 'fa-light fa-envelope'],
            ['type' => 'loyalty_stamp_added', 'title' => 'Tích thêm tem loyalty', 'module' => 'AppLoyaltyStampCards', 'icon' => 'fa-light fa-stamp'],
        ];

        foreach ($customers as $index => $customer) {
            $score = min(100, 20 + ($index % 75));
            $customer->forceFill([
                'team_id' => $teamId,
                'status' => $index % 11 === 0 ? 'vip' : 'active',
                'score' => $score,
                'lifetime_value' => 150000 + ($index * 12500),
                'total_bookings' => $index % 6,
                'total_coupon_claims' => $index % 5,
                'total_feedback' => $index % 4,
                'total_loyalty_stamps' => $index % 8,
                'total_referrals' => $index % 3,
                'last_activity_at' => now()->subDays($index % 30),
            ])->save();

            if ($index < min(3000, 150 * MLHUBAdminFakerConfig::volumeScale()) && class_exists(CustomerScoreService::class)) {
                $reason = '[DEMO] Điểm khách hàng mẫu';
                if (! CustomerScoreLog::query()
                    ->where('customer_id', $customer->id)
                    ->where('reason', $reason)
                    ->exists()) {
                    app(CustomerScoreService::class)->add($customer, $index % 5, $reason);
                }
            }

            $activity = $activityTypes[$index % count($activityTypes)];
            CustomerActivity::query()->updateOrCreate(
                [
                    'customer_id' => $customer->id,
                    'type' => $activity['type'],
                    'title' => $activity['title'],
                ],
                [
                    'team_id' => $teamId,
                    'business_id' => $customer->business_id,
                    'description' => ($customer->business?->name ?: 'MLHUB Demo').' — '.__('Investor demo timeline'),
                    'source_module' => $activity['module'],
                    'icon' => $activity['icon'],
                    'color' => '#ff5f5f',
                    'metadata' => ['source' => DemoMarker::SOURCE],
                    'created_by' => $user->id,
                    'occurred_at' => now()->subDays(($index % 45) + 1),
                ],
            );
            $counts['crm_activities'] = ($counts['crm_activities'] ?? 0) + 1;

            if ($index < min(2000, 100 * MLHUBAdminFakerConfig::volumeScale()) && class_exists(CustomerTask::class)) {
                CustomerTask::query()->updateOrCreate(
                    [
                        'customer_id' => $customer->id,
                        'title' => '[DEMO] Gọi lại '.$customer->name,
                    ],
                    [
                        'team_id' => $teamId,
                        'business_id' => $customer->business_id,
                        'assigned_to' => $user->id,
                        'description' => __('Follow-up sau chiến dịch growth tool.'),
                        'type' => 'follow_up',
                        'priority' => $index % 4 === 0 ? 'high' : 'medium',
                        'status' => $index % 3 === 0 ? 'completed' : 'open',
                        'due_at' => now()->addDays($index % 7),
                        'completed_at' => $index % 3 === 0 ? now()->subDay() : null,
                        'created_by' => $user->id,
                    ],
                );
                $counts['crm_tasks'] = ($counts['crm_tasks'] ?? 0) + 1;
            }

            if ($index < min(1500, 75 * MLHUBAdminFakerConfig::volumeScale()) && class_exists(CustomerNote::class)) {
                CustomerNote::query()->updateOrCreate(
                    [
                        'customer_id' => $customer->id,
                        'note' => '[DEMO] Khách thích combo cuối tuần, ưu tiên Zalo.',
                    ],
                    [
                        'team_id' => $teamId,
                        'business_id' => $customer->business_id,
                        'user_id' => $user->id,
                        'visibility' => 'team',
                        'pinned' => $index < 3,
                    ],
                );
                $counts['crm_notes'] = ($counts['crm_notes'] ?? 0) + 1;
            }
        }
    }

    protected function seedLoyaltyAndReferral(User $user, Team $team, array &$counts): void
    {
        if (! class_exists(LoyaltyCard::class) || ! Schema::hasTable('lb_loyalty_cards')) {
            return;
        }

        $teamId = $this->teamId($user, $team);
        $businesses = $this->businessMap($user);
        $customers = Customer::query()->where('user_id', $user->id)->orderBy('id')->get();

        if ($customers->isEmpty()) {
            return;
        }

        foreach (MLHUBAdminFakerConfig::extensions()['loyalty_cards'] ?? [] as $cardIndex => $row) {
            $business = $businesses[$row['business_key']] ?? null;

            if (! $business) {
                continue;
            }

            $card = LoyaltyCard::query()->updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'user_id' => $user->id,
                    'team_id' => $teamId,
                    'business_id' => $business->id,
                    'name' => $row['name'],
                    'required_stamps' => (int) $row['required_stamps'],
                    'stamp_method' => 'qr_scan',
                    'customer_identifier' => 'phone',
                    'reward_title' => $row['reward_title'],
                    'reward_type' => 'free_item',
                    'reward_value' => $row['reward_value'],
                    'expiry_days' => 90,
                    'status' => 'active',
                    'settings' => ['demo_marker' => DemoMarker::SOURCE],
                ],
            );

            $counts['loyalty_cards'] = ($counts['loyalty_cards'] ?? 0) + 1;

            $enrollCap = min(1500, 75 * MLHUBAdminFakerConfig::volumeScale());
            $enrolled = $customers->where('business_id', $business->id)->take($enrollCap);

            if ($enrolled->isEmpty()) {
                $enrolled = $customers->take($enrollCap);
            }

            foreach ($enrolled as $customerIndex => $customer) {
                $stampCount = min((int) $row['required_stamps'] - 1, 3 + ($customerIndex % 5));

                LoyaltyCustomer::query()->updateOrCreate(
                    ['card_id' => $card->id, 'customer_id' => $customer->id],
                    [
                        'stamps_count' => $stampCount,
                        'completed_count' => $customerIndex % 9 === 0 ? 1 : 0,
                        'last_stamp_at' => now()->subDays($customerIndex % 14),
                    ],
                );

                for ($s = 0; $s < $stampCount; $s++) {
                    LoyaltyStamp::query()->firstOrCreate(
                        [
                            'card_id' => $card->id,
                            'customer_id' => $customer->id,
                            'created_at' => now()->subDays($customerIndex + $s + 1)->startOfDay(),
                        ],
                        [
                            'source' => 'qr_scan',
                            'staff_id' => $user->id,
                        ],
                    );
                    $counts['loyalty_stamps'] = ($counts['loyalty_stamps'] ?? 0) + 1;
                }
            }
        }

        if (! class_exists(ReferralCampaign::class) || ! Schema::hasTable('lb_referral_campaigns')) {
            return;
        }

        foreach (MLHUBAdminFakerConfig::extensions()['referral_campaigns'] ?? [] as $refIndex => $row) {
            $business = $businesses[$row['business_key']] ?? null;

            if (! $business) {
                continue;
            }

            $campaign = ReferralCampaign::query()->updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'user_id' => $user->id,
                    'team_id' => $teamId,
                    'business_id' => $business->id,
                    'name' => $row['name'],
                    'reward_title' => $row['reward_title'],
                    'reward_type' => 'coupon',
                    'reward_value' => '20%',
                    'required_referrals' => (int) $row['required_referrals'],
                    'target_action' => $row['target_action'],
                    'expiry_days' => 60,
                    'expires_at' => now()->addDays(60),
                    'status' => 'active',
                    'settings' => ['demo_marker' => DemoMarker::SOURCE],
                ],
            );

            $counts['referral_campaigns'] = ($counts['referral_campaigns'] ?? 0) + 1;

            $referrerCap = min(800, 40 * MLHUBAdminFakerConfig::volumeScale());
            $referrers = $customers->where('business_id', $business->id)->take($referrerCap);

            if ($referrers->isEmpty()) {
                $referrers = $customers->take($referrerCap);
            }

            foreach ($referrers as $i => $referrer) {
                $code = 'DEMO'.strtoupper(Str::random(6)).$refIndex.$i;
                $link = ReferralLink::query()->updateOrCreate(
                    ['code' => $code],
                    [
                        'campaign_id' => $campaign->id,
                        'customer_id' => $referrer->id,
                        'clicks_count' => 12 + $i,
                        'conversions_count' => $i % 2,
                        'status' => 'active',
                    ],
                );

                $counts['referral_links'] = ($counts['referral_links'] ?? 0) + 1;

                if ($i % 2 !== 0) {
                    continue;
                }

                $referred = $customers->where('id', '!=', $referrer->id)->values()->get($i + 1);

                if (! $referred) {
                    continue;
                }

                Referral::query()->firstOrCreate(
                    [
                        'campaign_id' => $campaign->id,
                        'referral_link_id' => $link->id,
                        'referrer_customer_id' => $referrer->id,
                        'referred_customer_id' => $referred->id,
                    ],
                    [
                        'target_action' => $row['target_action'],
                        'status' => 'converted',
                        'converted_at' => now()->subDays($i + 3),
                    ],
                );

                $counts['referral_records'] = ($counts['referral_records'] ?? 0) + 1;
            }
        }
    }

    /**
     * @return array<string, LocalBusiness>
     */
    protected function businessMap(User $user): array
    {
        $keys = array_keys(MLHUBAdminFakerConfig::load()['businesses'] ?? []);
        $names = MLHUBAdminFakerConfig::businessNames();

        $businesses = LocalBusiness::query()
            ->where('user_id', $user->id)
            ->whereIn('name', $names)
            ->get()
            ->keyBy('name');

        $map = [];
        $config = MLHUBAdminFakerConfig::load()['businesses'] ?? [];

        foreach ($keys as $key) {
            $name = (string) ($config[$key]['name'] ?? '');

            if ($name !== '' && $businesses->has($name)) {
                $map[$key] = $businesses->get($name);
            }
        }

        return $map;
    }

    protected function teamId(User $user, Team $team): int
    {
        return (int) ($team->id ?: $user->ownedTeams()->value('id') ?: $user->id);
    }
}
