<?php

use Illuminate\Support\Facades\Route;
use Modules\APIPartnerFizaHUB\Http\Controllers\ApiFizaHubDocsController;
use Modules\APIPartnerFizaHUB\Http\Controllers\ConsumeOneTimeLoginController;
use Modules\APIPartnerFizaHUB\Livewire\FizaHubOnboardingIndex;

Route::middleware(['web'])
    ->group(function (): void {
        Route::get('/api-fizahub', [ApiFizaHubDocsController::class, 'show'])
            ->name('partner.fizahub.docs');
        Route::get('/api-fizahub/postman', [ApiFizaHubDocsController::class, 'postman'])
            ->name('partner.fizahub.docs.postman');
        Route::get('/api-fizahub/help-test', [ApiFizaHubDocsController::class, 'helpTest'])
            ->name('partner.fizahub.docs.help-test');
    });

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('admin/integrations/fizahub')
    ->name('admin-fizahub.')
    ->group(function (): void {
        Route::get('onboarding', FizaHubOnboardingIndex::class)->name('onboarding');
    });

Route::middleware(['web', 'signed'])
    ->get('/partners/fizahub/one-time-login/{token}', ConsumeOneTimeLoginController::class)
    ->name('partner.fizahub.login.consume');
