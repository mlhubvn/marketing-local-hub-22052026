<?php

namespace Modules\AppLoyaltyStampCards\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Modules\AdminUser\Models\User;
use Modules\AppBusinessProfiles\Support\BusinessQrRenderer;
use Modules\AppCustomers\Models\Customer;
use Modules\AppLoyaltyStampCards\Models\LoyaltyCard;
use Modules\AppLoyaltyStampCards\Models\LoyaltyCustomer;
use Modules\AppLoyaltyStampCards\Models\LoyaltyReward;
use Modules\AppLoyaltyStampCards\Models\LoyaltyStamp;

class LoyaltyPublicController extends Controller
{
    public function show(LoyaltyCard $card): View
    {
        abort_unless($card->status === 'active', 404);

        return view('apployaltystampcards::public.show', [
            'card' => $card->load('business'),
            'result' => session('loyalty_result'),
        ]);
    }

    public function stamp(Request $request, LoyaltyCard $card)
    {
        abort_unless($card->status === 'active', 404);

        $payload = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40', 'required_without:email'],
            'email' => ['nullable', 'email', 'max:160', 'required_without:phone'],
        ]);

        $stampId = null;

        $result = DB::transaction(function () use ($card, $payload, &$stampId): array {
            $customer = $this->upsertCustomer($card, $payload);

            $this->assertCanCollectStamp($card, $customer->id);

            $stamp = LoyaltyStamp::query()->create([
                'card_id' => $card->id,
                'customer_id' => $customer->id,
                'source' => 'qr_scan',
            ]);
            $stampId = $stamp->id;

            $this->incrementCustomerCounter($customer, 'total_loyalty_stamps');
            $this->recordCrmEvent($customer, 'loyalty_stamp_added', __('Loyalty stamp added'), 2, [
                'card_id' => $card->id,
                'card_name' => $card->name,
            ]);

            $this->ensureCustomerLimit($card, $customer->id);

            $progress = LoyaltyCustomer::query()->firstOrCreate(
                ['card_id' => $card->id, 'customer_id' => $customer->id],
                ['stamps_count' => 0, 'completed_count' => 0]
            );

            $progress->stamps_count++;
            $progress->last_stamp_at = now();
            $reward = null;

            if ($progress->stamps_count >= $card->required_stamps) {
                $progress->stamps_count = 0;
                $progress->completed_count++;

                $reward = LoyaltyReward::query()->create([
                    'card_id' => $card->id,
                    'customer_id' => $customer->id,
                    'code' => $this->uniqueRewardCode($card),
                    'status' => 'available',
                    'expires_at' => $card->expiry_days ? now()->addDays((int) $card->expiry_days) : null,
                ]);
                $this->recordCrmEvent($customer, 'reward_unlocked', __('Reward unlocked: :reward', ['reward' => $card->reward_title]), 5, [
                    'card_id' => $card->id,
                    'reward_code' => $reward->code,
                ]);
            }

            $progress->save();

            return [
                'customer' => $customer->name,
                'stamps' => $progress->stamps_count,
                'required' => $card->required_stamps,
                'reward' => $reward?->only(['code', 'expires_at']),
            ];
        });

        if ($stampId) {
            $this->sendWhatsAppStampNotification((int) $stampId);
        }

        return redirect()
            ->route('loyalty-cards.public', ['card' => $card->slug])
            ->with('loyalty_result', $result);
    }

    public function qr(LoyaltyCard $card, BusinessQrRenderer $renderer)
    {
        $card->loadMissing('business');

        return response($renderer->render($card->business, null, $card->publicUrl()), 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="'.$card->slug.'.svg"',
        ]);
    }

    public function png(LoyaltyCard $card, BusinessQrRenderer $renderer)
    {
        $card->loadMissing('business');

        return response($renderer->renderPng($card->business, null, $card->publicUrl()), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="'.$card->slug.'.png"',
        ]);
    }

    protected function upsertCustomer(LoyaltyCard $card, array $payload): Customer
    {
        $query = Customer::query()
            ->where('user_id', $card->user_id)
            ->where('business_id', $card->business_id);

        if (filled($payload['email'] ?? null)) {
            $query->where('email', $payload['email']);
        } else {
            $query->where('phone', $payload['phone']);
        }

        $customer = $query->first();
        $metadata = array_merge((array) ($customer?->metadata ?: []), [
            'last_source' => 'loyalty_stamp_card',
            'last_loyalty_card_id' => $card->id,
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
            'user_id' => $card->user_id,
            'business_id' => $card->business_id,
            'name' => $payload['name'],
            'phone' => $payload['phone'] ?? null,
            'email' => $payload['email'] ?? null,
            'tags' => ['loyalty'],
            'metadata' => $metadata,
        ]);
    }

    protected function uniqueRewardCode(LoyaltyCard $card): string
    {
        do {
            $code = strtoupper(Str::slug(Str::limit($card->reward_title, 6, ''), '').'-'.Str::random(6));
        } while (LoyaltyReward::query()->where('code', $code)->exists());

        return $code;
    }

    protected function assertCanCollectStamp(LoyaltyCard $card, int $customerId): void
    {
        $latestStamp = LoyaltyStamp::query()
            ->where('card_id', $card->id)
            ->where('customer_id', $customerId)
            ->latest()
            ->first();

        $cooldownMinutes = max(0, (int) ($card->stamp_cooldown_minutes ?? 0));

        if ($latestStamp && $cooldownMinutes > 0) {
            $nextAllowedAt = $latestStamp->created_at?->copy()->addMinutes($cooldownMinutes);

            if ($nextAllowedAt && $nextAllowedAt->isFuture()) {
                throw ValidationException::withMessages([
                    'phone' => __('You already collected a stamp for this card. Please come back after :time.', [
                        'time' => format_datetime_locale($nextAllowedAt),
                    ]),
                ]);
            }
        }

        $maxPerDay = max(1, (int) ($card->max_stamps_per_day ?? 1));
        $todayCount = LoyaltyStamp::query()
            ->where('card_id', $card->id)
            ->where('customer_id', $customerId)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        if ($todayCount >= $maxPerDay) {
            throw ValidationException::withMessages([
                'phone' => __('You reached today\'s stamp limit for this card. Please come back tomorrow.'),
            ]);
        }
    }

    protected function ensureCustomerLimit(LoyaltyCard $card, int $customerId): void
    {
        if (LoyaltyCustomer::query()->where('card_id', $card->id)->where('customer_id', $customerId)->exists()) {
            return;
        }

        $owner = User::query()->find($card->user_id);
        $limit = $owner?->planLimit('max_loyalty_customers', -1);

        if ((int) $limit === -1) {
            return;
        }

        abort_if(
            LoyaltyCustomer::query()->whereIn('card_id', LoyaltyCard::query()->where('user_id', $card->user_id)->pluck('id'))->count() >= (int) $limit,
            403,
            __('This loyalty program has reached its customer limit.')
        );
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

    protected function sendWhatsAppStampNotification(int $stampId): void
    {
        $serviceClass = 'Modules\\AppWhatsAppNotification\\Support\\WhatsAppNotificationService';

        if (! class_exists($serviceClass) || ! Schema::hasTable('lb_whatsapp_notifications')) {
            return;
        }

        $stamp = LoyaltyStamp::query()
            ->with(['card.business', 'customer'])
            ->find($stampId);

        if (! $stamp) {
            return;
        }

        app($serviceClass)->handle('loyalty.stamp_added', $stamp);
    }
}
