<?php

use Illuminate\Support\Facades\Route;
use Modules\AppCustomers\Livewire\CustomerIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.appcustomers.route_prefix', 'portal/customers'))
    ->group(fn () => Route::livewire('/', CustomerIndex::class)->name('portal.customers'));
