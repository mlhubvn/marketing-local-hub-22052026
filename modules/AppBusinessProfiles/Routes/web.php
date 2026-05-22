<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\AppQRCampaigns\Models\QrCampaign;
use Modules\AppBusinessProfiles\Http\Controllers\BusinessQrController;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppBusinessProfiles\Livewire\BusinessCreate;
use Modules\AppBusinessProfiles\Livewire\BusinessEdit;
use Modules\AppBusinessProfiles\Livewire\BusinessIndex;
use Modules\AppBusinessProfiles\Livewire\BusinessShow;
use Modules\AppBusinessProfiles\Livewire\BusinessCampaignIndex;
use Modules\AppBusinessProfiles\Livewire\BusinessLeadsIndex;
use Modules\AppBusinessProfiles\Livewire\BusinessReportsIndex;
use Modules\AppBusinessProfiles\Livewire\BusinessReviewsIndex;
use Modules\AppBusinessLocations\Livewire\LocationIndex;
use Modules\AppCustomers\Livewire\CustomerIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.appbusinessprofiles.route_prefix', 'portal/businesses'))
    ->group(function (): void {
        Route::livewire('/', BusinessIndex::class)->name('portal.businesses');
        Route::livewire('/create', BusinessCreate::class)->name('portal.businesses.create');
        Route::livewire('/{business}/campaigns', BusinessCampaignIndex::class)->name('portal.businesses.campaigns.index');
        Route::get('/{business}/campaigns/create', function (LocalBusiness $business, Request $request) {
            abort_unless((int) $business->user_id === (int) auth()->id(), 404);

            $type = (string) $request->query('type', 'review');
            $route = match ($type) {
                'booking' => 'portal.booking-pages',
                'coupon' => 'portal.coupon-campaigns',
                'feedback' => 'portal.feedback-forms',
                'lead' => 'portal.lead-forms',
                'qr' => 'portal.qr-campaigns',
                default => 'portal.review-booster',
            };

            return redirect()->route($route, ['business_id' => $business->id, 'type' => $type]);
        })->name('portal.businesses.campaigns.create');
        Route::get('/{business}/campaigns/{campaign}', function (LocalBusiness $business, QrCampaign $campaign) {
            abort_unless((int) $business->user_id === (int) auth()->id(), 404);
            abort_unless((int) $campaign->business_id === (int) $business->id && (int) $campaign->user_id === (int) auth()->id(), 404);

            return redirect()->away($campaign->publicUrl());
        })->name('portal.businesses.campaigns.show');
        Route::get('/{business}/campaigns/{campaign}/edit', function (LocalBusiness $business, QrCampaign $campaign) {
            abort_unless((int) $business->user_id === (int) auth()->id(), 404);
            abort_unless((int) $campaign->business_id === (int) $business->id && (int) $campaign->user_id === (int) auth()->id(), 404);

            $route = match ($campaign->type) {
                'review' => 'portal.review-booster',
                'booking' => 'portal.booking-pages',
                'coupon' => 'portal.coupon-campaigns',
                'feedback' => 'portal.feedback-forms',
                'lead' => 'portal.lead-forms',
                default => 'portal.qr-campaigns',
            };

            return redirect()->route($route, ['business_id' => $business->id, 'campaign_id' => $campaign->id]);
        })->name('portal.businesses.campaigns.edit');
        Route::get('/{business}/qr.svg', [BusinessQrController::class, 'svg'])->name('portal.businesses.qr.svg');
        Route::get('/{business}/qr-preview.svg', [BusinessQrController::class, 'preview'])->name('portal.businesses.qr.preview');
        Route::livewire('/{business}/locations', LocationIndex::class)->name('portal.businesses.locations');
        Route::livewire('/{business}/locations/create', LocationIndex::class)->name('portal.businesses.locations.create');
        Route::livewire('/{business}/locations/{location}/edit', LocationIndex::class)->name('portal.businesses.locations.edit');
        Route::livewire('/{business}/customers', CustomerIndex::class)->name('portal.businesses.customers.index');
        Route::livewire('/{business}/reviews', BusinessReviewsIndex::class)->name('portal.businesses.reviews');
        Route::livewire('/{business}/leads', BusinessLeadsIndex::class)->name('portal.businesses.leads');
        Route::livewire('/{business}/reports', BusinessReportsIndex::class)->name('portal.businesses.reports');
        Route::livewire('/{business}', BusinessShow::class)->name('portal.businesses.show');
        Route::livewire('/{business}/edit', BusinessEdit::class)->name('portal.businesses.edit');
    });

Route::middleware(['web'])
    ->get('/b/{business}', [BusinessQrController::class, 'show'])
    ->name('businesses.public');
