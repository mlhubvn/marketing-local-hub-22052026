<?php

namespace Modules\AppQRCampaigns\Livewire;

use App\Support\Plans\PlanLimitGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppBusinessProfiles\Support\BusinessQrRenderer;
use Modules\AppQRCampaigns\Models\QrCampaign;

#[Title('QR Codes')]
class QrCampaignIndex extends Component
{
    use WithPagination;

    public string $business_id = '';
    public string $name = '';
    public string $type = 'url';
    public string $destination_url = '';
    public string $cta_text = 'Scan me';
    public string $search = '';
    public string $businessFilter = '';
    public string $sourceFilter = '';
    public int $perPage = 10;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedBusinessFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSourceFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->perPage = (int) $this->perPage;

        if (! in_array($this->perPage, [10, 25, 50], true)) {
            $this->perPage = 10;
        }

        $this->resetPage();
    }

    public function save(): void
    {
        $payload = $this->validate([
            'business_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:url,review,booking,coupon,feedback,lead,landing_page'],
            'destination_url' => ['nullable', 'url', 'max:1000'],
            'cta_text' => ['nullable', 'string', 'max:120'],
        ]);

        LocalBusiness::query()->where('user_id', auth()->id())->findOrFail((int) $payload['business_id']);
        app(PlanLimitGuard::class)->ensureQrCodeCanBeCreated(auth()->user());

        QrCampaign::query()->create([
            'user_id' => auth()->id(),
            'business_id' => (int) $payload['business_id'],
            'slug' => $this->uniqueSlug($payload['name']),
            'name' => $payload['name'],
            'type' => $payload['type'],
            'destination_url' => $payload['destination_url'] ?: null,
            'settings' => [
                'cta_text' => $payload['cta_text'],
                'source' => 'manual',
            ],
            'published_at' => now(),
        ]);

        $this->name = '';
        $this->destination_url = '';
        $this->cta_text = 'Scan me';
        $this->type = 'url';
        $this->resetPage();

        $this->dispatch('qr-code-saved');
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->businessFilter = '';
        $this->sourceFilter = '';
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        QrCampaign::query()->where('user_id', auth()->id())->whereKey($id)->delete();
        $this->resetPage();
    }

    public function render(): View
    {
        $businesses = LocalBusiness::query()->where('user_id', auth()->id())->orderBy('name')->get();

        if ($this->business_id === '' && $businesses->isNotEmpty()) {
            $this->business_id = (string) $businesses->first()->id;
        }

        $baseQuery = QrCampaign::query()
            ->with('business')
            ->withCount('scans')
            ->where('user_id', auth()->id());

        $allCampaigns = (clone $baseQuery)->get();

        $campaignQuery = (clone $baseQuery)
            ->when($this->businessFilter !== '', fn ($query) => $query->where('business_id', (int) $this->businessFilter))
            ->when($this->sourceFilter !== '', function ($query): void {
                if ($this->sourceFilter === 'landing_page') {
                    $query->where('settings->source', 'landing_page');

                    return;
                }

                $query->where('type', $this->sourceFilter);
            })
            ->when(trim($this->search) !== '', function ($query): void {
                $search = '%'.trim($this->search).'%';
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('name', 'like', $search)
                        ->orWhere('destination_url', 'like', $search)
                        ->orWhereHas('business', fn ($businessQuery) => $businessQuery->where('name', 'like', $search));
                });
            });

        $typeOptions = [
            'url' => __('Custom URL QR'),
            'review' => __('Review QR'),
            'booking' => __('Booking QR'),
            'coupon' => __('Coupon QR'),
            'feedback' => __('Feedback QR'),
            'lead' => __('Lead Form QR'),
            'landing_page' => __('Campaign Page QR'),
        ];

        $campaigns = $campaignQuery->latest()->paginate($this->perPage);
        $campaigns->getCollection()->transform(function (QrCampaign $campaign): QrCampaign {
            $campaign->setAttribute('qr_svg', $this->qrSvg($campaign));

            return $campaign;
        });

        return view('appqrcampaigns::index', [
            'businesses' => $businesses,
            'campaigns' => $campaigns,
            'summary' => [
                'total' => $allCampaigns->count(),
                'campaign' => $allCampaigns->whereNotIn('type', ['url'])->count(),
                'custom' => $allCampaigns->where('type', 'url')->count(),
                'scans' => $allCampaigns->sum('scans_count'),
            ],
            'typeOptions' => $typeOptions,
            'sourceOptions' => $typeOptions,
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('QR Codes'),
        ]);
    }

    protected function qrSvg(QrCampaign $campaign): string
    {
        if (! $campaign->business) {
            return '';
        }

        return app(BusinessQrRenderer::class)->render($campaign->business, null, $campaign->publicUrl());
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'campaign';
        $slug = $base;
        $counter = 2;

        while (QrCampaign::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
