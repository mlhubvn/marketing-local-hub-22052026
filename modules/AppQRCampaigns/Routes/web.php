<?php

use Illuminate\Support\Facades\Route;
use Modules\AppQRCampaigns\Http\Controllers\QrCampaignPublicController;
use Modules\AppQRCampaigns\Livewire\QrCampaignAnalytics;
use Modules\AppQRCampaigns\Livewire\QrCampaignIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.appqrcampaigns.route_prefix', 'portal/qr-campaigns'))
    ->group(function (): void {
        Route::livewire('/', QrCampaignIndex::class)->name('portal.qr-campaigns');
        Route::livewire('/{campaign:slug}/analytics', QrCampaignAnalytics::class)->name('portal.qr-campaigns.analytics');
    });

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('portal/qr-codes')
    ->group(function (): void {
        Route::livewire('/{campaign:slug}/analytics', QrCampaignAnalytics::class)->name('portal.qr-codes.analytics');
    });

Route::middleware('web')->group(function (): void {
    Route::get('/qr/{campaign:slug}', [QrCampaignPublicController::class, 'show'])->name('qr-campaigns.public');
    Route::get('/qr/{campaign:slug}/qr.svg', [QrCampaignPublicController::class, 'qr'])->name('qr-campaigns.svg');
    Route::get('/qr/{campaign:slug}/qr.png', [QrCampaignPublicController::class, 'png'])->name('qr-campaigns.png');
});
