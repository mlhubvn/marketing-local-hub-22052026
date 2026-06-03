<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminLog\Http\Controllers\LogPreviewController;
use Modules\AdminLog\Livewire\LogIndex;

Route::prefix('admin/settings')->middleware(['web', 'auth'])->group(function () {
    Route::get('log/preview', LogPreviewController::class)->name('admin-log.preview');
    Route::livewire('log', LogIndex::class)->name('admin-log.index');
});
