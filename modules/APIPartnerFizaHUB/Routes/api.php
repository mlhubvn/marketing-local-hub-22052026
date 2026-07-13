<?php

use Illuminate\Support\Facades\Route;
use Modules\APIPartnerFizaHUB\Http\Controllers\DashboardController;
use Modules\APIPartnerFizaHUB\Http\Controllers\HealthController;
use Modules\APIPartnerFizaHUB\Http\Controllers\OnboardingController;
use Modules\APIPartnerFizaHUB\Http\Controllers\OneTimeLoginController;
use Modules\APIPartnerFizaHUB\Http\Controllers\PackageController;
use Modules\APIPartnerFizaHUB\Http\Controllers\SupportMessageController;
use Modules\APIPartnerFizaHUB\Http\Controllers\SupportTicketController;
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

        Route::post('businesses/{external_business_id}/support-tickets', [SupportTicketController::class, 'store'])
            ->name('businesses.support-tickets.store');
        Route::get('businesses/{external_business_id}/support-tickets', [SupportTicketController::class, 'index'])
            ->name('businesses.support-tickets.index');

        Route::get('support-tickets/{ticket_id}', [SupportTicketController::class, 'show'])
            ->name('support-tickets.show');
        Route::post('support-tickets/{ticket_id}/messages', [SupportMessageController::class, 'store'])
            ->name('support-tickets.messages.store');

        Route::post('businesses/{external_business_id}/one-time-login', [OneTimeLoginController::class, 'store'])
            ->name('businesses.one-time-login');
        Route::get('businesses/{external_business_id}/package', [PackageController::class, 'show'])
            ->name('businesses.package');
        Route::get('businesses/{external_business_id}/dashboard', [DashboardController::class, 'show'])
            ->name('businesses.dashboard');
    });
