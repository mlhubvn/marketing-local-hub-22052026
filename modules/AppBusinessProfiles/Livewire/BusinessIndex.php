<?php

namespace Modules\AppBusinessProfiles\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppBookingPages\Models\Booking;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppCouponCampaigns\Models\CouponRedemption;
use Modules\AppQRCampaigns\Models\QrCampaign;
use Modules\AppQRCampaigns\Models\QrScan;

#[Title('Businesses')]
class BusinessIndex extends Component
{
    public string $search = '';

    public function delete(int $id): void
    {
        $userId = (int) auth()->id();
        $businessId = (int) $id;

        DB::unprepared("delete from `lb_businesses` where `user_id` = {$userId} and `id` = {$businessId} limit 1");
    }

    public function render(): View
    {
        $businesses = LocalBusiness::query()
            ->where('user_id', auth()->id())
            ->when($this->search !== '', function ($query): void {
                $search = trim($this->search);

                $query->where(function ($builder) use ($search): void {
                    $builder->where('name', 'like', '%'.$search.'%')
                        ->orWhere('type', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('address', 'like', '%'.$search.'%');
                });
            })
            ->withCount('campaigns')
            ->latest()
            ->get();

        return view('appbusinessprofiles::index', [
            'businesses' => $businesses,
            'totals' => [
                'businesses' => LocalBusiness::query()->where('user_id', auth()->id())->count(),
                'campaigns' => QrCampaign::query()->where('user_id', auth()->id())->count(),
                'scans' => QrScan::query()->where('user_id', auth()->id())->count(),
                'bookings' => Booking::query()->where('user_id', auth()->id())->count(),
                'coupons' => CouponRedemption::query()->where('user_id', auth()->id())->count(),
            ],
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Businesses'),
        ]);
    }
}
