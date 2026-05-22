<?php

namespace Modules\AppBusinessProfiles\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppBookingPages\Models\Booking;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppCouponCampaigns\Models\CouponRedemption;
use Modules\AppFeedbackForms\Models\FeedbackResponse;
use Modules\AppLeadForms\Models\LeadSubmission;
use Modules\AppQRCampaigns\Models\QrCampaign;
use Modules\AppReviewBooster\Models\ReviewFeedback;

#[Title('Business Campaigns')]
class BusinessCampaignIndex extends Component
{
    use WithPagination;

    public LocalBusiness $business;
    public string $search = '';
    public string $typeFilter = 'all';
    public int $perPage = 10;

    public function mount(LocalBusiness $business): void
    {
        abort_unless((int) $business->user_id === (int) auth()->id(), 404);

        $this->business = $business;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
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

    public function render(): View
    {
        $baseQuery = QrCampaign::query()
            ->with('business')
            ->withCount('scans')
            ->where('user_id', auth()->id())
            ->where('business_id', $this->business->id);

        $campaigns = (clone $baseQuery)
            ->when($this->typeFilter !== 'all', function ($query): void {
                if ($this->typeFilter === 'qr') {
                    $query->whereNotIn('type', ['review', 'booking', 'coupon', 'feedback', 'lead']);

                    return;
                }

                $query->where('type', $this->typeFilter);
            })
            ->when(trim($this->search) !== '', function ($query): void {
                $search = trim($this->search);

                $query->where(function ($builder) use ($search): void {
                    $builder->where('name', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%')
                        ->orWhere('type', 'like', '%'.$search.'%');
                });
            })
            ->latest()
            ->paginate($this->perPage);

        $campaignIds = (clone $baseQuery)->pluck('id');

        return view('appbusinessprofiles::campaigns', [
            'campaigns' => $campaigns,
            'totalCampaigns' => $campaignIds->count(),
            'totalScans' => (clone $baseQuery)->get()->sum('scans_count'),
            'conversionCounts' => $this->conversionCounts($campaignIds->all()),
            'typeOptions' => [
                'all' => __('All Campaigns'),
                'review' => __('Review Booster'),
                'booking' => __('Booking Pages'),
                'coupon' => __('Coupons'),
                'feedback' => __('Feedback Forms'),
                'lead' => __('Lead Forms'),
                'qr' => __('QR Campaigns'),
            ],
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Campaigns').' - '.$this->business->name,
        ]);
    }

    private function conversionCounts(array $campaignIds): array
    {
        if ($campaignIds === []) {
            return [];
        }

        $counts = [];

        foreach ([
            ReviewFeedback::class,
            FeedbackResponse::class,
            LeadSubmission::class,
            Booking::class,
            CouponRedemption::class,
        ] as $model) {
            $model::query()
                ->whereIn('campaign_id', $campaignIds)
                ->selectRaw('campaign_id, count(*) as total')
                ->groupBy('campaign_id')
                ->pluck('total', 'campaign_id')
                ->each(function ($total, $campaignId) use (&$counts): void {
                    $counts[(int) $campaignId] = ($counts[(int) $campaignId] ?? 0) + (int) $total;
                });
        }

        return $counts;
    }
}
