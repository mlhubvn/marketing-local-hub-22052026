<?php

use Illuminate\Support\Facades\Route;
use Modules\AppAIStudio\Http\Controllers\AISettingsController;
use Modules\AppAIStudio\Http\Controllers\PromptHistoryController;
use Modules\AppAIStudio\Livewire\CampaignBuilderIndex;
use Modules\AppAIStudio\Livewire\ReviewReplyIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.appaistudio.route_prefix', 'portal/ai-studio'))
    ->group(function (): void {
        Route::livewire('/', CampaignBuilderIndex::class)->name('portal.ai-studio');
        Route::livewire('/review-reply', ReviewReplyIndex::class)->name('portal.ai-studio.review-reply');

        Route::get('/prompt-history', [PromptHistoryController::class, 'index'])->name('portal.ai-studio.prompt-history');
        Route::put('/prompt-history/{historyId}', [PromptHistoryController::class, 'update'])->name('portal.ai-studio.prompt-history.update');
        Route::delete('/prompt-history/{historyId}', [PromptHistoryController::class, 'destroy'])->name('portal.ai-studio.prompt-history.destroy');
        Route::get('/settings', [AISettingsController::class, 'index'])->name('portal.ai-studio.settings');
        Route::put('/settings/user', [AISettingsController::class, 'updateUser'])->name('portal.ai-studio.settings.user');
        Route::put('/settings/workspace', [AISettingsController::class, 'updateWorkspace'])->name('portal.ai-studio.settings.workspace');
    });
