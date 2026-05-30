<?php

use Illuminate\Support\Facades\Route;
use Modules\AppCouponCampaigns\Http\Controllers\CouponClaimController;
use Modules\AppCouponCampaigns\Livewire\CouponCampaignIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.appcouponcampaigns.route_prefix', 'portal/coupon-campaigns'))
    ->group(function (): void {
        Route::livewire('/', CouponCampaignIndex::class)->name('portal.coupon-campaigns');
    });

Route::middleware(['web', 'throttle:10,1'])->post('/qr/{campaign:slug}/coupon', [CouponClaimController::class, 'store'])->name('coupon-campaigns.claim');
