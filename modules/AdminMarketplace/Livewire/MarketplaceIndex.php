<?php

namespace Modules\AdminMarketplace\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Modules\AdminMarketplace\Models\MarketplacePackage;
use Modules\AdminMarketplace\Services\MarketplacePackageService;

#[Title('Marketplace')]
class MarketplaceIndex extends Component
{
    protected MarketplacePackageService $packageService;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: 'all')]
    public string $statusFilter = 'all';

    public function boot(MarketplacePackageService $packages): void
    {
        $this->packageService = $packages;
    }

    public function resetPackageFilters(): void
    {
        $this->search = '';
        $this->statusFilter = 'all';
    }

    public function render(): View
    {
        $filters = [
            'q' => $this->search,
            'status' => $this->statusFilter,
        ];

        $packages = $this->filterPackages(
            $this->packageService->discover()->values(),
            $filters
        )->values();

        return view('adminmarketplace::index', [
            'packages' => $packages,
            'filters' => $filters,
            'summary' => [
                'total' => $packages->count(),
                'active' => $packages->where('is_active', true)->count(),
                'inactive' => $packages->where('is_active', false)->count(),
                'zip' => $packages->where('source_type', 'zip')->count(),
            ],
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Marketplace'),
        ]);
    }

    protected function filterPackages(Collection $packages, array $filters): Collection
    {
        return $packages
            ->when(filled($filters['q'] ?? null), function (Collection $collection) use ($filters) {
                $search = mb_strtolower(trim((string) ($filters['q'] ?? '')));

                return $collection->filter(function (MarketplacePackage $package) use ($search): bool {
                    $haystack = mb_strtolower(implode(' ', array_filter([
                        $package->title,
                        $package->module_name,
                        $package->description,
                        $package->version,
                    ])));

                    return str_contains($haystack, $search);
                });
            })
            ->when(($filters['status'] ?? 'all') !== 'all', function (Collection $collection) use ($filters) {
                return $collection->where('is_active', (bool) ((int) $filters['status']));
            });
    }
}
