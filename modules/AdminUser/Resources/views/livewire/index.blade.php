@php
        $userCollection = $users->getCollection();
        $loadedUsers = $userCollection->count();
        $totalUsers = $users->total();
        $latestUser = $userCollection->first();
        $recentUsers = $userCollection->filter(fn ($user) => optional($user->created_at)?->isAfter(now()->subDays(7)))->count();
        $verifiedUsers = $userCollection->filter(fn ($user) => filled($user->email))->count();
        $usersWithRoles = $userCollection->filter(fn ($user) => filled($user->role_id))->count();
        $usersWithPlans = $userCollection->filter(fn ($user) => filled($user->plan_id))->count();
        $priorityUsers = $userCollection->filter(fn ($user) => blank($user->email) || blank($user->role_id) || blank($user->plan_id))->count();
        $localeCoverage = $userCollection->pluck('locale')->filter()->unique()->count();
        $completionRate = $loadedUsers > 0 ? (int) round(($verifiedUsers / $loadedUsers) * 100) : 0;
        $roleCoverage = $loadedUsers > 0 ? (int) round(($usersWithRoles / $loadedUsers) * 100) : 0;
        $planCoverage = $loadedUsers > 0 ? (int) round(($usersWithPlans / $loadedUsers) * 100) : 0;
        $authUser = auth()->user();

        $metricCards = [
            ['label' => __('Visible users'), 'value' => format_number_locale($totalUsers), 'description' => __('Users matching the current filters.'), 'tone' => 'var(--theme-accent)', 'progress' => 100],
            ['label' => __('Recent signups'), 'value' => format_number_locale($recentUsers), 'description' => __('Created in the last 7 days.'), 'tone' => '#10b981', 'progress' => $loadedUsers > 0 ? max(8, (int) round(($recentUsers / $loadedUsers) * 100)) : 8],
            ['label' => __('Role coverage'), 'value' => format_percent_locale($roleCoverage), 'description' => format_number_locale($usersWithRoles).' '.__('mapped accounts'), 'tone' => '#f59e0b', 'progress' => max(8, $roleCoverage)],
            ['label' => __('Plan coverage'), 'value' => format_percent_locale($planCoverage), 'description' => format_number_locale($usersWithPlans).' '.__('accounts with plans'), 'tone' => '#64748b', 'progress' => max(8, $planCoverage)],
        ];

        $controlLinks = [
            ['title' => __('Roles'), 'description' => __('Permission groups and access mapping'), 'href' => route('admin-user-roles.index'), 'icon' => 'fa-light fa-shield-keyhole'],
            ['title' => __('Teams'), 'description' => __('Ownership structure and member allocation'), 'href' => route('admin-user-teams.index'), 'icon' => 'fa-light fa-people-group'],
            ['title' => __('Reports'), 'description' => __('Growth, verification, and distribution signals'), 'href' => route('admin-user-report.index'), 'icon' => 'fa-light fa-chart-line'],
            ['title' => __('Audit logs'), 'description' => __('Identity and admin actions'), 'href' => route('admin-user-logs.index'), 'icon' => 'fa-light fa-clipboard-list-check'],
        ];

        $sortIcon = function (string $key) use ($filters): string {
            if (($filters['sort'] ?? 'created') !== $key) {
                return 'fa-light fa-arrows-up-down';
            }

            return ($filters['direction'] ?? 'desc') === 'asc'
                ? 'fa-light fa-arrow-up-wide-short'
                : 'fa-light fa-arrow-down-wide-short';
        };

    @endphp

<div class="mx-auto max-w-[88rem] space-y-6">

        <x-ui.page-hero
            :eyebrow="__('Access workspace')"
            :title="__('Admin Users')"
            :description="__('Identity review, role mapping, plan assignment, and account operations in one compact workspace.')"
            :count="$totalUsers"
            icon="fa-light fa-users-gear"
        >
            <x-slot:actions>
                <x-ui.button href="{{ route('admin-users.create') }}" wire:navigate>{{ __('Create user') }}</x-ui.button>
                <x-ui.button href="{{ route('admin-user-report.index') }}" variant="outline" wire:navigate>{{ __('Reports') }}</x-ui.button>
                <x-ui.button href="{{ route('dashboard') }}" variant="outline" wire:navigate>{{ __('Dashboard') }}</x-ui.button>
            </x-slot:actions>
        </x-ui.page-hero>

        <x-ui.metric-strip :items="$metricCards" :show-icons="false" columns="md:grid-cols-2 xl:grid-cols-4" />

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($controlLinks as $link)
                <a
                    href="{{ $link['href'] }}"
                    wire:navigate
                    class="rounded-[1rem] border p-5 transition hover:-translate-y-0.5 hover:shadow-[0_18px_34px_-30px_rgba(15,23,42,0.18)]"
                    style="border-color: var(--theme-border-color); background: var(--theme-surface-base);"
                >
                    <div class="inline-flex h-11 w-11 items-center justify-center rounded-[0.9rem] border text-sm" style="border-color: rgba(var(--theme-accent-rgb),0.14); background: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">
                        <i class="{{ $link['icon'] }}"></i>
                    </div>
                    <p class="mt-4 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $link['title'] }}</p>
                    <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $link['description'] }}</p>
                </a>
            @endforeach
        </div>

    <div
        x-data="{
            selectedIds: $wire.entangle('selectedUserIds').live,
            get selectedCount() {
                return this.selectedIds.length;
            },
            toggleAll(event) {
                this.selectedIds = event.target.checked ? @js($users->pluck('id')->map(fn ($id) => (string) $id)->values()->all()) : [];
            },
        }"
    >
        <x-ui.datatable-shell
                :title="__('User directory')"
                :info="__('Identity, access, plan coverage, and contact posture for recent backend users.')"
                header-class="py-4"
                eyebrow-class="text-[10px] tracking-[0.2em]"
                title-class="mt-1 text-[1.15rem] tracking-[-0.025em]"
                description-class="mt-1 leading-6"
                :footer-text="$users->hasPages()
                    ? null
                    : ($users->total() > 0
                        ? __('Showing :from to :to of :total records', ['from' => $users->firstItem(), 'to' => $users->lastItem(), 'total' => $users->total()])
                        : __('No records found.'))"
            >
            <div class="border-b px-6 py-5" style="border-color: var(--theme-border-color);">
                <div class="flex flex-col gap-4">
                    <div class="grid gap-3 xl:grid-cols-[minmax(0,1.5fr)_240px_auto]">
                        <x-ui.input
                            wire:model.live.debounce.300ms="q"
                            :label="__('Search')"
                            :placeholder="__('Name, username, or email...')"
                        />

                        <x-ui.select wire:model.live="status" :label="__('Filter')">
                            <option value="all">{{ __('All users') }}</option>
                            <option value="review">{{ __('Need review') }}</option>
                            <option value="ready">{{ __('Ready only') }}</option>
                        </x-ui.select>

                        <div class="flex flex-wrap items-end gap-3 xl:justify-end">
                            <x-ui.button type="button" wire:click="$refresh">{{ __('Apply') }}</x-ui.button>
                            <x-ui.button type="button" variant="outline" wire:click="resetFilters">{{ __('Reset') }}</x-ui.button>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                        <div class="flex flex-wrap items-center gap-3">
                            <x-ui.badge variant="success">{{ format_percent_locale($completionRate) }} {{ __('email-ready') }}</x-ui.badge>
                            <x-ui.badge variant="warning">{{ format_number_locale($priorityUsers) }} {{ __('need review') }}</x-ui.badge>
                            <x-ui.badge variant="primary">{{ format_number_locale($localeCoverage) }} {{ __('locales') }}</x-ui.badge>
                            <x-ui.badge variant="neutral">{{ format_number_locale($loadedUsers) }} {{ __('loaded') }}</x-ui.badge>
                        </div>

                        <div class="flex flex-wrap items-center gap-3 xl:justify-end">
                            <span class="text-xs" style="color: var(--theme-muted-text-color);" x-text="selectedCount > 0 ? `${selectedCount} {{ __('selected') }}` : `{{ __('No selection') }}`"></span>
                            @if ($latestUser?->created_at)
                                <span class="text-xs" style="color: var(--theme-muted-text-color);">{{ __('Latest') }}: {{ format_datetime_locale($latestUser->created_at) }}</span>
                            @endif

                            <x-ui.dialog :title="__('Delete selected users?')" :description="__('This permanently removes every selected account except the one currently signed in. This action cannot be undone.')" width="sm" dismissible>
                                <x-slot:trigger>
                                    <x-ui.button type="button" variant="danger" x-bind:disabled="selectedCount === 0">
                                        {{ __('Delete selected') }}
                                    </x-ui.button>
                                </x-slot:trigger>

                                <x-slot:footer>
                                    <div class="flex justify-end gap-3">
                                        <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                                        <x-ui.button type="button" variant="danger" wire:click="deleteSelectedUsers">{{ __('Delete') }}</x-ui.button>
                                    </div>
                                </x-slot:footer>
                            </x-ui.dialog>
                        </div>
                    </div>
                </div>
            </div>

                <div class="space-y-3 px-6 py-5 md:hidden">
                @forelse ($users as $user)
                    @include('adminuser::livewire.partials.user-mobile-card', ['user' => $user, 'authUser' => $authUser])
                @empty
                    <div class="py-10 text-center" style="color: var(--theme-muted-text-color);">{{ __('No users found.') }}</div>
                @endforelse
            </div>

            <div class="hidden md:block">
                <x-ui.table class="rounded-none border-0 shadow-none">
                    <x-ui.table-head>
                        <x-ui.table-cell head class="w-12">
                            <x-ui.checkbox
                                id="user-bulk-select-all"
                                minimal
                                x-on:change="toggleAll($event)"
                            />
                        </x-ui.table-cell>
                        <x-ui.table-cell head>
                            <button type="button" wire:click="sortBy('user')" class="inline-flex items-center gap-2 transition hover:opacity-80">
                                <span>{{ __('User') }}</span>
                                <i class="{{ $sortIcon('user') }} text-[11px]"></i>
                            </button>
                        </x-ui.table-cell>
                        <x-ui.table-cell head>
                            <button type="button" wire:click="sortBy('access')" class="inline-flex items-center gap-2 transition hover:opacity-80">
                                <span>{{ __('Access') }}</span>
                                <i class="{{ $sortIcon('access') }} text-[11px]"></i>
                            </button>
                        </x-ui.table-cell>
                        <x-ui.table-cell head>
                            <button type="button" wire:click="sortBy('plan')" class="inline-flex items-center gap-2 transition hover:opacity-80">
                                <span>{{ __('Plan') }}</span>
                                <i class="{{ $sortIcon('plan') }} text-[11px]"></i>
                            </button>
                        </x-ui.table-cell>
                        <x-ui.table-cell head>
                            <button type="button" wire:click="sortBy('contact')" class="inline-flex items-center gap-2 transition hover:opacity-80">
                                <span>{{ __('Contact') }}</span>
                                <i class="{{ $sortIcon('contact') }} text-[11px]"></i>
                            </button>
                        </x-ui.table-cell>
                        <x-ui.table-cell head>
                            <button type="button" wire:click="sortBy('created')" class="inline-flex items-center gap-2 transition hover:opacity-80">
                                <span>{{ __('Created') }}</span>
                                <i class="{{ $sortIcon('created') }} text-[11px]"></i>
                            </button>
                        </x-ui.table-cell>
                        <x-ui.table-cell head>{{ __('Actions') }}</x-ui.table-cell>
                    </x-ui.table-head>
                        <x-ui.table-body>
                            @forelse ($users as $user)
                                @include('adminuser::livewire.partials.user-table-row', ['user' => $user, 'authUser' => $authUser])
                            @empty
                                <x-ui.table-row>
                                    <x-ui.table-cell colspan="7" class="py-10 text-center" style="color: var(--theme-muted-text-color);">{{ __('No users found.') }}</x-ui.table-cell>
                                </x-ui.table-row>
                            @endforelse
                        </x-ui.table-body>
                </x-ui.table>
            </div>

                @if ($users->hasPages())
                    <div class="border-t px-6 py-4" style="border-color: var(--theme-border-color);">
                        {{ $users->links() }}
                    </div>
                @endif
        </x-ui.datatable-shell>
    </div>
</div>
