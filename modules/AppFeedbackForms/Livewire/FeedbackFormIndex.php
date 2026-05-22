<?php

namespace Modules\AppFeedbackForms\Livewire;

use App\Support\Plans\PlanLimitGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppFeedbackForms\Models\FeedbackResponse;
use Modules\AppLandingPages\Models\LandingPage;
use Modules\AppLandingPages\Support\Concerns\ManagesGrowthToolPageDesign;
use Modules\AppLandingPages\Support\LandingPageFactory;
use Modules\AppLandingPages\Support\PageTemplateCatalog;
use Modules\AppQRCampaigns\Models\QrCampaign;

#[Title('Feedback Forms')]
class FeedbackFormIndex extends Component
{
    use WithPagination;
    use ManagesGrowthToolPageDesign;

    public string $business_id = '';
    public string $name = '';
    public string $headline = 'Tell us about your experience';
    public string $thank_you_message = 'Thanks. Your feedback helps us improve.';
    public bool $rating_required = false;
    public bool $contact_required = false;
    public string $businessFilter = 'all';
    public string $search = '';
    public int $formsPerPage = 10;
    public int $responsesPerPage = 10;
    public ?string $statusMessage = null;

    public function mount(): void
    {
        $this->initializePageDesign('feedback');
    }

    public function updatedBusinessFilter(): void
    {
        $this->resetPage('formsPage');
        $this->resetPage('responsesPage');
    }

    public function updatedSearch(): void
    {
        $this->resetPage('formsPage');
        $this->resetPage('responsesPage');
    }

    public function updatedFormsPerPage(): void
    {
        if (! in_array($this->formsPerPage, [10, 25, 50], true)) {
            $this->formsPerPage = 10;
        }

        $this->resetPage('formsPage');
    }

    public function updatedResponsesPerPage(): void
    {
        if (! in_array($this->responsesPerPage, [10, 25, 50], true)) {
            $this->responsesPerPage = 10;
        }

        $this->resetPage('responsesPage');
    }

    public function save(): void
    {
        $payload = $this->validate([
            'business_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'headline' => ['required', 'string', 'max:255'],
            'thank_you_message' => ['required', 'string', 'max:500'],
            'rating_required' => ['boolean'],
            'contact_required' => ['boolean'],
            ...$this->pageDesignRules('feedback'),
        ]);

        LocalBusiness::query()->where('user_id', auth()->id())->findOrFail((int) $payload['business_id']);
        $guard = app(PlanLimitGuard::class);
        $guard->ensureCampaignCanBeCreated(auth()->user());
        $guard->ensureLandingPageCanBeCreated(auth()->user());

        $campaign = QrCampaign::query()->create([
            'user_id' => auth()->id(),
            'business_id' => (int) $payload['business_id'],
            'slug' => $this->uniqueSlug($payload['name']),
            'name' => $payload['name'],
            'type' => 'feedback',
            'settings' => array_merge([
                'headline' => $payload['headline'],
                'thank_you_message' => $payload['thank_you_message'],
                'rating_required' => $payload['rating_required'],
                'contact_required' => $payload['contact_required'],
            ], $this->pageDesignSettings($payload, 'feedback')),
            'published_at' => now(),
        ]);

        $page = $payload['create_public_page']
            ? app(LandingPageFactory::class)->syncFromCampaign($campaign)
            : null;

        $this->name = '';
        $this->headline = 'Tell us about your experience';
        $this->thank_you_message = 'Thanks. Your feedback helps us improve.';
        $this->rating_required = false;
        $this->contact_required = false;
        $this->resetPageDesign('feedback');
        $this->statusMessage = __('Feedback form created.');
        $this->setCreatedGrowthToolActions($page, $campaign, __('Feedback form created. Public feedback page created and QR code generated.'));
        $this->resetPage('formsPage');
        $this->dispatch('feedback-form-saved');
    }

    public function togglePublish(int $id): void
    {
        $campaign = QrCampaign::query()
            ->where('user_id', auth()->id())
            ->where('type', 'feedback')
            ->findOrFail($id);

        $campaign->forceFill(['published_at' => $campaign->published_at ? null : now()])->save();

        $this->statusMessage = $campaign->published_at ? __('Feedback form published.') : __('Feedback form paused.');
    }

    public function markResolved(int $id): void
    {
        FeedbackResponse::query()
            ->where('user_id', auth()->id())
            ->whereKey($id)
            ->update([
                'status' => 'resolved',
                'resolved_at' => now(),
            ]);

        $this->statusMessage = __('Feedback marked as resolved.');
        $this->resetPage('responsesPage');
    }

    public function undoResolved(int $id): void
    {
        FeedbackResponse::query()
            ->where('user_id', auth()->id())
            ->whereKey($id)
            ->update([
                'status' => 'new',
                'resolved_at' => null,
            ]);

        $this->statusMessage = __('Feedback response reopened.');
        $this->resetPage('responsesPage');
    }

    public function delete(int $id): void
    {
        LandingPage::query()
            ->where('user_id', auth()->id())
            ->where('campaign_id', $id)
            ->delete();

        QrCampaign::query()
            ->where('user_id', auth()->id())
            ->where('type', 'feedback')
            ->whereKey($id)
            ->delete();

        $this->statusMessage = __('Feedback form deleted.');
        $this->resetPage('formsPage');
        $this->resetPage('responsesPage');
    }

    public function render(): View
    {
        $businesses = LocalBusiness::query()->where('user_id', auth()->id())->orderBy('name')->get();
        if ($this->business_id === '' && $businesses->isNotEmpty()) {
            $this->business_id = (string) $businesses->first()->id;
        }

        $campaignQuery = QrCampaign::query()
            ->with('business', 'landingPage')
            ->withCount('scans')
            ->where('user_id', auth()->id())
            ->where('type', 'feedback');

        if ($this->businessFilter !== 'all') {
            $campaignQuery->where('business_id', (int) $this->businessFilter);
        }

        if (trim($this->search) !== '') {
            $campaignQuery->where(function ($query): void {
                $term = '%'.trim($this->search).'%';
                $query->where('name', 'like', $term)
                    ->orWhereHas('business', fn ($businessQuery) => $businessQuery->where('name', 'like', $term));
            });
        }

        $allCampaigns = (clone $campaignQuery)->latest()->get();
        $latestCampaign = $allCampaigns->first();
        $campaigns = (clone $campaignQuery)->latest()->paginate($this->formsPerPage, ['*'], 'formsPage');
        $campaignIds = $allCampaigns->pluck('id');

        $responseQuery = FeedbackResponse::query()
            ->with(['campaign.business'])
            ->where('user_id', auth()->id());

        if ($campaignIds->isNotEmpty()) {
            $responseQuery->whereIn('campaign_id', $campaignIds);
        } elseif ($this->businessFilter !== 'all' || trim($this->search) !== '') {
            $responseQuery->whereRaw('1 = 0');
        }

        $allResponses = FeedbackResponse::query()->where('user_id', auth()->id())->get();
        $filteredResponseCount = (clone $responseQuery)->count();
        $responses = (clone $responseQuery)->latest()->paginate($this->responsesPerPage, ['*'], 'responsesPage');
        $ratedResponses = $allResponses->whereNotNull('rating');

        return view('appfeedbackforms::index', [
            'businesses' => $businesses,
            'campaigns' => $campaigns,
            'latestCampaign' => $latestCampaign,
            'responses' => $responses,
            'filteredResponseCount' => $filteredResponseCount,
            'feedbackTemplates' => PageTemplateCatalog::forType('feedback'),
            'stats' => [
                'forms' => $allCampaigns->count(),
                'published' => $allCampaigns->whereNotNull('published_at')->count(),
                'visits' => $allCampaigns->sum('scans_count'),
                'responses' => $allResponses->count(),
                'low_score' => $allResponses->where('rating', '<=', 3)->whereNotNull('rating')->count(),
                'resolved' => $allResponses->where('status', 'resolved')->count(),
                'avg_rating' => $ratedResponses->count() > 0 ? round($ratedResponses->avg('rating'), 1) : 0,
            ],
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('Feedback Forms')]);
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'feedback';
        $slug = $base;
        $counter = 2;
        while (QrCampaign::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }
        return $slug;
    }
}
