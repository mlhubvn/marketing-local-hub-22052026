<?php

use Illuminate\Support\Facades\Route;
use Modules\AppMarketingTemplates\Livewire\MarketingTemplateIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.appmarketingtemplates.route_prefix', 'portal/marketing-templates'))
    ->group(fn () => Route::livewire('/', MarketingTemplateIndex::class)->name('portal.marketing-templates'));
