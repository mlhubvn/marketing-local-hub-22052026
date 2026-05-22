<?php

use Illuminate\Support\Facades\Route;
use Modules\AppLeadForms\Http\Controllers\LeadSubmissionController;
use Modules\AppLeadForms\Livewire\LeadFormIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.appleadforms.route_prefix', 'portal/lead-forms'))
    ->group(fn () => Route::livewire('/', LeadFormIndex::class)->name('portal.lead-forms'));

Route::middleware('web')->post('/qr/{campaign:slug}/lead', [LeadSubmissionController::class, 'store'])->name('lead-forms.submit');
