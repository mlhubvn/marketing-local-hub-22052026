<?php

namespace Modules\AppCouponCampaigns\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use App\Support\GrowthToolNotifier;
use Modules\AppCustomers\Support\CustomerUpserter;
use Modules\AppCouponCampaigns\Models\CouponRedemption;
use Modules\AppQRCampaigns\Models\QrCampaign;

class CouponClaimController extends Controller
{
    public function store(Request $request, QrCampaign $campaign): RedirectResponse
    {
        abort_unless($campaign->type === 'coupon', 404);

        $payload = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:80'],
            'customer_email' => ['nullable', 'email', 'max:255'],
        ]);

        $expiryDate = data_get($campaign->settings, 'expiry_date');
        if ($expiryDate && now()->startOfDay()->gt(\Carbon\Carbon::parse($expiryDate)->endOfDay())) {
            return back()->withErrors(['coupon' => __('This coupon has expired.')]);
        }

        $usageLimit = (int) data_get($campaign->settings, 'usage_limit', 0);
        if ($usageLimit > 0 && CouponRedemption::query()->where('campaign_id', $campaign->id)->count() >= $usageLimit) {
            return back()->withErrors(['coupon' => __('This coupon has reached its claim limit.')]);
        }

        app(CustomerUpserter::class)->fromCampaign($campaign, $payload, 'coupon_claim');
        $redemption = CouponRedemption::query()->create([
            'user_id' => $campaign->user_id,
            'campaign_id' => $campaign->id,
            'code' => $this->uniqueCode((string) data_get($campaign->settings, 'coupon_code', '')),
            ...$payload,
        ]);
        app(GrowthToolNotifier::class)->couponClaimed($campaign, (string) $payload['customer_name'], (string) $redemption->code);

        return back()->with('coupon_code', $redemption->code);
    }

    protected function uniqueCode(string $prefix = ''): string
    {
        $prefix = Str::of($prefix)->upper()->replaceMatches('/[^A-Z0-9]/', '')->substr(0, 10)->toString();

        do {
            $code = ($prefix !== '' ? $prefix.'-' : '').Str::upper(Str::random(6));
        } while (CouponRedemption::query()->where('code', $code)->exists());

        return $code;
    }
}
