<?php

namespace Modules\AppQRCampaigns\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppBookingPages\Models\Booking;
use Modules\AppCouponCampaigns\Models\CouponRedemption;
use Modules\AppFeedbackForms\Models\FeedbackResponse;
use Modules\AppLeadForms\Models\LeadSubmission;
use Modules\AppQRCampaigns\Models\QrCampaign;
use Modules\AppReviewBooster\Models\ReviewFeedback;

#[Title('QR Analytics')]
class QrCampaignAnalytics extends Component
{
    public QrCampaign $campaign;

    public function mount(QrCampaign $campaign): void
    {
        abort_unless((int) $campaign->user_id === (int) auth()->id(), 404);

        $this->campaign = $campaign->load('business');
    }

    public function render(): View
    {
        $scanQuery = $this->campaign->scans();
        $totalScans = (clone $scanQuery)->count();
        $uniqueVisitors = (clone $scanQuery)
            ->whereNotNull('ip_address')
            ->distinct('ip_address')
            ->count('ip_address');
        $conversions = $this->conversionCount();
        $lastScanned = (clone $scanQuery)->latest('created_at')->value('created_at');

        return view('appqrcampaigns::analytics', [
            'sourceLabel' => $this->sourceLabel(),
            'createdFrom' => $this->createdFrom(),
            'destinationUrl' => $this->campaign->destination_url ?: $this->campaign->publicUrl(),
            'metrics' => [
                'total_scans' => $totalScans,
                'unique_visitors' => $uniqueVisitors,
                'conversions' => $conversions,
                'conversion_rate' => $totalScans > 0 ? round(($conversions / $totalScans) * 100, 1) : 0,
                'last_scanned' => $lastScanned ? Carbon::parse($lastScanned) : null,
            ],
            'dailyScans' => $this->dailyScans(),
            'recentScans' => (clone $scanQuery)->latest('created_at')->limit(12)->get(),
            'deviceBreakdown' => (clone $scanQuery)
                ->selectRaw('COALESCE(device, ?) as label, COUNT(*) as total', [__('Unknown')])
                ->groupBy('label')
                ->orderByDesc('total')
                ->get(),
            'browserBreakdown' => $this->browserBreakdown(),
            'locationBreakdown' => (clone $scanQuery)
                ->selectRaw("CONCAT(COALESCE(city, ?), ', ', COALESCE(country, ?)) as label, COUNT(*) as total", [__('Unknown city'), __('Unknown country')])
                ->groupBy('label')
                ->orderByDesc('total')
                ->limit(8)
                ->get(),
            'sourceCampaignUrl' => $this->sourceCampaignUrl(),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('QR Analytics'),
        ]);
    }

    protected function conversionCount(): int
    {
        $campaignId = $this->campaign->id;

        return match ($this->campaign->type) {
            'review' => ReviewFeedback::query()->where('campaign_id', $campaignId)->count(),
            'booking' => Booking::query()->where('campaign_id', $campaignId)->count(),
            'coupon' => CouponRedemption::query()->where('campaign_id', $campaignId)->count(),
            'feedback' => FeedbackResponse::query()->where('campaign_id', $campaignId)->count(),
            'lead' => LeadSubmission::query()->where('campaign_id', $campaignId)->count(),
            default => ReviewFeedback::query()->where('campaign_id', $campaignId)->count()
                + Booking::query()->where('campaign_id', $campaignId)->count()
                + CouponRedemption::query()->where('campaign_id', $campaignId)->count()
                + FeedbackResponse::query()->where('campaign_id', $campaignId)->count()
                + LeadSubmission::query()->where('campaign_id', $campaignId)->count(),
        };
    }

    protected function dailyScans(): array
    {
        $start = now()->subDays(13)->startOfDay();
        $counts = $this->campaign->scans()
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as scan_date, COUNT(*) as total')
            ->groupBy('scan_date')
            ->pluck('total', 'scan_date');

        return collect(range(0, 13))->map(function (int $offset) use ($start, $counts): array {
            $date = $start->copy()->addDays($offset);

            return [
                'label' => $date->format('M d'),
                'date' => $date->toDateString(),
                'total' => (int) ($counts[$date->toDateString()] ?? 0),
            ];
        })->all();
    }

    protected function browserBreakdown()
    {
        return $this->campaign->scans()
            ->latest('created_at')
            ->get(['user_agent'])
            ->map(fn ($scan) => $this->browserName((string) $scan->user_agent))
            ->countBy()
            ->sortDesc()
            ->map(fn ($total, $label) => (object) ['label' => $label, 'total' => $total])
            ->values();
    }

    public function browserName(string $agent): string
    {
        return match (true) {
            str_contains($agent, 'Edg/') => 'Edge',
            str_contains($agent, 'Chrome/') => 'Chrome',
            str_contains($agent, 'Firefox/') => 'Firefox',
            str_contains($agent, 'Safari/') => 'Safari',
            $agent === '' => __('Unknown'),
            default => __('Other'),
        };
    }

    protected function sourceLabel(): string
    {
        $source = data_get($this->campaign->settings, 'source') === 'landing_page' ? 'landing_page' : $this->campaign->type;

        return match ($source) {
            'review' => __('Review QR'),
            'booking' => __('Booking QR'),
            'coupon' => __('Coupon QR'),
            'feedback' => __('Feedback QR'),
            'lead' => __('Lead Form QR'),
            'landing_page' => __('Campaign Page QR'),
            'url' => __('Custom URL QR'),
            default => str($source)->headline()->toString(),
        };
    }

    protected function createdFrom(): string
    {
        return match ((string) data_get($this->campaign->settings, 'source', '')) {
            'manual' => __('Manual'),
            'landing_page' => __('Landing Page'),
            'template' => __('Template'),
            default => $this->campaign->type === 'url' ? __('Manual') : __('Growth Tool'),
        };
    }

    protected function sourceCampaignUrl(): ?string
    {
        if (! $this->campaign->business || ! Route::has('portal.businesses.campaigns.edit')) {
            return null;
        }

        return route('portal.businesses.campaigns.edit', [$this->campaign->business, $this->campaign]);
    }
}
