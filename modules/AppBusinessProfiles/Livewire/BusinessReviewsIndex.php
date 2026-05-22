<?php

namespace Modules\AppBusinessProfiles\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppQRCampaigns\Models\QrCampaign;
use Modules\AppReviewBooster\Models\ReviewFeedback;

#[Title('Business Reviews')]
class BusinessReviewsIndex extends Component
{
    use WithPagination;

    public LocalBusiness $business;
    public string $search = '';
    public string $filter = 'all';
    public int $perPage = 10;
    public ?string $statusMessage = null;

    public function mount(LocalBusiness $business): void
    {
        abort_unless((int) $business->user_id === (int) auth()->id(), 404);

        $this->business = $business;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilter(): void
    {
        if (! in_array($this->filter, ['all', 'positive', 'low_score', 'sent_to_google', 'needs_reply', 'resolved'], true)) {
            $this->filter = 'all';
        }

        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->perPage = in_array((int) $this->perPage, [10, 25, 50], true) ? (int) $this->perPage : 10;
        $this->resetPage();
    }

    public function markReplied(int $feedbackId): void
    {
        $this->updateFeedbackStatus($feedbackId, 'replied');
    }

    public function markResolved(int $feedbackId): void
    {
        $this->updateFeedbackStatus($feedbackId, 'resolved');
    }

    public function render(): View
    {
        $hasStatusColumn = Schema::hasColumn('lb_review_feedbacks', 'status');
        $campaignIds = QrCampaign::query()
            ->where('user_id', auth()->id())
            ->where('business_id', $this->business->id)
            ->where('type', 'review')
            ->pluck('id');

        $feedbackQuery = ReviewFeedback::query()
            ->with('campaign')
            ->where('user_id', auth()->id())
            ->whereIn('campaign_id', $campaignIds)
            ->when(in_array($this->filter, ['positive', 'sent_to_google'], true), fn ($query) => $query->where('rating', '>=', 4))
            ->when($this->filter === 'low_score', fn ($query) => $query->where('rating', '<=', 3))
            ->when($this->filter === 'needs_reply', function ($query) use ($hasStatusColumn): void {
                $query->where('rating', '<=', 3);

                if ($hasStatusColumn) {
                    $query->whereNotIn('status', ['replied', 'resolved']);
                }
            })
            ->when($this->filter === 'resolved', fn ($query) => $hasStatusColumn ? $query->where('status', 'resolved') : $query->whereRaw('1 = 0'))
            ->when(trim($this->search) !== '', function ($query): void {
                $search = trim($this->search);

                $query->where(function ($builder) use ($search): void {
                    $builder->where('customer_name', 'like', '%'.$search.'%')
                        ->orWhere('customer_phone', 'like', '%'.$search.'%')
                        ->orWhere('customer_email', 'like', '%'.$search.'%')
                        ->orWhere('message', 'like', '%'.$search.'%');
                });
            });

        return view('appbusinessprofiles::reviews', [
            'feedbacks' => (clone $feedbackQuery)->latest()->paginate($this->perPage),
            'totalReviews' => (clone $feedbackQuery)->count(),
            'lowScoreCount' => (clone $feedbackQuery)->where('rating', '<=', 3)->count(),
            'averageRating' => round((float) (clone $feedbackQuery)->avg('rating'), 1),
            'hasFeedbackStatusColumn' => $hasStatusColumn,
            'ratingCounts' => ReviewFeedback::query()
                ->where('user_id', auth()->id())
                ->whereIn('campaign_id', $campaignIds)
                ->selectRaw('rating, count(*) as total')
                ->groupBy('rating')
                ->pluck('total', 'rating'),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Reviews').' - '.$this->business->name,
        ]);
    }

    protected function updateFeedbackStatus(int $feedbackId, string $status): void
    {
        if (! in_array($status, ['replied', 'resolved'], true) || ! Schema::hasColumn('lb_review_feedbacks', 'status')) {
            $this->statusMessage = __('Run migrations to enable review reply statuses.');

            return;
        }

        $campaignIds = QrCampaign::query()
            ->where('user_id', auth()->id())
            ->where('business_id', $this->business->id)
            ->where('type', 'review')
            ->pluck('id');

        $feedback = ReviewFeedback::query()
            ->where('user_id', auth()->id())
            ->whereIn('campaign_id', $campaignIds)
            ->findOrFail($feedbackId);

        $updates = [
            'status' => $status,
        ];

        if ($status === 'replied' && Schema::hasColumn('lb_review_feedbacks', 'replied_at')) {
            $updates['replied_at'] = now();
        }

        if ($status === 'resolved' && Schema::hasColumn('lb_review_feedbacks', 'resolved_at')) {
            $updates['resolved_at'] = now();
        }

        $feedback->forceFill($updates)->save();
        $this->statusMessage = $status === 'resolved'
            ? __('Feedback marked as resolved.')
            : __('Feedback marked as replied.');
    }
}
