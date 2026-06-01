<?php

namespace Modules\AppLoyaltyStampCards\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Modules\AppBusinessProfiles\Support\BusinessQrRenderer;
use Modules\AppCustomers\Models\Customer;
use Modules\AppLoyaltyStampCards\Models\Referral;
use Modules\AppLoyaltyStampCards\Models\ReferralCampaign;
use Modules\AppLoyaltyStampCards\Models\ReferralLink;
use Modules\AppLoyaltyStampCards\Models\ReferralReward;

class ReferralPublicController extends Controller
{
    public function campaign(ReferralCampaign $campaign): View
    {
        abort_unless($campaign->status === 'active', 404);

        return view('apployaltystampcards::public.referral-campaign', [
            'campaign' => $campaign->load('business'),
            'link' => session('referral_link'),
        ]);
    }

    public function createLink(Request $request, ReferralCampaign $campaign)
    {
        abort_unless($campaign->status === 'active', 404);

        $payload = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40', 'required_without:email'],
            'email' => ['nullable', 'email', 'max:160', 'required_without:phone'],
        ]);

        $link = DB::transaction(function () use ($campaign, $payload): ReferralLink {
            $customer = $this->upsertCustomer($campaign, $payload, 'referral_referrer');

            return ReferralLink::query()->firstOrCreate(
                ['campaign_id' => $campaign->id, 'customer_id' => $customer->id],
                ['code' => $this->uniqueLinkCode($campaign, $customer), 'status' => 'active']
            );
        });

        return redirect()
            ->route('referral-campaigns.public', ['campaign' => $campaign->slug])
            ->with('referral_link', [
                'url' => $link->publicUrl(),
                'code' => $link->code,
            ]);
    }

    public function link(ReferralLink $link): View
    {
        abort_unless($link->status === 'active' && $link->campaign?->status === 'active', 404);

        $link->increment('clicks_count');

        return view('apployaltystampcards::public.referral-link', [
            'link' => $link->load(['campaign.business', 'customer']),
            'result' => session('referral_result'),
        ]);
    }

    public function convert(Request $request, ReferralLink $link)
    {
        abort_unless($link->status === 'active' && $link->campaign?->status === 'active', 404);

        $payload = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40', 'required_without:email'],
            'email' => ['nullable', 'email', 'max:160', 'required_without:phone'],
            'message' => ['nullable', 'string', 'max:1200'],
        ]);

        $result = DB::transaction(function () use ($link, $payload): array {
            $campaign = $link->campaign()->lockForUpdate()->firstOrFail();
            $referrer = $link->customer()->firstOrFail();
            $referred = $this->upsertCustomer($campaign, $payload, 'referral_referred');

            if ((int) $referrer->id === (int) $referred->id) {
                throw ValidationException::withMessages([
                    'phone' => __('You cannot refer yourself.'),
                ]);
            }

            $referral = Referral::query()->create([
                'campaign_id' => $campaign->id,
                'referral_link_id' => $link->id,
                'referrer_customer_id' => $referrer->id,
                'referred_customer_id' => $referred->id,
                'target_action' => $campaign->target_action,
                'status' => 'converted',
                'converted_at' => now(),
            ]);

            $link->increment('conversions_count');
            $this->incrementCustomerCounter($referrer, 'total_referrals');
            $this->recordCrmEvent($referrer, 'referral_converted', __('Referral converted: :friend', ['friend' => $referred->name]), 10, [
                'campaign_id' => $campaign->id,
                'campaign_name' => $campaign->name,
                'referred_customer_id' => $referred->id,
            ]);

            $convertedCount = Referral::query()
                ->where('campaign_id', $campaign->id)
                ->where('referrer_customer_id', $referrer->id)
                ->where('status', 'converted')
                ->count();

            $reward = null;
            $required = max(1, (int) $campaign->required_referrals);

            if ($convertedCount % $required === 0) {
                $reward = ReferralReward::query()->create([
                    'campaign_id' => $campaign->id,
                    'customer_id' => $referrer->id,
                    'referral_id' => $referral->id,
                    'code' => $this->uniqueRewardCode($campaign),
                    'status' => 'available',
                    'expires_at' => $campaign->expiry_days ? now()->addDays((int) $campaign->expiry_days) : null,
                ]);
                $this->recordCrmEvent($referrer, 'reward_unlocked', __('Referral reward unlocked: :reward', ['reward' => $campaign->reward_title]), 5, [
                    'campaign_id' => $campaign->id,
                    'reward_code' => $reward->code,
                ]);
            }

            return [
                'friend' => $referred->name,
                'referrer' => $referrer->name,
                'converted_count' => $convertedCount,
                'required' => $required,
                'reward' => $reward?->only(['code', 'expires_at']),
            ];
        });

        return redirect()
            ->route('referral-links.public', ['link' => $link->code])
            ->with('referral_result', $result);
    }

    public function campaignQr(ReferralCampaign $campaign, BusinessQrRenderer $renderer)
    {
        $campaign->loadMissing('business');

        return response($renderer->render($campaign->business, null, $campaign->publicUrl()), 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="'.$campaign->slug.'.svg"',
        ]);
    }

    public function campaignPng(ReferralCampaign $campaign, BusinessQrRenderer $renderer)
    {
        $campaign->loadMissing('business');

        return response($renderer->renderPng($campaign->business, null, $campaign->publicUrl()), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="'.$campaign->slug.'.png"',
        ]);
    }

    protected function upsertCustomer(ReferralCampaign $campaign, array $payload, string $source): Customer
    {
        $query = Customer::query()
            ->where('user_id', $campaign->user_id)
            ->where('business_id', $campaign->business_id);

        if (filled($payload['email'] ?? null)) {
            $query->where('email', $payload['email']);
        } else {
            $query->where('phone', $payload['phone']);
        }

        $customer = $query->first();
        $metadata = array_merge((array) ($customer?->metadata ?: []), [
            'last_source' => $source,
            'last_referral_campaign_id' => $campaign->id,
        ]);

        if ($customer) {
            $customer->forceFill([
                'name' => $payload['name'],
                'phone' => $payload['phone'] ?: $customer->phone,
                'email' => $payload['email'] ?: $customer->email,
                'metadata' => $metadata,
            ])->save();

            return $customer;
        }

        return Customer::query()->create([
            'user_id' => $campaign->user_id,
            'business_id' => $campaign->business_id,
            'name' => $payload['name'],
            'phone' => $payload['phone'] ?? null,
            'email' => $payload['email'] ?? null,
            'tags' => ['referral'],
            'metadata' => $metadata,
        ]);
    }

    protected function uniqueLinkCode(ReferralCampaign $campaign, Customer $customer): string
    {
        $base = Str::upper(Str::slug(Str::limit($customer->name, 12, ''), ''));

        do {
            $code = ($base ?: 'REF').'-'.Str::upper(Str::random(6));
        } while (ReferralLink::query()->where('code', $code)->exists());

        return $code;
    }

    protected function uniqueRewardCode(ReferralCampaign $campaign): string
    {
        do {
            $code = Str::upper(Str::slug(Str::limit($campaign->reward_title, 6, ''), '').'-'.Str::random(6));
        } while (ReferralReward::query()->where('code', $code)->exists());

        return $code;
    }

    protected function recordCrmEvent(Customer $customer, string $event, string $title, int $points = 0, array $metadata = []): void
    {
        if (
            ! class_exists(\Modules\AppAdvancedCustomerCrm\Support\CustomerActivityService::class)
            || ! Schema::hasTable('lb_customer_activities')
            || ! Schema::hasColumn('lb_customers', 'first_seen_at')
            || ! Schema::hasColumn('lb_customers', 'last_activity_at')
        ) {
            return;
        }

        app(\Modules\AppAdvancedCustomerCrm\Support\CustomerActivityService::class)->record($customer->refresh(), $event, $title, [
            'source_module' => 'AppLoyaltyStampCards',
            'metadata' => $metadata,
        ]);

        if (
            $points !== 0
            && Schema::hasTable('lb_customer_score_logs')
            && Schema::hasColumn('lb_customers', 'score')
        ) {
            app(\Modules\AppAdvancedCustomerCrm\Support\CustomerScoreService::class)->add($customer->refresh(), $points, $title);
        }

        if (class_exists(\Modules\AppAdvancedCustomerCrm\Support\CrmAutomationService::class)) {
            app(\Modules\AppAdvancedCustomerCrm\Support\CrmAutomationService::class)->handle($event, $customer->refresh(), $metadata);
        }
    }

    protected function incrementCustomerCounter(Customer $customer, string $column): void
    {
        if (Schema::hasColumn($customer->getTable(), $column)) {
            $customer->increment($column);
        }
    }
}
