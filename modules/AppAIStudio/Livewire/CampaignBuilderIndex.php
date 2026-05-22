<?php

namespace Modules\AppAIStudio\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AdminSettings\Support\OptionStore;
use Modules\AppAIStudio\Models\AIPromptHistory;
use Modules\AppAIStudio\Models\AIStudioUserSetting;
use Modules\AppAIStudio\Models\AIStudioWorkspaceSetting;
use Modules\AppAIStudio\Support\AiContentStudioService;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppQRCampaigns\Models\QrCampaign;
use Modules\AppTeams\Support\TeamWorkspaceAccess;
use Throwable;

#[Title('AI Campaign Builder')]
class CampaignBuilderIndex extends Component
{
    use WithPagination;

    public string $tab = 'builder';
    public string $ideaFilter = 'all';
    public string $draftSearch = '';
    public string $draftType = 'all';
    public string $draftStatus = 'all';
    public int $draftPerPage = 5;
    public ?int $renamingDraftId = null;
    public string $draftTitle = '';
    public string $business_id = '';
    public string $goal = 'reviews';
    public string $offer = '';
    public string $target_customer = '';
    public string $tone = '';
    public string $language = '';
    public string $prompt = '';
    public ?array $plan = null;
    public ?int $draftId = null;
    public ?string $statusMessage = null;
    public ?string $aiSource = null;

    public function mount(): void
    {
        $defaults = $this->resolveAiDefaults();
        $this->tone = $defaults['tone'];
        $this->language = $defaults['language'];

        $business = LocalBusiness::query()->where('user_id', auth()->id())->orderBy('name')->first();

        if ($business) {
            $this->business_id = (string) $business->id;
        }
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['builder', 'ideas', 'drafts'], true)) {
            $this->tab = $tab;
        }
    }

    public function setIdeaFilter(string $filter): void
    {
        if (array_key_exists($filter, $this->ideaFilters())) {
            $this->ideaFilter = $filter;
        }
    }

    public function updatedDraftSearch(): void
    {
        $this->resetPage('draftsPage');
    }

    public function updatedDraftType(): void
    {
        $this->resetPage('draftsPage');
    }

    public function updatedDraftStatus(): void
    {
        $this->resetPage('draftsPage');
    }

    public function updatedDraftPerPage(): void
    {
        if (! in_array($this->draftPerPage, [5, 10, 25, 50], true)) {
            $this->draftPerPage = 5;
        }

        $this->resetPage('draftsPage');
    }

    public function useIdea(string $goal, string $offer, string $prompt = ''): void
    {
        $this->tab = 'builder';
        $this->goal = $goal;
        $this->offer = $offer;
        $this->prompt = $prompt !== '' ? $prompt : $offer;
    }

    public function useIdeaTemplate(int $index): void
    {
        $idea = $this->filteredOfferIdeas()[$index] ?? null;

        if (! $idea) {
            return;
        }

        $this->useIdea(
            (string) $idea['goal'],
            (string) $idea['label'],
            (string) $idea['description']
        );
        $this->target_customer = (string) ($idea['target_customer'] ?? $idea['best_for'] ?? '');
    }

    public function generate(): void
    {
        $payload = $this->validate([
            'business_id' => ['required', 'integer'],
            'goal' => ['required', 'string', 'in:reviews,bookings,coupon,feedback,leads,returning'],
            'offer' => ['nullable', 'string', 'max:255'],
            'target_customer' => ['nullable', 'string', 'max:255'],
            'tone' => ['required', 'string', 'max:40'],
            'language' => ['required', 'string', 'max:80'],
            'prompt' => ['nullable', 'string', 'max:1200'],
        ]);

        $business = LocalBusiness::query()
            ->where('user_id', auth()->id())
            ->findOrFail((int) $payload['business_id']);

        $fallbackPlan = $this->buildPlan($business, $payload);
        $this->plan = $fallbackPlan;
        $this->aiSource = 'fallback';

        try {
            $aiPlan = app(AiContentStudioService::class)->generateLocalCampaignPlan([
                'business_name' => $business->name,
                'business_type' => $business->type ?: __('Local business'),
                'goal' => data_get($this->goalOptions(), $payload['goal'].'.label', $payload['goal']),
                'campaign_type' => $fallbackPlan['campaign_type'],
                'offer' => $payload['offer'] ?: $payload['prompt'],
                'target_customer' => $payload['target_customer'],
                'tone' => $payload['tone'],
                'language' => $this->languageLabel($payload['language']),
                'prompt' => $payload['prompt'],
            ]);

            $this->plan = array_replace($fallbackPlan, $aiPlan, [
                'campaign_type_key' => $fallbackPlan['campaign_type_key'],
                'campaign_type' => $fallbackPlan['campaign_type'],
                'goal' => $fallbackPlan['goal'],
                'business_name' => $fallbackPlan['business_name'],
                'business_type' => $fallbackPlan['business_type'],
                'tone' => $payload['tone'],
                'language' => $this->languageLabel($payload['language']),
                'target_customer' => $payload['target_customer'],
                'source' => 'ai',
            ]);

            if ($this->shouldUseFallbackTitle((string) $payload['language'], (string) data_get($this->plan, 'campaign_name', ''))) {
                $this->plan['campaign_name'] = $fallbackPlan['campaign_name'];
            }

            $this->aiSource = 'ai';
            $this->statusMessage = __('AI campaign plan generated.');
        } catch (Throwable $exception) {
            $this->plan = array_replace($fallbackPlan, [
                'source' => 'fallback',
                'fallback_reason' => $exception->getMessage(),
            ]);
            $this->statusMessage = __('AI unavailable. A fallback campaign plan was generated.');
        }

        $this->draftId = $this->saveDraft($business, $payload, $this->plan);
    }

    public function createCampaign(): void
    {
        if (! $this->plan) {
            $this->generate();
        }

        $this->createCampaignFromPlan($this->plan, (int) $this->business_id, $this->draftId);

        $this->statusMessage = __('Campaign created from AI plan.');
    }

    public function createCampaignFromDraft(int $id): void
    {
        $draft = $this->draftQuery()->findOrFail($id);
        $plan = $draft->output_payload ?: [];
        $businessId = (int) data_get($draft->input_payload, 'business_id');

        $this->createCampaignFromPlan($plan, $businessId, $draft->id);
        $this->statusMessage = __('Campaign created from draft.');
    }

    public function duplicateDraft(int $id): void
    {
        $draft = $this->draftQuery()->findOrFail($id);
        $user = auth()->user();
        $team = TeamWorkspaceAccess::activeTeam($user);

        AIPromptHistory::query()->create([
            'owner_user_id' => TeamWorkspaceAccess::workspaceOwnerUserId($user),
            'requested_by_user_id' => $user->id,
            'team_id' => $team?->id,
            'module' => 'ai_campaign_builder',
            'title' => __('Copy of :title', ['title' => $draft->title]),
            'language' => $draft->language,
            'tone' => $draft->tone,
            'prompt' => $draft->prompt,
            'input_payload' => $draft->input_payload,
            'output_payload' => $draft->output_payload,
            'metadata' => ['status' => 'draft'],
        ]);

        $this->statusMessage = __('Draft duplicated.');
        $this->resetPage('draftsPage');
    }

    public function deleteDraft(int $id): void
    {
        $this->draftQuery()->whereKey($id)->delete();
        $this->statusMessage = __('Draft deleted.');
        $this->resetPage('draftsPage');
    }

    public function startRenameDraft(int $id): void
    {
        $draft = $this->draftQuery()->findOrFail($id);

        $this->renamingDraftId = $draft->id;
        $this->draftTitle = (string) $draft->title;
    }

    public function cancelRenameDraft(): void
    {
        $this->renamingDraftId = null;
        $this->draftTitle = '';
    }

    public function saveDraftTitle(): void
    {
        if (! $this->renamingDraftId) {
            return;
        }

        $payload = $this->validate([
            'draftTitle' => ['required', 'string', 'max:110'],
        ]);

        $draft = $this->draftQuery()->findOrFail($this->renamingDraftId);
        $draft->update(['title' => trim((string) $payload['draftTitle'])]);

        $this->cancelRenameDraft();
        $this->statusMessage = __('Draft title updated.');
    }

    public function saveCurrentDraft(): void
    {
        if (! $this->plan) {
            $this->generate();

            return;
        }

        $payload = $this->validate([
            'business_id' => ['required', 'integer'],
            'goal' => ['required', 'string', 'in:reviews,bookings,coupon,feedback,leads,returning'],
            'offer' => ['nullable', 'string', 'max:255'],
            'target_customer' => ['nullable', 'string', 'max:255'],
            'tone' => ['required', 'string', 'max:40'],
            'language' => ['required', 'string', 'max:80'],
            'prompt' => ['nullable', 'string', 'max:1200'],
        ]);

        $business = LocalBusiness::query()
            ->where('user_id', auth()->id())
            ->findOrFail((int) $payload['business_id']);

        $this->draftId = $this->saveDraft($business, $payload, $this->plan);
        $this->statusMessage = __('Campaign draft saved.');
    }

    public function loadDraft(int $id): void
    {
        $draft = AIPromptHistory::query()
            ->where('owner_user_id', TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user()))
            ->where('module', 'ai_campaign_builder')
            ->findOrFail($id);

        $this->draftId = $draft->id;
        $this->plan = $draft->output_payload ?: null;
        $this->aiSource = (string) data_get($this->plan, 'source', 'draft');
        $this->goal = (string) data_get($draft->input_payload, 'goal', $this->goal);
        $this->business_id = (string) data_get($draft->input_payload, 'business_id', $this->business_id);
        $this->offer = (string) data_get($draft->input_payload, 'offer', '');
        $this->target_customer = (string) data_get($draft->input_payload, 'target_customer', '');
        $this->tone = (string) data_get($draft->input_payload, 'tone', 'friendly');
        $this->language = $this->normalizeLanguageCode((string) data_get($draft->input_payload, 'language', $this->language));
        $this->prompt = (string) $draft->prompt;
        $this->tab = 'builder';
        $this->statusMessage = __('Draft loaded.');
    }

    public function render(): View
    {
        $businesses = LocalBusiness::query()->where('user_id', auth()->id())->orderBy('name')->get();
        $drafts = $this->filteredDrafts();
        $draftCollection = $drafts->getCollection();
        $createdCampaigns = QrCampaign::query()
            ->whereIn('id', $draftCollection->pluck('metadata.created_campaign_id')->filter()->map(fn ($id) => (int) $id)->all())
            ->get()
            ->keyBy('id');

        return view('appaistudio::campaign-builder', [
            'businesses' => $businesses,
            'drafts' => $drafts,
            'createdCampaigns' => $createdCampaigns,
            'goalOptions' => $this->goalOptions(),
            'ideaFilters' => $this->ideaFilters(),
            'offerIdeas' => $this->filteredOfferIdeas(),
            'draftTypes' => $this->draftTypes(),
            'draftStatuses' => $this->draftStatuses(),
            'toneOptions' => $this->toneOptions(),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('AI Campaign Builder'),
        ]);
    }

    protected function createCampaignFromPlan(array $plan, int $businessId, ?int $draftId = null): QrCampaign
    {
        $business = LocalBusiness::query()
            ->where('user_id', auth()->id())
            ->findOrFail($businessId);

        $campaign = QrCampaign::query()->create([
            'user_id' => auth()->id(),
            'business_id' => $business->id,
            'slug' => $this->uniqueSlug((string) data_get($plan, 'campaign_name', 'ai-campaign')),
            'name' => (string) data_get($plan, 'campaign_name', 'AI Campaign'),
            'type' => (string) data_get($plan, 'campaign_type_key', 'lead'),
            'settings' => $plan,
            'published_at' => now(),
        ]);

        if ($draftId) {
            AIPromptHistory::query()->whereKey($draftId)->update([
                'metadata' => [
                    'status' => 'created',
                    'created_campaign_id' => $campaign->id,
                ],
            ]);
        }

        return $campaign;
    }

    protected function draftQuery()
    {
        return AIPromptHistory::query()
            ->where('owner_user_id', TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user()))
            ->where('module', 'ai_campaign_builder');
    }

    protected function filteredDrafts(): LengthAwarePaginator
    {
        $search = trim($this->draftSearch);
        $pageName = 'draftsPage';
        $page = $this->getPage($pageName);
        $perPage = max(5, min(50, $this->draftPerPage));

        $items = $this->draftQuery()
            ->latest()
            ->get()
            ->filter(function (AIPromptHistory $draft) use ($search): bool {
                $type = (string) data_get($draft->output_payload, 'campaign_type_key', data_get($draft->output_payload, 'campaign_type'));
                $status = (string) data_get($draft->metadata, 'status', 'draft');

                if ($this->draftType !== 'all' && $type !== $this->draftType) {
                    return false;
                }

                if ($this->draftStatus !== 'all' && $status !== $this->draftStatus) {
                    return false;
                }

                if ($search === '') {
                    return true;
                }

                return Str::of($draft->title.' '.$draft->prompt.' '.data_get($draft->output_payload, 'campaign_type'))->lower()->contains(Str::lower($search));
            })
            ->values();

        return new LengthAwarePaginator(
            items: $items->forPage($page, $perPage)->values(),
            total: $items->count(),
            perPage: $perPage,
            currentPage: $page,
            options: [
                'path' => request()->url(),
                'pageName' => $pageName,
            ],
        );
    }

    protected function draftTypes(): array
    {
        return [
            'all' => __('All campaign types'),
            'review' => __('Review Booster'),
            'booking' => __('Booking Page'),
            'coupon' => __('Coupon Campaign'),
            'feedback' => __('Feedback Form'),
            'lead' => __('Lead Form'),
        ];
    }

    protected function draftStatuses(): array
    {
        return [
            'all' => __('All statuses'),
            'draft' => __('Draft'),
            'created' => __('Created'),
        ];
    }

    protected function buildPlan(LocalBusiness $business, array $payload): array
    {
        $goal = (string) $payload['goal'];
        $offer = trim((string) ($payload['offer'] ?: $payload['prompt'] ?: 'Local growth campaign'));
        $type = $this->campaignTypeForGoal($goal);
        $goalLabel = data_get($this->goalOptions(), $goal.'.label', __('Capture leads'));
        $businessName = $business->name;
        $category = $business->type ?: __('Local business');
        $cta = $this->ctaForType($type);

        return [
            'campaign_type_key' => $type,
            'campaign_type' => $this->campaignTypeLabel($type),
            'campaign_name' => Str::of($offer)->headline()->limit(52, '')->trim()->append(' Campaign')->toString(),
            'goal' => $goalLabel,
            'business_name' => $businessName,
            'business_type' => $category,
            'headline' => $this->headlineForType($type, $offer),
            'subheadline' => __('A focused local campaign for :business designed to turn attention into measurable customer action.', ['business' => $businessName]),
            'description' => __('Promote :offer with a clear call-to-action, public campaign page, QR placement, and follow-up messages for local customers.', ['offer' => $offer]),
            'cta' => $cta,
            'benefits' => [
                __('Fast to launch for in-store and social traffic'),
                __('Built around one measurable local growth goal'),
                __('Includes page copy, QR poster text, and follow-up messages'),
            ],
            'terms' => __('Limited time offer. Contact the business for availability and full details.'),
            'qr_poster_text' => $this->posterTextForType($type, $cta),
            'social_caption' => __('New from :business: :offer. Tap or scan to :cta.', ['business' => $businessName, 'offer' => $offer, 'cta' => Str::lower($cta)]),
            'sms_message' => __('Hi! :business has a new offer: :offer. Tap to :cta.', ['business' => $businessName, 'offer' => $offer, 'cta' => Str::lower($cta)]),
            'email_subject' => __(':offer at :business', ['offer' => Str::headline($offer), 'business' => $businessName]),
            'email_body' => __('Hi, we created a quick campaign for :offer. Use the link to :cta and let us know if you have questions.', ['offer' => $offer, 'cta' => Str::lower($cta)]),
            'follow_up_message' => __('Thanks for your interest. We will follow up shortly with the next step.'),
            'tone' => $payload['tone'],
            'language' => $this->languageLabel($payload['language']),
            'target_customer' => $payload['target_customer'],
        ];
    }

    protected function saveDraft(LocalBusiness $business, array $payload, array $plan): int
    {
        $user = auth()->user();
        $team = TeamWorkspaceAccess::activeTeam($user);

        return (int) AIPromptHistory::query()->create([
            'owner_user_id' => TeamWorkspaceAccess::workspaceOwnerUserId($user),
            'requested_by_user_id' => $user->id,
            'team_id' => $team?->id,
            'module' => 'ai_campaign_builder',
            'title' => $plan['campaign_name'],
            'language' => $payload['language'],
            'tone' => $payload['tone'],
            'prompt' => (string) ($payload['prompt'] ?: $payload['offer']),
            'input_payload' => ['business_id' => $business->id, ...$payload],
            'output_payload' => $plan,
            'metadata' => ['status' => 'draft'],
        ])->id;
    }

    protected function shouldUseFallbackTitle(string $language, string $title): bool
    {
        $language = $this->normalizeLanguageCode($language);

        return $language === 'en' && preg_match('/[^\x00-\x7F]/', $title) === 1;
    }

    protected function goalOptions(): array
    {
        return [
            'reviews' => ['label' => __('Get more reviews'), 'type' => __('Review Booster'), 'icon' => 'fa-star'],
            'bookings' => ['label' => __('Get more bookings'), 'type' => __('Booking Page'), 'icon' => 'fa-calendar-check'],
            'coupon' => ['label' => __('Promote a coupon'), 'type' => __('Coupon Campaign'), 'icon' => 'fa-ticket'],
            'feedback' => ['label' => __('Collect feedback'), 'type' => __('Feedback Form'), 'icon' => 'fa-message-lines'],
            'leads' => ['label' => __('Capture leads'), 'type' => __('Lead Form'), 'icon' => 'fa-address-card'],
            'returning' => ['label' => __('Bring customers back'), 'type' => __('Coupon + Follow-up'), 'icon' => 'fa-rotate-left'],
        ];
    }

    protected function offerIdeas(): array
    {
        return [
            [
                'goal' => 'reviews',
                'label' => __('Ask happy customers for Google reviews'),
                'type' => __('Review Booster'),
                'description' => __('Send satisfied customers to public reviews and keep low-score feedback private.'),
                'target_customer' => __('Happy recent customers'),
                'best_for' => __('Restaurants, salons, spas, clinics'),
            ],
            [
                'goal' => 'reviews',
                'label' => __('Table-side review request'),
                'type' => __('Review Booster'),
                'description' => __('Invite dine-in customers to rate their visit before they leave the location.'),
                'target_customer' => __('Dine-in customers after their visit'),
                'best_for' => __('Restaurants, cafes, bars'),
            ],
            [
                'goal' => 'reviews',
                'label' => __('Post-appointment rating check'),
                'type' => __('Review Booster'),
                'description' => __('Collect ratings after appointments and route happy customers to public reviews.'),
                'target_customer' => __('Customers after completed appointments'),
                'best_for' => __('Clinics, dentists, salons, spas'),
            ],
            [
                'goal' => 'reviews',
                'label' => __('Receipt review follow-up'),
                'type' => __('Review Booster'),
                'description' => __('Add a short review request to receipts, invoices, and thank-you messages.'),
                'target_customer' => __('Recent buyers and service customers'),
                'best_for' => __('Stores, repair services, restaurants'),
            ],
            [
                'goal' => 'leads',
                'label' => __('Free consultation request for new customers'),
                'type' => __('Lead Form'),
                'description' => __('Capture new prospects with a simple consultation or quote request form.'),
                'target_customer' => __('New prospects requesting advice'),
                'best_for' => __('Clinics, agencies, professional services'),
            ],
            [
                'goal' => 'leads',
                'label' => __('Get a fast quote request'),
                'type' => __('Lead Form'),
                'description' => __('Collect name, phone, budget, and service needs from high-intent prospects.'),
                'target_customer' => __('High-intent prospects comparing services'),
                'best_for' => __('Home services, agencies, repair services'),
            ],
            [
                'goal' => 'leads',
                'label' => __('Waitlist signup campaign'),
                'type' => __('Lead Form'),
                'description' => __('Build a list for new services, limited openings, or upcoming local launches.'),
                'target_customer' => __('People waiting for openings or launches'),
                'best_for' => __('Gyms, clinics, classes, events'),
            ],
            [
                'goal' => 'leads',
                'label' => __('Free trial lead capture'),
                'type' => __('Lead Form'),
                'description' => __('Offer a free trial or sample session and collect customer contact details.'),
                'target_customer' => __('First-time trial customers'),
                'best_for' => __('Gyms, studios, coaching, classes'),
            ],
            [
                'goal' => 'coupon',
                'label' => __('20% off next visit this weekend'),
                'type' => __('Coupon'),
                'description' => __('Create a limited-time offer to drive return visits and track coupon claims.'),
                'target_customer' => __('Returning customers and weekend shoppers'),
                'best_for' => __('Spa, salon, coffee shop, local store'),
            ],
            [
                'goal' => 'coupon',
                'label' => __('Buy one get one local offer'),
                'type' => __('Coupon'),
                'description' => __('Create a simple BOGO campaign to increase foot traffic during slow hours.'),
                'target_customer' => __('Local customers visiting with friends'),
                'best_for' => __('Coffee shops, restaurants, retail'),
            ],
            [
                'goal' => 'coupon',
                'label' => __('Free add-on with purchase'),
                'type' => __('Coupon'),
                'description' => __('Promote an add-on gift or upgrade without discounting the core service.'),
                'target_customer' => __('Customers considering an upgrade'),
                'best_for' => __('Salons, spas, clinics, stores'),
            ],
            [
                'goal' => 'coupon',
                'label' => __('Slow-day flash offer'),
                'type' => __('Coupon'),
                'description' => __('Fill quiet time slots with a short campaign that expires quickly.'),
                'target_customer' => __('Nearby customers available on slow days'),
                'best_for' => __('Restaurants, salons, gyms, spas'),
            ],
            [
                'goal' => 'feedback',
                'label' => __('Post-visit private feedback form'),
                'type' => __('Feedback'),
                'description' => __('Collect private customer feedback after a visit before issues become public.'),
                'target_customer' => __('Customers after a recent visit'),
                'best_for' => __('Any service business'),
            ],
            [
                'goal' => 'feedback',
                'label' => __('Service quality check-in'),
                'type' => __('Feedback'),
                'description' => __('Ask customers about staff, wait time, cleanliness, and overall satisfaction.'),
                'target_customer' => __('Customers who just completed a service'),
                'best_for' => __('Clinics, salons, gyms, restaurants'),
            ],
            [
                'goal' => 'feedback',
                'label' => __('New service feedback survey'),
                'type' => __('Feedback'),
                'description' => __('Collect early reactions after launching a new treatment, menu item, or offer.'),
                'target_customer' => __('Customers trying the new service'),
                'best_for' => __('Spas, restaurants, stores, studios'),
            ],
            [
                'goal' => 'feedback',
                'label' => __('Complaint recovery form'),
                'type' => __('Feedback'),
                'description' => __('Give unhappy customers a private channel to explain what went wrong.'),
                'target_customer' => __('Customers who had a poor experience'),
                'best_for' => __('Restaurants, hotels, clinics, service teams'),
            ],
            [
                'goal' => 'bookings',
                'label' => __('Limited weekend appointment slots'),
                'type' => __('Booking'),
                'description' => __('Promote available time slots and turn interest into appointment requests.'),
                'target_customer' => __('Customers looking for weekend appointments'),
                'best_for' => __('Spa, salon, gym, clinic'),
            ],
            [
                'goal' => 'bookings',
                'label' => __('Free first consultation booking'),
                'type' => __('Booking'),
                'description' => __('Let prospects book a consultation from social posts, flyers, or local ads.'),
                'target_customer' => __('New prospects ready to book a consultation'),
                'best_for' => __('Clinics, dentists, agencies, coaches'),
            ],
            [
                'goal' => 'bookings',
                'label' => __('Same-week availability campaign'),
                'type' => __('Booking'),
                'description' => __('Promote open slots this week and reduce unused appointment capacity.'),
                'target_customer' => __('Customers who can book this week'),
                'best_for' => __('Salons, spas, fitness, clinics'),
            ],
            [
                'goal' => 'bookings',
                'label' => __('Trial session booking page'),
                'type' => __('Booking'),
                'description' => __('Create a focused campaign for trial classes, gym sessions, or intro calls.'),
                'target_customer' => __('First-time visitors interested in a trial'),
                'best_for' => __('Gyms, studios, coaches, consultants'),
            ],
            [
                'goal' => 'returning',
                'label' => __('Come back offer for previous customers'),
                'type' => __('Retention'),
                'description' => __('Win back past customers with a follow-up offer and campaign message.'),
                'target_customer' => __('Previous customers who have not returned recently'),
                'best_for' => __('Restaurants, stores, beauty services'),
            ],
            [
                'goal' => 'returning',
                'label' => __('Birthday month comeback offer'),
                'type' => __('Retention'),
                'description' => __('Invite customers back with a personal birthday-month promotion.'),
                'target_customer' => __('Customers with upcoming birthday months'),
                'best_for' => __('Restaurants, salons, spas, retail'),
            ],
            [
                'goal' => 'returning',
                'label' => __('VIP customer appreciation campaign'),
                'type' => __('Retention'),
                'description' => __('Reward repeat customers with a private offer and follow-up message.'),
                'target_customer' => __('Loyal repeat customers'),
                'best_for' => __('Stores, restaurants, salons, gyms'),
            ],
            [
                'goal' => 'returning',
                'label' => __('Refer a friend reward'),
                'type' => __('Retention'),
                'description' => __('Bring existing customers back while encouraging them to invite a friend.'),
                'target_customer' => __('Existing customers likely to refer friends'),
                'best_for' => __('Gyms, salons, clinics, studios'),
            ],
            [
                'goal' => 'coupon',
                'label' => __('First-time customer welcome offer'),
                'type' => __('Coupon'),
                'description' => __('Give new customers a reason to try the business for the first time.'),
                'target_customer' => __('First-time local customers'),
                'best_for' => __('Local stores, gyms, salons'),
            ],
            [
                'goal' => 'returning',
                'label' => __('Win back inactive customers'),
                'type' => __('Retention'),
                'description' => __('Create a friendly return campaign for customers who have not visited recently.'),
                'target_customer' => __('Inactive customers from the past 60-90 days'),
                'best_for' => __('Restaurants, spa, clinic, retail'),
            ],
            [
                'goal' => 'reviews',
                'label' => __('After-service review request'),
                'type' => __('Review Booster'),
                'description' => __('Ask customers for a rating right after their service while the visit is fresh.'),
                'target_customer' => __('Customers immediately after service completion'),
                'best_for' => __('Dentists, clinics, salons, repair services'),
            ],
            [
                'goal' => 'coupon',
                'label' => __('Holiday promotion campaign'),
                'type' => __('Coupon'),
                'description' => __('Launch a seasonal offer with social copy, claim page text, and follow-up messages.'),
                'target_customer' => __('Holiday shoppers and seasonal buyers'),
                'best_for' => __('Retail, restaurants, coffee shops'),
            ],
        ];
    }

    protected function filteredOfferIdeas(): array
    {
        if ($this->ideaFilter === 'all') {
            return $this->offerIdeas();
        }

        return array_values(array_filter(
            $this->offerIdeas(),
            fn (array $idea): bool => $idea['goal'] === $this->ideaFilter
        ));
    }

    protected function ideaFilters(): array
    {
        return [
            'all' => __('All'),
            'reviews' => __('Reviews'),
            'bookings' => __('Bookings'),
            'coupon' => __('Coupons'),
            'feedback' => __('Feedback'),
            'leads' => __('Leads'),
            'returning' => __('Retention'),
        ];
    }

    protected function resolveAiDefaults(): array
    {
        $user = auth()->user();
        $team = TeamWorkspaceAccess::activeTeam($user);
        $ownerUserId = TeamWorkspaceAccess::workspaceOwnerUserId($user);
        $options = app(OptionStore::class);

        $adminLanguage = (string) $options->get('ai_default_language', $user?->locale ?: app()->getLocale() ?: 'en');
        $adminTone = (string) $options->get('ai_default_tone_of_voice', 'friendly');

        $workspaceSettings = (array) (AIStudioWorkspaceSetting::query()
            ->ownedBy($ownerUserId)
            ->forTeam($team?->id)
            ->value('settings') ?? []);

        $userSettings = (array) (AIStudioUserSetting::query()
            ->forUser((int) $user->id)
            ->value('settings') ?? []);

        $language = (string) ($userSettings['default_language']
            ?? $workspaceSettings['default_language']
            ?? $adminLanguage
            ?? 'en');
        $tone = (string) ($userSettings['default_tone']
            ?? $workspaceSettings['default_tone']
            ?? $adminTone
            ?? 'friendly');

        return [
            'language' => $this->normalizeLanguageCode($language),
            'tone' => $this->normalizeTone($tone),
        ];
    }

    protected function toneOptions(): array
    {
        return [
            'professional' => __('Professional'),
            'friendly' => __('Friendly'),
            'sales' => __('Sales'),
            'educational' => __('Educational'),
            'bold' => __('Bold'),
            'casual' => __('Casual'),
            'luxury' => __('Luxury'),
            'fun' => __('Fun'),
        ];
    }

    protected function normalizeLanguageCode(string $language): string
    {
        $language = strtolower(trim($language));

        if ($language === '') {
            return 'en';
        }

        if (collect(world_languages())->contains(fn ($item) => strtolower((string) data_get($item, 'code')) === $language)) {
            return $language;
        }

        $matched = collect(world_languages())->first(fn ($item) => strtolower((string) data_get($item, 'name')) === $language);

        return (string) data_get($matched, 'code', 'en');
    }

    protected function languageLabel(string $language): string
    {
        $code = $this->normalizeLanguageCode($language);

        return (string) (collect(world_languages())->firstWhere('code', $code)['name'] ?? strtoupper($code));
    }

    protected function normalizeTone(string $tone): string
    {
        $tone = strtolower(trim($tone));

        return $tone !== '' ? $tone : 'friendly';
    }

    protected function campaignTypeForGoal(string $goal): string
    {
        return match ($goal) {
            'reviews' => 'review',
            'bookings' => 'booking',
            'coupon', 'returning' => 'coupon',
            'feedback' => 'feedback',
            default => 'lead',
        };
    }

    protected function campaignTypeLabel(string $type): string
    {
        return match ($type) {
            'review' => __('Review Booster'),
            'booking' => __('Booking Page'),
            'coupon' => __('Coupon Campaign'),
            'feedback' => __('Feedback Form'),
            default => __('Lead Form'),
        };
    }

    protected function ctaForType(string $type): string
    {
        return match ($type) {
            'review' => __('Leave a Review'),
            'booking' => __('Book Now'),
            'coupon' => __('Claim Offer'),
            'feedback' => __('Send Feedback'),
            default => __('Request Info'),
        };
    }

    protected function headlineForType(string $type, string $offer): string
    {
        return match ($type) {
            'review' => __('How was your experience?'),
            'feedback' => __('Tell us how we can improve'),
            default => Str::headline($offer),
        };
    }

    protected function posterTextForType(string $type, string $cta): string
    {
        return match ($type) {
            'review' => __('Scan to rate your experience'),
            'feedback' => __('Scan to share private feedback'),
            default => __('Scan to :cta', ['cta' => Str::lower($cta)]),
        };
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'ai-campaign';
        $slug = $base;
        $counter = 2;

        while (QrCampaign::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
