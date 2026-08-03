<?php

use Illuminate\Support\Facades\Route;
use Modules\APIPartnerFizaHUB\Http\Controllers\ApiFizaHubDocsController;
use Modules\APIPartnerFizaHUB\Http\Controllers\ConsumeOneTimeLoginController;
use Modules\APIPartnerFizaHUB\Http\Controllers\SupportAttachmentController;
use Modules\APIPartnerFizaHUB\Livewire\FizaHubOnboardingIndex;
use Modules\APIPartnerFizaHUB\Livewire\FizaHubPartnerDashboard;
use Modules\APIPartnerFizaHUB\Livewire\FizaHubPartnerOnboardingShow;

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

// Signed image preview for FizaHUB chat UI: no partner token / X-Partner required.
// URL ends with a real image extension (preview.jpg) so <img> / ImageView can load it.
// Signature + expiry are required; only image/* attachments are served.
Route::middleware(['web', 'signed', 'throttle:60,1'])
    ->get('/partners/fizahub/support-attachments/{attachment_id}/{filename}', [SupportAttachmentController::class, 'preview'])
    ->where('filename', '[A-Za-z0-9._-]+\.(jpe?g|png|gif|webp)')
    ->name('partner.fizahub.support-attachments.preview');

// Signed download for any attachment type: click download_url in browser without Bearer / X-Partner.
// Keep separate from preview so the chat <img> path stays image-only.
Route::middleware(['web', 'signed', 'throttle:60,1'])
    ->get('/partners/fizahub/support-attachments/{attachment_id}/download/{filename}', [SupportAttachmentController::class, 'signedDownload'])
    ->where('filename', '[A-Za-z0-9._-]+\.(jpe?g|png|gif|webp|mp4|mov|webm|pdf|docx?|xlsx?|bin)')
    ->name('partner.fizahub.support-attachments.download');

// FizaHUB Partner Reporting Portal — view-only dashboard on its own domain
// (FIZAHUB_DOMAIN). Reuses the standard MLHUB login (auth/verified) plus a plain user-ID
// allowlist (partner.fizahub.reporting-access, see EnsureFizaHubPartnerAccess) — no admin
// role required. RestrictFizaHubDomainHost (registered globally on the `web` group by the
// service provider) confines this host to only this group of routes plus sign-in/out.
$partnerReportingDomain = trim((string) config('modules.apipartnerfizahub.partner_reporting_domain', ''));

if ($partnerReportingDomain !== '') {
    Route::domain($partnerReportingDomain)
        ->middleware(['web', 'auth', 'verified', 'partner.fizahub.reporting-access'])
        ->name('fizahub-partner.')
        ->group(function (): void {
            Route::get('/', FizaHubPartnerDashboard::class)->name('dashboard');
            // Route param intentionally NOT named `onboardingRequest` — see the docblock on
            // FizaHubPartnerOnboardingShow::mount() for why that would collide with
            // Livewire's implicit route-model binding.
            Route::get('/onboarding/{onboardingRequestId}', FizaHubPartnerOnboardingShow::class)->name('onboarding.show');
        });
}
