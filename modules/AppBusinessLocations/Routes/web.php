<?php

use Illuminate\Support\Facades\Route;
use Modules\AppBusinessLocations\Http\Controllers\LocationQrController;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.appbusinesslocations.route_prefix', 'portal/locations'))
    ->group(function (): void {
        Route::get('/', fn () => redirect()->route('portal.businesses'))->name('portal.locations');
        Route::get('/{location}/qr.svg', [LocationQrController::class, 'svg'])->name('portal.locations.qr.svg');
        Route::get('/{location}/qr-preview.svg', [LocationQrController::class, 'preview'])->name('portal.locations.qr.preview');
    });

Route::middleware(['web'])
    ->get('/l/{location}', [LocationQrController::class, 'show'])
    ->name('locations.public');
