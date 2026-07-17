<div>
    <x-ui.card>
        <p class="text-[11px] font-semibold uppercase tracking-[0.22em]" style="color: var(--theme-muted-text-color);">{{ __('FizaHUB onboarding') }}</p>

        @if (! $onboarding)
            <p class="mt-3 text-sm leading-7" style="color: var(--theme-muted-text-color);">
                {{ __('No FizaHUB onboarding request is linked to this user.') }}
            </p>
        @else
            <div class="mt-3 flex items-center gap-2">
                <h3 class="text-[1.2rem] font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">{{ $onboarding->statusLabel() }}</h3>
                <x-ui.badge variant="neutral">{{ $onboarding->current_step }}</x-ui.badge>
            </div>

            <dl class="mt-4 grid gap-2 text-sm">
                <div class="flex justify-between gap-3">
                    <dt style="color: var(--theme-muted-text-color);">{{ __('Business ID') }}</dt>
                    <dd class="font-medium" style="color: var(--theme-header-text-color);">{{ $onboarding->external_business_id }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt style="color: var(--theme-muted-text-color);">{{ __('Package') }}</dt>
                    <dd class="font-medium" style="color: var(--theme-header-text-color);">{{ $onboarding->package_code ?: '—' }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt style="color: var(--theme-muted-text-color);">{{ __('Request') }}</dt>
                    <dd class="font-medium" style="color: var(--theme-header-text-color);">{{ $onboarding->request_id }}</dd>
                </div>
            </dl>

            @if ($statusMessage)
                <p class="mt-4 text-sm font-medium" style="color: var(--theme-success-color);">{{ $statusMessage }}</p>
            @endif

            @if ($errorMessage)
                <p class="mt-4 text-sm font-medium" style="color: var(--theme-danger-color);">{{ $errorMessage }}</p>
            @endif

            <div class="mt-5 flex flex-wrap gap-2">
                @if (in_array($onboarding->status, [
                    \Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine::AWAITING_CONSULTANT,
                    \Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine::NEEDS_REVIEW,
                ], true))
                    <x-ui.button size="sm" variant="outline" wire:click="markContacted">{{ __('Contacted') }}</x-ui.button>
                @endif
                @if ($onboarding->status === \Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine::CONFIGURING)
                    <x-ui.button size="sm" wire:click="markReady">{{ __('Mark ready') }}</x-ui.button>
                @endif
                @if ($onboarding->status === \Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine::READY)
                    <x-ui.button size="sm" wire:click="markCompleted">{{ __('Complete') }}</x-ui.button>
                @endif
                <x-ui.button size="sm" variant="outline" href="{{ route('admin-fizahub.onboarding') }}" wire:navigate>{{ __('Open onboarding board') }}</x-ui.button>
            </div>
        @endif
    </x-ui.card>
</div>
