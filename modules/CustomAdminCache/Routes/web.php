<?php

use Illuminate\Support\Facades\Route;
use Modules\CustomAdminCache\Livewire\SafeCacheIndex;

Route::middleware(['web', 'auth'])->group(function () {
    Route::livewire('settings/cache', SafeCacheIndex::class);
});

Route::prefix('admin/settings')->middleware(['web', 'auth'])->group(function () {
    Route::livewire('cache', SafeCacheIndex::class)->name('admin-cache.index');
});
