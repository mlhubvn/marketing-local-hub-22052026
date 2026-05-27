<?php

use Illuminate\Support\Facades\Route;
use Modules\AppEmailAutomation\Livewire\AutomationsIndex;
use Modules\AppEmailAutomation\Livewire\TemplatesIndex;
use Modules\AppEmailAutomation\Livewire\LogsIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('portal/email-automation')
    ->group(function (): void {
        Route::get('/automations', AutomationsIndex::class)->name('portal.email-automations');
        Route::get('/templates', TemplatesIndex::class)->name('portal.email-templates');
        Route::get('/logs', LogsIndex::class)->name('portal.email-logs');
    });
