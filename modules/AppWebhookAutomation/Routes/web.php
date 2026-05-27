<?php

use Illuminate\Support\Facades\Route;
use Modules\AppWebhookAutomation\Livewire\AutomationsIndex;
use Modules\AppWebhookAutomation\Livewire\LogsIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('portal/webhook-automation')
    ->group(function (): void {
        Route::get('/automations', AutomationsIndex::class)->name('portal.webhook-automations');
        Route::get('/logs', LogsIndex::class)->name('portal.webhook-logs');
    });
