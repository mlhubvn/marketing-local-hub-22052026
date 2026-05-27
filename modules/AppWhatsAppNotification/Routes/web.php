<?php

use Illuminate\Support\Facades\Route;
use Modules\AppWhatsAppNotification\Livewire\AutomationsIndex;
use Modules\AppWhatsAppNotification\Livewire\TemplatesIndex;
use Modules\AppWhatsAppNotification\Livewire\LogsIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('portal/whatsapp-notification')
    ->group(function (): void {
        Route::get('/automations', AutomationsIndex::class)->name('portal.whatsapp-notifications');
        Route::get('/templates', TemplatesIndex::class)->name('portal.whatsapp-templates');
        Route::get('/logs', LogsIndex::class)->name('portal.whatsapp-logs');
    });
