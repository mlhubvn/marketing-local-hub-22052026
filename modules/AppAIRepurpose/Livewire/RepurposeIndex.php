<?php

namespace Modules\AppAIRepurpose\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppAIStudio\Models\AIPromptHistory;
use Modules\AppAIStudio\Support\AiContentStudioService;
use Modules\AppAIStudio\Support\AIStudioAccess;
use Throwable;

#[Title('AI Repurpose')]
class RepurposeIndex extends Component
{
    use AIStudioAccess;

    public string $sourceContent = '';

    public string $tone = 'professional';

    public string $language = 'vi';

    public array $result = [];

    public function mount(): void
    {
        $this->tone = (string) $this->aiStudioSetting('default_tone', $this->tone);
        $this->language = (string) $this->aiStudioSetting('default_language', $this->language);
    }

    public function repurpose(AiContentStudioService $studio): void
    {
        abort_unless($this->aiStudioFeatureEnabled('ai_studio_repurpose'), 404);

        $validated = $this->validate([
            'sourceContent' => ['required', 'string', 'max:12000'],
        ]);

        try {
            $planOwner = $this->aiStudioPlanOwner();
            if (function_exists('credit_service')) {
                credit_service()->ensureCanConsume($planOwner, 'ai_studio_repurpose_content');
            }

            $this->result = $studio->repurposeContent($validated['sourceContent'], [], [
                'tone' => $this->tone,
                'language' => $this->language,
                ...$this->aiStudioWorkspacePromptConfig(),
            ]);

            $studio->recordPromptHistory(
                auth()->user(),
                'repurpose',
                $validated['sourceContent'],
                [
                    'tone' => $this->tone,
                    'language' => $this->language,
                ],
                $this->result,
                [
                    'title' => __('Repurpose - :tone - :language', ['tone' => ucfirst($this->tone), 'language' => strtoupper($this->language)]),
                    'language' => $this->language,
                    'tone' => $this->tone,
                ],
            );

            if (function_exists('consume_credits')) {
                consume_credits($planOwner, 'ai_studio_repurpose_content', [
                    'feature' => 'ai-studio.repurpose',
                    'metadata' => [
                        'language' => $this->language,
                        'tone' => $this->tone,
                    ],
                ]);
            }
        } catch (Throwable $exception) {
            $this->addError('sourceContent', $exception->getMessage());
        }
    }

    public function loadPromptHistory(int $historyId): void
    {
        $history = $this->promptHistoryQuery()->whereKey($historyId)->first();

        if (! $history) {
            return;
        }

        $this->sourceContent = (string) $history->prompt;
        $this->tone = (string) data_get($history->input_payload, 'tone', $this->tone);
        $this->language = (string) data_get($history->input_payload, 'language', $this->language);
        $this->result = is_array($history->output_payload) ? $history->output_payload : [];
    }

    public function render(): View
    {
        abort_unless($this->aiStudioFeatureEnabled('ai_studio_repurpose'), 404);

        return view('appairepurpose::index', [
            'languageLabel' => collect(world_languages())
                ->firstWhere('code', $this->language)['name']
                ?? strtoupper((string) $this->language),
            'toneOptions' => [
                ['value' => 'professional', 'label' => __('Professional')],
                ['value' => 'friendly', 'label' => __('Friendly')],
                ['value' => 'sales', 'label' => __('Sales')],
                ['value' => 'educational', 'label' => __('Educational')],
                ['value' => 'bold', 'label' => __('Bold')],
                ['value' => 'casual', 'label' => __('Casual')],
            ],
            'creditPreview' => $this->aiStudioCreditPreview('ai_studio_repurpose_content'),
            'promptHistory' => $this->promptHistoryQuery()->latest('id')->limit(8)->get(),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('AI Repurpose'),
        ]);
    }

    protected function promptHistoryQuery()
    {
        return AIPromptHistory::query()
            ->ownedBy($this->workspaceOwnerUserId())
            ->where('module', 'repurpose');
    }
}
