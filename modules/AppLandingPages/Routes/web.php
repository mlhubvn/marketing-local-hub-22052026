<?php

use Illuminate\Support\Facades\Route;
use Modules\AppLandingPages\Http\Controllers\LandingPagePublicController;
use Modules\AppLandingPages\Livewire\LandingPageIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.applandingpages.route_prefix', 'portal/landing-pages'))
    ->group(function (): void {
        Route::get('/preview', [LandingPagePublicController::class, 'preview'])->name('landing-pages.preview');
        Route::livewire('/', LandingPageIndex::class)->name('portal.landing-pages');
    });

Route::middleware('web')->get('/lp/{landingPage:slug}', [LandingPagePublicController::class, 'show'])->name('landing-pages.public');
Route::middleware('web')->post('/lp/{landingPage:slug}', [LandingPagePublicController::class, 'submit'])->name('landing-pages.submit');
Route::middleware('web')->get('/lp/{landingPage:slug}/qr.svg', [LandingPagePublicController::class, 'qr'])->name('landing-pages.qr');
Route::middleware('web')->get('/lp/{landingPage:slug}/qr.png', [LandingPagePublicController::class, 'png'])->name('landing-pages.qr.png');
