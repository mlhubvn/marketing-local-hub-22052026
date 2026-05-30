<?php

use Illuminate\Support\Facades\Route;
use Modules\AppReviewBooster\Http\Controllers\ReviewFeedbackController;
use Modules\AppReviewBooster\Livewire\ReviewBoosterIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.appreviewbooster.route_prefix', 'portal/review-booster'))
    ->group(function (): void {
        Route::livewire('/', ReviewBoosterIndex::class)->name('portal.review-booster');
    });

Route::middleware(['web', 'throttle:10,1'])->post('/qr/{campaign:slug}/feedback', [ReviewFeedbackController::class, 'store'])->name('review-booster.feedback');
