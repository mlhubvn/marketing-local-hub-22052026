<?php

use Illuminate\Support\Facades\Route;
use Modules\AppGoogleBusiness\Http\Controllers\GoogleBusinessOAuthController;
use Modules\AppGoogleBusiness\Livewire\GoogleBusinessIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('portal/integrations/google-business')
    ->group(function (): void {
        Route::get('/', GoogleBusinessIndex::class)->name('portal.google-business');
        Route::get('/connect', [GoogleBusinessOAuthController::class, 'connect'])->name('portal.google-business.connect');
        Route::get('/callback', [GoogleBusinessOAuthController::class, 'callback'])->name('portal.google-business.callback');
    });
