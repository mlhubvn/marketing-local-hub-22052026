<?php

use Illuminate\Support\Facades\Route;
use Modules\APIPartnerFizaHUB\Http\Controllers\HealthController;
use Modules\APIPartnerFizaHUB\Http\Controllers\OnboardingController;
use Modules\APIPartnerFizaHUB\Http\Middleware\HandlePartnerRequest;
use Modules\APIPartnerFizaHUB\Http\Middleware\VerifyPartnerToken;

Route::middleware([
    'api',
    VerifyPartnerToken::class,
    HandlePartnerRequest::class,
    'throttle:fizahub-partner',
])
    ->prefix('api/v1/partners/fizahub')
    ->name('partner.fizahub.')
    ->group(function (): void {
        Route::get('health', HealthController::class)->name('health');
        Route::post('onboarding-requests', [OnboardingController::class, 'store'])->name('onboarding.store');
        Route::get('onboarding-requests/{request_id}', [OnboardingController::class, 'show'])->name('onboarding.show');
    });
