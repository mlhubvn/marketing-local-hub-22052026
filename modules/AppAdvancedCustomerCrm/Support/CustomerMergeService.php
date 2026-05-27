<?php

namespace Modules\AppAdvancedCustomerCrm\Support;

use Illuminate\Support\Facades\DB;
use Modules\AppAdvancedCustomerCrm\Models\CustomerMergeLog;
use Modules\AppCustomers\Models\Customer;

class CustomerMergeService
{
    public function merge(Customer $primary, Customer $duplicate): Customer
    {
        abort_if((int) $primary->user_id !== (int) $duplicate->user_id, 403);
        abort_if((int) $primary->id === (int) $duplicate->id, 422);

        return DB::transaction(function () use ($primary, $duplicate): Customer {
            $primaryTagIds = DB::table('lb_customer_tag_maps')->where('customer_id', $primary->id)->pluck('tag_id')->all();
            if ($primaryTagIds !== []) {
                DB::table('lb_customer_tag_maps')
                    ->where('customer_id', $duplicate->id)
                    ->whereIn('tag_id', $primaryTagIds)
                    ->delete();
            }

            foreach (['lb_customer_activities', 'lb_customer_notes', 'lb_customer_tasks', 'lb_customer_tag_maps', 'lb_customer_score_logs'] as $table) {
                DB::table($table)->where('customer_id', $duplicate->id)->update(['customer_id' => $primary->id]);
            }

            foreach (['lb_loyalty_customers', 'lb_loyalty_stamps', 'lb_loyalty_rewards', 'lb_referral_links', 'lb_referral_rewards'] as $table) {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    DB::table($table)->where('customer_id', $duplicate->id)->update(['customer_id' => $primary->id]);
                }
            }

            if (DB::getSchemaBuilder()->hasTable('lb_referrals')) {
                DB::table('lb_referrals')->where('referrer_customer_id', $duplicate->id)->update(['referrer_customer_id' => $primary->id]);
                DB::table('lb_referrals')->where('referred_customer_id', $duplicate->id)->update(['referred_customer_id' => $primary->id]);
            }

            $lastActivity = collect([$primary->last_activity_at, $duplicate->last_activity_at])->filter()->sort()->last();

            $primary->forceFill([
                'phone' => $primary->phone ?: $duplicate->phone,
                'email' => $primary->email ?: $duplicate->email,
                'score' => max((int) $primary->score, (int) $duplicate->score),
                'last_activity_at' => $lastActivity,
            ])->save();

            CustomerMergeLog::query()->create([
                'team_id' => $primary->team_id,
                'primary_customer_id' => $primary->id,
                'merged_customer_id' => $duplicate->id,
                'merged_by' => auth()->id(),
                'metadata' => ['merged_name' => $duplicate->name, 'merged_email' => $duplicate->email, 'merged_phone' => $duplicate->phone],
                'created_at' => now(),
            ]);

            $duplicate->delete();

            return $primary->refresh();
        });
    }
}
