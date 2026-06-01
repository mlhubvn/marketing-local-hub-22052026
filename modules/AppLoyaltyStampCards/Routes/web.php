<?php

use Illuminate\Support\Facades\Route;
use Modules\AppLoyaltyStampCards\Http\Controllers\LoyaltyPublicController;
use Modules\AppLoyaltyStampCards\Http\Controllers\ReferralPublicController;
use Modules\AppLoyaltyStampCards\Livewire\LoyaltyCardsIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.apployaltystampcards.route_prefix', 'portal/loyalty-cards'))
    ->group(function (): void {
        Route::livewire('/', LoyaltyCardsIndex::class)->name('portal.loyalty-cards');
    });

Route::middleware('web')->group(function (): void {
    Route::get('/loyalty/{card:slug}', [LoyaltyPublicController::class, 'show'])->name('loyalty-cards.public');
    Route::post('/loyalty/{card:slug}/stamp', [LoyaltyPublicController::class, 'stamp'])->name('loyalty-cards.stamp');
    Route::get('/loyalty/{card:slug}/qr.svg', [LoyaltyPublicController::class, 'qr'])->name('loyalty-cards.svg');
    Route::get('/loyalty/{card:slug}/qr.png', [LoyaltyPublicController::class, 'png'])->name('loyalty-cards.png');
    Route::get('/referral/{campaign:slug}', [ReferralPublicController::class, 'campaign'])->name('referral-campaigns.public');
    Route::post('/referral/{campaign:slug}/link', [ReferralPublicController::class, 'createLink'])->name('referral-campaigns.link');
    Route::get('/referral/{campaign:slug}/qr.svg', [ReferralPublicController::class, 'campaignQr'])->name('referral-campaigns.svg');
    Route::get('/referral/{campaign:slug}/qr.png', [ReferralPublicController::class, 'campaignPng'])->name('referral-campaigns.png');
    Route::get('/r/{link:code}', [ReferralPublicController::class, 'link'])->name('referral-links.public');
    Route::post('/r/{link:code}/convert', [ReferralPublicController::class, 'convert'])->name('referral-links.convert');
});
