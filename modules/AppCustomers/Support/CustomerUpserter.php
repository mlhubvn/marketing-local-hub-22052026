<?php

namespace Modules\AppCustomers\Support;

use Modules\AppCustomers\Models\Customer;
use Modules\AppQRCampaigns\Models\QrCampaign;

class CustomerUpserter
{
    public function fromCampaign(QrCampaign $campaign, array $payload, string $source): ?Customer
    {
        $name = (string) (data_get($payload, 'name') ?: data_get($payload, 'customer_name') ?: 'Guest');
        $phone = data_get($payload, 'phone') ?: data_get($payload, 'customer_phone');
        $email = data_get($payload, 'email') ?: data_get($payload, 'customer_email');

        if (! filled($phone) && ! filled($email) && $name === 'Guest') {
            return null;
        }

        $query = Customer::query()
            ->where('user_id', $campaign->user_id)
            ->where('business_id', $campaign->business_id);

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
            'last_campaign_id' => $campaign->id,
            'last_campaign_type' => $campaign->type,
        ]);

        if ($customer) {
            $customer->forceFill([
                'name' => $name !== 'Guest' ? $name : $customer->name,
                'phone' => $phone ?: $customer->phone,
                'email' => $email ?: $customer->email,
                'metadata' => $metadata,
            ])->save();

            return $customer;
        }

        return Customer::query()->create([
            'user_id' => $campaign->user_id,
            'business_id' => $campaign->business_id,
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'tags' => ['campaign', $campaign->type],
            'metadata' => $metadata,
        ]);
    }
}
