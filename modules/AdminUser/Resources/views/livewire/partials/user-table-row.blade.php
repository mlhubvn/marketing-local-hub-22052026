@php
    $needsAttention = blank($user->email) || blank($user->role_id) || blank($user->plan_id);
@endphp
<x-ui.table-row wire:key="user-row-{{ $user->id }}">
    <x-ui.table-cell>
        <x-ui.checkbox x-model="selectedIds" value="{{ (string) $user->id }}" minimal />
    </x-ui.table-cell>
    <x-ui.table-cell>
        <div class="flex items-center gap-3">
            <div class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[0.9rem] border text-xs font-semibold uppercase" style="border-color: rgba(var(--theme-accent-rgb),0.14); background: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">
                {{ $user->initials() }}
            </div>
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <p class="truncate font-semibold" style="color: var(--theme-header-text-color);">{{ $user->name }}</p>
                    <x-ui.badge :variant="$needsAttention ? 'danger' : 'success'">{{ $needsAttention ? __('Needs review') : __('Ready') }}</x-ui.badge>
                </div>
                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('ID') }} #{{ $user->id }} / {{ strtoupper($user->locale ?: 'UNSET') }}</p>
            </div>
        </div>
    </x-ui.table-cell>
    <x-ui.table-cell>
        <div class="space-y-1">
            <p class="font-medium" style="color: var(--theme-header-text-color);">{{ $user->role?->name ?? __('No role assigned') }}</p>
            <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ '@'.$user->username }}</p>
        </div>
    </x-ui.table-cell>
    <x-ui.table-cell>
        <div class="space-y-1">
            <p class="font-medium" style="color: var(--theme-header-text-color);">{{ $user->plan?->name ?? __('No plan') }}</p>
            <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ $user->plan_expires_at ? __('Expires').' '.format_date_locale($user->plan_expires_at) : __('No expiry set') }}</p>
        </div>
    </x-ui.table-cell>
    <x-ui.table-cell>
        <div class="space-y-1">
            <p class="font-medium break-all" style="color: var(--theme-header-text-color);">{{ $user->email ?: '-' }}</p>
            <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ filled($user->email) ? __('Primary contact channel') : __('No email attached') }}</p>
        </div>
    </x-ui.table-cell>
    <x-ui.table-cell>
        <div class="space-y-1">
            <p class="font-medium" style="color: var(--theme-header-text-color);">{{ $user->created_at ? format_date_locale($user->created_at) : '-' }}</p>
            <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ $user->created_at?->diffForHumans() ?? '-' }}</p>
        </div>
    </x-ui.table-cell>
    <x-ui.table-cell>
        <div class="flex items-center justify-end pr-4">
            <x-ui.dropdown-menu align="right" width="auto">
                <x-slot:trigger>
                    <x-ui.button type="button" variant="outline" size="sm">{{ __('Actions') }} <i class="fa-light fa-chevron-down text-[10px]"></i></x-ui.button>
                </x-slot:trigger>

                @if ($authUser?->canImpersonate() && $user->canBeImpersonatedBy($authUser))
                    <x-ui.dropdown-menu-item class="w-full pr-6" icon="fa-light fa-user-secret" x-on:click.stop="open = false; document.getElementById('user-impersonate-trigger-{{ $user->id }}')?.click()">
                        {{ __('View as user') }}
                    </x-ui.dropdown-menu-item>
                @endif

                <x-ui.dropdown-menu-item href="{{ route('admin-users.edit', $user) }}" icon="fa-light fa-pen-to-square" class="w-full pr-6" wire:navigate>
                    {{ __('Edit') }}
                </x-ui.dropdown-menu-item>

                <x-ui.dropdown-menu-divider />

                <x-ui.dropdown-menu-item class="w-full pr-6" icon="fa-light fa-trash-can" variant="danger" x-on:click.stop="open = false; document.getElementById('user-delete-trigger-{{ $user->id }}')?.click()">
                    {{ __('Delete') }}
                </x-ui.dropdown-menu-item>
            </x-ui.dropdown-menu>
        </div>

        @if ($authUser?->canImpersonate() && $user->canBeImpersonatedBy($authUser))
            <x-ui.dialog :title="__('View as this user?')" :description="__('You will temporarily browse the app using this account until you return to admin mode.')" width="sm" dismissible>
                <x-slot:trigger><button id="user-impersonate-trigger-{{ $user->id }}" type="button" class="hidden"></button></x-slot:trigger>
                <x-slot:footer>
                    <div class="flex justify-end gap-3">
                        <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                        <form method="POST" action="{{ route('admin-users.impersonate', $user) }}">
                            @csrf
                            <x-ui.button type="submit">{{ __('Continue') }}</x-ui.button>
                        </form>
                    </div>
                </x-slot:footer>
            </x-ui.dialog>
        @endif

        <x-ui.dialog :title="__('Xóa User và toàn bộ dữ liệu')" :description="__('Xóa vĩnh viễn tài khoản, dữ liệu SQL, file, tích hợp và lịch sử liên quan. Hành động này không thể hoàn tác.')" width="sm" dismissible>
            <x-slot:trigger><button id="user-delete-trigger-{{ $user->id }}" type="button" class="hidden"></button></x-slot:trigger>
            @php($desktopUserDeletePhrase = 'XOA USER '.$user->id)
            <div class="space-y-2">
                <label class="text-xs font-medium" style="color: var(--theme-muted-text-color);">
                    {{ __('Nhập ":phrase" để xác nhận', ['phrase' => $desktopUserDeletePhrase]) }}
                </label>
                <x-ui.input
                    type="text"
                    wire:model.live.debounce.200ms="deleteConfirmation.{{ $user->id }}"
                    autocomplete="off"
                    placeholder="{{ $desktopUserDeletePhrase }}"
                />
            </div>
            <x-slot:footer>
                <div class="flex justify-end gap-3">
                    <x-ui.button type="button" variant="outline" wire:click="resetDeleteConfirmation({{ $user->id }})" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                    <x-ui.button
                        type="button"
                        variant="danger"
                        wire:click="deleteUser({{ $user->id }})"
                        :disabled="($deleteConfirmation[$user->id] ?? '') !== $desktopUserDeletePhrase"
                    >{{ __('Xóa User và toàn bộ dữ liệu') }}</x-ui.button>
                </div>
            </x-slot:footer>
        </x-ui.dialog>
    </x-ui.table-cell>
</x-ui.table-row>
