<?php

namespace Modules\AppLoyaltyStampCards\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppLandingPages\Support\Concerns\ManagesGrowthToolPageDesign;
use Modules\AppLandingPages\Support\PageTemplateCatalog;
use Modules\AppLoyaltyStampCards\Models\LoyaltyCard;
use Modules\AppLoyaltyStampCards\Models\LoyaltyCustomer;
use Modules\AppLoyaltyStampCards\Models\LoyaltyReward;
use Modules\AppLoyaltyStampCards\Models\Referral;
use Modules\AppLoyaltyStampCards\Models\ReferralCampaign;
use Modules\AppLoyaltyStampCards\Models\ReferralReward;

#[Title('Loyalty Cards')]
class LoyaltyCardsIndex extends Component
{
    use ManagesGrowthToolPageDesign;
    use WithPagination;

    public string $business_id = '';
    public string $name = 'Coffee Stamp Card';
    public int $required_stamps = 10;
    public string $stamp_method = 'qr_scan';
    public string $customer_identifier = 'phone';
    public string $reward_title = 'Free drink reward';
    public string $reward_type = 'free_item';
    public string $reward_value = 'Free drink';
    public string $expiry_days = '30';
    public int $stamp_cooldown_minutes = 1440;
    public int $max_stamps_per_day = 1;
    public string $status = 'active';
    public string $cardFilter = 'all';
    public int $cardsPerPage = 10;
    public int $customersPerPage = 10;
    public int $stampsPerPage = 10;
    public int $rewardsPerPage = 10;
    public int $referralCampaignsPerPage = 10;
    public int $referralsPerPage = 10;
    public ?int $editingId = null;
    public ?int $editingReferralCampaignId = null;
    public ?string $statusMessage = null;
    public string $referral_business_id = '';
    public string $referral_name = 'Invite a Friend';
    public string $referral_reward_title = '20% off next visit';
    public string $referral_reward_type = 'discount';
    public string $referral_reward_value = '20% off';
    public int $referral_required_referrals = 1;
    public string $referral_target_action = 'lead';
    public string $referral_expiry_days = '30';
    public string $referral_status = 'active';

    public function mount(): void
    {
        abort_unless(auth()->user()?->canUsePlanFeature('loyalty_stamp_cards'), 403);

        $this->initializePageDesign('loyalty');
    }

    public function updatedCardFilter(): void
    {
        $this->resetPage('customersPage');
        $this->resetPage('stampsPage');
        $this->resetPage('rewardsPage');
    }

    public function updatedCardsPerPage(): void
    {
        $this->cardsPerPage = in_array((int) $this->cardsPerPage, [10, 25, 50], true) ? (int) $this->cardsPerPage : 10;
        $this->resetPage('cardsPage');
    }

    public function updatedCustomersPerPage(): void
    {
        $this->customersPerPage = in_array((int) $this->customersPerPage, [10, 25, 50], true) ? (int) $this->customersPerPage : 10;
        $this->resetPage('customersPage');
    }

    public function updatedStampsPerPage(): void
    {
        $this->stampsPerPage = in_array((int) $this->stampsPerPage, [10, 25, 50], true) ? (int) $this->stampsPerPage : 10;
        $this->resetPage('stampsPage');
    }

    public function updatedRewardsPerPage(): void
    {
        $this->rewardsPerPage = in_array((int) $this->rewardsPerPage, [10, 25, 50], true) ? (int) $this->rewardsPerPage : 10;
        $this->resetPage('rewardsPage');
    }

    public function updatedReferralCampaignsPerPage(): void
    {
        $this->referralCampaignsPerPage = in_array((int) $this->referralCampaignsPerPage, [10, 25, 50], true) ? (int) $this->referralCampaignsPerPage : 10;
        $this->resetPage('referralCampaignsPage');
    }

    public function updatedReferralsPerPage(): void
    {
        $this->referralsPerPage = in_array((int) $this->referralsPerPage, [10, 25, 50], true) ? (int) $this->referralsPerPage : 10;
        $this->resetPage('referralsPage');
    }

    public function save(): void
    {
        $payload = $this->validate([
            'business_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'required_stamps' => ['required', 'integer', 'min:1', 'max:100'],
            'stamp_method' => ['required', 'string', 'in:qr_scan,staff_approval'],
            'customer_identifier' => ['required', 'string', 'in:phone,email,phone_or_email'],
            'reward_title' => ['required', 'string', 'max:255'],
            'reward_type' => ['required', 'string', 'in:coupon,free_item,discount,custom'],
            'reward_value' => ['nullable', 'string', 'max:120'],
            'expiry_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'stamp_cooldown_minutes' => ['required', 'integer', 'min:0', 'max:525600'],
            'max_stamps_per_day' => ['required', 'integer', 'min:1', 'max:100'],
            'status' => ['required', 'string', 'in:draft,active'],
            ...$this->pageDesignRules('loyalty'),
        ]);

        LocalBusiness::query()->where('user_id', auth()->id())->findOrFail((int) $payload['business_id']);

        $data = [
            'business_id' => (int) $payload['business_id'],
            'name' => $payload['name'],
            'required_stamps' => (int) $payload['required_stamps'],
            'stamp_method' => $payload['stamp_method'],
            'customer_identifier' => $payload['customer_identifier'],
            'reward_title' => $payload['reward_title'],
            'reward_type' => $payload['reward_type'],
            'reward_value' => $payload['reward_value'],
            'expiry_days' => filled($payload['expiry_days']) ? (int) $payload['expiry_days'] : null,
            'stamp_cooldown_minutes' => (int) $payload['stamp_cooldown_minutes'],
            'max_stamps_per_day' => (int) $payload['max_stamps_per_day'],
            'settings' => $this->pageDesignSettings($payload, 'loyalty'),
            'status' => $payload['status'],
        ];

        if ($this->editingId) {
            LoyaltyCard::query()
                ->where('user_id', auth()->id())
                ->whereKey($this->editingId)
                ->update($data);

            $this->statusMessage = __('Loyalty stamp card updated.');
        } else {
            $this->ensureCardLimit();

            LoyaltyCard::query()->create([
                'user_id' => auth()->id(),
                'team_id' => $this->currentTeamId(),
                'slug' => $this->uniqueSlug($payload['name']),
                ...$data,
            ]);

            $this->statusMessage = __('Loyalty stamp card created.');
        }

        $this->resetForm();
        $this->resetPage('cardsPage');
        $this->dispatch('loyalty-card-saved');
    }

    public function edit(int $id): void
    {
        $card = LoyaltyCard::query()
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $this->editingId = $card->id;
        $this->business_id = (string) $card->business_id;
        $this->name = (string) $card->name;
        $this->required_stamps = (int) $card->required_stamps;
        $this->stamp_method = (string) $card->stamp_method;
        $this->customer_identifier = (string) $card->customer_identifier;
        $this->reward_title = (string) $card->reward_title;
        $this->reward_type = (string) $card->reward_type;
        $this->reward_value = (string) ($card->reward_value ?? '');
        $this->expiry_days = $card->expiry_days !== null ? (string) $card->expiry_days : '';
        $this->stamp_cooldown_minutes = (int) ($card->stamp_cooldown_minutes ?? 1440);
        $this->max_stamps_per_day = (int) ($card->max_stamps_per_day ?? 1);
        $this->status = (string) $card->status;
        $this->applyPageDesignSettings((array) ($card->settings ?: []));
        $this->resetValidation();
    }

    public function create(): void
    {
        $this->resetForm();
    }

    public function toggleStatus(int $id): void
    {
        $card = LoyaltyCard::query()->where('user_id', auth()->id())->findOrFail($id);
        $card->forceFill(['status' => $card->status === 'active' ? 'draft' : 'active'])->save();

        $this->statusMessage = $card->status === 'active' ? __('Loyalty card activated.') : __('Loyalty card paused.');
    }

    public function delete(int $id): void
    {
        LoyaltyCard::query()->where('user_id', auth()->id())->whereKey($id)->delete();
        $this->statusMessage = __('Loyalty card deleted.');
        $this->resetPage('cardsPage');
    }

    public function markRewardUsed(int $id): void
    {
        LoyaltyReward::query()
            ->whereHas('card', fn ($query) => $query->where('user_id', auth()->id()))
            ->whereKey($id)
            ->update(['status' => 'used', 'used_at' => now()]);

        $this->statusMessage = __('Reward marked as used.');
    }

    public function restoreReward(int $id): void
    {
        LoyaltyReward::query()
            ->whereHas('card', fn ($query) => $query->where('user_id', auth()->id()))
            ->whereKey($id)
            ->update(['status' => 'available', 'used_at' => null]);

        $this->statusMessage = __('Reward returned to available.');
    }

    public function saveReferralCampaign(): void
    {
        $payload = $this->validate([
            'referral_business_id' => ['required', 'integer'],
            'referral_name' => ['required', 'string', 'max:255'],
            'referral_reward_title' => ['required', 'string', 'max:255'],
            'referral_reward_type' => ['required', 'string', 'in:coupon,free_item,discount,custom'],
            'referral_reward_value' => ['nullable', 'string', 'max:120'],
            'referral_required_referrals' => ['required', 'integer', 'min:1', 'max:100'],
            'referral_target_action' => ['required', 'string', 'in:lead,booking,coupon_claim'],
            'referral_expiry_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'referral_status' => ['required', 'string', 'in:draft,active'],
        ]);

        LocalBusiness::query()->where('user_id', auth()->id())->findOrFail((int) $payload['referral_business_id']);

        $data = [
            'business_id' => (int) $payload['referral_business_id'],
            'name' => $payload['referral_name'],
            'reward_title' => $payload['referral_reward_title'],
            'reward_type' => $payload['referral_reward_type'],
            'reward_value' => $payload['referral_reward_value'],
            'required_referrals' => (int) $payload['referral_required_referrals'],
            'target_action' => $payload['referral_target_action'],
            'expiry_days' => filled($payload['referral_expiry_days']) ? (int) $payload['referral_expiry_days'] : null,
            'status' => $payload['referral_status'],
        ];

        if ($this->editingReferralCampaignId) {
            ReferralCampaign::query()
                ->where('user_id', auth()->id())
                ->whereKey($this->editingReferralCampaignId)
                ->update($data);

            $this->statusMessage = __('Referral campaign updated.');
        } else {
            $this->ensureReferralCampaignLimit();

            ReferralCampaign::query()->create([
                'user_id' => auth()->id(),
                'team_id' => $this->currentTeamId(),
                'slug' => $this->uniqueReferralSlug($payload['referral_name']),
                ...$data,
            ]);

            $this->statusMessage = __('Referral campaign created.');
        }

        $this->resetReferralForm();
        $this->resetPage('referralCampaignsPage');
        $this->dispatch('referral-campaign-saved');
    }

    public function editReferralCampaign(int $id): void
    {
        $campaign = ReferralCampaign::query()->where('user_id', auth()->id())->findOrFail($id);

        $this->editingReferralCampaignId = $campaign->id;
        $this->referral_business_id = (string) $campaign->business_id;
        $this->referral_name = (string) $campaign->name;
        $this->referral_reward_title = (string) $campaign->reward_title;
        $this->referral_reward_type = (string) $campaign->reward_type;
        $this->referral_reward_value = (string) ($campaign->reward_value ?? '');
        $this->referral_required_referrals = (int) $campaign->required_referrals;
        $this->referral_target_action = (string) $campaign->target_action;
        $this->referral_expiry_days = $campaign->expiry_days !== null ? (string) $campaign->expiry_days : '';
        $this->referral_status = (string) $campaign->status;
        $this->resetValidation();
    }

    public function createReferralCampaign(): void
    {
        $this->resetReferralForm();
    }

    public function toggleReferralCampaignStatus(int $id): void
    {
        $campaign = ReferralCampaign::query()->where('user_id', auth()->id())->findOrFail($id);
        $campaign->forceFill(['status' => $campaign->status === 'active' ? 'draft' : 'active'])->save();

        $this->statusMessage = $campaign->status === 'active' ? __('Referral campaign activated.') : __('Referral campaign paused.');
    }

    public function deleteReferralCampaign(int $id): void
    {
        ReferralCampaign::query()->where('user_id', auth()->id())->whereKey($id)->delete();
        $this->statusMessage = __('Referral campaign deleted.');
        $this->resetPage('referralCampaignsPage');
    }

    public function render(): View
    {
        $businesses = LocalBusiness::query()->where('user_id', auth()->id())->orderBy('name')->get();

        if ($this->business_id === '' && $businesses->isNotEmpty()) {
            $this->business_id = (string) $businesses->first()->id;
        }
        if ($this->referral_business_id === '' && $businesses->isNotEmpty()) {
            $this->referral_business_id = (string) $businesses->first()->id;
        }

        $cardsQuery = LoyaltyCard::query()
            ->with('business')
            ->withCount(['customers', 'stamps', 'rewards'])
            ->where('user_id', auth()->id());

        $allCards = (clone $cardsQuery)->get();
        $cardIds = $allCards->pluck('id')->all();

        $customersQuery = LoyaltyCustomer::query()
            ->with(['card.business', 'customer'])
            ->whereHas('card', fn ($query) => $query->where('user_id', auth()->id()))
            ->when($this->cardFilter !== 'all', fn ($query) => $query->where('card_id', (int) $this->cardFilter))
            ->latest();

        $rewardsQuery = LoyaltyReward::query()
            ->with(['card.business', 'customer'])
            ->whereIn('card_id', $cardIds)
            ->when($this->cardFilter !== 'all', fn ($query) => $query->where('card_id', (int) $this->cardFilter))
            ->latest();

        $stampsQuery = \Modules\AppLoyaltyStampCards\Models\LoyaltyStamp::query()
            ->with(['card.business', 'customer'])
            ->whereIn('card_id', $cardIds)
            ->when($this->cardFilter !== 'all', fn ($query) => $query->where('card_id', (int) $this->cardFilter))
            ->latest();

        $allRewards = (clone $rewardsQuery)->get();
        $referralCampaignsQuery = ReferralCampaign::query()
            ->with('business')
            ->withCount(['links', 'referrals', 'rewards'])
            ->where('user_id', auth()->id());

        $allReferralCampaigns = (clone $referralCampaignsQuery)->get();
        $referralCampaignIds = $allReferralCampaigns->pluck('id')->all();

        $referralsQuery = Referral::query()
            ->with(['campaign.business', 'referrer', 'referred'])
            ->whereIn('campaign_id', $referralCampaignIds)
            ->latest();

        $referralRewardsQuery = ReferralReward::query()
            ->with(['campaign.business', 'customer'])
            ->whereIn('campaign_id', $referralCampaignIds)
            ->latest();
        $allReferralRewards = (clone $referralRewardsQuery)->get();

        return view('apployaltystampcards::index', [
            'businesses' => $businesses,
            'cards' => (clone $cardsQuery)->latest()->paginate($this->cardsPerPage, ['*'], 'cardsPage'),
            'allCards' => $allCards,
            'loyaltyCustomers' => $customersQuery->paginate($this->customersPerPage, ['*'], 'customersPage'),
            'stamps' => $stampsQuery->paginate($this->stampsPerPage, ['*'], 'stampsPage'),
            'rewards' => $rewardsQuery->paginate($this->rewardsPerPage, ['*'], 'rewardsPage'),
            'referralCampaigns' => (clone $referralCampaignsQuery)->latest()->paginate($this->referralCampaignsPerPage, ['*'], 'referralCampaignsPage'),
            'referrals' => $referralsQuery->paginate($this->referralsPerPage, ['*'], 'referralsPage'),
            'referralRewards' => $referralRewardsQuery->paginate($this->rewardsPerPage, ['*'], 'referralRewardsPage'),
            'loyaltyTemplates' => PageTemplateCatalog::forType('loyalty'),
            'stats' => [
                'cards' => $allCards->count(),
                'active' => $allCards->where('status', 'active')->count(),
                'stamps' => $allCards->sum('stamps_count'),
                'customers' => LoyaltyCustomer::query()->whereIn('card_id', $cardIds)->count(),
                'available_rewards' => $allRewards->where('status', 'available')->count(),
                'used_rewards' => $allRewards->where('status', 'used')->count(),
                'referral_campaigns' => $allReferralCampaigns->count(),
                'referrals' => Referral::query()->whereIn('campaign_id', $referralCampaignIds)->count(),
                'referral_rewards' => $allReferralRewards->where('status', 'available')->count(),
            ],
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Loyalty Cards'),
        ]);
    }

    protected function ensureCardLimit(): void
    {
        $limit = auth()->user()?->planLimit('max_loyalty_cards', -1);

        if ((int) $limit === -1) {
            return;
        }

        abort_if(LoyaltyCard::query()->where('user_id', auth()->id())->count() >= (int) $limit, 403, __('Your plan loyalty card limit has been reached.'));
    }

    protected function ensureReferralCampaignLimit(): void
    {
        $limit = auth()->user()?->planLimit('max_referral_campaigns', -1);

        if ((int) $limit === -1) {
            return;
        }

        abort_if(ReferralCampaign::query()->where('user_id', auth()->id())->count() >= (int) $limit, 403, __('Your plan referral campaign limit has been reached.'));
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'loyalty-card';
        $slug = $base;
        $counter = 2;

        while (LoyaltyCard::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    protected function uniqueReferralSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'referral-campaign';
        $slug = $base;
        $counter = 2;

        while (ReferralCampaign::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->name = 'Coffee Stamp Card';
        $this->required_stamps = 10;
        $this->stamp_method = 'qr_scan';
        $this->customer_identifier = 'phone';
        $this->reward_title = 'Free drink reward';
        $this->reward_type = 'free_item';
        $this->reward_value = 'Free drink';
        $this->expiry_days = '30';
        $this->stamp_cooldown_minutes = 1440;
        $this->max_stamps_per_day = 1;
        $this->status = 'active';
        $this->resetPageDesign('loyalty');
        $this->resetValidation();
    }

    protected function resetReferralForm(): void
    {
        $this->editingReferralCampaignId = null;
        $this->referral_name = 'Invite a Friend';
        $this->referral_reward_title = '20% off next visit';
        $this->referral_reward_type = 'discount';
        $this->referral_reward_value = '20% off';
        $this->referral_required_referrals = 1;
        $this->referral_target_action = 'lead';
        $this->referral_expiry_days = '30';
        $this->referral_status = 'active';
        $this->resetValidation();
    }

    protected function applyPageDesignSettings(array $settings): void
    {
        $template = (string) data_get($settings, 'landing_template', PageTemplateCatalog::defaultForType('loyalty'));
        if (! array_key_exists($template, PageTemplateCatalog::forType('loyalty'))) {
            $template = PageTemplateCatalog::defaultForType('loyalty');
        }

        $design = array_merge(PageTemplateCatalog::designFor($template), (array) data_get($settings, 'design', []));

        $this->create_public_page = (bool) data_get($settings, 'create_public_page', true);
        $this->generate_qr_code = (bool) data_get($settings, 'generate_qr_code', true);
        $this->landing_template = $template;
        $this->primary_color = (string) data_get($design, 'primary_color', '#0f766e');
        $this->background_type = (string) data_get($design, 'background_type', 'gradient');
        $this->font_style = (string) data_get($design, 'font_style', 'modern');
        $this->button_style = (string) data_get($design, 'button_style', 'pill');
        $this->card_style = (string) data_get($design, 'card_style', 'soft');
        $this->logo_url = (string) data_get($design, 'logo_url', '');
        $this->cover_image = (string) data_get($design, 'cover_image', '');
    }

    protected function currentTeamId(): ?int
    {
        $access = 'Modules\\AppTeams\\Support\\TeamWorkspaceAccess';

        if (! class_exists($access)) {
            return null;
        }

        $team = $access::activeTeam(auth()->user());

        return $team ? (int) $team->id : null;
    }
}
