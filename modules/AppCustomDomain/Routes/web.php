<?php

use Illuminate\Support\Facades\Route;
use Modules\AppCustomDomain\Livewire\DomainsIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.appqrcodes.route_prefix', 'portal/qr-codes'))
    ->name('portal.qr-codes.')
    ->group(function (): void {
        Route::get('/domains', DomainsIndex::class)->name('domains');
    });

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('portal/brand')
    ->name('portal.brand.')
    ->group(function (): void {
        Route::get('/custom-domains', DomainsIndex::class)->name('domains');
    });
