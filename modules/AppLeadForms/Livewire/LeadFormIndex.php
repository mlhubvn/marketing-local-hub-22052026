<?php

namespace Modules\AppLeadForms\Livewire;

use App\Support\Plans\PlanLimitGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppLandingPages\Models\LandingPage;
use Modules\AppLandingPages\Support\Concerns\ManagesGrowthToolPageDesign;
use Modules\AppLandingPages\Support\LandingPageFactory;
use Modules\AppLandingPages\Support\PageTemplateCatalog;
use Modules\AppLeadForms\Models\LeadSubmission;
use Modules\AppQRCampaigns\Models\QrCampaign;

#[Title('Lead Forms')]
class LeadFormIndex extends Component
{
    use WithPagination;
    use ManagesGrowthToolPageDesign;

    public string $business_id = '';
    public string $name = '';
    public string $headline = 'Request a callback';
    public string $businessFilter = 'all';
    public string $search = '';
    public int $formsPerPage = 10;
    public int $leadsPerPage = 10;
    public ?string $statusMessage = null;

    public function mount(): void
    {
        $this->initializePageDesign('lead');
    }

    public function updatedBusinessFilter(): void
    {
        $this->resetPage('formsPage');
        $this->resetPage('leadsPage');
    }

    public function updatedSearch(): void
    {
        $this->resetPage('formsPage');
        $this->resetPage('leadsPage');
    }

    public function updatedFormsPerPage(): void
    {
        if (! in_array($this->formsPerPage, [10, 25, 50], true)) {
            $this->formsPerPage = 10;
        }

        $this->resetPage('formsPage');
    }

    public function updatedLeadsPerPage(): void
    {
        if (! in_array($this->leadsPerPage, [10, 25, 50], true)) {
            $this->leadsPerPage = 10;
        }

        $this->resetPage('leadsPage');
    }

    public function save(): void
    {
        $payload = $this->validate([
            'business_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'headline' => ['required', 'string', 'max:255'],
            ...$this->pageDesignRules('lead'),
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
            'type' => 'lead',
            'settings' => array_merge(['headline' => $payload['headline']], $this->pageDesignSettings($payload, 'lead')),
            'published_at' => now(),
        ]);

        $page = $payload['create_public_page']
            ? app(LandingPageFactory::class)->syncFromCampaign($campaign)
            : null;

        $this->name = '';
        $this->headline = 'Request a callback';
        $this->resetPageDesign('lead');
        $this->statusMessage = __('Lead form created.');
        $this->setCreatedGrowthToolActions($page, $campaign, __('Lead form created. Public lead page created and QR code generated.'));
        $this->dispatch('lead-form-saved');
    }

    public function togglePublish(int $id): void
    {
        $campaign = QrCampaign::query()
            ->where('user_id', auth()->id())
            ->where('type', 'lead')
            ->findOrFail($id);

        $campaign->forceFill(['published_at' => $campaign->published_at ? null : now()])->save();

        $this->statusMessage = $campaign->published_at ? __('Lead form published.') : __('Lead form paused.');
    }

    public function delete(int $id): void
    {
        LandingPage::query()
            ->where('user_id', auth()->id())
            ->where('campaign_id', $id)
            ->delete();

        QrCampaign::query()
            ->where('user_id', auth()->id())
            ->where('type', 'lead')
            ->whereKey($id)
            ->delete();

        $this->statusMessage = __('Lead form deleted.');
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
            ->where('type', 'lead');

        if ($this->businessFilter !== 'all') {
            $campaignQuery->where('business_id', (int) $this->businessFilter);
        }

        if (trim($this->search) !== '') {
            $campaignQuery->where(function ($query): void {
                $query->where('name', 'like', '%'.trim($this->search).'%')
                    ->orWhereHas('business', fn ($businessQuery) => $businessQuery->where('name', 'like', '%'.trim($this->search).'%'));
            });
        }

        $allCampaigns = (clone $campaignQuery)->latest()->get();
        $latestCampaign = $allCampaigns->first();
        $campaigns = (clone $campaignQuery)->latest()->paginate($this->formsPerPage, ['*'], 'formsPage');
        $campaignIds = $allCampaigns->pluck('id');

        $leadQuery = LeadSubmission::query()
            ->with(['campaign.business'])
            ->where('user_id', auth()->id());

        if ($campaignIds->isNotEmpty()) {
            $leadQuery->whereIn('campaign_id', $campaignIds);
        } elseif ($this->businessFilter !== 'all' || trim($this->search) !== '') {
            $leadQuery->whereRaw('1 = 0');
        }

        $allLeadCount = LeadSubmission::query()->where('user_id', auth()->id())->count();
        $filteredLeadCount = (clone $leadQuery)->count();
        $leads = (clone $leadQuery)->latest()->paginate($this->leadsPerPage, ['*'], 'leadsPage');
        $visits = $allCampaigns->sum('scans_count');

        return view('appleadforms::index', [
            'businesses' => $businesses,
            'campaigns' => $campaigns,
            'latestCampaign' => $latestCampaign,
            'leads' => $leads,
            'filteredLeadCount' => $filteredLeadCount,
            'leadTemplates' => PageTemplateCatalog::forType('lead'),
            'stats' => [
                'forms' => $allCampaigns->count(),
                'published' => $allCampaigns->whereNotNull('published_at')->count(),
                'visits' => $visits,
                'leads' => $filteredLeadCount,
                'all_leads' => $allLeadCount,
                'lead_rate' => $visits > 0 ? round(($filteredLeadCount / $visits) * 100) : 0,
            ],
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('Lead Forms')]);
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'lead';
        $slug = $base;
        $counter = 2;
        while (QrCampaign::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }
        return $slug;
    }
}
