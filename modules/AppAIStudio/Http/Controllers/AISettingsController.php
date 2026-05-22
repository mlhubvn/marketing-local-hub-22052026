<?php

namespace Modules\AppAIStudio\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\AppAIStudio\Models\AIStudioUserSetting;
use Modules\AppAIStudio\Models\AIStudioWorkspaceSetting;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

class AISettingsController extends Controller
{
    public function index(Request $request): View
    {
        [$user, $team, $planOwner] = $this->context();

        $workspaceRecord = AIStudioWorkspaceSetting::query()
            ->ownedBy((int) $planOwner->id)
            ->forTeam($team?->id)
            ->first();

        $userRecord = AIStudioUserSetting::query()->forUser((int) $user->id)->first();

        $defaults = $this->defaults($user);
        $workspaceSettings = array_replace($defaults, (array) ($workspaceRecord?->settings ?? []));
        $userSettings = array_replace($defaults, (array) ($userRecord?->settings ?? []));

        return view('appaistudio::settings.index', [
            'defaults' => $defaults,
            'userSettings' => $userSettings,
            'workspaceSettings' => $workspaceSettings,
            'canManageWorkspace' => (int) ($team?->owner_user_id ?: $user->id) === (int) $user->id,
            'team' => $team,
            'toneOptions' => $this->toneOptions(),
            'imageStyleOptions' => $this->imageStyleOptions(),
            'imageRatioOptions' => $this->imageRatioOptions(),
        ]);
    }

    public function updateUser(Request $request): RedirectResponse
    {
        [$user] = $this->context();

        $validated = $this->validatePayload($request);

        AIStudioUserSetting::query()->updateOrCreate(
            ['user_id' => $user->id],
            ['settings' => $validated]
        );

        return back()->with('status', __('Your AI settings were updated successfully.'));
    }

    public function updateWorkspace(Request $request): RedirectResponse
    {
        [$user, $team, $planOwner] = $this->context();

        abort_unless((int) ($team?->owner_user_id ?: $user->id) === (int) $user->id, 403);

        $validated = $this->validatePayload($request, workspace: true);

        AIStudioWorkspaceSetting::query()->updateOrCreate(
            [
                'owner_user_id' => (int) $planOwner->id,
                'team_id' => $team?->id,
            ],
            ['settings' => $validated]
        );

        return back()->with('status', __('Workspace AI settings were updated successfully.'));
    }

    protected function validatePayload(Request $request, bool $workspace = false): array
    {
        $languageCodes = collect(world_languages())->pluck('code')->map(fn ($code) => (string) $code)->all();

        $validated = $request->validate([
            'default_language' => ['nullable', 'string', 'max:12', Rule::in($languageCodes)],
            'default_tone' => ['nullable', 'string', 'max:40'],
            'image_style' => ['nullable', 'string', 'max:40'],
            'image_ratio' => ['nullable', 'string', 'max:20'],
            'planner_days' => ['nullable', 'integer', 'min:3', 'max:31'],
            'brand_voice' => $workspace ? ['nullable', 'string', 'max:5000'] : ['nullable'],
            'banned_words' => $workspace ? ['nullable', 'string', 'max:1000'] : ['nullable'],
            'preferred_cta_style' => $workspace ? ['nullable', 'string', 'max:120'] : ['nullable'],
        ]);

        return array_replace($this->defaults(auth()->user()), $validated);
    }

    protected function defaults($user): array
    {
        $defaultLanguage = (string) ($user?->locale ?: app()->getLocale() ?: 'en');

        return [
            'default_language' => $defaultLanguage,
            'default_tone' => 'professional',
            'image_style' => 'product',
            'image_ratio' => '1:1',
            'planner_days' => 14,
            'brand_voice' => '',
            'banned_words' => '',
            'preferred_cta_style' => '',
        ];
    }

    protected function toneOptions(): array
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

    protected function imageStyleOptions(): array
    {
        return [
            ['value' => 'product', 'label' => __('Product')],
            ['value' => 'lifestyle', 'label' => __('Lifestyle')],
            ['value' => 'editorial', 'label' => __('Editorial')],
            ['value' => 'minimal', 'label' => __('Minimal')],
            ['value' => 'cinematic', 'label' => __('Cinematic')],
        ];
    }

    protected function imageRatioOptions(): array
    {
        return [
            ['value' => '1:1', 'label' => '1:1'],
            ['value' => '4:5', 'label' => '4:5'],
            ['value' => '16:9', 'label' => '16:9'],
            ['value' => '9:16', 'label' => '9:16'],
        ];
    }

    protected function context(): array
    {
        $user = auth()->user();
        $team = TeamWorkspaceAccess::activeTeam($user);
        $planOwner = $team?->owner ?: $user;

        abort_unless(TeamWorkspaceAccess::teamHasModule($team, 'publishing'), 404);
        abort_unless(! $planOwner?->plan || $planOwner->hasPlanFeature('ai_studio'), 404);

        return [$user, $team, $planOwner];
    }
}
