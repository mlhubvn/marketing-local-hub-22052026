<?php

use Illuminate\Support\Facades\Route;
use Modules\APIPartnerFizaHUB\Http\Controllers\ConsumeOneTimeLoginController;

Route::middleware(['web', 'signed'])
    ->get('/partners/fizahub/one-time-login/{token}', ConsumeOneTimeLoginController::class)
    ->name('partner.fizahub.login.consume');
