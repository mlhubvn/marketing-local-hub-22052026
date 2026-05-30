<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminLog\Livewire\LogIndex;

Route::prefix('admin/settings')->middleware(['web', 'auth'])->group(function () {
    Route::livewire('log', LogIndex::class)->name('admin-log.index');
});
