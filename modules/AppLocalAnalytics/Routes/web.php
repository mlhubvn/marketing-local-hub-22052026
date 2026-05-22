<?php

use Illuminate\Support\Facades\Route;
use Modules\AppLocalAnalytics\Livewire\LocalAnalyticsIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.applocalanalytics.route_prefix', 'portal/reports'))
    ->group(function (): void {
        Route::livewire('/', LocalAnalyticsIndex::class)->name('portal.reports');
    });

Route::middleware(['web', 'auth', 'verified'])
    ->get('portal/local-analytics', fn () => redirect()->route('portal.reports'))
    ->name('portal.local-analytics');
