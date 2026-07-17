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
use Modules\APIPartnerFizaHUB\Http\Controllers\SupportAttachmentController;
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

        Route::get('packages', [PackageCatalogController::class, 'index'])->name('packages.index');

        Route::post('onboarding-requests', [OnboardingController::class, 'store'])->name('onboarding.store');
        Route::get('onboarding-requests/{request_id}', [OnboardingController::class, 'show'])->name('onboarding.show');
        Route::post('onboarding-requests/{request_id}/confirm', [OnboardingController::class, 'confirm'])
            ->name('onboarding.confirm');
        Route::post('onboarding-requests/{request_id}/cancel', [OnboardingController::class, 'cancel'])
            ->name('onboarding.cancel');

        Route::get('businesses/{external_business_id}/integration-status', [BusinessProfileController::class, 'status'])
            ->name('businesses.integration-status');
        Route::patch('businesses/{external_business_id}/profile', [BusinessProfileController::class, 'update'])
            ->name('businesses.profile.update');

        Route::post('businesses/{external_business_id}/one-time-login', [OneTimeLoginController::class, 'store'])
            ->name('businesses.one-time-login');
        Route::get('businesses/{external_business_id}/package', [PackageController::class, 'show'])
            ->name('businesses.package');

        Route::get('businesses/{external_business_id}/dashboard', [DashboardController::class, 'show'])
            ->name('businesses.dashboard');
        Route::get('businesses/{external_business_id}/insights', [DashboardController::class, 'insights'])
            ->name('businesses.insights');
        Route::get('businesses/{external_business_id}/recommendations', [DashboardController::class, 'recommendations'])
            ->name('businesses.recommendations');
        Route::get('businesses/{external_business_id}/campaigns', [DashboardController::class, 'campaigns'])
            ->name('businesses.campaigns.index');
        Route::get('businesses/{external_business_id}/campaigns/{campaign_id}', [DashboardController::class, 'campaignShow'])
            ->name('businesses.campaigns.show');

        Route::get('businesses/{external_business_id}/support-summary', [SupportTicketController::class, 'summary'])
            ->name('businesses.support-summary');
        Route::post('businesses/{external_business_id}/support-tickets', [SupportTicketController::class, 'store'])
            ->name('businesses.support-tickets.store');
        Route::get('businesses/{external_business_id}/support-tickets', [SupportTicketController::class, 'index'])
            ->name('businesses.support-tickets.index');

        Route::get('support-tickets/{ticket_id}', [SupportTicketController::class, 'show'])
            ->name('support-tickets.show');
        Route::post('support-tickets/{ticket_id}/messages', [SupportMessageController::class, 'store'])
            ->name('support-tickets.messages.store');
        Route::post('support-tickets/{ticket_id}/attachments', [SupportAttachmentController::class, 'store'])
            ->name('support-tickets.attachments.store');
        Route::patch('support-tickets/{ticket_id}/close', [SupportTicketController::class, 'close'])
            ->name('support-tickets.close');
        Route::post('support-tickets/{ticket_id}/reopen', [SupportTicketController::class, 'reopen'])
            ->name('support-tickets.reopen');
    });
