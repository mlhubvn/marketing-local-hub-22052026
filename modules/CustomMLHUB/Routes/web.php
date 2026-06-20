<?php

use Illuminate\Support\Facades\Route;
use Modules\CustomMLHUB\Livewire\ChatMLHUBAI;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('custommlhub.route_prefix', 'portal/chatmlhubai'))
    ->group(function (): void {
        Route::livewire('/', ChatMLHUBAI::class)->name('portal.chatmlhubai');
    });
