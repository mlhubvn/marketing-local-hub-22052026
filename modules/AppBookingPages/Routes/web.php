<?php

use Illuminate\Support\Facades\Route;
use Modules\AppBookingPages\Http\Controllers\BookingSubmissionController;
use Modules\AppBookingPages\Livewire\BookingPageIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.appbookingpages.route_prefix', 'portal/booking-pages'))
    ->group(function (): void {
        Route::livewire('/', BookingPageIndex::class)->name('portal.booking-pages');
    });

Route::middleware(['web', 'throttle:10,1'])->post('/qr/{campaign:slug}/booking', [BookingSubmissionController::class, 'store'])->name('booking-pages.submit');
