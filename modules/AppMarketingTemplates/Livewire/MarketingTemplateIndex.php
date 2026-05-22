<?php

namespace Modules\AppMarketingTemplates\Livewire;

use App\Support\Plans\PlanLimitGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Modules\AdminSettings\Support\OptionStore;
use Modules\AppAIStudio\Models\AIStudioUserSetting;
use Modules\AppAIStudio\Models\AIStudioWorkspaceSetting;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppLandingPages\Models\LandingPage;
use Modules\AppMarketingTemplates\Models\MarketingTemplate;
use Modules\AppMarketingTemplates\Support\AITemplateGeneratorService;
use Modules\AppMarketingTemplates\Support\TemplateImportExportService;
use Modules\AppMarketingTemplates\Support\TemplatePackService;
use Modules\AppMarketingTemplates\Support\TemplateUseService;
use Modules\AppQRCampaigns\Models\QrCampaign;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

#[Title('Templates')]
class MarketingTemplateIndex extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $activeTab = 'all';
    public string $search = '';
    public string $typeFilter = '';
    public string $categoryFilter = '';
    public string $goalFilter = '';
    public string $originFilter = '';
    public string $statusFilter = 'active';
    public ?int $previewId = null;
    public ?int $useId = null;
    public int $useStep = 1;
    public ?int $editingId = null;
    public string $statusMessage = '';
    public ?int $createdCampaignId = null;
    public ?int $createdLandingPageId = null;
    public ?string $createdRedirectUrl = null;
    public ?string $createdPublicUrl = null;
    public ?string $createdQrUrl = null;
    public bool $importing = false;
    public string $importJson = '';
    public mixed $importFile = null;
    public bool $aiGenerating = false;
    public ?int $previewPackId = null;

    /** @var array<string, mixed> */
    public array $form = [];

    /** @var array<string, mixed> */
    public array $useForm = [];

    /** @var array<string, mixed> */
    public array $builderNewField = [];

    /** @var array<string, mixed> */
    public array $builderNewBlock = [];

    public string $builderNewVariable = '';

    /** @var array<string, mixed> */
    public array $aiForm = [];

    public function mount(): void
    {
        $this->resetForm();
        $this->resetAiForm();
        $this->ensureSystemTemplates();
        app(TemplatePackService::class)->ensureSystemPacks();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->typeFilter = in_array($tab, array_keys($this->typeOptions()), true) ? $tab : '';
        $this->originFilter = match ($tab) {
            'my' => 'custom',
            'team' => 'team',
            'marketplace' => 'marketplace',
            default => '',
        };
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedGoalFilter(): void
    {
        $this->resetPage();
    }

    public function updatedOriginFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedImportFile(): void
    {
        $this->validate([
            'importFile' => ['nullable', 'file', 'max:5120', 'mimes:json,txt'],
        ]);

        if (! $this->importFile) {
            return;
        }

        $contents = file_get_contents($this->importFile->getRealPath());

        if ($contents === false) {
            $this->addError('importFile', __('Unable to read the uploaded template file.'));

            return;
        }

        $this->importJson = $contents;
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->resetForm();
        $this->editingId = 0;
    }

    public function openEditModal(int $id): void
    {
        $template = $this->userVisibleQuery()->whereKey($id)->firstOrFail();

        if (! $template->canBeManagedBy(auth()->user())) {
            $this->statusMessage = __('Duplicate system templates before editing them.');

            return;
        }

        $this->resetValidation();
        $this->editingId = $template->id;
        $this->form = [
            'name' => (string) $template->name,
            'type' => (string) $template->type,
            'category' => (string) $template->category,
            'goal' => (string) $template->goal,
            'description' => (string) $template->description,
            'icon' => (string) $template->icon,
            'status' => (string) $template->status,
            'source' => (string) ($template->source ?: ($template->is_system ? 'system' : 'custom')),
            'visibility' => (string) ($template->visibility ?: 'private'),
            'version' => (string) ($template->version ?: '1.0.0'),
            'settings_json' => json_encode($template->settings ?: new \stdClass(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            'content_json' => json_encode($template->content ?: new \stdClass(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            'design_json' => json_encode($template->design ?: new \stdClass(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            'builder_schema_json' => json_encode($template->builder_schema ?: new \stdClass(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        ];
        $this->hydrateBuilderState();
    }

    public function save(): void
    {
        $this->syncBuilderToJson();

        $payload = $this->validate([
            'form.name' => ['required', 'string', 'max:255'],
            'form.type' => ['required', 'string', 'in:campaign,landing_page,form,content,email,whatsapp,automation'],
            'form.category' => ['required', 'string', 'max:60'],
            'form.goal' => ['required', 'string', 'max:60'],
            'form.description' => ['nullable', 'string', 'max:1000'],
            'form.icon' => ['required', 'string', 'max:120'],
            'form.status' => ['required', 'string', 'in:active,draft,archived'],
            'form.source' => ['required', 'string', 'in:custom,imported,ai_generated,marketplace'],
            'form.visibility' => ['required', 'string', 'in:private,team,public'],
            'form.version' => ['required', 'string', 'max:30'],
            'form.settings_json' => ['nullable', 'json'],
            'form.content_json' => ['nullable', 'json'],
            'form.design_json' => ['nullable', 'json'],
            'form.builder_schema_json' => ['nullable', 'json'],
        ])['form'];

        $data = [
            'user_id' => auth()->id(),
            'created_by' => auth()->id(),
            'name' => $payload['name'],
            'slug' => Str::slug($payload['name']).'-'.Str::lower(Str::random(5)),
            'type' => $payload['type'],
            'category' => $payload['category'],
            'goal' => $payload['goal'],
            'description' => $payload['description'] ?? '',
            'icon' => $payload['icon'],
            'status' => $payload['status'],
            'is_system' => false,
            'source' => $payload['source'],
            'visibility' => $payload['visibility'],
            'team_id' => $payload['visibility'] === 'team' ? TeamWorkspaceAccess::activeTeam(auth()->user())?->id : null,
            'marketplace_status' => $payload['visibility'] === 'public' ? 'pending' : 'none',
            'submitted_at' => $payload['visibility'] === 'public' ? now() : null,
            'approved_at' => null,
            'approved_by' => null,
            'featured' => false,
            'version' => $payload['version'],
            'settings' => $this->decodeJson($payload['settings_json'] ?? '{}'),
            'content' => $this->decodeJson($payload['content_json'] ?? '{}'),
            'design' => $this->decodeJson($payload['design_json'] ?? '{}'),
            'builder_schema' => $this->decodeJson($payload['builder_schema_json'] ?? '{}'),
        ];

        if ($this->editingId) {
            $template = MarketingTemplate::query()
                ->where('user_id', auth()->id())
                ->where('is_system', false)
                ->whereKey($this->editingId)
                ->firstOrFail();

            unset($data['slug']);
            if ($template->marketplace_status === 'approved' && $payload['visibility'] === 'public') {
                unset($data['marketplace_status'], $data['submitted_at'], $data['approved_at'], $data['approved_by'], $data['featured']);
            }
            $template->update($data);
            $this->statusMessage = __('Template updated.');
        } else {
            app(PlanLimitGuard::class)->ensureTemplateCanBeCreated(auth()->user());

            MarketingTemplate::query()->create($data);
            $this->statusMessage = __('Template created.');
        }

        $this->editingId = null;
        $this->resetForm();
    }

    public function addBuilderField(): void
    {
        $name = Str::snake((string) ($this->builderNewField['name'] ?: $this->builderNewField['label'] ?: 'field'));

        $this->form['form_fields'][] = [
            'id' => 'field_'.Str::random(10),
            'name' => $name,
            'type' => (string) ($this->builderNewField['type'] ?: 'text'),
            'label' => (string) ($this->builderNewField['label'] ?: str($name)->replace('_', ' ')->headline()),
            'placeholder' => (string) ($this->builderNewField['placeholder'] ?? ''),
            'required' => (bool) ($this->builderNewField['required'] ?? false),
            'options' => collect(explode(',', (string) ($this->builderNewField['options'] ?? '')))
                ->map(fn (string $option): string => trim($option))
                ->filter()
                ->values()
                ->all(),
            'help_text' => (string) ($this->builderNewField['help_text'] ?? ''),
            'validation' => [
                'min' => $this->builderNewField['min'] !== '' ? (int) $this->builderNewField['min'] : null,
                'max' => $this->builderNewField['max'] !== '' ? (int) $this->builderNewField['max'] : null,
                'pattern' => (string) ($this->builderNewField['pattern'] ?? ''),
            ],
        ];

        $this->resetBuilderNewField();
        $this->syncBuilderToJson();
    }

    public function removeBuilderField(int $index): void
    {
        unset($this->form['form_fields'][$index]);
        $this->form['form_fields'] = array_values($this->form['form_fields'] ?? []);
        $this->syncBuilderToJson();
    }

    public function duplicateBuilderField(int $index): void
    {
        $fields = array_values($this->form['form_fields'] ?? []);

        if (! array_key_exists($index, $fields)) {
            return;
        }

        $copy = $fields[$index];
        $copy['id'] = 'field_'.Str::random(10);
        $copy['name'] = $this->uniqueBuilderName((string) ($copy['name'] ?? 'field'), $fields);
        $copy['label'] = (string) ($copy['label'] ?? $copy['name']).' '.__('copy');
        array_splice($fields, $index + 1, 0, [$copy]);
        $this->form['form_fields'] = $fields;
        $this->syncBuilderToJson();
    }

    public function moveBuilderField(int $index, int $direction): void
    {
        $this->form['form_fields'] = $this->moveArrayItem($this->form['form_fields'] ?? [], $index, $direction);
        $this->syncBuilderToJson();
    }

    public function reorderBuilderFields(array $fieldIds): void
    {
        $this->form['form_fields'] = $this->reorderItemsById($this->form['form_fields'] ?? [], $fieldIds, 'field');
        $this->syncBuilderToJson();
    }

    public function addBuilderBlock(): void
    {
        $type = (string) ($this->builderNewBlock['type'] ?: 'hero');
        $title = (string) ($this->builderNewBlock['title'] ?: str($type)->replace('_', ' ')->headline());

        $this->form['landing_page_blocks'][] = [
            'id' => 'block_'.Str::random(10),
            'type' => $type,
            'title' => $title,
            'visible' => (bool) ($this->builderNewBlock['visible'] ?? true),
            'settings' => [
                'headline' => (string) ($this->builderNewBlock['headline'] ?? $title),
                'body' => (string) ($this->builderNewBlock['body'] ?? ''),
                'cta' => (string) ($this->builderNewBlock['cta'] ?? ''),
            ],
        ];

        $this->resetBuilderNewBlock();
        $this->syncBuilderToJson();
    }

    public function removeBuilderBlock(int $index): void
    {
        unset($this->form['landing_page_blocks'][$index]);
        $this->form['landing_page_blocks'] = array_values($this->form['landing_page_blocks'] ?? []);
        $this->syncBuilderToJson();
    }

    public function duplicateBuilderBlock(int $index): void
    {
        $blocks = array_values($this->form['landing_page_blocks'] ?? []);

        if (! array_key_exists($index, $blocks)) {
            return;
        }

        $copy = $blocks[$index];
        $copy['id'] = 'block_'.Str::random(10);
        $copy['title'] = (string) ($copy['title'] ?? $copy['type'] ?? 'Block').' '.__('copy');
        array_splice($blocks, $index + 1, 0, [$copy]);
        $this->form['landing_page_blocks'] = $blocks;
        $this->syncBuilderToJson();
    }

    public function reorderBuilderBlocks(array $blockIds): void
    {
        $this->form['landing_page_blocks'] = $this->reorderItemsById($this->form['landing_page_blocks'] ?? [], $blockIds, 'block');
        $this->syncBuilderToJson();
    }

    public function toggleBuilderBlockVisibility(int $index): void
    {
        if (! isset($this->form['landing_page_blocks'][$index])) {
            return;
        }

        $this->form['landing_page_blocks'][$index]['visible'] = ! (bool) ($this->form['landing_page_blocks'][$index]['visible'] ?? true);
        $this->syncBuilderToJson();
    }

    public function moveBuilderBlock(int $index, int $direction): void
    {
        $this->form['landing_page_blocks'] = $this->moveArrayItem($this->form['landing_page_blocks'] ?? [], $index, $direction);
        $this->syncBuilderToJson();
    }

    public function addBuilderVariable(): void
    {
        $variable = trim($this->builderNewVariable, " \t\n\r\0\x0B{}");

        if ($variable === '') {
            return;
        }

        $variables = $this->form['variables'] ?? [];
        $variables[] = Str::snake($variable);
        $this->form['variables'] = array_values(array_unique($variables));
        $this->builderNewVariable = '';
        $this->syncBuilderToJson();
    }

    public function removeBuilderVariable(int $index): void
    {
        unset($this->form['variables'][$index]);
        $this->form['variables'] = array_values($this->form['variables'] ?? []);
        $this->syncBuilderToJson();
    }

    public function preview(int $id): void
    {
        $this->previewId = $id;
    }

    public function useTemplate(int $id): void
    {
        $template = $this->userVisibleQuery()->whereKey($id)->firstOrFail();
        $this->useId = $id;
        $this->useStep = 1;
        $this->createdCampaignId = null;
        $this->createdLandingPageId = null;
        $this->createdRedirectUrl = null;
        $this->createdPublicUrl = null;
        $this->createdQrUrl = null;
        $this->resetUseForm($template);
    }

    public function setUseStep(int $step): void
    {
        if (! in_array($step, [1, 2, 3], true)) {
            return;
        }

        if ($step > $this->useStep) {
            $this->validateUseStep($this->useStep);
        }

        $this->useStep = $step;
    }

    public function nextUseStep(): void
    {
        $this->validateUseStep($this->useStep);
        $this->useStep = min(3, $this->useStep + 1);
    }

    public function previousUseStep(): void
    {
        $this->useStep = max(1, $this->useStep - 1);
    }

    public function createFromTemplate(): ?Redirector
    {
        $template = $this->useId ? $this->userVisibleQuery()->whereKey($this->useId)->firstOrFail() : null;

        if (! $template) {
            return null;
        }

        $this->validateUseStep(3);

        $business = LocalBusiness::query()
            ->where('user_id', auth()->id())
            ->findOrFail((int) $this->useForm['business_id']);

        if ($template->type === 'content') {
            $template->increment('usage_count');
            app(TemplateUseService::class)->recordUsage($template, $business, $template->type, null, auth()->id());

            return redirect()->to($this->contentWriterUrl($template, $business));
        }

        if ($template->type === 'email') {
            $created = app(TemplateUseService::class)->createEmailTemplate($template, $business, (int) auth()->id());
            $template->increment('usage_count');
            app(TemplateUseService::class)->recordUsage($template, $business, 'email_template', $created->id, auth()->id());

            return redirect()->to($this->targetUrlFor($template) ?: '#');
        }

        if ($template->type === 'whatsapp') {
            $created = app(TemplateUseService::class)->createWhatsAppTemplate($template, $business, (int) auth()->id());
            $template->increment('usage_count');
            app(TemplateUseService::class)->recordUsage($template, $business, 'whatsapp_template', $created->id, auth()->id());

            return redirect()->to($this->targetUrlFor($template) ?: '#');
        }

        if ($template->type === 'automation') {
            $created = app(TemplateUseService::class)->createWebhookAutomation($template, $business, (int) auth()->id());
            $template->increment('usage_count');
            app(TemplateUseService::class)->recordUsage($template, $business, 'webhook_automation', $created->id, auth()->id());

            return redirect()->to($this->targetUrlFor($template) ?: '#');
        }

        $guard = app(PlanLimitGuard::class);
        if ($template->type === 'landing_page') {
            $guard->ensureLandingPageCanBeCreated(auth()->user());

            $landingPage = app(TemplateUseService::class)->createLandingPage($template, $business, $this->useForm, (int) auth()->id());
            $template->increment('usage_count');
            app(TemplateUseService::class)->recordUsage($template, $business, 'landing_page', $landingPage->id, auth()->id());
            $this->markCreated(null, $landingPage);
            $this->useStep = 3;
            $this->statusMessage = __('Landing page draft created from template.');

            return null;
        }

        $guard->ensureCampaignCanBeCreated(auth()->user());
        $guard->ensureLandingPageCanBeCreated(auth()->user());

        $created = app(TemplateUseService::class)->createCampaign($template, $business, $this->useForm, (int) auth()->id());
        $campaign = $created['campaign'];
        $landingPage = $created['landing_page'];
        $template->increment('usage_count');
        app(TemplateUseService::class)->recordUsage($template, $business, 'campaign', $campaign->id, auth()->id());
        $this->markCreated($campaign, $landingPage);
        $this->useStep = 3;
        $this->statusMessage = __('Campaign created from template.');

        return null;
    }

    public function duplicate(int $id): void
    {
        $template = $this->userVisibleQuery()->whereKey($id)->firstOrFail();

        app(PlanLimitGuard::class)->ensureTemplateCanBeCreated(auth()->user());

        $copy = $template->replicate(['slug', 'is_system', 'usage_count']);
        $copy->user_id = auth()->id();
        $copy->created_by = auth()->id();
        $copy->name = $template->name.' '.__('copy');
        $copy->slug = Str::slug($copy->name).'-'.Str::lower(Str::random(5));
        $copy->is_system = false;
        $copy->status = 'draft';
        $copy->source = 'custom';
        $copy->visibility = 'private';
        $copy->team_id = null;
        $copy->marketplace_status = 'none';
        $copy->submitted_at = null;
        $copy->approved_at = null;
        $copy->approved_by = null;
        $copy->featured = false;
        $copy->rating_count = 0;
        $copy->rating_sum = 0;
        $copy->usage_count = 0;
        $copy->save();

        $this->statusMessage = __('Template duplicated as draft.');
    }

    public function shareWithTeam(int $id): void
    {
        $team = TeamWorkspaceAccess::activeTeam(auth()->user());

        if (! $team) {
            $this->statusMessage = __('Choose an active team workspace before sharing templates.');

            return;
        }

        $template = MarketingTemplate::query()
            ->where('user_id', auth()->id())
            ->where('is_system', false)
            ->whereKey($id)
            ->firstOrFail();

        $template->update([
            'team_id' => $team->id,
            'visibility' => 'team',
        ]);

        $this->statusMessage = __('Template shared with the active team.');
    }

    public function unshareFromTeam(int $id): void
    {
        $template = MarketingTemplate::query()
            ->where('user_id', auth()->id())
            ->where('is_system', false)
            ->whereKey($id)
            ->firstOrFail();

        $template->update([
            'team_id' => null,
            'visibility' => 'private',
        ]);

        $this->statusMessage = __('Template is private again.');
    }

    public function submitToMarketplace(int $id): void
    {
        $template = MarketingTemplate::query()
            ->where('user_id', auth()->id())
            ->where('is_system', false)
            ->whereKey($id)
            ->firstOrFail();

        $template->update([
            'visibility' => 'public',
            'marketplace_status' => 'pending',
            'submitted_at' => now(),
            'approved_at' => null,
            'approved_by' => null,
            'featured' => false,
        ]);

        $this->statusMessage = __('Template submitted to the public marketplace for approval.');
    }

    public function approveMarketplaceTemplate(int $id): void
    {
        abort_unless($this->canModerateMarketplace(), 403);

        MarketingTemplate::query()
            ->whereKey($id)
            ->where('visibility', 'public')
            ->update([
                'source' => 'marketplace',
                'status' => 'active',
                'marketplace_status' => 'approved',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);

        $this->statusMessage = __('Marketplace template approved.');
    }

    public function rejectMarketplaceTemplate(int $id): void
    {
        abort_unless($this->canModerateMarketplace(), 403);

        MarketingTemplate::query()
            ->whereKey($id)
            ->where('visibility', 'public')
            ->update([
                'marketplace_status' => 'rejected',
                'approved_at' => null,
                'approved_by' => null,
                'featured' => false,
            ]);

        $this->statusMessage = __('Marketplace submission rejected.');
    }

    public function toggleFeatured(int $id): void
    {
        abort_unless($this->canModerateMarketplace(), 403);

        $template = MarketingTemplate::query()
            ->whereKey($id)
            ->where('marketplace_status', 'approved')
            ->firstOrFail();

        $template->update(['featured' => ! (bool) $template->featured]);
        $this->statusMessage = $template->featured ? __('Template featured.') : __('Template unfeatured.');
    }

    public function rateTemplate(int $id, int $rating): void
    {
        $rating = max(1, min(5, $rating));
        $template = $this->userVisibleQuery()
            ->whereKey($id)
            ->where('marketplace_status', 'approved')
            ->firstOrFail();

        DB::table('lb_template_ratings')->updateOrInsert(
            ['template_id' => $template->id, 'user_id' => auth()->id()],
            ['rating' => $rating, 'updated_at' => now(), 'created_at' => now()]
        );

        $summary = DB::table('lb_template_ratings')
            ->where('template_id', $template->id)
            ->selectRaw('COUNT(*) as rating_count, COALESCE(SUM(rating), 0) as rating_sum')
            ->first();

        $template->update([
            'rating_count' => (int) ($summary->rating_count ?? 0),
            'rating_sum' => (int) ($summary->rating_sum ?? 0),
        ]);

        $this->statusMessage = __('Thanks for rating this template.');
    }

    public function delete(int $id): void
    {
        MarketingTemplate::query()
            ->where('user_id', auth()->id())
            ->where('is_system', false)
            ->whereKey($id)
            ->delete();

        $this->statusMessage = __('Template deleted.');
    }

    public function openImportModal(): void
    {
        $this->resetValidation();
        $this->importing = true;
        $this->importFile = null;
        $this->importJson = json_encode([
            'name' => 'Imported Coupon Template',
            'type' => 'campaign',
            'category' => 'general',
            'goal' => 'coupon',
            'description' => 'Imported reusable template.',
            'settings' => ['campaign_type' => 'coupon', 'creates_landing_page' => true, 'creates_qr_code' => true],
            'content' => ['headline' => 'Claim your offer', 'cta' => 'Claim Offer', 'form_fields' => []],
            'design' => ['theme' => 'clean', 'accent_color' => '#2563eb'],
            'builder_schema' => ['form_builder' => true, 'landing_page_blocks' => true],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function openAIGenerateModal(): void
    {
        $this->resetValidation();
        $this->resetAiForm();
        $this->aiGenerating = true;
    }

    public function generateTemplateFromAI(): void
    {
        $payload = $this->validate([
            'aiForm.category' => ['required', 'string', 'max:60'],
            'aiForm.goal' => ['required', 'string', 'max:60'],
            'aiForm.campaign_type' => ['required', 'string', 'in:campaign,landing_page,form,content,email,whatsapp,automation'],
            'aiForm.offer' => ['nullable', 'string', 'max:255'],
            'aiForm.tone' => ['required', 'string', 'max:60', Rule::in($this->aiToneValues())],
            'aiForm.language' => ['required', 'string', 'max:12', Rule::in($this->languageCodes())],
        ])['aiForm'];

        app(PlanLimitGuard::class)->ensureTemplateCanBeCreated(auth()->user());

        $generated = app(AITemplateGeneratorService::class)->generate($payload);

        MarketingTemplate::query()->create([
            'user_id' => auth()->id(),
            'created_by' => auth()->id(),
            'name' => $generated['name'],
            'slug' => $this->uniqueTemplateSlug($generated['name']),
            'type' => $payload['campaign_type'],
            'category' => $payload['category'],
            'goal' => $payload['goal'],
            'description' => $generated['description'],
            'icon' => $generated['icon'],
            'is_system' => false,
            'source' => 'ai_generated',
            'visibility' => 'private',
            'marketplace_status' => 'none',
            'status' => 'draft',
            'version' => '1.0.0',
            'usage_count' => 0,
            'settings' => $generated['settings'],
            'content' => $generated['content'],
            'design' => $generated['design'],
            'builder_schema' => $generated['builder_schema'],
        ]);

        $this->aiGenerating = false;
        $this->statusMessage = __('AI-generated template saved as draft.');
    }

    public function importTemplate(): void
    {
        $this->validate([
            'importJson' => ['required', 'json'],
        ]);

        $payload = json_decode($this->importJson, true);
        $templateCount = isset($payload['templates']) && is_array($payload['templates']) ? count($payload['templates']) : 1;

        for ($i = 0; $i < $templateCount; $i++) {
            app(PlanLimitGuard::class)->ensureTemplateCanBeCreated(auth()->user());
        }

        $result = app(TemplateImportExportService::class)->import(
            $payload,
            auth()->user(),
            $this->typeOptions(),
            fn (string $name): string => $this->uniqueTemplateSlug($name)
        );

        $this->importing = false;
        $this->importJson = '';
        $this->importFile = null;
        $this->statusMessage = $result['failed'] === 0
            ? __('Imported :count template(s).', ['count' => $result['imported']])
            : __('Imported :count template(s); :failed row(s) failed validation.', ['count' => $result['imported'], 'failed' => $result['failed']]);
    }

    public function exportTemplate(int $id)
    {
        $template = $this->userVisibleQuery()->whereKey($id)->firstOrFail();
        $payload = app(TemplateImportExportService::class)->exportPayload($template);
        $filename = Str::slug($template->name ?: 'template').'.localboost-template.json';

        return response()->streamDownload(function () use ($payload): void {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }, $filename, ['Content-Type' => 'application/json']);
    }

    public function exportPack(int $id)
    {
        $payload = app(TemplatePackService::class)->exportPayload($id);

        if (! $payload) {
            return null;
        }

        $filename = Str::slug((string) ($payload['name'] ?? 'template-pack')).'.localboost-template-pack.json';

        return response()->streamDownload(function () use ($payload): void {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }, $filename, ['Content-Type' => 'application/json']);
    }

    public function installPack(int $id): void
    {
        $result = app(TemplatePackService::class)->installPack(
            $id,
            auth()->user(),
            fn (string $name): string => $this->uniqueTemplateSlug($name)
        );

        if (! $result['pack']) {
            return;
        }

        $this->statusMessage = __('Installed :count template(s) from :pack.', [
            'count' => $result['installed'],
            'pack' => $result['pack']->name,
        ]);
    }

    public function previewPack(int $id): void
    {
        $this->previewPackId = $id;
    }

    public function closeModal(): void
    {
        $this->previewId = null;
        $this->useId = null;
        $this->useStep = 1;
        $this->useForm = [];
        $this->createdCampaignId = null;
        $this->createdLandingPageId = null;
        $this->createdRedirectUrl = null;
        $this->createdPublicUrl = null;
        $this->createdQrUrl = null;
        $this->editingId = null;
        $this->importing = false;
        $this->importJson = '';
        $this->importFile = null;
        $this->aiGenerating = false;
        $this->previewPackId = null;
    }

    public function render(): View
    {
        $query = $this->userVisibleQuery()
            ->when($this->typeFilter !== '', fn ($query) => $query->where('type', $this->typeFilter))
            ->when($this->categoryFilter !== '', fn ($query) => $query->where('category', $this->categoryFilter))
            ->when($this->goalFilter !== '', fn ($query) => $query->where('goal', $this->goalFilter))
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->originFilter === 'system', fn ($query) => $query->where('is_system', true))
            ->when($this->originFilter === 'custom', fn ($query) => $query->where('is_system', false)->where('user_id', auth()->id()))
            ->when($this->originFilter === 'team', fn ($query) => $query->where('visibility', 'team')->where('team_id', TeamWorkspaceAccess::activeTeam(auth()->user())?->id ?: 0))
            ->when($this->originFilter === 'marketplace', fn ($query) => $query->where('visibility', 'public')->whereIn('marketplace_status', $this->canModerateMarketplace() ? ['pending', 'approved', 'rejected'] : ['approved']))
            ->when(trim($this->search) !== '', function ($query): void {
                $term = '%'.trim($this->search).'%';
                $query->where(fn ($builder) => $builder
                    ->where('name', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('goal', 'like', $term)
                    ->orWhere('category', 'like', $term));
            })
            ->orderByDesc('is_system')
            ->orderByDesc('featured')
            ->orderByDesc('usage_count')
            ->latest();

        $templates = $query->paginate(12);
        $allVisible = $this->userVisibleQuery()->get(['type', 'is_system', 'status']);

        return view('appmarketingtemplates::index', [
            'templates' => $templates,
            'summary' => [
                'total' => $allVisible->count(),
                'system' => $allVisible->where('is_system', true)->count(),
                'custom' => $allVisible->where('is_system', false)->count(),
                'active' => $allVisible->where('status', 'active')->count(),
            ],
            'activeTab' => $this->activeTab,
            'editingId' => $this->editingId,
            'statusMessage' => $this->statusMessage,
            'search' => $this->search,
            'form' => $this->form,
            'tabs' => $this->tabs(),
            'typeOptions' => $this->typeOptions(),
            'categoryOptions' => $this->categoryOptions(),
            'goalOptions' => $this->goalOptions(),
            'statusOptions' => $this->statusOptions(),
            'sourceOptions' => $this->sourceOptions(),
            'visibilityOptions' => $this->visibilityOptions(),
            'aiToneOptions' => $this->aiToneOptions(),
            'fieldTypeOptions' => $this->fieldTypeOptions(),
            'blockTypeOptions' => $this->blockTypeOptions(),
            'businesses' => LocalBusiness::query()->where('user_id', auth()->id())->orderBy('name')->get(),
            'previewTemplate' => $this->previewId ? $this->userVisibleQuery()->whereKey($this->previewId)->first() : null,
            'useTemplate' => $this->useId ? $this->userVisibleQuery()->whereKey($this->useId)->first() : null,
            'useTargetUrl' => $this->useId ? $this->targetUrlFor($this->userVisibleQuery()->whereKey($this->useId)->first()) : null,
            'useStep' => $this->useStep,
            'useForm' => $this->useForm,
            'createdRedirectUrl' => $this->createdRedirectUrl,
            'createdPublicUrl' => $this->createdPublicUrl,
            'createdQrUrl' => $this->createdQrUrl,
            'marketplacePacks' => app(TemplatePackService::class)->activePacks(),
            'previewPack' => $this->previewPackId ? app(TemplatePackService::class)->packWithTemplates($this->previewPackId) : null,
            'canModerateMarketplace' => $this->canModerateMarketplace(),
            'activeTeam' => TeamWorkspaceAccess::activeTeam(auth()->user()),
            'importing' => $this->importing,
            'importJson' => $this->importJson,
            'aiGenerating' => $this->aiGenerating,
            'aiForm' => $this->aiForm,
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('Templates')]);
    }

    protected function userVisibleQuery()
    {
        $teamId = TeamWorkspaceAccess::activeTeam(auth()->user())?->id;

        return MarketingTemplate::query()
            ->where(function ($query) use ($teamId): void {
                $query->where('is_system', true)
                    ->orWhere('user_id', auth()->id())
                    ->orWhere(fn ($shared) => $shared
                        ->where('visibility', 'team')
                        ->whereNotNull('team_id')
                        ->when($teamId, fn ($teamQuery) => $teamQuery->where('team_id', $teamId), fn ($teamQuery) => $teamQuery->whereRaw('1 = 0')))
                    ->orWhere(fn ($marketplace) => $marketplace
                        ->where('visibility', 'public')
                        ->where('marketplace_status', 'approved')
                        ->where('status', 'active'));

                if ($this->canModerateMarketplace()) {
                    $query->orWhere(fn ($moderation) => $moderation
                        ->where('visibility', 'public')
                        ->whereIn('marketplace_status', ['pending', 'rejected']));
                }
            });
    }

    protected function canModerateMarketplace(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->canAccessAdmin() || $user?->isSuperAdmin());
    }

    /**
     * @return array<string, string>
     */
    protected function typeOptions(): array
    {
        return [
            'campaign' => __('Campaign'),
            'landing_page' => __('Landing Page'),
            'form' => __('Form'),
            'content' => __('Content'),
            'email' => __('Email'),
            'whatsapp' => __('WhatsApp'),
            'automation' => __('Automation'),
        ];
    }

    protected function categoryOptions(): array
    {
        return [
            'general' => __('General'),
            'restaurant' => __('Restaurant'),
            'spa' => __('Spa'),
            'salon' => __('Salon'),
            'clinic' => __('Clinic'),
            'dentist' => __('Dentist'),
            'gym' => __('Gym'),
            'retail' => __('Retail'),
            'agency' => __('Agency'),
            'real_estate' => __('Real Estate'),
            'auto_service' => __('Auto Service'),
            'local_service' => __('Local Service'),
        ];
    }

    protected function goalOptions(): array
    {
        return [
            'review' => __('Reviews'),
            'booking' => __('Bookings'),
            'coupon' => __('Coupons'),
            'feedback' => __('Feedback'),
            'lead' => __('Leads'),
            'retention' => __('Retention'),
            'loyalty' => __('Loyalty'),
            'referral' => __('Referral'),
        ];
    }

    protected function sourceOptions(): array
    {
        return [
            'custom' => __('Custom'),
            'imported' => __('Imported'),
            'ai_generated' => __('AI generated'),
            'marketplace' => __('Marketplace'),
        ];
    }

    protected function visibilityOptions(): array
    {
        return [
            'private' => __('Private'),
            'team' => __('Team Shared'),
            'public' => __('Public Marketplace'),
        ];
    }

    protected function fieldTypeOptions(): array
    {
        return [
            'text' => __('Text'),
            'email' => __('Email'),
            'phone' => __('Phone'),
            'textarea' => __('Textarea'),
            'select' => __('Select'),
            'radio' => __('Radio'),
            'checkbox' => __('Checkbox'),
            'date' => __('Date'),
            'time' => __('Time'),
            'rating' => __('Rating'),
            'hidden' => __('Hidden'),
        ];
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

    protected function statusOptions(): array
    {
        return [
            'active' => __('Active'),
            'draft' => __('Draft'),
            'archived' => __('Archived'),
        ];
    }

    protected function tabs(): array
    {
        return [
            'all' => __('All Templates'),
            'campaign' => __('Campaigns'),
            'landing_page' => __('Landing Pages'),
            'form' => __('Forms'),
            'content' => __('Content'),
            'email' => __('Email'),
            'whatsapp' => __('WhatsApp'),
            'automation' => __('Automation'),
            'team' => __('Team Shared'),
            'marketplace' => __('Marketplace'),
            'my' => __('My Templates'),
        ];
    }

    protected function resetForm(): void
    {
        $this->form = [
            'name' => '',
            'type' => 'campaign',
            'category' => 'general',
            'goal' => 'lead',
            'description' => '',
            'icon' => 'fa-light fa-grid-2',
            'status' => 'draft',
            'source' => 'custom',
            'visibility' => 'private',
            'version' => '1.0.0',
            'settings_json' => json_encode([
                'target_module' => 'lead',
                'creates_campaign' => true,
                'requires_business' => true,
                'creates_landing_page' => true,
                'creates_qr_code' => true,
                'creates_tracking' => true,
                'default_status' => 'draft',
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            'content_json' => json_encode([
                'headline' => '',
                'description' => '',
                'cta' => '',
                'thank_you_message' => '',
                'form_fields' => [],
                'landing_page_blocks' => [],
                'variables' => ['business_name', 'customer_name', 'offer'],
                'prompt_template' => '',
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            'form_fields' => [],
            'landing_page_blocks' => [],
            'variables' => ['business_name', 'customer_name', 'offer'],
            'design_json' => json_encode([
                'theme' => 'clean',
                'accent_color' => '#2563eb',
                'layout' => 'mobile_first',
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            'builder_schema_json' => json_encode([
                'form_builder' => true,
                'landing_page_blocks' => true,
                'campaign_settings' => true,
                'prompt_variables' => true,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        ];
        $this->resetBuilderNewField();
        $this->resetBuilderNewBlock();
        $this->builderNewVariable = '';
    }

    protected function decodeJson(string $json): array
    {
        $decoded = json_decode($json ?: '{}', true);

        return is_array($decoded) ? $decoded : [];
    }

    protected function hydrateBuilderState(): void
    {
        $content = $this->decodeJson((string) ($this->form['content_json'] ?? '{}'));
        $this->form['form_fields'] = collect((array) ($content['form_fields'] ?? []))
            ->map(fn ($field) => array_merge(['id' => 'field_'.Str::random(10)], is_array($field) ? $field : []))
            ->values()
            ->all();
        $this->form['landing_page_blocks'] = collect((array) ($content['landing_page_blocks'] ?? []))
            ->map(fn ($block) => array_merge(['id' => 'block_'.Str::random(10)], is_array($block) ? $block : ['type' => (string) $block]))
            ->values()
            ->all();
        $this->form['variables'] = array_values((array) ($content['variables'] ?? []));
        $this->resetBuilderNewField();
        $this->resetBuilderNewBlock();
        $this->builderNewVariable = '';
    }

    protected function syncBuilderToJson(): void
    {
        $contentRaw = (string) ($this->form['content_json'] ?? '{}');
        $content = json_decode($contentRaw ?: '{}', true);

        if (! is_array($content)) {
            return;
        }

        $content['form_fields'] = array_values((array) ($this->form['form_fields'] ?? []));
        $content['landing_page_blocks'] = array_values((array) ($this->form['landing_page_blocks'] ?? []));
        $content['variables'] = array_values((array) ($this->form['variables'] ?? []));
        $this->form['content_json'] = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $builderRaw = (string) ($this->form['builder_schema_json'] ?? '{}');
        $builderSchema = json_decode($builderRaw ?: '{}', true);

        if (! is_array($builderSchema)) {
            return;
        }

        $builderSchema['form_builder'] = in_array($this->form['type'] ?? 'campaign', ['campaign', 'landing_page', 'form'], true);
        $builderSchema['landing_page_blocks'] = in_array($this->form['type'] ?? 'campaign', ['campaign', 'landing_page'], true);
        $builderSchema['campaign_settings'] = ($this->form['type'] ?? '') === 'campaign';
        $builderSchema['prompt_variables'] = in_array($this->form['type'] ?? '', ['content', 'email', 'whatsapp'], true);
        $this->form['builder_schema_json'] = json_encode($builderSchema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    protected function resetBuilderNewField(): void
    {
        $this->builderNewField = [
            'name' => '',
            'type' => 'text',
            'label' => '',
            'placeholder' => '',
            'required' => false,
            'options' => '',
            'help_text' => '',
            'min' => '',
            'max' => '',
            'pattern' => '',
        ];
    }

    protected function resetBuilderNewBlock(): void
    {
        $this->builderNewBlock = [
            'type' => 'hero',
            'title' => '',
            'headline' => '',
            'body' => '',
            'cta' => '',
            'visible' => true,
        ];
    }

    protected function resetAiForm(): void
    {
        $defaults = $this->resolveAiDefaults();

        $this->aiForm = [
            'category' => 'spa',
            'goal' => 'booking',
            'campaign_type' => 'campaign',
            'offer' => '20% off this weekend',
            'tone' => $defaults['tone'],
            'language' => $defaults['language'],
        ];
    }

    /**
     * @return array{language: string, tone: string}
     */
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

        return [
            'language' => $this->normalizeLanguageCode((string) ($userSettings['default_language'] ?? $workspaceSettings['default_language'] ?? $adminLanguage)),
            'tone' => $this->normalizeTone((string) ($userSettings['default_tone'] ?? $workspaceSettings['default_tone'] ?? $adminTone)),
        ];
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    protected function aiToneOptions(): array
    {
        return [
            ['value' => 'professional', 'label' => __('Professional')],
            ['value' => 'friendly', 'label' => __('Friendly')],
            ['value' => 'sales', 'label' => __('Sales')],
            ['value' => 'educational', 'label' => __('Educational')],
            ['value' => 'bold', 'label' => __('Bold')],
            ['value' => 'casual', 'label' => __('Casual')],
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function aiToneValues(): array
    {
        return collect($this->aiToneOptions())->pluck('value')->all();
    }

    /**
     * @return array<int, string>
     */
    protected function languageCodes(): array
    {
        return collect(world_languages())
            ->pluck('code')
            ->flatMap(fn ($code) => [(string) $code, strtolower((string) $code)])
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function normalizeLanguageCode(string $language): string
    {
        $language = strtolower(trim($language));

        if ($language === '') {
            return 'en';
        }

        $matchedCode = collect(world_languages())->first(fn ($item) => strtolower((string) data_get($item, 'code')) === $language);

        if ($matchedCode) {
            return (string) data_get($matchedCode, 'code', $language);
        }

        $matched = collect(world_languages())->first(fn ($item) => strtolower((string) data_get($item, 'name')) === $language);

        return (string) data_get($matched, 'code', 'en');
    }

    protected function normalizeTone(string $tone): string
    {
        $tone = strtolower(trim($tone));

        return in_array($tone, $this->aiToneValues(), true) ? $tone : 'friendly';
    }

    protected function moveArrayItem(array $items, int $index, int $direction): array
    {
        $items = array_values($items);
        $target = $index + $direction;

        if (! array_key_exists($index, $items) || ! array_key_exists($target, $items)) {
            return $items;
        }

        [$items[$index], $items[$target]] = [$items[$target], $items[$index]];

        return $items;
    }

    protected function reorderItemsById(array $items, array $orderedIds, string $prefix): array
    {
        $items = collect(array_values($items))
            ->map(fn ($item) => array_merge(['id' => $prefix.'_'.Str::random(10)], is_array($item) ? $item : []));

        $ordered = collect($orderedIds)
            ->map(fn ($id) => (string) $id)
            ->filter()
            ->unique()
            ->map(fn (string $id) => $items->firstWhere('id', $id))
            ->filter()
            ->values();

        $remaining = $items
            ->reject(fn (array $item) => $ordered->contains(fn (array $orderedItem) => $orderedItem['id'] === $item['id']))
            ->values();

        return $ordered->merge($remaining)->values()->all();
    }

    protected function uniqueBuilderName(string $name, array $fields): string
    {
        $base = Str::snake($name) ?: 'field';
        $existing = collect($fields)
            ->map(fn ($field) => (string) ($field['name'] ?? ''))
            ->filter()
            ->all();
        $candidate = $base.'_copy';
        $counter = 2;

        while (in_array($candidate, $existing, true)) {
            $candidate = $base.'_copy_'.$counter++;
        }

        return $candidate;
    }

    protected function targetUrlFor(?MarketingTemplate $template): ?string
    {
        if (! $template) {
            return null;
        }

        $campaignType = $template->settings['campaign_type'] ?? $template->goal;

        $routeName = match ($template->type) {
            'campaign' => match ($campaignType) {
                'review' => 'portal.review-booster',
                'booking' => 'portal.booking-pages',
                'coupon' => 'portal.coupon-campaigns',
                'feedback' => 'portal.feedback-forms',
                'lead' => 'portal.lead-forms',
                default => 'portal.qr-campaigns',
            },
            'landing_page' => 'portal.landing-pages',
            'form' => $template->goal === 'feedback' ? 'portal.feedback-forms' : 'portal.lead-forms',
            'content' => 'portal.ai-content',
            'email' => 'portal.email-templates',
            'whatsapp' => 'portal.whatsapp-templates',
            'automation' => 'portal.webhook-automations',
            default => 'portal.marketing-templates',
        };

        return Route::has($routeName) ? route($routeName) : null;
    }

    protected function resetUseForm(MarketingTemplate $template): void
    {
        $content = $template->content ?: [];
        $settings = $template->settings ?: [];
        $business = LocalBusiness::query()->where('user_id', auth()->id())->orderBy('name')->first();
        $campaignType = app(TemplateUseService::class)->campaignTypeFor($template);

        $this->useForm = [
            'business_id' => $business ? (string) $business->id : '',
            'campaign_name' => (string) data_get($content, 'campaign_name', $template->name),
            'headline' => (string) data_get($content, 'headline', $template->name),
            'description' => (string) data_get($content, 'description', $template->description),
            'cta' => (string) data_get($content, 'cta', 'Continue'),
            'thank_you_message' => (string) data_get($content, 'thank_you_message', 'Thank you.'),
            'google_review_url' => '',
            'facebook_review_url' => '',
            'positive_threshold' => (string) data_get($content, 'rules.positive_rating_min', 4),
            'negative_feedback_message' => (string) data_get($content, 'negative_feedback_message', 'Please tell us what happened so we can make it right.'),
            'discount_type' => (string) data_get($settings, 'discount_type', 'percentage'),
            'discount_value' => (string) data_get($settings, 'discount_value', $campaignType === 'coupon' ? '20' : ''),
            'coupon_code' => (string) data_get($settings, 'coupon_code', Str::upper(Str::slug(Str::words($template->name, 2, ''), ''))),
            'expiry_date' => '',
            'usage_limit' => '',
            'terms' => (string) data_get($settings, 'terms', ''),
            'service_name' => (string) data_get($settings, 'service_name', data_get($content, 'campaign_name', $template->name)),
            'duration_minutes' => (string) data_get($settings, 'duration_minutes', 60),
            'price' => (string) data_get($settings, 'price', ''),
            'rating_required' => $campaignType === 'feedback',
            'contact_required' => false,
            'tone' => 'friendly',
            'language' => app()->getLocale() ?: 'en',
        ];
    }

    protected function validateUseStep(int $step): void
    {
        $template = $this->useId ? $this->userVisibleQuery()->whereKey($this->useId)->first() : null;

        if (! $template) {
            return;
        }

        if ($step === 1) {
            $this->validate([
                'useForm.business_id' => ['required', 'integer'],
            ]);

            LocalBusiness::query()
                ->where('user_id', auth()->id())
                ->findOrFail((int) $this->useForm['business_id']);
        }

        if ($step >= 2) {
            $rules = [
                'useForm.business_id' => ['required', 'integer'],
                'useForm.campaign_name' => ['required', 'string', 'max:255'],
                'useForm.headline' => ['required', 'string', 'max:255'],
                'useForm.description' => ['nullable', 'string', 'max:2000'],
                'useForm.cta' => ['required', 'string', 'max:80'],
                'useForm.thank_you_message' => ['required', 'string', 'max:1000'],
            ];

            $campaignType = app(TemplateUseService::class)->campaignTypeFor($template);

            if ($campaignType === 'review') {
                $rules['useForm.google_review_url'] = ['required', 'url', 'max:1000'];
                $rules['useForm.facebook_review_url'] = ['nullable', 'url', 'max:1000'];
                $rules['useForm.positive_threshold'] = ['required', 'integer', 'min:3', 'max:5'];
                $rules['useForm.negative_feedback_message'] = ['required', 'string', 'max:1000'];
            }

            if ($campaignType === 'coupon') {
                $rules['useForm.discount_type'] = ['required', 'string', 'in:percentage,fixed,free_item,custom'];
                $rules['useForm.discount_value'] = ['required', 'string', 'max:80'];
                $rules['useForm.coupon_code'] = ['nullable', 'string', 'max:24'];
                $rules['useForm.expiry_date'] = ['nullable', 'date'];
                $rules['useForm.usage_limit'] = ['nullable', 'integer', 'min:1', 'max:100000'];
                $rules['useForm.terms'] = ['nullable', 'string', 'max:1000'];
            }

            if ($campaignType === 'booking') {
                $rules['useForm.service_name'] = ['required', 'string', 'max:255'];
                $rules['useForm.duration_minutes'] = ['required', 'integer', 'min:5', 'max:1440'];
                $rules['useForm.price'] = ['nullable', 'numeric', 'min:0'];
            }

            $this->validate($rules);
        }
    }

    protected function markCreated(?QrCampaign $campaign, ?LandingPage $landingPage): void
    {
        $this->createdCampaignId = $campaign?->id;
        $this->createdLandingPageId = $landingPage?->id;
        $this->createdRedirectUrl = $campaign ? $this->targetUrlForCampaign($campaign) : (Route::has('portal.landing-pages') ? route('portal.landing-pages') : null);
        $this->createdPublicUrl = $landingPage?->publicUrl() ?: $campaign?->publicUrl();
        $this->createdQrUrl = $campaign && Route::has('qr-campaigns.svg') ? route('qr-campaigns.svg', ['campaign' => $campaign->slug]) : ($landingPage?->qrUrl());
    }

    protected function targetUrlForCampaign(QrCampaign $campaign): ?string
    {
        $routeName = match ($campaign->type) {
            'review' => 'portal.review-booster',
            'booking' => 'portal.booking-pages',
            'coupon' => 'portal.coupon-campaigns',
            'feedback' => 'portal.feedback-forms',
            'lead' => 'portal.lead-forms',
            default => 'portal.qr-campaigns',
        };

        return Route::has($routeName) ? route($routeName) : null;
    }

    protected function contentWriterUrl(MarketingTemplate $template, LocalBusiness $business): string
    {
        if (in_array($template->type, ['email', 'whatsapp', 'automation'], true)) {
            return $this->targetUrlFor($template) ?: '#';
        }

        $content = $template->content ?: [];
        $query = [
            'business_id' => $business->id,
            'type' => $template->goal.'_request',
            'goal' => data_get($content, 'headline', $template->name),
            'offer' => data_get($content, 'description', $template->description),
            'target_customer' => 'Local customers',
            'details' => data_get($content, 'prompt', data_get($content, 'cta', '')),
            'source_type' => 'template',
            'source_id' => $template->id,
        ];

        return Route::has('portal.ai-content')
            ? route('portal.ai-content', array_filter($query, fn ($value) => $value !== null && $value !== ''))
            : '#';
    }

    protected function uniqueTemplateSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'template';
        $slug = $base;
        $counter = 2;

        while (MarketingTemplate::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    protected function ensureSystemTemplates(): void
    {
        foreach ($this->systemTemplates() as $template) {
            MarketingTemplate::query()->updateOrCreate(
                ['slug' => $template['slug'], 'is_system' => true],
                array_merge($template, [
                    'user_id' => null,
                    'created_by' => null,
                    'status' => 'active',
                    'usage_count' => MarketingTemplate::query()->where('slug', $template['slug'])->value('usage_count') ?? 0,
                ])
            );
        }
    }

    protected function systemTemplates(): array
    {
        return array_merge([
            $this->template('get-more-google-reviews', 'Get More Google Reviews', 'campaign', 'general', 'review', 'fa-light fa-star', 'Route 4-5 star customers to Google and collect low-score feedback privately.', 'review', 'Google Review Request', 'How was your visit?', 'Share your experience', 'Thanks for your feedback.'),
            $this->template('facebook-review-request', 'Facebook Review Request', 'campaign', 'general', 'review', 'fa-light fa-thumbs-up', 'Ask happy customers for a Facebook review after a visit.', 'review', 'Facebook Review Request', 'Enjoyed your experience?', 'Leave a Review', 'Thank you for supporting us.'),
            $this->template('low-score-recovery-form', 'Low-Score Recovery Form', 'form', 'general', 'feedback', 'fa-light fa-shield-heart', 'Capture private customer concerns before they become public reviews.', 'feedback', 'Private Feedback', 'Tell us what went wrong', 'Send Feedback', 'We received your feedback.'),
            $this->template('post-visit-rating-funnel', 'Post-Visit Rating Funnel', 'landing_page', 'general', 'review', 'fa-light fa-ranking-star', 'A public page that routes ratings into reviews or private recovery.', 'review', 'Post-Visit Rating Funnel', 'Rate your recent visit', 'Continue', 'Thanks for sharing your rating.'),
            $this->template('weekend-booking-boost', 'Weekend Booking Boost', 'campaign', 'spa', 'booking', 'fa-light fa-calendar-check', 'Promote available weekend appointment slots.', 'booking', 'Weekend Booking Boost', 'Book your weekend appointment', 'Book Now', 'Your request has been sent.'),
            $this->template('free-consultation-booking', 'Free Consultation Booking', 'campaign', 'clinic', 'booking', 'fa-light fa-user-doctor', 'Collect consultation appointment requests from a public booking page.', 'booking', 'Free Consultation', 'Book a free consultation', 'Request Time', 'We will confirm your appointment soon.'),
            $this->template('limited-slots-campaign', 'Limited Slots Campaign', 'campaign', 'salon', 'booking', 'fa-light fa-clock', 'Create urgency around limited service slots.', 'booking', 'Limited Slots', 'Only a few appointment slots left', 'Reserve My Slot', 'Your slot request has been received.'),
            $this->template('first-time-appointment', 'First-Time Appointment', 'landing_page', 'general', 'booking', 'fa-light fa-calendar-plus', 'Help new customers request their first appointment.', 'booking', 'First-Time Appointment', 'Book your first visit', 'Get Started', 'We will contact you soon.'),
            $this->template('20-off-next-visit', '20% Off Next Visit', 'campaign', 'general', 'coupon', 'fa-light fa-ticket', 'Let customers claim a limited-time coupon and return sooner.', 'coupon', '20% Off Next Visit', 'Get 20% off your next visit', 'Claim Coupon', 'Your coupon code is ready.'),
            $this->template('buy-one-get-one-trial', 'Buy 1 Get 1 Trial', 'campaign', 'retail', 'coupon', 'fa-light fa-bags-shopping', 'Promote a simple BOGO offer with redemption tracking.', 'coupon', 'Buy 1 Get 1 Trial', 'Bring a friend and save', 'Claim Offer', 'Show this offer at checkout.'),
            $this->template('birthday-offer', 'Birthday Offer', 'landing_page', 'restaurant', 'coupon', 'fa-light fa-cake-candles', 'Collect birthday coupon claims and customer contacts.', 'coupon', 'Birthday Offer', 'Celebrate with a special offer', 'Get My Birthday Offer', 'Your birthday offer is ready.'),
            $this->template('come-back-coupon', 'Come Back Coupon', 'campaign', 'general', 'retention', 'fa-light fa-rotate-left', 'Win back past customers with a return visit incentive.', 'coupon', 'Come Back Offer', 'We would love to see you again', 'Claim Comeback Offer', 'Your return offer has been saved.'),
            $this->template('post-visit-feedback', 'Post-Visit Feedback', 'campaign', 'general', 'feedback', 'fa-light fa-comment-dots', 'Ask customers for private feedback after a service.', 'feedback', 'Post-Visit Feedback', 'How did we do?', 'Send Feedback', 'Thanks for helping us improve.'),
            $this->template('service-quality-survey', 'Service Quality Survey', 'form', 'clinic', 'feedback', 'fa-light fa-list-check', 'Collect structured feedback on service quality.', 'feedback', 'Service Quality Survey', 'Tell us about your service experience', 'Submit Survey', 'Your response has been recorded.'),
            $this->template('complaint-recovery-form', 'Complaint Recovery Form', 'form', 'general', 'feedback', 'fa-light fa-life-ring', 'Capture issue details and assign recovery follow-up.', 'feedback', 'Complaint Recovery', 'Let us make it right', 'Send Details', 'Our team will review your request.'),
            $this->template('quick-satisfaction-check', 'Quick Satisfaction Check', 'landing_page', 'general', 'feedback', 'fa-light fa-face-smile', 'A short satisfaction page for QR follow-up.', 'feedback', 'Satisfaction Check', 'Quick question about your visit', 'Submit Rating', 'Thank you for your rating.'),
            $this->template('free-consultation-lead-form', 'Free Consultation Lead Form', 'campaign', 'clinic', 'lead', 'fa-light fa-address-card', 'Collect consultation leads with name, phone, email and service interest.', 'lead', 'Free Consultation Lead Form', 'Request a free consultation', 'Request Consultation', 'We have received your request.'),
            $this->template('quote-request-form', 'Quote Request Form', 'form', 'general', 'lead', 'fa-light fa-file-invoice-dollar', 'Capture project or service quote requests.', 'lead', 'Quote Request', 'Request a local service quote', 'Get Quote', 'Your quote request has been sent.'),
            $this->template('new-customer-inquiry', 'New Customer Inquiry', 'landing_page', 'general', 'lead', 'fa-light fa-user-plus', 'A simple lead capture page for first-time customers.', 'lead', 'New Customer Inquiry', 'Tell us what you need', 'Contact Me', 'We will follow up shortly.'),
            $this->template('service-interest-form', 'Service Interest Form', 'form', 'gym', 'lead', 'fa-light fa-clipboard-question', 'Ask prospects which service they are interested in.', 'lead', 'Service Interest', 'Which service are you interested in?', 'Send Request', 'Thanks, we will reach out soon.'),
            $this->template('review-request-message', 'Review Request Message', 'content', 'general', 'review', 'fa-light fa-message-star', 'AI prompt for a friendly review request message.', 'content', 'Review Request Message', 'Ask {customer_name} to review {business_name}', 'Generate Message', 'Message generated.'),
            $this->template('booking-reminder', 'Booking Reminder', 'content', 'general', 'booking', 'fa-light fa-bell', 'AI prompt for appointment reminder copy.', 'content', 'Booking Reminder', 'Remind {customer_name} about {service_name}', 'Generate Reminder', 'Reminder generated.'),
            $this->template('lead-follow-up', 'Lead Follow-up', 'content', 'general', 'lead', 'fa-light fa-paper-plane', 'AI prompt for follow-up messages after a lead submission.', 'content', 'Lead Follow-up', 'Follow up with a new lead for {service_name}', 'Generate Follow-up', 'Follow-up generated.'),
            $this->template('win-back-message', 'Win-back Message', 'content', 'general', 'retention', 'fa-light fa-reply-clock', 'AI prompt for bringing previous customers back.', 'content', 'Win-back Message', 'Invite {customer_name} back with {offer}', 'Generate Copy', 'Win-back copy generated.'),
            $this->template('review-request-email', 'Review Request Email', 'email', 'general', 'review', 'fa-light fa-envelope-open-text', 'Email template that asks recent customers for a public review.', 'review', 'Review Request Email', 'Thanks for visiting {business_name}', 'Leave a Review', 'Thanks for supporting us.'),
            $this->template('coupon-follow-up-email', 'Coupon Follow-up Email', 'email', 'retail', 'coupon', 'fa-light fa-envelope-circle-check', 'Follow up with customers who claimed a coupon but have not returned.', 'coupon', 'Coupon Follow-up Email', 'Your offer is still waiting', 'Use My Offer', 'Your offer has been saved.'),
            $this->template('booking-confirmation-whatsapp', 'Booking Confirmation WhatsApp', 'whatsapp', 'spa', 'booking', 'fa-brands fa-whatsapp', 'Short WhatsApp confirmation for appointment requests.', 'booking', 'Booking WhatsApp', 'Your booking request is received', 'Confirm Booking', 'We will confirm your time soon.'),
            $this->template('post-visit-whatsapp-review', 'Post-Visit WhatsApp Review', 'whatsapp', 'restaurant', 'review', 'fa-brands fa-whatsapp', 'WhatsApp message that sends happy customers to a review page.', 'review', 'WhatsApp Review Request', 'How was your visit today?', 'Share Feedback', 'Thanks for your feedback.'),
            $this->template('lead-nurture-automation', 'Lead Nurture Automation', 'automation', 'general', 'lead', 'fa-light fa-diagram-project', 'Automation template for new lead follow-up across email and WhatsApp.', 'lead', 'Lead Nurture Automation', 'New lead follow-up sequence', 'Start Follow-up', 'Lead workflow is ready.'),
            $this->template('low-rating-recovery-automation', 'Low Rating Recovery Automation', 'automation', 'general', 'feedback', 'fa-light fa-arrows-spin', 'Automation template that opens a recovery task after low feedback.', 'feedback', 'Low Rating Recovery', 'A customer needs recovery follow-up', 'Create Task', 'Recovery workflow is ready.'),
            $this->template('restaurant-lunch-coupon', 'Restaurant Lunch Coupon', 'campaign', 'restaurant', 'coupon', 'fa-light fa-utensils', 'Restaurant pack template for weekday lunch offers.', 'coupon', 'Lunch Coupon Offer', 'Get a weekday lunch reward', 'Claim Lunch Offer', 'Show this offer when you visit.'),
            $this->template('dental-review-booster', 'Dental Review Booster', 'campaign', 'dentist', 'review', 'fa-light fa-tooth', 'Dental pack template for post-appointment review requests.', 'review', 'Dental Review Booster', 'How was your appointment?', 'Leave a Review', 'Thank you for trusting our team.'),
            $this->template('gym-trial-lead-form', 'Gym Trial Lead Form', 'form', 'gym', 'lead', 'fa-light fa-dumbbell', 'Gym pack form for free trial or consultation leads.', 'lead', 'Free Gym Trial', 'Start your free trial', 'Request Trial', 'We will contact you shortly.'),
            $this->template('real-estate-consultation', 'Real Estate Consultation', 'landing_page', 'real_estate', 'lead', 'fa-light fa-house-building', 'Real estate pack page for buyer or seller consultation leads.', 'lead', 'Real Estate Consultation', 'Plan your next property move', 'Book Consultation', 'We will follow up with next steps.'),
        ], $this->industryGeneratedTemplates());
    }

    protected function industryGeneratedTemplates(): array
    {
        $industries = [
            'restaurant' => ['Restaurant', 'fa-light fa-utensils', 'Lunch Coupon', 'Table Feedback', 'Birthday Reward', 'Reservation Boost', 'Referral Dessert'],
            'spa' => ['Spa', 'fa-light fa-spa', 'Massage Booking', 'Post-Service Review', 'Weekend Coupon', 'Relaxation Lead Form', 'Come Back Offer'],
            'salon' => ['Salon', 'fa-light fa-scissors', 'Color Appointment', 'Stylist Review', 'First Visit Coupon', 'Service Feedback', 'Referral Blowout'],
            'clinic' => ['Clinic', 'fa-light fa-user-doctor', 'Consultation Lead', 'Appointment Reminder', 'Patient Feedback', 'Review Booster', 'Follow-up Automation'],
            'dentist' => ['Dental', 'fa-light fa-tooth', 'Cleaning Reminder', 'Patient Review', 'Whitening Coupon', 'Referral Campaign', 'Post-Visit Feedback'],
            'gym' => ['Gym', 'fa-light fa-dumbbell', 'Trial Pass Lead', 'Member Review', 'Class Booking', 'Come Back Offer', 'Referral Challenge'],
            'retail' => ['Retail', 'fa-light fa-bags-shopping', 'Weekend Sale', 'Loyalty Coupon', 'Product Feedback', 'Birthday Reward', 'Referral Offer'],
            'agency' => ['Agency', 'fa-light fa-bullhorn', 'Free Audit Lead', 'Client Review', 'Consultation Booking', 'Proposal Follow-up', 'Referral Intro'],
            'real_estate' => ['Real Estate', 'fa-light fa-house-building', 'Seller Consultation', 'Buyer Lead Form', 'Open House Booking', 'Client Review', 'Referral Campaign'],
            'auto_service' => ['Auto Service', 'fa-light fa-car-wrench', 'Oil Change Coupon', 'Repair Review', 'Service Reminder', 'Inspection Booking', 'Referral Tune-up'],
            'local_service' => ['Local Service', 'fa-light fa-screwdriver-wrench', 'Quote Request', 'Job Review', 'Seasonal Coupon', 'Booking Page', 'Referral Offer'],
        ];

        $templates = [];

        foreach ($industries as $category => [$label, $icon, $leadName, $reviewName, $couponName, $bookingName, $referralName]) {
            $templates[] = $this->template(
                $category.'-'.Str::slug($leadName),
                $label.' '.$leadName,
                'form',
                $category,
                'lead',
                $icon,
                'Capture qualified '.$label.' leads with a focused form.',
                'lead',
                $label.' '.$leadName,
                'Request '.$leadName,
                'Send Request',
                'We received your request.'
            );

            $templates[] = $this->template(
                $category.'-'.Str::slug($reviewName),
                $label.' '.$reviewName,
                'campaign',
                $category,
                'review',
                $icon,
                'Ask recent '.$label.' customers for a review and route feedback privately.',
                'review',
                $label.' '.$reviewName,
                'How was your experience?',
                'Leave a Review',
                'Thanks for sharing your experience.'
            );

            $templates[] = $this->template(
                $category.'-'.Str::slug($couponName),
                $label.' '.$couponName,
                'campaign',
                $category,
                'coupon',
                $icon,
                'Promote a limited '.$label.' offer with QR and claim tracking.',
                'coupon',
                $label.' '.$couponName,
                'Claim your limited-time offer',
                'Claim Offer',
                'Your offer is ready.'
            );

            $templates[] = $this->template(
                $category.'-'.Str::slug($bookingName),
                $label.' '.$bookingName,
                'landing_page',
                $category,
                'booking',
                $icon,
                'Create a booking-focused landing page for '.$label.' customers.',
                'booking',
                $label.' '.$bookingName,
                'Book your next visit',
                'Book Now',
                'We will confirm your request soon.'
            );

            $templates[] = $this->template(
                $category.'-'.Str::slug($referralName),
                $label.' '.$referralName,
                'email',
                $category,
                'referral',
                $icon,
                'Email template for referral and word-of-mouth campaigns.',
                'lead',
                $label.' '.$referralName,
                'Invite a friend to {business_name}',
                'Send Referral',
                'Referral message generated.'
            );
        }

        return $templates;
    }

    protected function template(string $slug, string $name, string $type, string $category, string $goal, string $icon, string $description, string $campaignType, string $campaignName, string $headline, string $cta, string $thanks): array
    {
        return [
            'slug' => $slug,
            'name' => $name,
            'type' => $type,
            'category' => $category,
            'goal' => $goal,
            'description' => $description,
            'icon' => $icon,
            'is_system' => true,
            'source' => 'system',
            'visibility' => 'public',
            'version' => '1.0.0',
            'settings' => [
                'target_module' => $campaignType,
                'creates_campaign' => $type === 'campaign',
                'campaign_type' => $campaignType,
                'page_type' => $campaignType,
                'tracking_goal' => $goal === 'retention' ? 'coupon_claim' : $goal,
                'requires_business' => true,
                'creates_landing_page' => in_array($type, ['campaign', 'landing_page'], true),
                'creates_qr_code' => in_array($type, ['campaign', 'landing_page', 'form'], true),
                'creates_tracking' => in_array($type, ['campaign', 'landing_page', 'form'], true),
                'default_status' => 'published',
            ],
            'content' => [
                'campaign_name' => $campaignName,
                'headline' => $headline,
                'description' => $description,
                'cta' => $cta,
                'benefits' => ['Fast setup', 'Mobile-first page', 'Track every result'],
                'thank_you_message' => $thanks,
                'form_fields' => [
                    ['name' => 'name', 'type' => 'text', 'label' => 'Your name', 'required' => true],
                    ['name' => 'phone', 'type' => 'phone', 'label' => 'Phone number', 'required' => $type !== 'content'],
                    ['name' => 'email', 'type' => 'email', 'label' => 'Email address', 'required' => false],
                ],
                'qr_text' => 'Scan to open this local campaign.',
                'prompt_template' => 'Write a concise local marketing message for {business_name}. Goal: '.$goal.'. Offer: {offer}.',
                'variables' => ['business_name', 'customer_name', 'offer', 'service_name', 'discount_value', 'expiry_date'],
                'landing_page_blocks' => [
                    ['type' => 'hero', 'settings' => ['headline' => $headline, 'cta' => $cta]],
                    ['type' => 'benefits', 'settings' => ['items' => ['Fast setup', 'Mobile-first page', 'Track every result']]],
                    ['type' => 'form', 'settings' => ['submit_button' => $cta, 'thank_you_message' => $thanks]],
                ],
                'email_content' => [
                    'subject' => $campaignName,
                    'body' => $headline."\n\n".$description,
                ],
                'whatsapp_content' => [
                    'message' => $headline.' '.$cta,
                ],
            ],
            'design' => [
                'theme' => 'clean',
                'accent_color' => match ($goal) {
                    'review' => '#d09100',
                    'booking' => '#0f766e',
                    'coupon', 'retention' => '#84a900',
                    'feedback' => '#0891b2',
                    default => '#2563eb',
                },
                'layout' => 'mobile_first',
            ],
            'builder_schema' => [
                'form_builder' => in_array($type, ['campaign', 'landing_page', 'form'], true),
                'landing_page_blocks' => in_array($type, ['campaign', 'landing_page'], true),
                'campaign_settings' => $type === 'campaign',
                'prompt_variables' => in_array($type, ['content', 'email', 'whatsapp'], true),
            ],
        ];
    }
}
