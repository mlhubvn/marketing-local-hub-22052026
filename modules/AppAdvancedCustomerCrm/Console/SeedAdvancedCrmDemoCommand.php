<?php

namespace Modules\AppAdvancedCustomerCrm\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Modules\AdminUser\Models\User;
use Modules\AppAdvancedCustomerCrm\Models\CustomerActivity;
use Modules\AppAdvancedCustomerCrm\Models\CustomerNote;
use Modules\AppAdvancedCustomerCrm\Models\CustomerScoreLog;
use Modules\AppAdvancedCustomerCrm\Models\CustomerTag;
use Modules\AppAdvancedCustomerCrm\Models\CustomerTask;
use Modules\AppAdvancedCustomerCrm\Support\CustomerScoreService;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppCustomers\Models\Customer;

class SeedAdvancedCrmDemoCommand extends Command
{
    protected $signature = 'advanced-crm:seed-demo {--user=1}';

    protected $description = 'Seed demo CRM customers, tags, notes, tasks, and activities.';

    public function handle(): int
    {
        $user = User::query()->find((int) $this->option('user')) ?: User::query()->first();
        if (! $user) {
            $this->error('No user found.');

            return self::FAILURE;
        }

        $business = LocalBusiness::query()->where('user_id', $user->id)->first();
        if (! $business) {
            $this->error('No business found for user.');

            return self::FAILURE;
        }

        $tags = collect([
            ['VIP', '#f59e0b'],
            ['Needs Follow-up', '#dc2626'],
            ['Loyal Customer', '#16a34a'],
            ['Referral Customer', '#0891b2'],
            ['Coupon Claimed', '#7c3aed'],
        ])->map(fn ($tag) => CustomerTag::query()->firstOrCreate(
            ['owner_user_id' => $user->id, 'slug' => Str::slug($tag[0])],
            ['name' => $tag[0], 'color' => $tag[1], 'is_system' => true]
        ));

        for ($i = 1; $i <= 30; $i++) {
            $customer = Customer::query()->firstOrCreate(
                ['user_id' => $user->id, 'business_id' => $business->id, 'email' => "crm{$i}@demo.test"],
                [
                    'name' => "CRM Demo Customer {$i}",
                    'phone' => '555-01'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                    'status' => $i % 7 === 0 ? 'vip' : 'active',
                    'first_seen_at' => now()->subDays(60 - $i),
                    'last_activity_at' => now()->subDays($i % 20),
                    'score' => ($i * 7) % 100,
                    'total_bookings' => $i % 5,
                    'total_coupon_claims' => $i % 4,
                    'total_feedback' => $i % 3,
                    'total_loyalty_stamps' => $i % 9,
                    'total_referrals' => $i % 2,
                ]
            );

            $tag = $tags[$i % $tags->count()];
            $customer->crmTags()->syncWithoutDetaching([$tag->id => ['owner_user_id' => $user->id, 'created_by' => $user->id, 'created_at' => now()]]);

            $this->recordDemoActivity($customer, 'customer_created', 'Customer profile created', 'CRM demo import', 'advanced_crm', $user->id, $i + 12);
            $this->recordDemoActivity($customer, 'booking_completed', 'Booking completed', 'Massage Therapy · '.$business->name, 'AppBookingPages', $user->id, $i + 8);
            $this->recordDemoActivity($customer, 'coupon_claimed', 'Coupon claimed', 'SAVE20 · 20% Off Next Visit', 'AppCouponCampaigns', $user->id, $i + 6);
            $this->recordDemoActivity($customer, 'loyalty_stamp_added', 'Loyalty stamp added', 'Coffee Stamp Card · '.($i % 9 + 1).' stamps collected', 'AppLoyaltyStampCards', $user->id, $i + 4);
            $this->recordDemoActivity($customer, 'email_sent', 'Email sent', 'Booking reminder email delivered', 'AppEmailAutomation', $user->id, $i + 3);

            if ($i % 2 === 0) {
                $this->recordDemoActivity($customer, 'feedback_submitted', 'Feedback submitted', 'Customer left private feedback after visit', 'AppFeedbackForms', $user->id, $i + 2);
            }

            if ($i % 3 === 0) {
                $this->recordDemoActivity($customer, 'referral_converted', 'Referral converted', 'Friend submitted a booking from referral link', 'AppReferralInviteFriend', $user->id, $i + 1);
            }

            $this->recordDemoActivity($customer, 'tag_added', 'Tag added: '.$tag->name, $tag->name, 'advanced_crm', $user->id, $i);
            if (! CustomerScoreLog::query()->where('customer_id', $customer->id)->where('reason', 'Demo score adjustment')->exists()) {
                app(CustomerScoreService::class)->add($customer, $i % 4, 'Demo score adjustment');
            }

            if ($i <= 12) {
                CustomerTask::query()->firstOrCreate(
                    ['customer_id' => $customer->id, 'title' => "Follow up demo customer {$i}"],
                    ['owner_user_id' => $user->id, 'business_id' => $business->id, 'assigned_to' => $user->id, 'type' => 'follow_up', 'priority' => $i % 3 === 0 ? 'high' : 'medium', 'status' => 'open', 'due_at' => now()->addDays($i % 7), 'created_by' => $user->id]
                );
            }

            if ($i <= 5) {
                CustomerNote::query()->firstOrCreate(
                    ['customer_id' => $customer->id, 'note' => "Demo CRM note for customer {$i}."],
                    ['owner_user_id' => $user->id, 'business_id' => $business->id, 'user_id' => $user->id, 'visibility' => 'team']
                );
            }
        }

        $this->info('Advanced CRM demo data seeded.');

        return self::SUCCESS;
    }

    protected function recordDemoActivity(Customer $customer, string $type, string $title, string $description, string $sourceModule, int $userId, int $daysAgo): void
    {
        CustomerActivity::query()->firstOrCreate(
            [
                'customer_id' => $customer->id,
                'type' => $type,
                'title' => $title,
            ],
            [
                'owner_user_id' => $customer->user_id,
                'business_id' => $customer->business_id,
                'description' => $description,
                'source_module' => $sourceModule,
                'icon' => match ($type) {
                    'booking_completed' => 'fa-light fa-calendar-check',
                    'coupon_claimed' => 'fa-light fa-ticket',
                    'loyalty_stamp_added' => 'fa-light fa-stamp',
                    'email_sent' => 'fa-light fa-envelope',
                    'feedback_submitted' => 'fa-light fa-message-lines',
                    'referral_converted' => 'fa-light fa-share-nodes',
                    'tag_added' => 'fa-light fa-tag',
                    default => 'fa-light fa-timeline',
                },
                'color' => '#0f766e',
                'metadata' => [],
                'created_by' => $userId,
                'occurred_at' => now()->subDays($daysAgo),
            ]
        );
    }
}
