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
        Route::get('/api-fizahub/postman/extended', [ApiFizaHubDocsController::class, 'postmanExtended'])
            ->name('partner.fizahub.docs.postman.extended');
        Route::get('/api-fizahub/help-test', [ApiFizaHubDocsController::class, 'helpTest'])
            ->name('partner.fizahub.docs.help-test');
    });

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('admin/integrations/fizahub')
    ->name('admin-fizahub.')
    ->group(function (): void {
        Route::get('onboarding', FizaHubOnboardingIndex::class)->name('onboarding');
    });

// GET only renders a confirmation page and never marks the token used — a chat-app link
// preview crawler (Zalo/Messenger/Telegram fetch the raw URL to build a preview the
// instant it is pasted into a message, before any human clicks it) must not be able to
// burn a single-use login link. The actual login only happens on POST, which the
// confirmation page auto-submits via JS for a real browser (see one-time-login-confirm
// view) — real users still experience a single tap/click.
Route::middleware(['web', 'signed'])
    ->match(['get', 'post'], '/partners/fizahub/one-time-login/{token}', ConsumeOneTimeLoginController::class)
    ->name('partner.fizahub.login.consume');
