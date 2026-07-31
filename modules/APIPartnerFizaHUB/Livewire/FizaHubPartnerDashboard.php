<?php

namespace Modules\APIPartnerFizaHUB\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\APIPartnerFizaHUB\Services\PartnerReportingService;
use Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine;

/**
 * FizaHUB Partner Reporting Portal — overview dashboard (view-only).
 *
 * Reachable only on FIZAHUB_DOMAIN, behind `auth`, `verified` and the
 * `partner.fizahub.reporting-access` user-ID allowlist (see Routes/web.php). All data comes
 * from {@see PartnerReportingService}, which only ever queries this module's own
 * FizaHUB-scoped tables — there is no code path here that can reach unrelated MLHUB data.
 */
#[Title('FizaHUB Partner Reporting')]
class FizaHubPartnerDashboard extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: 'all')]
    public string $statusFilter = 'all';

    #[Url(as: 'package', except: 'all')]
    public string $packageFilter = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPackageFilter(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'statusFilter', 'packageFilter']);
        $this->resetPage();
    }

    public function render(): View
    {
        $service = app(PartnerReportingService::class);

        return view('apipartnerfizahub::livewire.partner-dashboard', [
            'metrics' => $service->overviewMetrics(),
            'dailyGrowth' => $service->dailyGrowth(30),
            'monthlyGrowth' => $service->monthlyGrowth(12),
            'businesses' => $service->businessList($this->search, $this->statusFilter, $this->packageFilter),
            'statusOptions' => OnboardingStatusMachine::labels(),
            'packageOptions' => $service->availablePackageCodes(),
        ])->layout('apipartnerfizahub::layouts.partner-reporting', [
            'title' => __('FizaHUB Partner Reporting'),
        ]);
    }
}
