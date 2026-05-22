<?php

namespace Modules\AppReviewBooster\Livewire;

use App\Support\Plans\PlanLimitGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppLandingPages\Support\Concerns\ManagesGrowthToolPageDesign;
use Modules\AppLandingPages\Support\LandingPageFactory;
use Modules\AppLandingPages\Support\PageTemplateCatalog;
use Modules\AppQRCampaigns\Models\QrCampaign;
use Modules\AppReviewBooster\Models\ReviewFeedback;

#[Title('Review Booster')]
class ReviewBoosterIndex extends Component
{
    use WithPagination;
    use ManagesGrowthToolPageDesign;

    public string $business_id = '';
    public string $name = 'Get More Google Reviews';
    public string $google_review_url = '';
    public string $facebook_review_url = '';
    public string $thank_you_message = 'Thank you for visiting us. Your feedback helps us improve and serve you better.';
    public string $negative_feedback_message = 'We are sorry your experience was not perfect. Please tell us what happened so we can make it right.';
    public int $positive_threshold = 4;
    public string $preferred_destination = 'google';
    public string $businessFilter = 'all';
    public string $search = '';
    public int $perPage = 10;
    public ?int $editingId = null;
    public ?string $statusMessage = null;

    public function mount(): void
    {
        $this->initializePageDesign('review');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedBusinessFilter(): void
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

    public function create(): void
    {
        $this->resetForm();

        $firstBusiness = LocalBusiness::query()->where('user_id', auth()->id())->orderBy('name')->first();
        $this->business_id = $firstBusiness ? (string) $firstBusiness->id : '';
    }

    public function edit(int $id): void
    {
        $campaign = QrCampaign::query()
            ->where('user_id', auth()->id())
            ->where('type', 'review')
            ->findOrFail($id);

        $this->editingId = $campaign->id;
        $this->business_id = (string) $campaign->business_id;
        $this->name = (string) $campaign->name;
        $this->google_review_url = (string) data_get($campaign->settings, 'google_review_url', '');
        $this->facebook_review_url = (string) data_get($campaign->settings, 'facebook_review_url', '');
        $this->thank_you_message = (string) data_get($campaign->settings, 'thank_you_message', $this->thank_you_message);
        $this->negative_feedback_message = (string) data_get($campaign->settings, 'negative_feedback_message', $this->negative_feedback_message);
        $this->positive_threshold = (int) data_get($campaign->settings, 'positive_threshold', 4);
        $this->preferred_destination = (string) data_get($campaign->settings, 'preferred_destination', 'google');
        $this->landing_template = (string) data_get($campaign->settings, 'landing_template', PageTemplateCatalog::defaultForType('review'));
        $design = (array) data_get($campaign->settings, 'design', PageTemplateCatalog::designFor($this->landing_template));
        $this->primary_color = (string) data_get($design, 'primary_color', $this->primary_color);
        $this->background_type = (string) data_get($design, 'background_type', $this->background_type);
        $this->font_style = (string) data_get($design, 'font_style', $this->font_style);
        $this->button_style = (string) data_get($design, 'button_style', $this->button_style);
        $this->card_style = (string) data_get($design, 'card_style', $this->card_style);
        $this->logo_url = (string) data_get($design, 'logo_url', '');
        $this->cover_image = (string) data_get($design, 'cover_image', '');
        $this->resetValidation();
    }

    public function save(): void
    {
        $payload = $this->validate([
            'business_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'google_review_url' => ['required', 'url', 'max:1000'],
            'facebook_review_url' => ['nullable', 'url', 'max:1000'],
            'thank_you_message' => ['required', 'string', 'max:1000'],
            'negative_feedback_message' => ['required', 'string', 'max:1000'],
            'positive_threshold' => ['required', 'integer', 'min:3', 'max:5'],
            'preferred_destination' => ['required', 'string', 'in:google,facebook'],
            ...$this->pageDesignRules('review'),
        ]);

        LocalBusiness::query()->where('user_id', auth()->id())->findOrFail((int) $payload['business_id']);

        $settings = array_merge(
            collect($payload)->except(['business_id', 'name', 'create_public_page', 'generate_qr_code', 'landing_template', 'primary_color', 'background_type', 'font_style', 'button_style', 'card_style', 'logo_url', 'cover_image'])->all(),
            $this->pageDesignSettings($payload, 'review')
        );
        $data = [
            'user_id' => auth()->id(),
            'business_id' => (int) $payload['business_id'],
            'name' => $payload['name'],
            'type' => 'review',
            'settings' => $settings,
            'published_at' => now(),
        ];

        if ($this->editingId) {
            QrCampaign::query()
                ->where('user_id', auth()->id())
                ->where('type', 'review')
                ->whereKey($this->editingId)
                ->update(collect($data)->except(['user_id', 'type', 'published_at'])->all());

            $campaign = QrCampaign::query()->findOrFail($this->editingId);
            $this->statusMessage = __('Review booster updated.');
        } else {
            $guard = app(PlanLimitGuard::class);
            $guard->ensureCampaignCanBeCreated(auth()->user());
            $guard->ensureLandingPageCanBeCreated(auth()->user());

            $campaign = QrCampaign::query()->create([
                ...$data,
                'slug' => $this->uniqueSlug($payload['name']),
            ]);

            $this->statusMessage = __('Review booster created.');
        }

        $page = $payload['create_public_page']
            ? app(LandingPageFactory::class)->syncFromCampaign($campaign)
            : null;

        $this->resetForm();
        $this->resetPage();
        $this->setCreatedGrowthToolActions($page, $campaign, $this->statusMessage);
        $this->dispatch('app-toast', type: 'success', message: $this->statusMessage);
        $this->dispatch('review-booster-saved');
    }

    public function duplicate(int $id): void
    {
        $campaign = QrCampaign::query()
            ->where('user_id', auth()->id())
            ->where('type', 'review')
            ->findOrFail($id);

        $guard = app(PlanLimitGuard::class);
        $guard->ensureCampaignCanBeCreated(auth()->user());
        $guard->ensureLandingPageCanBeCreated(auth()->user());

        QrCampaign::query()->create([
            'user_id' => auth()->id(),
            'business_id' => $campaign->business_id,
            'slug' => $this->uniqueSlug($campaign->name.' copy'),
            'name' => $campaign->name.' copy',
            'type' => 'review',
            'settings' => $campaign->settings,
            'published_at' => now(),
        ]);

        $this->statusMessage = __('Review booster duplicated.');
        $this->resetPage();
    }

    public function togglePublished(int $id): void
    {
        $campaign = QrCampaign::query()
            ->where('user_id', auth()->id())
            ->where('type', 'review')
            ->findOrFail($id);

        $campaign->forceFill([
            'published_at' => $campaign->published_at ? null : now(),
        ])->save();

        $this->statusMessage = $campaign->published_at ? __('Review booster activated.') : __('Review booster paused.');
    }

    public function delete(int $id): void
    {
        QrCampaign::query()
            ->where('user_id', auth()->id())
            ->where('type', 'review')
            ->whereKey($id)
            ->delete();

        $this->statusMessage = __('Review booster deleted.');
        $this->resetPage();
    }

    public function render(): View
    {
        $businesses = LocalBusiness::query()->where('user_id', auth()->id())->orderBy('name')->get();

        if ($this->business_id === '' && $businesses->isNotEmpty()) {
            $this->business_id = (string) $businesses->first()->id;
        }

        if ($this->businessFilter !== 'all' && ! $businesses->contains('id', (int) $this->businessFilter)) {
            $this->businessFilter = 'all';
        }

        $campaignQuery = QrCampaign::query()
            ->with('business', 'landingPage')
            ->withCount('scans')
            ->where('user_id', auth()->id())
            ->where('type', 'review')
            ->when($this->businessFilter !== 'all', fn ($query) => $query->where('business_id', (int) $this->businessFilter))
            ->when(trim($this->search) !== '', function ($query): void {
                $search = trim($this->search);
                $query->where(function ($builder) use ($search): void {
                    $builder->where('name', 'like', '%'.$search.'%')
                        ->orWhereHas('business', fn ($businessQuery) => $businessQuery->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->latest();

        $allCampaigns = QrCampaign::query()
            ->with('landingPage')
            ->withCount('scans')
            ->where('user_id', auth()->id())
            ->where('type', 'review')
            ->get();

        $publicReviewClicks = ReviewFeedback::query()
            ->where('user_id', auth()->id())
            ->where('rating', '>=', 4)
            ->count();
        $privateFeedbackCount = ReviewFeedback::query()
            ->where('user_id', auth()->id())
            ->where('rating', '<=', 3)
            ->count();

        return view('appreviewbooster::index', [
            'businesses' => $businesses,
            'campaigns' => $campaignQuery->paginate($this->perPage),
            'totalCampaigns' => $allCampaigns->count(),
            'totalScans' => $allCampaigns->sum('scans_count'),
            'activeCampaigns' => $allCampaigns->whereNotNull('published_at')->count(),
            'publicReviewClicks' => $publicReviewClicks,
            'feedbackCount' => $privateFeedbackCount,
            'reviewTemplates' => PageTemplateCatalog::forType('review'),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Review Booster'),
        ]);
    }

    protected function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'review';
        $slug = $base;
        $counter = 2;

        while (QrCampaign::query()->where('slug', $slug)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = 'Get More Google Reviews';
        $this->google_review_url = '';
        $this->facebook_review_url = '';
        $this->thank_you_message = 'Thank you for visiting us. Your feedback helps us improve and serve you better.';
        $this->negative_feedback_message = 'We are sorry your experience was not perfect. Please tell us what happened so we can make it right.';
        $this->positive_threshold = 4;
        $this->preferred_destination = 'google';
        $this->resetPageDesign('review');
        $this->resetValidation();
    }
}
