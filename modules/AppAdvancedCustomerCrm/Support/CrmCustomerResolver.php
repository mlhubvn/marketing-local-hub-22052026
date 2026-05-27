<?php

namespace Modules\AppAdvancedCustomerCrm\Support;

use Illuminate\Database\Eloquent\Model;
use Modules\AppCustomers\Models\Customer;

class CrmCustomerResolver
{
    public function fromRelated(Model $related, ?object $campaign = null, ?object $business = null, string $source = 'crm'): ?Customer
    {
        $userId = (int) ($related->user_id ?? $related->team_id ?? $campaign?->user_id ?? $business?->user_id ?? 0);
        $businessId = $business?->id ?? $campaign?->business_id ?? $related->business_id ?? null;
        $name = (string) ($related->customer_name ?? $related->reviewer_name ?? $related->name ?? 'Guest');
        $phone = (string) ($related->customer_phone ?? $related->phone ?? '');
        $email = (string) ($related->customer_email ?? $related->email ?? '');

        if ($userId < 1 || (! filled($email) && ! filled($phone) && $name === 'Guest')) {
            return null;
        }

        $query = Customer::query()->where('user_id', $userId);
        if ($businessId) {
            $query->where('business_id', $businessId);
        }

        if (filled($email)) {
            $query->where('email', $email);
        } elseif (filled($phone)) {
            $query->where('phone', $phone);
        } else {
            $query->where('name', $name);
        }

        $customer = $query->first();
        $metadata = array_merge((array) ($customer?->metadata ?: []), [
            'last_source' => $source,
            'last_related_type' => $related::class,
            'last_related_id' => $related->getKey(),
        ]);

        if ($customer) {
            $customer->forceFill([
                'team_id' => $customer->team_id ?: $userId,
                'business_id' => $customer->business_id ?: $businessId,
                'name' => $name !== 'Guest' ? $name : $customer->name,
                'phone' => $phone ?: $customer->phone,
                'email' => $email ?: $customer->email,
                'source_type' => $customer->source_type ?: $source,
                'source_id' => $customer->source_id ?: $related->getKey(),
                'first_seen_at' => $customer->first_seen_at ?: now(),
                'last_activity_at' => now(),
                'metadata' => $metadata,
            ])->save();

            return $customer;
        }

        return Customer::query()->create([
            'user_id' => $userId,
            'team_id' => $userId,
            'business_id' => $businessId,
            'name' => $name,
            'phone' => $phone ?: null,
            'email' => $email ?: null,
            'status' => 'active',
            'source_type' => $source,
            'source_id' => $related->getKey(),
            'first_seen_at' => now(),
            'last_activity_at' => now(),
            'metadata' => $metadata,
        ]);
    }
}
