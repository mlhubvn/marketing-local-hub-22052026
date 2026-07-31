<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Modules\AdminSupport\Livewire\SupportCreate;
use Modules\AdminSupport\Livewire\SupportIndex;
use Modules\AdminSupport\Livewire\SupportShow;
use Modules\AdminSupport\Livewire\SupportTaxonomy;
use Modules\AdminSupport\Models\SupportTicket;
use Modules\APIPartnerFizaHUB\Models\PartnerSupportAttachment;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('admin/support')
    ->name('admin-support.')
    ->group(function (): void {
        Route::get('/', SupportIndex::class)->name('index');
        Route::get('/create', SupportCreate::class)->name('create');

        Route::get('/categories', SupportTaxonomy::class)
            ->defaults('resource', 'categories')
            ->name('categories.index');

        Route::get('/labels', SupportTaxonomy::class)
            ->defaults('resource', 'labels')
            ->name('labels.index');

        Route::get('/types', SupportTaxonomy::class)
            ->defaults('resource', 'types')
            ->name('types.index');

        Route::get('/{ticket}', SupportShow::class)->name('show');

        Route::get('/{ticket}/attachments/{attachment}', function (SupportTicket $ticket, string $attachment) {
            $model = PartnerSupportAttachment::query()
                ->where('support_ticket_id', $ticket->id)
                ->where('id_secure', $attachment)
                ->firstOrFail();

            abort_unless(
                filled($model->path) && Storage::disk($model->disk)->exists($model->path),
                404
            );

            return Storage::disk($model->disk)->download($model->path, $model->original_name);
        })->name('attachments.show');
    });
