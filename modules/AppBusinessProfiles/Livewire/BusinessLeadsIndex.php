<?php

namespace Modules\AppBusinessProfiles\Livewire;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppBookingPages\Models\Booking;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppCouponCampaigns\Models\CouponRedemption;
use Modules\AppFeedbackForms\Models\FeedbackResponse;
use Modules\AppLeadForms\Models\LeadSubmission;
use Modules\AppQRCampaigns\Models\QrCampaign;

#[Title('Business Leads')]
class BusinessLeadsIndex extends Component
{
    use WithPagination;

    public LocalBusiness $business;
    public string $search = '';
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

    public function updatedPerPage(): void
    {
        $this->perPage = in_array((int) $this->perPage, [10, 25, 50], true) ? (int) $this->perPage : 10;
        $this->resetPage();
    }

    public function render(): View
    {
        $campaigns = QrCampaign::query()
            ->where('user_id', auth()->id())
            ->where('business_id', $this->business->id)
            ->get(['id', 'name', 'type'])
            ->keyBy('id');

        $records = collect()
            ->merge($this->leadForms($campaigns))
            ->merge($this->bookings($campaigns))
            ->merge($this->coupons($campaigns))
            ->merge($this->feedbacks($campaigns))
            ->filter(fn (array $lead): bool => $this->matchesSearch($lead))
            ->sortByDesc('created_at')
            ->values();

        return view('appbusinessprofiles::leads', [
            'leads' => $this->paginateCollection($records),
            'totalLeads' => $records->count(),
            'campaignCount' => $campaigns->count(),
            'sourceCounts' => $records->countBy('source')
                ->merge([
                    __('Lead Form') => $records->countBy('source')->get(__('Lead Form'), 0),
                    __('Booking Page') => $records->countBy('source')->get(__('Booking Page'), 0),
                    __('Coupon') => $records->countBy('source')->get(__('Coupon'), 0),
                    __('Feedback') => $records->countBy('source')->get(__('Feedback'), 0),
                    __('Landing Page') => $records->countBy('source')->get(__('Landing Page'), 0),
                ]),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Leads').' - '.$this->business->name,
        ]);
    }

    private function leadForms(Collection $campaigns): Collection
    {
        return LeadSubmission::query()
            ->where('user_id', auth()->id())
            ->whereIn('campaign_id', $campaigns->keys())
            ->latest()
            ->get()
            ->map(fn (LeadSubmission $lead): array => $this->leadRow(
                source: __('Lead Form'),
                name: $lead->name,
                phone: $lead->phone,
                email: $lead->email,
                note: $lead->message,
                campaign: $campaigns->get($lead->campaign_id)?->name,
                createdAt: $lead->created_at,
            ));
    }

    private function bookings(Collection $campaigns): Collection
    {
        return Booking::query()
            ->where('user_id', auth()->id())
            ->whereIn('campaign_id', $campaigns->keys())
            ->latest()
            ->get()
            ->map(fn (Booking $booking): array => $this->leadRow(
                source: __('Booking Page'),
                name: $booking->customer_name,
                phone: $booking->customer_phone,
                email: $booking->customer_email,
                note: trim(($booking->booking_date?->format('Y-m-d') ?: '').' '.$booking->booking_time.' '.$booking->note),
                campaign: $campaigns->get($booking->campaign_id)?->name,
                createdAt: $booking->created_at,
                status: $booking->status,
            ));
    }

    private function coupons(Collection $campaigns): Collection
    {
        return CouponRedemption::query()
            ->where('user_id', auth()->id())
            ->whereIn('campaign_id', $campaigns->keys())
            ->latest()
            ->get()
            ->map(fn (CouponRedemption $coupon): array => $this->leadRow(
                source: __('Coupon'),
                name: $coupon->customer_name,
                phone: $coupon->customer_phone,
                email: $coupon->customer_email,
                note: $coupon->code,
                campaign: $campaigns->get($coupon->campaign_id)?->name,
                createdAt: $coupon->created_at,
                status: $coupon->status,
            ));
    }

    private function feedbacks(Collection $campaigns): Collection
    {
        return FeedbackResponse::query()
            ->where('user_id', auth()->id())
            ->whereIn('campaign_id', $campaigns->keys())
            ->latest()
            ->get()
            ->map(fn (FeedbackResponse $feedback): array => $this->leadRow(
                source: __('Feedback'),
                name: $feedback->customer_name ?: __('Unknown'),
                phone: $feedback->customer_phone,
                email: $feedback->customer_email,
                note: $feedback->message,
                campaign: $campaigns->get($feedback->campaign_id)?->name,
                createdAt: $feedback->created_at,
            ));
    }

    private function leadRow(string $source, ?string $name, ?string $phone, ?string $email, ?string $note, ?string $campaign, mixed $createdAt, string $status = 'new'): array
    {
        return [
            'source' => $source,
            'name' => $name ?: __('Unknown'),
            'phone' => $phone,
            'email' => $email,
            'note' => $note,
            'campaign' => $campaign ?: __('Campaign removed'),
            'status' => $status ?: 'new',
            'created_at' => $createdAt,
        ];
    }

    private function matchesSearch(array $lead): bool
    {
        $search = strtolower(trim($this->search));

        if ($search === '') {
            return true;
        }

        return str_contains(strtolower(implode(' ', array_filter([
            $lead['name'],
            $lead['phone'],
            $lead['email'],
            $lead['note'],
            $lead['campaign'],
            $lead['source'],
        ]))), $search);
    }

    private function paginateCollection(Collection $records): LengthAwarePaginator
    {
        $page = Paginator::resolveCurrentPage();
        $items = $records->slice(($page - 1) * $this->perPage, $this->perPage)->values();

        return new Paginator($items, $records->count(), $this->perPage, $page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }
}
