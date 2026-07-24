<?php

namespace Modules\AppAdvancedCustomerCrm\Support;

use Modules\AppAdvancedCustomerCrm\Models\CustomerScoreLog;
use Modules\AppCustomers\Models\Customer;

class CustomerScoreService
{
    public function add(Customer $customer, int $points, string $reason, array $related = []): Customer
    {
        $oldScore = (int) ($customer->score ?? 0);
        $newScore = max(0, $oldScore + $points);

        $customer->forceFill(['score' => $newScore])->save();

        CustomerScoreLog::query()->create([
            'owner_user_id' => $customer->user_id,
            'customer_id' => $customer->id,
            'old_score' => $oldScore,
            'new_score' => $newScore,
            'reason' => $reason,
            'related_type' => data_get($related, 'related_type'),
            'related_id' => data_get($related, 'related_id'),
        ]);

        return $customer;
    }

    public function label(int $score): string
    {
        return match (true) {
            $score >= 81 => 'VIP',
            $score >= 51 => 'Loyal',
            $score >= 21 => 'Engaged',
            default => 'New',
        };
    }
}
