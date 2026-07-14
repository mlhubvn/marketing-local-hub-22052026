<?php

use Illuminate\Support\Facades\Route;
use Modules\APIPartnerFizaHUB\Http\Controllers\ApiFizaHubDocsController;
use Modules\APIPartnerFizaHUB\Http\Controllers\ConsumeOneTimeLoginController;

Route::middleware(['web'])
    ->group(function (): void {
        Route::get('/api-fizahub', [ApiFizaHubDocsController::class, 'show'])
            ->name('partner.fizahub.docs');
        Route::get('/api-fizahub/postman', [ApiFizaHubDocsController::class, 'postman'])
            ->name('partner.fizahub.docs.postman');
        Route::get('/api-fizahub/help-test', [ApiFizaHubDocsController::class, 'helpTest'])
            ->name('partner.fizahub.docs.help-test');
    });

Route::middleware(['web', 'signed'])
    ->get('/partners/fizahub/one-time-login/{token}', ConsumeOneTimeLoginController::class)
    ->name('partner.fizahub.login.consume');
