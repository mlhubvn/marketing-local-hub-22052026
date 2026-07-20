<?php

use Illuminate\Support\Facades\Route;
use Modules\APIPartnerFizaHUB\Http\Controllers\BusinessProfileController;
use Modules\APIPartnerFizaHUB\Http\Controllers\DashboardController;
use Modules\APIPartnerFizaHUB\Http\Controllers\HealthController;
use Modules\APIPartnerFizaHUB\Http\Controllers\OnboardingController;
use Modules\APIPartnerFizaHUB\Http\Controllers\OneTimeLoginController;
use Modules\APIPartnerFizaHUB\Http\Controllers\PackageCatalogController;
use Modules\APIPartnerFizaHUB\Http\Controllers\PackageController;
use Modules\APIPartnerFizaHUB\Http\Controllers\SsoController;
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

        Route::post('partner/sso/verify', [SsoController::class, 'verify'])->name('sso.verify');

        Route::get('marketing-catalog', [PackageCatalogController::class, 'index'])->name('marketing-catalog');

        Route::post('onboarding-requests', [OnboardingController::class, 'store'])->name('onboarding.store');
        Route::get('onboarding-requests/{request_id}', [OnboardingController::class, 'show'])->name('onboarding.show');
        Route::get('businesses/{external_business_id}/marketing-status', [BusinessProfileController::class, 'status'])
            ->name('businesses.marketing-status');
        Route::patch('businesses/{external_business_id}/profile', [BusinessProfileController::class, 'update'])
            ->name('businesses.profile.update');
        Route::patch('businesses/{external_business_id}/marketing-preferences', [BusinessProfileController::class, 'updatePreferences'])
            ->name('businesses.marketing-preferences.update');

        Route::post('businesses/{external_business_id}/crm-login-links', [OneTimeLoginController::class, 'store'])
            ->name('businesses.crm-login-links.store');
        Route::get('businesses/{external_business_id}/package', [PackageController::class, 'show'])
            ->name('businesses.package');

        Route::get('businesses/{external_business_id}/dashboard', [DashboardController::class, 'show'])
            ->name('businesses.dashboard');
        Route::get('businesses/{external_business_id}/growth-insights', [DashboardController::class, 'growthInsights'])
            ->name('businesses.growth-insights');
        Route::get('businesses/{external_business_id}/campaigns', [DashboardController::class, 'campaigns'])
            ->name('businesses.campaigns.index');
        Route::get('businesses/{external_business_id}/campaigns/{campaign_id}', [DashboardController::class, 'campaignShow'])
            ->name('businesses.campaigns.show');
        Route::post('businesses/{external_business_id}/campaigns/{campaign_id}/approval', [DashboardController::class, 'approveCampaign'])
            ->name('businesses.campaigns.approval');

        Route::get('businesses/{external_business_id}/support-presets', [SupportTicketController::class, 'presets'])
            ->name('businesses.support-presets');
        Route::post('businesses/{external_business_id}/support-tickets', [SupportTicketController::class, 'store'])
            ->name('businesses.support-tickets.store');
        Route::get('businesses/{external_business_id}/support-tickets', [SupportTicketController::class, 'index'])
            ->name('businesses.support-tickets.index');

        Route::get('businesses/{external_business_id}/support-tickets/{ticket_id}', [SupportTicketController::class, 'show'])
            ->name('businesses.support-tickets.show');
        Route::post('businesses/{external_business_id}/support-tickets/{ticket_id}/messages', [SupportMessageController::class, 'store'])
            ->name('businesses.support-tickets.messages.store');
        Route::post('businesses/{external_business_id}/support-tickets/{ticket_id}/close', [SupportTicketController::class, 'close'])
            ->name('businesses.support-tickets.close');
        Route::post('businesses/{external_business_id}/support-tickets/{ticket_id}/reopen', [SupportTicketController::class, 'reopen'])
            ->name('businesses.support-tickets.reopen');
    });
