@component(theme_view('layouts.app', 'app'), ['title' => __('AI Settings')])
    @php
        $resolveLanguageLabel = function ($code) {
            $code = (string) $code;

            return collect(world_languages())->firstWhere('code', $code)['name'] ?? strtoupper($code);
        };

        $toneLabel = collect($toneOptions)->firstWhere('value', $userSettings['default_tone'] ?? $defaults['default_tone'])['label'] ?? ucfirst((string) ($userSettings['default_tone'] ?? $defaults['default_tone']));
        $workspaceToneLabel = collect($toneOptions)->firstWhere('value', $workspaceSettings['default_tone'] ?? $defaults['default_tone'])['label'] ?? ucfirst((string) ($workspaceSettings['default_tone'] ?? $defaults['default_tone']));
    @endphp

    <div class="mx-auto w-full max-w-[88rem] space-y-6">
        <section class="rounded-[1.25rem] border p-6 shadow-sm md:p-7" style="border-color: rgba(var(--theme-border-color-rgb), .58); background: linear-gradient(135deg, rgba(var(--theme-accent-rgb), .11), transparent 42%), var(--theme-surface-base);">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb), .12); color: var(--theme-accent);">
                        <i class="fa-light fa-sparkles"></i>
                    </span>
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('AI Studio') }}</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ __('AI Settings') }}</h1>
                        <p class="mt-3 max-w-3xl text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Set the defaults AI tools use before each module adds its own task-specific prompt.') }}</p>
                    </div>
                </div>

                <x-ui.button :href="route('portal.ai-studio.prompt-history')" variant="outline" wire:navigate>
                    <i class="fa-light fa-clock-rotate-left"></i>
                    {{ __('Prompt History') }}
                </x-ui.button>
            </div>
        </section>

        @if (session('status'))
            <div class="rounded-xl border px-4 py-3 text-sm font-medium" style="border-color: rgba(var(--theme-success-color-rgb), .28); background-color: rgba(var(--theme-success-color-rgb), .08); color: var(--theme-success-color);">
                {{ session('status') }}
            </div>
        @endif

        <section class="grid gap-5 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
            <form method="POST" action="{{ route('portal.ai-studio.settings.user') }}" class="overflow-hidden rounded-[1rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: var(--theme-surface-base);">
                @csrf
                @method('PUT')

                <div class="border-b p-5" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('My defaults') }}</p>
                            <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Used only for your own AI Studio sessions.') }}</p>
                        </div>
                        <x-ui.badge variant="neutral">{{ __('User') }}</x-ui.badge>
                    </div>
                </div>

                <div class="space-y-5 p-5">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: rgba(var(--theme-border-color-rgb), .025);">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Language') }}</p>
                            <p class="mt-2 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ $resolveLanguageLabel($userSettings['default_language'] ?? $defaults['default_language']) }}</p>
                        </div>
                        <div class="rounded-xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: rgba(var(--theme-border-color-rgb), .025);">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Tone') }}</p>
                            <p class="mt-2 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ $toneLabel }}</p>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <x-ai.language-field name="default_language" :value="$userSettings['default_language']" />
                        <x-ai.tone-field name="default_tone" :value="$userSettings['default_tone'] ?? $defaults['default_tone']" :options="$toneOptions" />
                        <x-ai.planner-days-field :value="$userSettings['planner_days']" />
                    </div>

                    <div class="border-t pt-5" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Image defaults') }}</p>
                        <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Used when generating new visual assets.') }}</p>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <x-ai.image-style-field :value="$userSettings['image_style'] ?? $defaults['image_style']" :options="$imageStyleOptions" />
                            <x-ai.image-ratio-field :value="$userSettings['image_ratio'] ?? $defaults['image_ratio']" :options="$imageRatioOptions" />
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <x-ui.button type="submit">
                            <i class="fa-light fa-floppy-disk"></i>
                            {{ __('Save my defaults') }}
                        </x-ui.button>
                    </div>
                </div>
            </form>

            <section class="overflow-hidden rounded-[1rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: var(--theme-surface-base);">
                <div class="border-b p-5" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Workspace rules') }}</p>
                            <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Shared prompt rules for captions, repurpose, planner, and review replies.') }}</p>
                        </div>
                        @if ($team)
                            <x-ui.badge variant="neutral">{{ $team->name }}</x-ui.badge>
                        @else
                            <x-ui.badge variant="neutral">{{ __('Personal workspace') }}</x-ui.badge>
                        @endif
                    </div>
                </div>

                @if ($canManageWorkspace)
                    <form method="POST" action="{{ route('portal.ai-studio.settings.workspace') }}" class="space-y-5 p-5">
                        @csrf
                        @method('PUT')

                        <div class="grid gap-4 md:grid-cols-2">
                            <x-ai.language-field name="default_language" :label="__('Workspace Language')" :value="$workspaceSettings['default_language']" />
                            <x-ai.tone-field name="default_tone" :label="__('Workspace Tone')" :value="$workspaceSettings['default_tone'] ?? $defaults['default_tone']" :options="$toneOptions" />
                        </div>

                        <div class="rounded-xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: rgba(var(--theme-border-color-rgb), .025);">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Current workspace baseline') }}</p>
                                    <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $resolveLanguageLabel($workspaceSettings['default_language'] ?? $defaults['default_language']) }} · {{ $workspaceToneLabel }}</p>
                                </div>
                                <span class="text-xs" style="color: var(--theme-muted-text-color);">{{ __('User defaults override this.') }}</span>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <x-ai.brand-voice-field :value="$workspaceSettings['brand_voice'] ?? ''" />
                            <x-ai.cta-style-field :value="$workspaceSettings['preferred_cta_style'] ?? ''" />
                            <x-ai.banned-words-field :value="$workspaceSettings['banned_words'] ?? ''" />
                        </div>

                        <div class="flex justify-end">
                            <x-ui.button type="submit">
                                <i class="fa-light fa-floppy-disk"></i>
                                {{ __('Save workspace rules') }}
                            </x-ui.button>
                        </div>
                    </form>
                @else
                    <div class="p-5">
                        <x-ui.empty
                            icon="fa-light fa-lock-keyhole"
                            :title="__('Workspace defaults are restricted')"
                            :description="__('Only the workspace owner can edit shared AI rules for this team.')"
                        />
                    </div>
                @endif
            </section>
        </section>
    </div>
@endcomponent
