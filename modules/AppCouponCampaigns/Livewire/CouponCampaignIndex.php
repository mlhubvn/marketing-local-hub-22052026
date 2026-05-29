<?php

namespace Modules\AppCouponCampaigns\Livewire;

use App\Support\Plans\PlanLimitGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppCouponCampaigns\Models\CouponRedemption;
use Modules\AppLandingPages\Models\LandingPage;
use Modules\AppLandingPages\Support\Concerns\ManagesGrowthToolPageDesign;
use Modules\AppLandingPages\Support\LandingPageFactory;
use Modules\AppLandingPages\Support\PageTemplateCatalog;
use Modules\AppQRCampaigns\Models\QrCampaign;

#[Title('Coupons')]
class CouponCampaignIndex extends Component
{
    use WithPagination;
    use ManagesGrowthToolPageDesign;

    public string $business_id = '';
    public string $name = '';
    public string $discount_type = 'percentage';
    public string $discount_value = '20';
    public string $coupon_code = '';
    public string $usage_limit = '';
    public string $expiry_date = '';
    public string $terms = '';
    public string $status = 'active';
    public ?string $statusMessage = null;
    public int $campaignsPerPage = 10;
    public int $claimsPerPage = 10;

    public function mount(): void
    {
        $this->initializePageDesign('coupon');
    }

    public function updatedCampaignsPerPage(): void
    {
        $this->campaignsPerPage = in_array((int) $this->campaignsPerPage, [10, 25, 50], true) ? (int) $this->campaignsPerPage : 10;
        $this->resetPage('campaignsPage');
    }

    public function updatedClaimsPerPage(): void
    {
        $this->claimsPerPage = in_array((int) $this->claimsPerPage, [10, 25, 50], true) ? (int) $this->claimsPerPage : 10;
        $this->resetPage('claimsPage');
    }

    public function save(): void
    {
        $payload = $this->validate([
            'business_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'discount_type' => ['required', 'string', 'in:percentage,fixed,free_item,custom'],
            'discount_value' => ['required', 'string', 'max:80'],
            'coupon_code' => ['nullable', 'string', 'max:24'],
            'usage_limit' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'expiry_date' => ['nullable', 'date'],
            'terms' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', 'in:draft,active'],
            ...$this->pageDesignRules('coupon'),
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
            'type' => 'coupon',
            'settings' => array_merge(
                collect($payload)->except(['business_id', 'name', 'create_public_page', 'generate_qr_code', 'landing_template', 'primary_color', 'background_type', 'font_style', 'button_style', 'card_style', 'logo_url', 'cover_image'])->all(),
                $this->pageDesignSettings($payload, 'coupon')
            ),
            'published_at' => $payload['status'] === 'active' ? now() : null,
        ]);

        $page = $payload['create_public_page']
            ? app(LandingPageFactory::class)->syncFromCampaign($campaign)
            : null;

        $this->name = '';
        $this->discount_type = 'percentage';
        $this->coupon_code = '';
        $this->usage_limit = '';
        $this->terms = '';
        $this->discount_value = '20';
        $this->expiry_date = '';
        $this->status = 'active';
        $this->resetPageDesign('coupon');
        $this->statusMessage = __('Coupon campaign created.');
        $this->setCreatedGrowthToolActions($page, $campaign, __('Coupon campaign created. Public coupon page created and QR code generated.'));
        $this->resetPage('campaignsPage');
        $this->dispatch('coupon-campaign-saved');
    }

    public function markUsed(int $id): void
    {
        CouponRedemption::query()
            ->where('user_id', auth()->id())
            ->whereKey($id)
            ->update([
                'status' => 'used',
                'used_at' => now(),
            ]);

        $this->statusMessage = __('Coupon marked as used.');
    }

    public function markClaimed(int $id): void
    {
        CouponRedemption::query()
            ->where('user_id', auth()->id())
            ->whereKey($id)
            ->update([
                'status' => 'claimed',
                'used_at' => null,
            ]);

        $this->statusMessage = __('Coupon returned to claimed.');
    }

    public function cancelClaim(int $id): void
    {
        CouponRedemption::query()
            ->where('user_id', auth()->id())
            ->whereKey($id)
            ->update(['status' => 'cancelled']);

        $this->statusMessage = __('Coupon claim cancelled.');
    }

    public function togglePublish(int $id): void
    {
        $campaign = QrCampaign::query()
            ->where('user_id', auth()->id())
            ->where('type', 'coupon')
            ->findOrFail($id);

        $campaign->forceFill(['published_at' => $campaign->published_at ? null : now()])->save();

        $this->statusMessage = $campaign->published_at ? __('Coupon campaign published.') : __('Coupon campaign moved to draft.');
    }

    public function delete(int $id): void
    {
        LandingPage::query()
            ->where('user_id', auth()->id())
            ->where('campaign_id', $id)
            ->delete();

        QrCampaign::query()
            ->where('user_id', auth()->id())
            ->where('type', 'coupon')
            ->whereKey($id)
            ->delete();

        $this->statusMessage = __('Coupon campaign deleted.');
        $this->resetPage('campaignsPage');
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
            ->where('type', 'coupon');

        $allCampaigns = (clone $campaignQuery)->get();
        $campaigns = (clone $campaignQuery)
            ->latest()
            ->paginate($this->campaignsPerPage, ['*'], 'campaignsPage');

        $allClaims = CouponRedemption::query()
            ->where('user_id', auth()->id())
            ->get();
        $claims = CouponRedemption::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate($this->claimsPerPage, ['*'], 'claimsPage');

        return view('appcouponcampaigns::index', [
            'businesses' => $businesses,
            'campaigns' => $campaigns,
            'campaignLookup' => $allCampaigns->keyBy('id'),
            'claims' => $claims,
            'couponTemplates' => PageTemplateCatalog::forType('coupon'),
            'stats' => [
                'campaigns' => $allCampaigns->count(),
                'visits' => $allCampaigns->sum('scans_count'),
                'claims' => $allClaims->count(),
                'used' => $allClaims->where('status', 'used')->count(),
                'claimed' => $allClaims->where('status', 'claimed')->count(),
                'redemption_rate' => $allClaims->count() > 0 ? round(($allClaims->where('status', 'used')->count() / $allClaims->count()) * 100) : 0,
            ],
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Coupons'),
        ]);
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'coupon';
        $slug = $base;
        $counter = 2;

        while (QrCampaign::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
