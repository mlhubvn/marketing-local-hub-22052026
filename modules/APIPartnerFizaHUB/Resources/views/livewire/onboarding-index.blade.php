<div class="mx-auto max-w-[84rem] space-y-5">
    <x-ui.sub-header
        :eyebrow="__('Partner integrations')"
        :title="__('FizaHUB onboarding')"
        :description="__('Track and advance FizaHUB partner onboarding requests through the consulting workflow.')"
    />

    @if ($statusMessage)
        <x-ui.alert variant="success">{{ $statusMessage }}</x-ui.alert>
    @endif

    @if ($errorMessage)
        <x-ui.alert variant="danger">{{ $errorMessage }}</x-ui.alert>
    @endif

    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.card>
            <p class="text-[11px] font-semibold uppercase tracking-[0.22em]" style="color: var(--theme-muted-text-color);">{{ __('Total requests') }}</p>
            <p class="mt-2 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($totalCount) }}</p>
        </x-ui.card>
        @foreach ([
            \Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine::AWAITING_CONSULTANT,
            \Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine::CONSULTING,
            \Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine::READY,
        ] as $statusKey)
            <x-ui.card>
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em]" style="color: var(--theme-muted-text-color);">{{ $statusOptions[$statusKey] ?? $statusKey }}</p>
                <p class="mt-2 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ format_number_locale($counts[$statusKey] ?? 0) }}</p>
            </x-ui.card>
        @endforeach
    </div>

    <x-ui.card padding="none">
        <div class="flex flex-wrap items-center gap-3 border-b px-5 py-4" style="border-color: var(--theme-border-color);">
            <div class="min-w-[220px] flex-1">
                <x-ui.input wire:model.live.debounce.400ms="search" name="search" :placeholder="__('Search by business or request id')" />
            </div>
            <div class="min-w-[200px]">
                <x-ui.select wire:model.live="statusFilter" name="statusFilter">
                    <option value="all">{{ __('All statuses') }}</option>
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <x-ui.button type="button" variant="outline" wire:click="resetFilters">{{ __('Reset') }}</x-ui.button>
        </div>

        <div class="divide-y" style="border-color: var(--theme-border-color);">
            @forelse ($requests as $request)
                @php($deletionPreview = $userDeletionPreviews[$request->id] ?? ['id' => null, 'name' => __('Unresolved user'), 'identity' => '—', 'business' => $request->external_business_id, 'resolvable' => false])
                @php($userDeleteConfirmPhrase = $deletionPreview['id'] ? 'XOA USER '.$deletionPreview['id'] : null)
                <div class="flex flex-wrap items-start justify-between gap-4 px-5 py-4">
                    <div class="min-w-[240px] space-y-1">
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">
                                {{ $request->business?->name ?: ($request->payload['business']['name'] ?? $request->external_business_id) }}
                            </p>
                            <x-ui.badge variant="neutral">{{ $request->statusLabel() }}</x-ui.badge>
                        </div>
                        <p class="text-xs" style="color: var(--theme-muted-text-color);">
                            {{ __('Business ID') }}: {{ $request->external_business_id }}
                        </p>
                        <p class="text-xs" style="color: var(--theme-muted-text-color);">
                            {{ __('Request') }}: {{ $request->request_id }}
                        </p>
                        <p class="text-xs" style="color: var(--theme-muted-text-color);">
                            {{ __('Owner') }}: {{ $request->user?->name ?: ($request->payload['owner']['name'] ?? '—') }}
                            @if ($request->assigned_consultant_id)
                                · {{ __('Consultant') }}: {{ $request->consultant?->name ?: '#'.$request->assigned_consultant_id }}
                            @endif
                        </p>
                        <p class="text-xs" style="color: var(--theme-muted-text-color);">
                            {{ __('Package') }}: {{ strtoupper((string) ($request->package_code ?: '—')) }}
                            @if ($request->requested_package_code)
                                · {{ __('Requested package') }}: {{ strtoupper((string) $request->requested_package_code) }}
                            @endif
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        @php($stageOptions = $stageOptionsById[$request->id] ?? [])
                        @if ($stageOptions !== [])
                            <div class="min-w-[200px] max-w-[280px] flex-1 sm:flex-none">
                                <x-ui.select
                                    wire:model="stageSelections.{{ $request->id }}"
                                    name="stageSelections.{{ $request->id }}"
                                    aria-label="{{ __('Choose stage') }}"
                                >
                                    @foreach ($stageOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </x-ui.select>
                            </div>
                            <x-ui.button type="button" size="sm" wire:click="applyStage({{ $request->id }})">
                                {{ __('Send') }}
                            </x-ui.button>
                        @endif

                        @if ($packageOptions !== [])
                            <div class="min-w-[140px] max-w-[180px] flex-1 sm:flex-none">
                                <x-ui.select
                                    wire:model="packageSelections.{{ $request->id }}"
                                    name="packageSelections.{{ $request->id }}"
                                    aria-label="{{ __('Choose package') }}"
                                >
                                    @foreach ($packageOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </x-ui.select>
                            </div>
                            <x-ui.button type="button" size="sm" variant="outline" wire:click="applyPackage({{ $request->id }})">
                                {{ __('Apply package') }}
                            </x-ui.button>
                        @endif

                        <x-ui.button
                            type="button"
                            size="sm"
                            variant="outline"
                            class="!px-2.5"
                            wire:click="resendWebhook({{ $request->id }})"
                            title="{{ __('Resend webhook') }}"
                            aria-label="{{ __('Resend webhook') }}"
                        >
                            <i class="fa-light fa-rotate-right" aria-hidden="true"></i>
                        </x-ui.button>

                        <x-ui.dialog
                            :title="__('Xóa dữ liệu onboarding')"
                            :description="__('Hành động này chỉ xóa dữ liệu kết nối và onboarding FizaHUB. Tài khoản MKT và dữ liệu độc lập của người dùng sẽ được giữ lại.')"
                            width="sm"
                            dismissible
                        >
                            <x-slot:trigger>
                                <x-ui.button
                                    type="button"
                                    size="sm"
                                    variant="outline"
                                    title="{{ __('Xóa dữ liệu onboarding') }}"
                                    aria-label="{{ __('Xóa dữ liệu onboarding') }}"
                                >
                                    {{ __('Xóa dữ liệu onboarding') }}
                                </x-ui.button>
                            </x-slot:trigger>

                            <div class="space-y-2">
                                <label class="text-xs font-medium" style="color: var(--theme-muted-text-color);">
                                    {{ __('Nhập ":phrase" để xác nhận', ['phrase' => 'XOA ONBOARDING']) }}
                                </label>
                                <x-ui.input
                                    type="text"
                                    wire:model.live.debounce.200ms="onboardingDeleteConfirmation.{{ $request->id }}"
                                    autocomplete="off"
                                    placeholder="XOA ONBOARDING"
                                />
                            </div>

                            <x-slot:footer>
                                <div class="flex justify-end gap-3">
                                    <x-ui.button
                                        type="button"
                                        variant="outline"
                                        wire:click="resetOnboardingDeleteConfirmation({{ $request->id }})"
                                        x-on:click="open = false"
                                    >{{ __('Cancel') }}</x-ui.button>
                                    <x-ui.button
                                        type="button"
                                        variant="danger"
                                        wire:click="deleteOnboarding({{ $request->id }})"
                                        x-on:click="open = false"
                                        :disabled="($onboardingDeleteConfirmation[$request->id] ?? '') !== 'XOA ONBOARDING'"
                                    >{{ __('Xóa dữ liệu onboarding') }}</x-ui.button>
                                </div>
                            </x-slot:footer>
                        </x-ui.dialog>

                        <x-ui.dialog
                            :title="__('Xóa User và toàn bộ dữ liệu')"
                            :description="__('Xóa vĩnh viễn tài khoản, dữ liệu SQL, file, tích hợp và lịch sử liên quan. Hành động này không thể hoàn tác.')"
                            width="md"
                            dismissible
                        >
                            <x-slot:trigger>
                                <x-ui.button
                                    type="button"
                                    size="sm"
                                    variant="danger"
                                    :disabled="! $deletionPreview['resolvable']"
                                >
                                    {{ __('Xóa User và toàn bộ dữ liệu') }}
                                </x-ui.button>
                            </x-slot:trigger>

                            <div class="mt-4 space-y-3 rounded-xl border p-4 text-sm" style="border-color: var(--theme-border-color);">
                                <p><span class="font-semibold">{{ __('User') }}:</span> {{ $deletionPreview['name'] }}</p>
                                <p><span class="font-semibold">{{ __('Email/username') }}:</span> {{ $deletionPreview['identity'] }}</p>
                                <p><span class="font-semibold">{{ __('Business') }}:</span> {{ $deletionPreview['business'] }}</p>
                                <p class="font-semibold text-red-600">{{ __('Confirm that this deletes the entire MKT account and all owned data.') }}</p>

                                @if ($userDeleteConfirmPhrase)
                                    <div class="space-y-2">
                                        <label class="text-xs font-medium" style="color: var(--theme-muted-text-color);">
                                            {{ __('Nhập ":phrase" để xác nhận', ['phrase' => $userDeleteConfirmPhrase]) }}
                                        </label>
                                        <x-ui.input
                                            type="text"
                                            wire:model.live.debounce.200ms="userDeleteConfirmation.{{ $request->id }}"
                                            autocomplete="off"
                                            placeholder="{{ $userDeleteConfirmPhrase }}"
                                        />
                                    </div>
                                @endif
                            </div>

                            <x-slot:footer>
                                <div class="flex justify-end gap-3">
                                    <x-ui.button
                                        type="button"
                                        variant="outline"
                                        wire:click="resetUserDeleteConfirmation({{ $request->id }})"
                                        x-on:click="open = false"
                                    >{{ __('Cancel') }}</x-ui.button>
                                    <x-ui.button
                                        type="button"
                                        variant="danger"
                                        wire:click="deleteUserAndData({{ $request->id }})"
                                        x-on:click="open = false"
                                        :disabled="! $deletionPreview['resolvable'] || ($userDeleteConfirmation[$request->id] ?? '') !== $userDeleteConfirmPhrase"
                                    >{{ __('Xóa User và toàn bộ dữ liệu') }}</x-ui.button>
                                </div>
                            </x-slot:footer>
                        </x-ui.dialog>
                    </div>
                </div>
            @empty
                <div class="px-5 py-10 text-center text-sm" style="color: var(--theme-muted-text-color);">
                    {{ __('No onboarding requests found.') }}
                </div>
            @endforelse
        </div>

        @if ($requests->hasPages())
            <div class="border-t px-5 py-4" style="border-color: var(--theme-border-color);">
                {{ $requests->links() }}
            </div>
        @endif
    </x-ui.card>
</div>
