<?php

use Illuminate\Support\Facades\Route;
use Modules\AppAdvancedCustomerCrm\Http\Controllers\CrmExportController;
use Modules\AppAdvancedCustomerCrm\Livewire\CrmAutomationsIndex;
use Modules\AppAdvancedCustomerCrm\Livewire\CrmCustomerShow;
use Modules\AppAdvancedCustomerCrm\Livewire\CrmCustomersIndex;
use Modules\AppAdvancedCustomerCrm\Livewire\CrmReportsIndex;
use Modules\AppAdvancedCustomerCrm\Livewire\CrmSegmentsIndex;
use Modules\AppAdvancedCustomerCrm\Livewire\CrmTagsIndex;
use Modules\AppAdvancedCustomerCrm\Livewire\CrmTasksIndex;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix(config('modules.appadvancedcustomercrm.route_prefix', 'portal/crm'))
    ->group(function (): void {
        Route::livewire('/customers', CrmCustomersIndex::class)->name('portal.crm.customers');
        Route::livewire('/customers/{customer}', CrmCustomerShow::class)->name('portal.crm.customers.show');
        Route::livewire('/segments', CrmSegmentsIndex::class)->name('portal.crm.segments');
        Route::livewire('/tags', CrmTagsIndex::class)->name('portal.crm.tags');
        Route::livewire('/tasks', CrmTasksIndex::class)->name('portal.crm.tasks');
        Route::livewire('/automations', CrmAutomationsIndex::class)->name('portal.crm.automations');
        Route::livewire('/reports', CrmReportsIndex::class)->name('portal.crm.reports');
        Route::get('/export/customers.csv', [CrmExportController::class, 'customers'])->name('portal.crm.export.customers');
        Route::redirect('/', '/portal/crm/customers')->name('portal.crm');
    });
