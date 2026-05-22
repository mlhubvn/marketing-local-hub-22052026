<?php

use Illuminate\Support\Facades\Route;
use Modules\AppFeedbackForms\Http\Controllers\FeedbackSubmissionController;
use Modules\AppFeedbackForms\Livewire\FeedbackFormIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.appfeedbackforms.route_prefix', 'portal/feedback-forms'))
    ->group(fn () => Route::livewire('/', FeedbackFormIndex::class)->name('portal.feedback-forms'));

Route::middleware('web')->post('/qr/{campaign:slug}/feedback-form', [FeedbackSubmissionController::class, 'store'])->name('feedback-forms.submit');
