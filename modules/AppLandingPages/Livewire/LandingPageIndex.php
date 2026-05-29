<?php

namespace Modules\AppLandingPages\Livewire;

use App\Support\Plans\PlanLimitGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppLandingPages\Models\LandingPage;
use Modules\AppLandingPages\Support\PageTemplateCatalog;
use Modules\AppQRCampaigns\Models\QrCampaign;

#[Title('Landing Pages')]
class LandingPageIndex extends Component
{
    use WithPagination;

    public string $business_id = '';
    public string $campaign_id = '';
    public string $type = 'lead';
    public string $template = 'local_campaign';
    public string $status = 'published';
    public string $title = '';
    public string $headline = '';
    public string $subheadline = '';
    public string $description = '';
    public string $cta_text = 'Submit';
    public string $benefits = '';
    public string $thank_you_message = 'Thank you. We have received your request.';
    public string $review_url = '';
    public string $coupon_title = '';
    public string $discount = '';
    public string $expiry = '';
    public string $terms = '';
    public string $service = '';
    public string $duration = '';
    public string $price = '';
    public string $available_slots = '';
    public string $primary_color = '#0f766e';
    public string $background_color = '#f4fbf8';
    public string $background_type = 'gradient';
    public string $font_style = 'modern';
    public string $button_style = 'pill';
    public string $card_style = 'soft';
    public string $logo_url = '';
    public string $logo_shape = 'circle';
    public string $cover_image = '';
    public bool $show_logo = true;
    public bool $show_business_info = true;
    public bool $show_social_links = true;
    public bool $show_benefits = true;
    public bool $show_terms = true;
    public bool $show_faq = true;
    public array $landing_blocks = [];
    public array $builderNewBlock = [
        'type' => 'benefits',
        'title' => '',
        'headline' => '',
        'body' => '',
        'cta' => '',
        'visible' => true,
    ];
    public string $businessFilter = 'all';
    public string $typeFilter = 'all';
    public string $statusFilter = 'all';
    public int $perPage = 10;
    public string $public_url = '';
    public string $slug = '';
    public string $return_url = '';
    public ?int $editingId = null;
    public ?string $statusMessage = null;

    public function mount(): void
    {
        $this->return_url = $this->safeReturnUrl(request('return'));

        if (request()->filled('edit')) {
            $this->edit((int) request('edit'));

            return;
        }

        $this->applyTypeDefaults();
        $this->landing_blocks = $this->defaultLandingBlocks($this->type);
    }

    public function updatedType(): void
    {
        $this->applyTypeDefaults();
        $this->landing_blocks = [];
    }

    public function updatedTemplate(): void
    {
        $this->applyTemplateDesign($this->template, false);
    }

    public function updatedBusinessFilter(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
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

    public function selectTemplate(string $template): void
    {
        $preset = PageTemplateCatalog::all()[$template] ?? null;

        if (! $preset) {
            return;
        }

        $presetType = (string) ($preset['type'] ?? $this->type);

        if ($presetType !== $this->type && array_key_exists($presetType, $this->pageTypes())) {
            $this->type = $presetType;
            $this->applyTypeDefaults();
            $this->landing_blocks = [];
        }

        $this->template = $template;
        $this->applyTemplateDesign($template, false);
    }

    public function create(): void
    {
        $this->resetForm();
        $this->dispatch('landing-page-editor-ready');
    }

    public function selectPageType(string $type): void
    {
        abort_unless(array_key_exists($type, $this->pageTypes()), 422);

        $this->type = $type;
        $this->applyTypeDefaults();
        $this->landing_blocks = [];
    }

    public function edit(int $id): void
    {
        $page = LandingPage::query()->where('user_id', auth()->id())->findOrFail($id);
        $content = $page->content ?: [];
        $settings = $page->settings ?: [];

        $this->editingId = $page->id;
        $this->business_id = (string) $page->business_id;
        $this->campaign_id = (string) $page->campaign_id;
        $this->type = (string) $page->type;
        $this->template = array_key_exists((string) $page->template, PageTemplateCatalog::all())
            ? (string) $page->template
            : PageTemplateCatalog::defaultForType((string) $page->type);
        $this->status = (string) $page->status;
        $this->slug = (string) $page->slug;
        $this->public_url = $page->publicUrl();
        $this->title = (string) $page->title;
        $this->headline = (string) data_get($content, 'headline', '');
        $this->subheadline = (string) data_get($content, 'subheadline', '');
        $this->description = (string) data_get($content, 'description', '');
        $this->cta_text = (string) data_get($content, 'cta', 'Submit');
        $this->benefits = implode("\n", (array) data_get($content, 'benefits', []));
        $this->thank_you_message = (string) data_get($content, 'thank_you_message', '');
        $this->review_url = (string) data_get($settings, 'review_url', '');
        $this->coupon_title = (string) data_get($settings, 'coupon_title', '');
        $this->discount = (string) data_get($settings, 'discount', '');
        $this->expiry = (string) data_get($settings, 'expiry', '');
        $this->terms = (string) data_get($settings, 'terms', '');
        $this->service = (string) data_get($settings, 'service', '');
        $this->duration = (string) data_get($settings, 'duration', '');
        $this->price = (string) data_get($settings, 'price', '');
        $this->available_slots = implode("\n", (array) data_get($settings, 'available_slots', []));
        $design = array_merge(PageTemplateCatalog::designFor($this->template), (array) data_get($settings, 'design', []));
        $this->primary_color = (string) data_get($design, 'primary_color', '#0f766e');
        $this->background_color = (string) data_get($design, 'background_color', '#f4fbf8');
        $this->background_type = (string) data_get($design, 'background_type', 'gradient');
        $this->font_style = (string) data_get($design, 'font_style', 'modern');
        $this->button_style = (string) data_get($design, 'button_style', 'pill');
        $this->card_style = (string) data_get($design, 'card_style', 'soft');
        $this->logo_url = (string) data_get($design, 'logo_url', '');
        $this->logo_shape = (string) data_get($design, 'logo_shape', 'circle');
        $this->cover_image = (string) data_get($design, 'cover_image', '');
        $this->show_logo = (bool) data_get($design, 'show_logo', true);
        $this->show_business_info = (bool) data_get($design, 'show_business_info', true);
        $this->show_social_links = (bool) data_get($design, 'show_social_links', true);
        $this->show_benefits = (bool) data_get($design, 'show_benefits', true);
        $this->show_terms = (bool) data_get($design, 'show_terms', true);
        $this->show_faq = (bool) data_get($design, 'show_faq', true);
        $this->landing_blocks = $this->normalizeLandingBlocks((array) data_get($content, 'landing_page_blocks', data_get($settings, 'blocks', [])));
        $this->repairStaleReviewContent();
        $this->resetValidation();
        $this->dispatch('landing-page-editor-ready');
    }

    public function addLandingBlock(): void
    {
        $type = (string) ($this->builderNewBlock['type'] ?: 'hero');
        $title = trim((string) ($this->builderNewBlock['title'] ?: $this->blockTypeOptions()[$type] ?? str($type)->replace('_', ' ')->headline()));

        $this->landing_blocks[] = [
            'id' => 'block_'.Str::random(10),
            'type' => $type,
            'title' => $title,
            'visible' => (bool) ($this->builderNewBlock['visible'] ?? true),
            'settings' => $this->normalizeBlockSettings($type, $this->builderNewBlock, $title),
        ];

        $this->resetBuilderNewBlock();
    }

    public function removeLandingBlock(int $index): void
    {
        unset($this->landing_blocks[$index]);
        $this->landing_blocks = array_values($this->landing_blocks);
    }

    public function duplicateLandingBlock(int $index): void
    {
        $blocks = array_values($this->landing_blocks);

        if (! array_key_exists($index, $blocks)) {
            return;
        }

        $copy = $blocks[$index];
        $copy['id'] = 'block_'.Str::random(10);
        $copy['title'] = (string) ($copy['title'] ?? $copy['type'] ?? 'Block').' '.__('copy');
        array_splice($blocks, $index + 1, 0, [$copy]);
        $this->landing_blocks = $blocks;
    }

    public function toggleLandingBlockVisibility(int $index): void
    {
        if (! isset($this->landing_blocks[$index])) {
            return;
        }

        $this->landing_blocks[$index]['visible'] = ! (bool) ($this->landing_blocks[$index]['visible'] ?? true);
    }

    public function moveLandingBlock(int $index, int $direction): void
    {
        $this->landing_blocks = $this->moveArrayItem($this->landing_blocks, $index, $direction);
    }

    public function movePublicLandingBlock(int $index, int $direction): void
    {
        $blocks = array_values($this->landing_blocks);
        $publicIndexes = collect($blocks)
            ->keys()
            ->reject(fn (int $blockIndex) => in_array((string) data_get($blocks, $blockIndex.'.type', 'hero'), ['hero', 'form'], true))
            ->values()
            ->all();
        $position = array_search($index, $publicIndexes, true);

        if ($position === false) {
            return;
        }

        $targetPosition = $position + $direction;

        if (! array_key_exists($targetPosition, $publicIndexes)) {
            return;
        }

        $targetIndex = $publicIndexes[$targetPosition];
        [$blocks[$index], $blocks[$targetIndex]] = [$blocks[$targetIndex], $blocks[$index]];

        $this->landing_blocks = $blocks;
    }

    public function reorderLandingBlocks(array $blockIds): void
    {
        $blocks = collect($this->landing_blocks)
            ->map(fn (array $block) => array_merge(['id' => 'block_'.Str::random(10)], $block));

        $ordered = collect($blockIds)
            ->map(fn ($id) => (string) $id)
            ->filter()
            ->unique()
            ->map(fn (string $id) => $blocks->firstWhere('id', $id))
            ->filter()
            ->values();

        $remaining = $blocks
            ->reject(fn (array $block) => $ordered->contains(fn (array $orderedBlock) => $orderedBlock['id'] === $block['id']))
            ->values();

        $this->landing_blocks = $ordered->merge($remaining)->values()->all();
    }

    public function save(): void
    {
        $payload = $this->validate([
            'business_id' => ['required', 'integer'],
            'campaign_id' => ['nullable', 'integer'],
            'type' => ['required', 'string', 'in:review,booking,coupon,feedback,lead,promotion,custom'],
            'template' => ['required', 'string', 'in:'.implode(',', array_keys(PageTemplateCatalog::all()))],
            'status' => ['required', 'string', 'in:draft,published'],
            'title' => ['required', 'string', 'max:255'],
            'headline' => ['required', 'string', 'max:255'],
            'subheadline' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string', 'max:2000'],
            'cta_text' => ['required', 'string', 'max:80'],
            'benefits' => ['nullable', 'string', 'max:1500'],
            'thank_you_message' => ['required', 'string', 'max:1000'],
            'review_url' => ['nullable', 'url', 'max:1000'],
            'coupon_title' => ['nullable', 'string', 'max:255'],
            'discount' => ['nullable', 'string', 'max:120'],
            'expiry' => ['nullable', 'string', 'max:120'],
            'terms' => ['nullable', 'string', 'max:1000'],
            'service' => ['nullable', 'string', 'max:255'],
            'duration' => ['nullable', 'string', 'max:80'],
            'price' => ['nullable', 'string', 'max:80'],
            'available_slots' => ['nullable', 'string', 'max:1500'],
            'primary_color' => ['required', 'string', 'max:20'],
            'background_color' => ['required', 'string', 'max:20'],
            'background_type' => ['required', 'string', 'in:solid,gradient,image'],
            'font_style' => ['required', 'string', 'in:modern,classic,elegant,friendly'],
            'button_style' => ['required', 'string', 'in:rounded,pill,square'],
            'card_style' => ['required', 'string', 'in:soft,bordered,flat'],
            'logo_url' => ['nullable', 'url', 'max:1000'],
            'logo_shape' => ['required', 'string', 'in:circle,square'],
            'cover_image' => ['nullable', 'url', 'max:1000'],
            'show_logo' => ['boolean'],
            'show_business_info' => ['boolean'],
            'show_social_links' => ['boolean'],
            'show_benefits' => ['boolean'],
            'show_terms' => ['boolean'],
            'show_faq' => ['boolean'],
            'landing_blocks' => ['array'],
        ]);

        LocalBusiness::query()->where('user_id', auth()->id())->findOrFail((int) $payload['business_id']);

        if ($payload['campaign_id']) {
            QrCampaign::query()->where('user_id', auth()->id())->findOrFail((int) $payload['campaign_id']);
        }

        $campaignId = $payload['campaign_id'] ? (int) $payload['campaign_id'] : null;
        $guard = app(PlanLimitGuard::class);

        if (! $this->editingId) {
            $guard->ensureLandingPageCanBeCreated(auth()->user());
        }

        if (! $campaignId && ! $this->editingId) {
            $guard->ensureCampaignCanBeCreated(auth()->user());
            $campaignId = $this->createBackingCampaign($payload);
        }

        $data = [
            'user_id' => auth()->id(),
            'business_id' => (int) $payload['business_id'],
            'campaign_id' => $campaignId,
            'type' => $payload['type'],
            'template' => $payload['template'],
            'title' => $payload['title'],
            'status' => $payload['status'],
            'content' => [
                'headline' => $payload['headline'],
                'subheadline' => $payload['subheadline'],
                'description' => $payload['description'],
                'cta' => $payload['cta_text'],
                'benefits' => $this->lines($payload['benefits']),
                'thank_you_message' => $payload['thank_you_message'],
                'landing_page_blocks' => [],
            ],
            'settings' => [
                'review_url' => $payload['review_url'],
                'coupon_title' => $payload['coupon_title'],
                'discount' => $payload['discount'],
                'expiry' => $payload['expiry'],
                'terms' => $payload['terms'],
                'service' => $payload['service'],
                'duration' => $payload['duration'],
                'price' => $payload['price'],
                'available_slots' => $this->lines($payload['available_slots']),
                'design' => [
                    'template' => $payload['template'],
                    'primary_color' => $payload['primary_color'],
                    'background_color' => $payload['background_color'],
                    'background_type' => $payload['background_type'],
                    'font_style' => $payload['font_style'],
                    'button_style' => $payload['button_style'],
                    'card_style' => $payload['card_style'],
                    'logo_url' => $payload['logo_url'],
                    'logo_shape' => $payload['logo_shape'],
                    'cover_image' => $payload['cover_image'],
                    'show_logo' => (bool) $payload['show_logo'],
                    'show_business_info' => (bool) $payload['show_business_info'],
                    'show_social_links' => (bool) $payload['show_social_links'],
                    'show_benefits' => (bool) $payload['show_benefits'],
                    'show_terms' => (bool) $payload['show_terms'],
                    'show_faq' => (bool) $payload['show_faq'],
                ],
                'blocks' => [],
            ],
            'published_at' => $payload['status'] === 'published' ? now() : null,
        ];

        if ($this->editingId) {
            LandingPage::query()
                ->where('user_id', auth()->id())
                ->whereKey($this->editingId)
                ->update($data);

            $this->statusMessage = __('Landing page updated.');
        } else {
            LandingPage::query()->create([
                ...$data,
                'slug' => $this->uniqueSlug($payload['title']),
            ]);

            $this->statusMessage = __('Landing page created.');
        }

        $returnUrl = $this->return_url;
        $this->resetForm();
        $this->resetPage();
        $this->dispatch('landing-page-saved');

        if ($returnUrl !== '') {
            $this->redirect($returnUrl, navigate: true);
        }
    }

    public function duplicate(int $id): void
    {
        $page = LandingPage::query()->where('user_id', auth()->id())->findOrFail($id);
        app(PlanLimitGuard::class)->ensureLandingPageCanBeCreated(auth()->user());

        LandingPage::query()->create([
            'user_id' => auth()->id(),
            'business_id' => $page->business_id,
            'campaign_id' => $page->campaign_id,
            'slug' => $this->uniqueSlug($page->title.' copy'),
            'title' => $page->title.' copy',
            'type' => $page->type,
            'template' => $page->template,
            'content' => $page->content,
            'settings' => $page->settings,
            'status' => 'draft',
        ]);

        $this->statusMessage = __('Landing page duplicated as draft.');
        $this->resetPage();
    }

    public function toggleStatus(int $id): void
    {
        $page = LandingPage::query()->where('user_id', auth()->id())->findOrFail($id);
        $published = $page->status !== 'published';

        $page->forceFill([
            'status' => $published ? 'published' : 'draft',
            'published_at' => $published ? now() : null,
        ])->save();

        $this->statusMessage = $published ? __('Landing page published.') : __('Landing page moved to draft.');
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        LandingPage::query()->where('user_id', auth()->id())->whereKey($id)->delete();

        $this->statusMessage = __('Landing page deleted.');
        $this->resetPage();
    }

    public function render(): View
    {
        $businesses = LocalBusiness::query()->where('user_id', auth()->id())->orderBy('name')->get();

        if ($this->business_id === '' && $businesses->isNotEmpty()) {
            $this->business_id = (string) $businesses->first()->id;
        }

        $campaigns = QrCampaign::query()
            ->with('business')
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->get();

        $query = LandingPage::query()
            ->with('business', 'campaign')
            ->where('user_id', auth()->id())
            ->when($this->businessFilter !== 'all', fn ($builder) => $builder->where('business_id', (int) $this->businessFilter))
            ->when($this->typeFilter !== 'all', fn ($builder) => $builder->where('type', $this->typeFilter))
            ->when($this->statusFilter !== 'all', fn ($builder) => $builder->where('status', $this->statusFilter))
            ->latest();

        $allPages = LandingPage::query()->where('user_id', auth()->id())->get();
        $visits = (int) $allPages->sum('visits_count');
        $conversions = (int) $allPages->sum('conversions_count');

        return view('applandingpages::index', [
            'businesses' => $businesses,
            'campaigns' => $campaigns,
            'pages' => $query->paginate($this->perPage),
            'pageTypes' => $this->pageTypes(),
            'blockTypeOptions' => $this->blockTypeOptions(),
            'templates' => $this->templates(),
            'templateCatalog' => PageTemplateCatalog::all(),
            'templateOptionsForType' => PageTemplateCatalog::forType($this->type),
            'stats' => [
                'total' => $allPages->count(),
                'published' => $allPages->where('status', 'published')->count(),
                'draft' => $allPages->where('status', 'draft')->count(),
                'visits' => $visits,
                'conversions' => $conversions,
                'conversion_rate' => $visits > 0 ? round(($conversions / $visits) * 100) : 0,
            ],
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('Landing Pages')]);
    }

    protected function pageTypes(): array
    {
        return [
            'lead' => __('Lead Capture'),
            'promotion' => __('Promotion'),
            'booking' => __('Booking'),
            'coupon' => __('Coupon'),
            'feedback' => __('Feedback'),
            'review' => __('Review'),
            'custom' => __('Custom Campaign'),
        ];
    }

    protected function templates(): array
    {
        return PageTemplateCatalog::options();
    }

    protected function blockTypeOptions(): array
    {
        return [
            'hero' => __('Hero'),
            'benefits' => __('Benefits'),
            'form' => __('Form'),
            'offer' => __('Offer'),
            'coupon_details' => __('Coupon Details'),
            'booking_services' => __('Booking Services'),
            'business_info' => __('Business Info'),
            'social_links' => __('Social Links'),
            'faq' => __('FAQ'),
            'testimonials' => __('Testimonials'),
            'map' => __('Map'),
            'opening_hours' => __('Opening Hours'),
            'thank_you' => __('Thank You'),
            'custom_html' => __('Custom HTML'),
        ];
    }

    protected function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'landing-page';
        $slug = $base;
        $counter = 2;

        while (LandingPage::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    private function applyTypeDefaults(bool $replaceContent = true): void
    {
        $defaults = $this->typeDefaults($this->type);

        if ($replaceContent || $this->title === '') {
            $this->title = $defaults['title'];
            $this->headline = $defaults['headline'];
            $this->subheadline = $defaults['subheadline'];
            $this->cta_text = $defaults['cta'];
            $this->benefits = $defaults['benefits'];
            $this->description = '';
            $this->thank_you_message = 'Thank you. We have received your request.';
            $this->review_url = '';
            $this->coupon_title = (string) ($defaults['coupon_title'] ?? '');
            $this->discount = (string) ($defaults['discount'] ?? '');
            $this->expiry = in_array($this->type, ['coupon', 'promotion'], true) ? now()->addDays(14)->toDateString() : '';
            $this->terms = in_array($this->type, ['coupon', 'promotion'], true) ? 'Valid for one customer. Cannot be combined with other offers.' : '';
            $this->service = $this->type === 'booking' ? 'Appointment' : '';
            $this->duration = $this->type === 'booking' ? '60 minutes' : '';
            $this->price = $this->type === 'booking' ? 'Ask us' : '';
            $this->available_slots = $this->type === 'booking' ? "09:00\n10:00\n14:00\n15:00" : '';
        }

        $validTemplates = PageTemplateCatalog::forType($this->type);

        if (! array_key_exists($this->template, $validTemplates)) {
            $this->template = PageTemplateCatalog::defaultForType($this->type);
            $this->applyTemplateDesign($this->template);
        }
    }

    private function repairStaleReviewContent(): void
    {
        if ($this->type === 'review') {
            return;
        }

        $reviewDefaults = $this->typeDefaults('review');
        $looksLikeReviewCopy = trim($this->headline) === $reviewDefaults['headline']
            || trim($this->subheadline) === $reviewDefaults['subheadline']
            || trim($this->title) === $reviewDefaults['title'];

        if (! $looksLikeReviewCopy) {
            return;
        }

        $defaults = $this->typeDefaults($this->type);
        $this->title = $defaults['title'];
        $this->headline = $defaults['headline'];
        $this->subheadline = $defaults['subheadline'];
        $this->cta_text = $defaults['cta'];
        $this->benefits = $defaults['benefits'];
    }

    private function typeDefaults(string $type): array
    {
        return match ($type) {
            'review' => ['title' => 'Google review request page', 'headline' => 'How was your visit?', 'subheadline' => 'Your rating helps us improve and helps other local customers choose us.', 'cta' => 'Continue', 'benefits' => "Fast response\nFriendly local team\nSimple next step"],
            'booking' => ['title' => 'Book your appointment', 'headline' => 'Book a time that works for you', 'subheadline' => 'Choose a service, pick a slot, and we will confirm your appointment.', 'cta' => 'Request booking', 'benefits' => "Choose a service\nPick an available slot\nGet confirmation from the team"],
            'coupon' => ['title' => 'Claim your local offer', 'headline' => 'Get 20% off your next visit', 'subheadline' => 'Claim this limited-time offer and show your code in-store.', 'cta' => 'Claim coupon', 'benefits' => "Fast response\nFriendly local team\nSimple next step", 'coupon_title' => 'LOCAL20', 'discount' => '20% off'],
            'feedback' => ['title' => 'Share your feedback', 'headline' => 'Tell us how we did', 'subheadline' => 'Your private feedback helps our team improve the next visit.', 'cta' => 'Send feedback', 'benefits' => "Private feedback\nTeam follow-up\nQuick response"],
            'promotion' => ['title' => 'Local promotion page', 'headline' => 'A special offer for local customers', 'subheadline' => 'Leave your details and our team will help you claim it.', 'cta' => 'Get offer', 'benefits' => "Limited-time offer\nEasy claim\nLocal team follow-up", 'coupon_title' => 'LOCAL20', 'discount' => 'Special offer'],
            'custom' => ['title' => 'Custom campaign page', 'headline' => 'A local campaign built for your audience', 'subheadline' => 'Share the offer, collect interest, and track results.', 'cta' => 'Send request', 'benefits' => "Flexible campaign\nSimple next step\nTracked responses"],
            default => ['title' => 'Lead capture page', 'headline' => 'Request a free consultation', 'subheadline' => 'Tell us what you need and our local team will follow up.', 'cta' => 'Send request', 'benefits' => "Fast response\nFriendly local team\nSimple next step"],
        };
    }

    private function createBackingCampaign(array $payload): int
    {
        $campaignType = match ($payload['type']) {
            'promotion' => 'coupon',
            'custom' => 'lead',
            default => $payload['type'],
        };

        $campaign = QrCampaign::query()->create([
            'user_id' => auth()->id(),
            'business_id' => (int) $payload['business_id'],
            'slug' => $this->uniqueCampaignSlug($payload['title']),
            'name' => $payload['title'],
            'type' => $campaignType,
            'settings' => [
                'source' => 'landing_page',
                'headline' => $payload['headline'],
                'goal' => $payload['type'],
                'template' => $payload['template'],
            ],
            'published_at' => $payload['status'] === 'published' ? now() : null,
        ]);

        return (int) $campaign->id;
    }

    private function uniqueCampaignSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'landing-page';
        $slug = $base;
        $counter = 2;

        while (QrCampaign::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->campaign_id = '';
        $this->type = 'lead';
        $this->template = PageTemplateCatalog::defaultForType('lead');
        $this->status = 'published';
        $this->slug = '';
        $this->public_url = '';
        $this->return_url = '';
        $this->description = '';
        $this->benefits = "Fast response\nFriendly local team\nSimple next step";
        $this->thank_you_message = 'Thank you. We have received your request.';
        $this->review_url = '';
        $this->coupon_title = '';
        $this->discount = '';
        $this->expiry = '';
        $this->terms = '';
        $this->service = '';
        $this->duration = '';
        $this->price = '';
        $this->available_slots = "09:00\n10:00\n14:00\n15:00";
        $this->applyTypeDefaults();
        $this->applyTemplateDesign($this->template);
        $this->landing_blocks = $this->defaultLandingBlocks($this->type);
        $this->resetBuilderNewBlock();
        $this->resetValidation();
    }

    private function applyTemplateDesign(string $template, bool $replaceAll = true): void
    {
        $design = PageTemplateCatalog::designFor($template);

        $this->primary_color = (string) $design['primary_color'];
        $this->background_color = (string) $design['background_color'];
        $this->logo_shape = (string) $design['logo_shape'];

        if ($replaceAll) {
            $this->background_type = (string) $design['background_type'];
            $this->font_style = (string) $design['font_style'];
            $this->button_style = (string) $design['button_style'];
            $this->card_style = (string) $design['card_style'];
            $this->show_logo = (bool) $design['show_logo'];
            $this->show_business_info = (bool) $design['show_business_info'];
            $this->show_social_links = (bool) $design['show_social_links'];
            $this->show_benefits = (bool) $design['show_benefits'];
            $this->show_terms = (bool) $design['show_terms'];
            $this->show_faq = (bool) $design['show_faq'];
        }
    }

    private function lines(?string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $value))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    private function defaultLandingBlocks(string $type): array
    {
        $blocks = match ($type) {
            'booking' => [
                ['type' => 'hero', 'title' => __('Hero'), 'headline' => $this->headline ?: __('Book your next visit'), 'body' => $this->subheadline, 'cta' => $this->cta_text],
                ['type' => 'booking_services', 'title' => __('Booking Services'), 'headline' => __('Choose a service'), 'body' => $this->service ?: __('Select a service and preferred time.'), 'cta' => __('Request booking')],
                ['type' => 'business_info', 'title' => __('Business Info'), 'headline' => __('Visit details'), 'body' => '', 'cta' => ''],
            ],
            'coupon', 'promotion' => [
                ['type' => 'hero', 'title' => __('Hero'), 'headline' => $this->headline ?: __('Claim your local offer'), 'body' => $this->subheadline, 'cta' => $this->cta_text],
                ['type' => 'coupon_details', 'title' => __('Coupon Details'), 'headline' => $this->coupon_title ?: __('Offer details'), 'body' => $this->discount ?: __('Limited-time offer'), 'cta' => __('Claim coupon')],
                ['type' => 'thank_you', 'title' => __('Thank You'), 'headline' => __('Thanks for claiming'), 'body' => $this->thank_you_message, 'cta' => ''],
            ],
            'review' => [
                ['type' => 'hero', 'title' => __('Hero'), 'headline' => $this->headline ?: __('How was your visit?'), 'body' => $this->subheadline, 'cta' => $this->cta_text],
                ['type' => 'form', 'title' => __('Rating Form'), 'headline' => __('Choose your rating'), 'body' => __('High ratings go to public review. Low ratings stay private.'), 'cta' => __('Continue')],
                ['type' => 'thank_you', 'title' => __('Thank You'), 'headline' => __('Thank you'), 'body' => $this->thank_you_message, 'cta' => ''],
            ],
            'feedback' => [
                ['type' => 'hero', 'title' => __('Hero'), 'headline' => $this->headline ?: __('Share your feedback'), 'body' => $this->subheadline, 'cta' => $this->cta_text],
                ['type' => 'form', 'title' => __('Feedback Form'), 'headline' => __('Tell us how we did'), 'body' => __('Your response helps us improve.'), 'cta' => __('Send feedback')],
                ['type' => 'thank_you', 'title' => __('Thank You'), 'headline' => __('Feedback received'), 'body' => $this->thank_you_message, 'cta' => ''],
            ],
            default => [
                ['type' => 'hero', 'title' => __('Hero'), 'headline' => $this->headline ?: __('Request a free consultation'), 'body' => $this->subheadline, 'cta' => $this->cta_text],
                ['type' => 'benefits', 'title' => __('Benefits'), 'headline' => __('Why choose us'), 'body' => $this->benefits, 'cta' => ''],
                ['type' => 'form', 'title' => __('Lead Form'), 'headline' => __('Send your details'), 'body' => __('We will follow up soon.'), 'cta' => $this->cta_text],
            ],
        };

        return $this->normalizeLandingBlocks($blocks);
    }

    private function normalizeLandingBlocks(array $blocks): array
    {
        return collect($blocks)
            ->map(function (mixed $block): array {
                if (is_string($block)) {
                    $block = [
                        'type' => str_replace(['booking_service', 'review_rating', 'feedback_question'], ['booking_services', 'form', 'form'], $block),
                    ];
                }

                if (! is_array($block)) {
                    $block = [];
                }

                $type = (string) ($block['type'] ?? 'hero');
                $settings = (array) ($block['settings'] ?? []);
                $type = array_key_exists($type, $this->blockTypeOptions()) ? $type : 'hero';

                return [
                    'id' => (string) ($block['id'] ?? 'block_'.Str::random(10)),
                    'type' => $type,
                    'title' => (string) ($block['title'] ?? $this->blockTypeOptions()[$type] ?? 'Block'),
                    'visible' => (bool) ($block['visible'] ?? true),
                    'settings' => $this->normalizeBlockSettings($type, array_merge($settings, [
                        'headline' => $settings['headline'] ?? $block['headline'] ?? '',
                        'body' => $settings['body'] ?? $block['body'] ?? '',
                        'cta' => $settings['cta'] ?? $block['cta'] ?? '',
                    ]), (string) ($block['title'] ?? $this->blockTypeOptions()[$type] ?? 'Block')),
                ];
            })
            ->values()
            ->all();
    }

    private function normalizeBlockSettings(string $type, array $settings = [], string $title = ''): array
    {
        $defaults = $this->defaultBlockSettings($type, $title);

        return array_merge($defaults, collect($settings)
            ->except(['id', 'type', 'title', 'visible'])
            ->map(fn ($value) => is_bool($value) ? $value : (string) $value)
            ->all());
    }

    private function defaultBlockSettings(string $type, string $title = ''): array
    {
        $headline = $title !== '' ? $title : (string) ($this->blockTypeOptions()[$type] ?? __('Section'));

        return match ($type) {
            'benefits' => [
                'headline' => $headline,
                'items' => "Fast response\nFriendly local team\nSimple next step",
                'cta' => '',
            ],
            'form' => [
                'form_title' => $headline,
                'submit_button' => $this->cta_text ?: __('Submit'),
                'success_message' => $this->thank_you_message,
            ],
            'offer', 'coupon_details' => [
                'offer_title' => $headline,
                'discount' => $this->discount,
                'expiry' => $this->expiry,
                'terms' => $this->terms,
                'redemption_instructions' => __('Show this offer to the local team when you visit.'),
            ],
            'booking_services' => [
                'service_title' => $this->service ?: $headline,
                'duration' => $this->duration,
                'price' => $this->price,
                'description' => __('Choose a service and preferred time.'),
            ],
            'business_info' => [
                'headline' => $headline,
                'show_address' => true,
                'show_phone' => true,
                'show_website' => true,
                'show_hours' => false,
            ],
            'social_links' => [
                'headline' => $headline,
                'facebook_url' => '',
                'instagram_url' => '',
                'website_url' => '',
            ],
            'faq' => [
                'question' => $headline,
                'answer' => __('Your submission is sent directly to the local team and tracked for follow-up.'),
            ],
            'testimonials' => [
                'quote' => __('Great service and easy follow-up.'),
                'author' => __('Local customer'),
            ],
            'map' => [
                'map_title' => $headline,
                'map_text' => __('Use the business address to show a map or location note.'),
            ],
            'opening_hours' => [
                'hours_text' => "Monday - Friday: 9:00 AM - 5:00 PM\nSaturday: By appointment",
            ],
            'thank_you' => [
                'message' => $this->thank_you_message,
                'cta' => '',
            ],
            'custom_html' => [
                'html' => '',
            ],
            default => [
                'headline' => $headline,
                'body' => '',
                'cta' => '',
            ],
        };
    }

    private function resetBuilderNewBlock(): void
    {
        $this->builderNewBlock = [
            'type' => 'benefits',
            'title' => '',
            'headline' => '',
            'body' => '',
            'cta' => '',
            'items' => '',
            'form_title' => '',
            'submit_button' => '',
            'success_message' => '',
            'offer_title' => '',
            'discount' => '',
            'expiry' => '',
            'terms' => '',
            'redemption_instructions' => '',
            'service_title' => '',
            'duration' => '',
            'price' => '',
            'description' => '',
            'show_address' => true,
            'show_phone' => true,
            'show_website' => true,
            'show_hours' => false,
            'facebook_url' => '',
            'instagram_url' => '',
            'website_url' => '',
            'question' => '',
            'answer' => '',
            'quote' => '',
            'author' => '',
            'map_title' => '',
            'map_text' => '',
            'hours_text' => '',
            'message' => '',
            'html' => '',
            'visible' => true,
        ];
    }

    private function moveArrayItem(array $items, int $index, int $direction): array
    {
        $items = array_values($items);
        $target = $index + $direction;

        if (! array_key_exists($index, $items) || $target < 0 || $target >= count($items)) {
            return $items;
        }

        [$item] = array_splice($items, $index, 1);
        array_splice($items, $target, 0, [$item]);

        return array_values($items);
    }

    private function safeReturnUrl(mixed $url): string
    {
        $url = is_string($url) ? $url : '';

        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return '';
        }

        return parse_url($url, PHP_URL_HOST) === request()->getHost() ? $url : '';
    }
}
