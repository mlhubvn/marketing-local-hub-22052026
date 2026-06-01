<div class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6" x-data="{ tab: @entangle('tab').live }">
    @if ($statusMessage)
        <x-ui.alert variant="success" :title="__('Updated')" :description="$statusMessage" />
    @endif

    <section class="overflow-hidden rounded-[1.35rem] border p-6" style="border-color: rgba(var(--theme-border-color-rgb), .68); background: linear-gradient(135deg, rgba(var(--theme-accent-rgb),.13), transparent 34%), linear-gradient(35deg, rgba(var(--theme-warning-color-rgb),.10), transparent 38%), color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <a href="{{ route('portal.crm.customers') }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-semibold" style="color: var(--theme-muted-text-color);"><i class="fa-light fa-arrow-left"></i>{{ __('CRM Customers') }}</a>
        <div class="mt-5 flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
            <div>
                <div class="mb-4 inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 80%, transparent);"><i class="fa-light fa-address-card"></i>{{ __('Customer Profile') }}</div>
                <h1 class="text-[2.35rem] font-semibold leading-none tracking-[-0.055em]" style="color: var(--theme-header-text-color);">{{ $customer->name }}</h1>
                <p class="mt-3 text-sm" style="color: var(--theme-muted-text-color);">{{ $customer->phone ?: __('No phone') }} &middot; {{ $customer->email ?: __('No email') }}</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($customer->crmTags as $tag)
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold" style="background-color: {{ $tag->color }}1a; color: {{ $tag->color }};">{{ $tag->name }}</span>
                    @endforeach
                </div>
            </div>
            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .58);"><p class="text-xs" style="color: var(--theme-muted-text-color);">{{ __('Score') }}</p><p class="mt-1 text-2xl font-semibold">{{ (int) $customer->score }}</p><p class="text-xs" style="color: var(--theme-accent);">{{ $scoreLabel }}</p></div>
                <div class="rounded-xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .58);"><p class="text-xs" style="color: var(--theme-muted-text-color);">{{ __('Status') }}</p><p class="mt-1 text-lg font-semibold">{{ str($customer->status ?: 'active')->headline() }}</p></div>
                <div class="rounded-xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), .58);"><p class="text-xs" style="color: var(--theme-muted-text-color);">{{ __('Last activity') }}</p><p class="mt-1 text-sm font-semibold">{{ format_datetime_locale($customer->last_activity_at) ?: __('No activity') }}</p></div>
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="flex gap-2 overflow-x-auto border-b p-2" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            @foreach (['overview' => __('Overview'), 'timeline' => __('Timeline'), 'notes' => __('Notes'), 'tasks' => __('Tasks'), 'tags' => __('Tags')] as $key => $label)
                <button type="button" x-on:click="tab = @js($key)" class="rounded-xl px-4 py-2 text-sm font-semibold" :style="tab === @js($key) ? 'background-color: rgba(var(--theme-accent-rgb),.12); color: var(--theme-accent);' : 'color: var(--theme-muted-text-color);'">{{ $label }}</button>
            @endforeach
        </div>

        <div x-show="tab === 'overview'" class="space-y-5 p-5">
            <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_22rem]">
                <div class="space-y-5">
                    <div class="rounded-xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-semibold">{{ __('Customer info') }}</p>
                                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Core profile context and source details.') }}</p>
                            </div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb),.11); color: var(--theme-accent);"><i class="fa-light fa-address-card"></i></span>
                        </div>
                        <div class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                            <div><p class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Business') }}</p><p class="mt-1 font-semibold">{{ $customer->business?->name ?: __('No business') }}</p></div>
                            <div><p class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('First seen') }}</p><p class="mt-1 font-semibold">{{ format_date_locale($customer->first_seen_at) ?: format_date_locale($customer->created_at) }}</p></div>
                            <div><p class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Source') }}</p><p class="mt-1 font-semibold">{{ $customer->source_type ? str($customer->source_type)->headline() : __('Manual / unknown') }}</p></div>
                            <div><p class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Last activity') }}</p><p class="mt-1 font-semibold">{{ format_datetime_locale($customer->last_activity_at) ?: __('No activity') }}</p></div>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                        @foreach ([
                            ['label' => __('Bookings'), 'value' => (int) $customer->total_bookings, 'icon' => 'fa-light fa-calendar-check'],
                            ['label' => __('Coupons'), 'value' => (int) $customer->total_coupon_claims, 'icon' => 'fa-light fa-ticket'],
                            ['label' => __('Loyalty Stamps'), 'value' => (int) $customer->total_loyalty_stamps, 'icon' => 'fa-light fa-stamp'],
                            ['label' => __('Referrals'), 'value' => (int) $customer->total_referrals, 'icon' => 'fa-light fa-share-nodes'],
                            ['label' => __('Score'), 'value' => (int) $customer->score, 'icon' => 'fa-light fa-gauge-high'],
                        ] as $metric)
                            <article class="rounded-xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: color-mix(in srgb, var(--theme-surface-base) 72%, transparent);">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-xl" style="background-color: rgba(var(--theme-accent-rgb),.10); color: var(--theme-accent);"><i class="{{ $metric['icon'] }}"></i></span>
                                    <span class="text-2xl font-semibold tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ format_number_locale($metric['value']) }}</span>
                                </div>
                                <p class="mt-3 text-sm font-semibold" style="color: var(--theme-muted-text-color);">{{ $metric['label'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                    <p class="font-semibold">{{ __('Quick actions') }}</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Create follow-ups or contact this customer quickly.') }}</p>
                    <div class="mt-4 grid gap-2">
                        <x-ui.button type="button" size="sm" variant="outline" x-on:click="tab = 'notes'"><i class="fa-light fa-note-sticky"></i>{{ __('Add note') }}</x-ui.button>
                        <x-ui.button type="button" size="sm" variant="outline" x-on:click="tab = 'tasks'"><i class="fa-light fa-list-check"></i>{{ __('Create task') }}</x-ui.button>
                        <x-ui.button type="button" size="sm" variant="outline" x-on:click="tab = 'tags'"><i class="fa-light fa-tag"></i>{{ __('Add tag') }}</x-ui.button>
                        @if($emailUrl)
                            <x-ui.button href="{{ $emailUrl }}" size="sm" variant="outline"><i class="fa-light fa-envelope"></i>{{ __('Send email') }}</x-ui.button>
                        @else
                            <button type="button" disabled class="inline-flex h-9 items-center justify-center gap-2 rounded-xl border px-3 text-sm font-semibold opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-muted-text-color);"><i class="fa-light fa-envelope"></i>{{ __('Send email') }}</button>
                        @endif
                        @if($whatsappUrl)
                            <x-ui.button href="{{ $whatsappUrl }}" target="_blank" size="sm" variant="outline"><i class="fa-brands fa-whatsapp"></i>{{ __('Send WhatsApp') }}</x-ui.button>
                        @else
                            <button type="button" disabled class="inline-flex h-9 items-center justify-center gap-2 rounded-xl border px-3 text-sm font-semibold opacity-45" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-muted-text-color);"><i class="fa-brands fa-whatsapp"></i>{{ __('Send WhatsApp') }}</button>
                        @endif
                    </div>
                    <div class="mt-4 border-t pt-4" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                        <x-ui.select wire:model="status" name="status"><option value="new">{{ __('New') }}</option><option value="active">{{ __('Active') }}</option><option value="inactive">{{ __('Inactive') }}</option><option value="vip">{{ __('VIP') }}</option><option value="blocked">{{ __('Blocked') }}</option></x-ui.select>
                        <div class="mt-2 grid grid-cols-2 gap-2">
                            <x-ui.button type="button" size="sm" variant="outline" wire:click="updateStatus">{{ __('Update status') }}</x-ui.button>
                            <x-ui.button type="button" size="sm" wire:click="addScore(10, 'Manual VIP boost')"><i class="fa-light fa-plus"></i>{{ __('Add score') }}</x-ui.button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-5 lg:grid-cols-2">
                <div class="rounded-xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                    <div class="flex items-center justify-between gap-3">
                        <p class="font-semibold">{{ __('Recent activity') }}</p>
                        <button type="button" x-on:click="tab = 'timeline'" class="text-xs font-semibold" style="color: var(--theme-accent);">{{ __('View all') }}</button>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse ($recentActivities as $activity)
                            <div class="flex gap-3 rounded-xl border p-3" style="border-color: rgba(var(--theme-border-color-rgb), .52);">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl" style="background-color: {{ $activity->color ?: '#0f766e' }}1a; color: {{ $activity->color ?: '#0f766e' }};"><i class="{{ $activity->icon ?: 'fa-light fa-timeline' }}"></i></span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold">{{ $activity->title }}</p>
                                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ format_datetime_locale($activity->occurred_at) ?: format_datetime_locale($activity->created_at) }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('No recent activity yet.') }}</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                    <div class="flex items-center justify-between gap-3">
                        <p class="font-semibold">{{ __('Open tasks') }}</p>
                        <button type="button" x-on:click="tab = 'tasks'" class="text-xs font-semibold" style="color: var(--theme-accent);">{{ __('Manage tasks') }}</button>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse ($openTasks as $task)
                            @php
                                $isOverdue = $task->due_at && $task->due_at->isPast() && ! $task->due_at->isToday();
                                $dueLabel = $task->due_at
                                    ? ($task->due_at->isToday() ? __('Due today') : ($task->due_at->isTomorrow() ? __('Due tomorrow') : ($isOverdue ? __('Overdue by :days days', ['days' => $task->due_at->diffInDays(now())]) : __('Due: :date', ['date' => format_date_locale($task->due_at)]))))
                                    : __('No due date');
                            @endphp
                            <div class="flex items-center justify-between gap-3 rounded-xl border p-3" style="border-color: rgba(var(--theme-border-color-rgb), .52);">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold">{{ $task->title }}</p>
                                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Priority: :priority', ['priority' => str($task->priority)->headline()]) }} &middot; {{ $dueLabel }}</p>
                                </div>
                                <button type="button" wire:click="completeTask({{ $task->id }})" class="shrink-0 rounded-lg border px-3 py-2 text-xs font-semibold" style="border-color: rgba(var(--theme-accent-rgb), .24); color: var(--theme-accent);">{{ __('Done') }}</button>
                            </div>
                        @empty
                            <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('No open tasks.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="rounded-xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                <p class="font-semibold">{{ __('Possible duplicates') }}</p>
                <div class="mt-4 space-y-2">
                    @forelse ($duplicates as $duplicate)
                        <div class="flex items-center justify-between gap-3 rounded-xl border px-3 py-2" style="border-color: rgba(var(--theme-border-color-rgb), .52);">
                            <div>
                                <p class="font-semibold">{{ $duplicate->name }}</p>
                                <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ $duplicate->phone ?: __('No phone') }} &middot; {{ $duplicate->email ?: __('No email') }}</p>
                            </div>
                            <button type="button" wire:click="mergeDuplicate({{ $duplicate->id }})" wire:confirm="{{ __('Merge this duplicate into the current customer?') }}" class="rounded-lg border px-3 py-2 text-xs font-semibold">{{ __('Merge') }}</button>
                        </div>
                    @empty
                        <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('No duplicates found by email or phone.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div x-show="tab === 'timeline'" class="p-5">
            <div class="mb-5 flex gap-2 overflow-x-auto pb-1">
                @foreach ($timelineCategories as $key => $category)
                    <button type="button" wire:click="$set('timelineFilter', '{{ $key }}')" class="inline-flex h-10 shrink-0 items-center gap-2 rounded-xl border px-3 text-sm font-semibold transition" style="{{ $timelineFilter === $key ? 'border-color: rgba(var(--theme-accent-rgb), .28); background-color: rgba(var(--theme-accent-rgb), .12); color: var(--theme-accent);' : 'border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 74%, transparent);' }}">
                        <i class="{{ $category['icon'] }}"></i>{{ $category['label'] }}
                    </button>
                @endforeach
            </div>

            <div class="relative space-y-0 pl-5 before:absolute before:bottom-4 before:left-[1.05rem] before:top-4 before:w-px before:bg-[color:rgba(var(--theme-border-color-rgb),0.72)]">
                @forelse ($timelineActivities as $activity)
                    <article class="relative pb-4 pl-7">
                        <span class="absolute left-[-0.1rem] top-4 z-10 flex h-9 w-9 -translate-x-1/2 items-center justify-center rounded-full border" style="border-color: {{ $activity->color ?: '#0f766e' }}33; background-color: var(--theme-surface-overlay); color: {{ $activity->color ?: '#0f766e' }};"><i class="{{ $activity->icon ?: 'fa-light fa-timeline' }}"></i></span>
                        <div class="rounded-xl border p-4 transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.035)]" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <div class="mb-2 flex flex-wrap items-center gap-2">
                                        <span class="rounded-full border px-2 py-0.5 text-[11px] font-semibold uppercase tracking-[0.13em]" style="border-color: {{ $activity->color ?: '#0f766e' }}33; background-color: {{ $activity->color ?: '#0f766e' }}12; color: {{ $activity->color ?: '#0f766e' }};">{{ $activity->crm_category_label }}</span>
                                        <span class="text-xs" style="color: var(--theme-muted-text-color);">{{ $activity->crm_module_label }}</span>
                                    </div>
                                    <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $activity->title }}</p>
                                    @if ($activity->description)
                                        <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $activity->description }}</p>
                                    @endif
                                </div>
                                <div class="shrink-0 text-left sm:text-right">
                                    <p class="text-xs font-semibold" style="color: var(--theme-muted-text-color);">{{ format_datetime_locale($activity->occurred_at) ?: format_datetime_locale($activity->created_at) }}</p>
                                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $activity->occurred_at?->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl border p-6" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                        <x-ui.empty
                            icon="fa-light fa-timeline"
                            :title="__('No timeline activity yet')"
                            :description="__('Customer activity will appear here when this customer submits leads, books appointments, claims coupons, sends feedback, receives messages, earns stamps, or gets referral rewards.')"
                        />
                    </div>
                @endforelse
            </div>
        </div>

        <div x-show="tab === 'notes'" class="grid gap-5 p-5 lg:grid-cols-[minmax(0,1fr)_22rem]">
            <div class="space-y-3">
                @forelse ($customer->notes as $item)
                    <article class="rounded-xl border p-4 {{ $item->pinned ? 'shadow-[0_18px_40px_-34px_rgba(20,125,120,.9)]' : '' }}" style="border-color: {{ $item->pinned ? 'rgba(var(--theme-accent-rgb), .34)' : 'rgba(var(--theme-border-color-rgb), .58)' }}; background-color: {{ $item->pinned ? 'rgba(var(--theme-accent-rgb), .045)' : 'transparent' }};">
                        @if ($editingNoteId === $item->id)
                            <form wire:submit="updateNote" class="space-y-3">
                                <x-ui.textarea wire:model="editingNote" name="editingNote" :label="__('Edit note')" rows="5" :placeholder="__('Write an internal note about this customer...')" :error="$errors->first('editingNote')">{{ $editingNote }}</x-ui.textarea>
                                <x-ui.checkbox wire:model="editingNotePinned" :checked="$editingNotePinned" :label="__('Pin note')" />
                                <div class="flex justify-end gap-2">
                                    <x-ui.button type="button" size="sm" variant="outline" wire:click="cancelEditNote">{{ __('Cancel') }}</x-ui.button>
                                    <x-ui.button type="submit" size="sm"><i class="fa-light fa-floppy-disk"></i>{{ __('Save changes') }}</x-ui.button>
                                </div>
                            </form>
                        @else
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <div class="mb-2 flex flex-wrap items-center gap-2">
                                        @if ($item->pinned)
                                            <span class="rounded-full border px-2 py-0.5 text-[11px] font-semibold uppercase tracking-[0.14em]" style="border-color: rgba(var(--theme-accent-rgb), .28); background-color: rgba(var(--theme-accent-rgb), .10); color: var(--theme-accent);">{{ __('Pinned') }}</span>
                                        @endif
                                        <span class="text-xs" style="color: var(--theme-muted-text-color);">{{ __('Added by :name', ['name' => $item->user?->name ?: __('Unknown')]) }} &middot; {{ format_datetime_locale($item->created_at) }}</span>
                                    </div>
                                    <p class="whitespace-pre-line leading-7" style="color: var(--theme-header-text-color);">{{ $item->note }}</p>
                                </div>
                                <div class="flex shrink-0 items-center gap-2">
                                    <button type="button" wire:click="editNote({{ $item->id }})" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-muted-text-color);" title="{{ __('Edit') }}"><i class="fa-light fa-pen"></i></button>
                                    <button type="button" wire:click="toggleNotePin({{ $item->id }})" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border" style="border-color: rgba(var(--theme-accent-rgb), .28); color: var(--theme-accent);" title="{{ $item->pinned ? __('Unpin') : __('Pin') }}"><i class="{{ $item->pinned ? 'fa-solid' : 'fa-light' }} fa-thumbtack"></i></button>
                                    <button type="button" wire:click="deleteNote({{ $item->id }})" wire:confirm="{{ __('Delete this note?') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border" style="border-color: rgba(var(--theme-danger-color-rgb), .28); color: var(--theme-danger-color);" title="{{ __('Delete') }}"><i class="fa-light fa-trash"></i></button>
                                </div>
                            </div>
                        @endif
                    </article>
                @empty
                    <x-ui.empty icon="fa-light fa-note-sticky" :title="__('No notes yet')" :description="__('Internal notes about this customer will appear here for your team.')" />
                @endforelse
            </div>
            <form wire:submit="addNote" class="rounded-xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                <x-ui.textarea wire:model="note" name="note" :label="__('Add note')" rows="6" :placeholder="__('Write an internal note about this customer...')" :error="$errors->first('note')">{{ $note }}</x-ui.textarea>
                <div class="mt-3"><x-ui.checkbox wire:model="notePinned" :checked="$notePinned" :label="__('Pin note')" /></div>
                <x-ui.button type="submit" class="mt-4 w-full justify-center">{{ __('Save note') }}</x-ui.button>
            </form>
        </div>

        <div x-show="tab === 'tasks'" class="grid gap-5 p-5 lg:grid-cols-[minmax(0,1fr)_24rem]">
            <div class="space-y-3">
                @forelse ($customer->tasks as $task)
                    @php
                        $isOverdue = $task->due_at && $task->due_at->isPast() && ! $task->due_at->isToday() && ! in_array($task->status, ['done', 'cancelled'], true);
                        $statusText = $isOverdue ? __('Overdue') : str($task->status)->headline();
                        $dueText = $task->due_at
                            ? ($task->due_at->isToday() ? __('Due today') : ($task->due_at->isTomorrow() ? __('Due tomorrow') : ($isOverdue ? __('Overdue by :days days', ['days' => $task->due_at->diffInDays(now())]) : __('Due: :date', ['date' => format_date_locale($task->due_at)]))))
                            : __('No due date');
                        $statusColor = $isOverdue ? 'var(--theme-danger-color)' : ($task->status === 'done' ? 'var(--theme-success-color)' : 'var(--theme-accent)');
                    @endphp
                    <article class="rounded-xl border p-4" style="border-color: {{ $isOverdue ? 'rgba(var(--theme-danger-color-rgb), .32)' : 'rgba(var(--theme-border-color-rgb), .58)' }}; background-color: {{ $isOverdue ? 'rgba(var(--theme-danger-color-rgb), .035)' : 'transparent' }};">
                        @if ($editingTaskId === $task->id)
                            <form wire:submit="updateTask" class="space-y-3">
                                <x-ui.input wire:model="editingTaskTitle" name="editingTaskTitle" :label="__('Task title')" :error="$errors->first('editingTaskTitle')" />
                                <x-ui.textarea wire:model="editingTaskDescription" name="editingTaskDescription" :label="__('Description')" rows="3" :placeholder="__('Add context for this follow-up task...')">{{ $editingTaskDescription }}</x-ui.textarea>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <x-ui.select wire:model="editingTaskType" :label="__('Task type')"><option value="call">{{ __('Call') }}</option><option value="email">{{ __('Email') }}</option><option value="whatsapp">{{ __('WhatsApp') }}</option><option value="meeting">{{ __('Meeting') }}</option><option value="follow_up">{{ __('Follow-up') }}</option><option value="custom">{{ __('Custom') }}</option></x-ui.select>
                                    <x-ui.select wire:model="editingTaskPriority" :label="__('Priority')"><option value="low">{{ __('Low') }}</option><option value="medium">{{ __('Medium') }}</option><option value="high">{{ __('High') }}</option><option value="urgent">{{ __('Urgent') }}</option></x-ui.select>
                                    <x-ui.select wire:model="editingTaskStatus" :label="__('Status')"><option value="open">{{ __('Open') }}</option><option value="in_progress">{{ __('In progress') }}</option><option value="done">{{ __('Done') }}</option><option value="cancelled">{{ __('Cancelled') }}</option></x-ui.select>
                                    <x-ui.select wire:model="editingTaskAssignedTo" :label="__('Assigned to')"><option value="">{{ __('Unassigned') }}</option>@foreach($assignableUsers as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</x-ui.select>
                                </div>
                                <x-ui.datetime-picker
                                    wire:model="editingTaskDueAt"
                                    name="editingTaskDueAt"
                                    :label="__('Due at')"
                                    :value="$editingTaskDueAt"
                                    :placeholder="__('Choose due date and time')"
                                    picker-align="right"
                                    picker-position="auto"
                                    :error="$errors->first('editingTaskDueAt')"
                                />
                                <div class="flex justify-end gap-2">
                                    <x-ui.button type="button" size="sm" variant="outline" wire:click="cancelEditTask">{{ __('Cancel') }}</x-ui.button>
                                    <x-ui.button type="submit" size="sm"><i class="fa-light fa-floppy-disk"></i>{{ __('Save task') }}</x-ui.button>
                                </div>
                            </form>
                        @else
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <div class="mb-2 flex flex-wrap items-center gap-2">
                                        <span class="rounded-full border px-2 py-0.5 text-[11px] font-semibold uppercase tracking-[0.13em]" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-muted-text-color);">{{ str($task->type)->headline() }}</span>
                                        <span class="rounded-full border px-2 py-0.5 text-[11px] font-semibold uppercase tracking-[0.13em]" style="border-color: rgba(var(--theme-warning-color-rgb), .28); color: var(--theme-warning-color);">{{ str($task->priority)->headline() }}</span>
                                        <span class="rounded-full border px-2 py-0.5 text-[11px] font-semibold uppercase tracking-[0.13em]" style="border-color: color-mix(in srgb, {{ $statusColor }} 28%, transparent); color: {{ $statusColor }};">{{ $statusText }}</span>
                                    </div>
                                    <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $task->title }}</p>
                                    @if($task->description)
                                        <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $task->description }}</p>
                                    @endif
                                    <p class="mt-2 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Priority: :priority', ['priority' => str($task->priority)->headline()]) }} &middot; {{ $dueText }} &middot; {{ __('Assigned to: :name', ['name' => $task->assignedUser?->name ?: __('Unassigned')]) }}</p>
                                </div>
                                <div class="flex shrink-0 items-center gap-2">
                                    @if ($task->status !== 'done')
                                        <button type="button" wire:click="completeTask({{ $task->id }})" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border" style="border-color: rgba(var(--theme-accent-rgb), .28); color: var(--theme-accent);" title="{{ __('Done') }}"><i class="fa-light fa-check"></i></button>
                                    @endif
                                    <button type="button" wire:click="editTask({{ $task->id }})" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-muted-text-color);" title="{{ __('Edit') }}"><i class="fa-light fa-pen"></i></button>
                                    <button type="button" wire:click="rescheduleTask({{ $task->id }})" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border" style="border-color: rgba(var(--theme-border-color-rgb), .68); color: var(--theme-muted-text-color);" title="{{ __('Reschedule for tomorrow') }}"><i class="fa-light fa-calendar-plus"></i></button>
                                    <button type="button" wire:click="deleteTask({{ $task->id }})" wire:confirm="{{ __('Delete this task?') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border" style="border-color: rgba(var(--theme-danger-color-rgb), .28); color: var(--theme-danger-color);" title="{{ __('Delete') }}"><i class="fa-light fa-trash"></i></button>
                                </div>
                            </div>
                        @endif
                    </article>
                @empty
                    <x-ui.empty icon="fa-light fa-list-check" :title="__('No tasks yet')" :description="__('Follow-up tasks for this customer will appear here.')" />
                @endforelse
            </div>
            <form wire:submit="createTask" class="rounded-xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                <x-ui.input wire:model="taskTitle" name="taskTitle" :label="__('Task title')" :error="$errors->first('taskTitle')" />
                <x-ui.textarea wire:model="taskDescription" name="taskDescription" :label="__('Description')" rows="3" :placeholder="__('Add context for this follow-up task...')">{{ $taskDescription }}</x-ui.textarea>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <x-ui.select wire:model="taskType" :label="__('Task type')"><option value="call">{{ __('Call') }}</option><option value="email">{{ __('Email') }}</option><option value="whatsapp">{{ __('WhatsApp') }}</option><option value="meeting">{{ __('Meeting') }}</option><option value="follow_up">{{ __('Follow-up') }}</option><option value="custom">{{ __('Custom') }}</option></x-ui.select>
                    <x-ui.select wire:model="taskPriority" :label="__('Priority')"><option value="low">{{ __('Low') }}</option><option value="medium">{{ __('Medium') }}</option><option value="high">{{ __('High') }}</option><option value="urgent">{{ __('Urgent') }}</option></x-ui.select>
                </div>
                <x-ui.select class="mt-3" wire:model="taskAssignedTo" :label="__('Assigned to')"><option value="">{{ __('Unassigned') }}</option>@foreach($assignableUsers as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</x-ui.select>
                <x-ui.datetime-picker
                    class="mt-3"
                    wire:model="taskDueAt"
                    name="taskDueAt"
                    :label="__('Due at')"
                    :value="$taskDueAt"
                    :placeholder="__('Choose due date and time')"
                    picker-align="right"
                    picker-position="auto"
                    :error="$errors->first('taskDueAt')"
                />
                <x-ui.button type="submit" class="mt-4 w-full justify-center">{{ __('Create task') }}</x-ui.button>
            </form>
        </div>

        <div x-show="tab === 'tags'" class="grid gap-5 p-5 lg:grid-cols-[minmax(0,1fr)_24rem]">
            <div class="space-y-5">
                <section class="rounded-xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-semibold">{{ __('Current tags') }}</p>
                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Labels currently attached to this customer profile.') }}</p>
                        </div>
                        <span class="rounded-full border px-2.5 py-1 text-xs font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-muted-text-color);">{{ format_number_locale($currentTags->count()) }} {{ __('tags') }}</span>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @forelse ($currentTags as $tag)
                            <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-sm font-semibold" style="border-color: {{ $tag['color'] }}33; background-color: {{ $tag['color'] }}14; color: {{ $tag['color'] }};">
                                {{ $tag['name'] }}
                                <button type="button" wire:click="removeTag({{ $tag['id'] }})" wire:confirm="{{ __('Remove this tag from the customer?') }}" class="inline-flex h-5 w-5 items-center justify-center rounded-full border text-[10px]" style="border-color: {{ $tag['color'] }}55;" title="{{ __('Remove tag') }}"><i class="fa-light fa-xmark"></i></button>
                            </span>
                        @empty
                            <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('No tags attached yet.') }}</p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                    <p class="font-semibold">{{ __('Tag history') }}</p>
                    <div class="mt-4 space-y-3">
                        @forelse ($currentTags as $tag)
                            <div class="flex items-start gap-3 rounded-xl border p-3" style="border-color: rgba(var(--theme-border-color-rgb), .52);">
                                <span class="mt-1 h-3 w-3 shrink-0 rounded-full" style="background-color: {{ $tag['color'] }};"></span>
                                <div class="min-w-0">
                                    <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $tag['name'] }}</p>
                                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">
                                        @if($tag['created_by_name'])
                                            {{ __('Added by :name', ['name' => $tag['created_by_name']]) }}
                                        @else
                                            {{ __('Added automatically') }}
                                        @endif
                                        @if($tag['created_at_label'])
                                            &middot; {{ $tag['created_at_label'] }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @empty
                            <x-ui.empty icon="fa-light fa-tags" :title="__('No tag history yet')" :description="__('Tags added manually or automatically will appear here.')" />
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="space-y-5">
                <form wire:submit="addTag" class="rounded-xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                    <p class="font-semibold">{{ __('Add tag') }}</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Search or choose an existing CRM tag.') }}</p>
                    <x-ui.combobox
                        class="mt-4"
                        model="selectedTagId"
                        name="selectedTagId"
                        :selected="$selectedTagId"
                        :options="$tags->map(fn ($tag) => ['value' => (string) $tag->id, 'label' => $tag->name, 'meta' => $tag->slug, 'icon' => 'fa-tag'])->values()->all()"
                        :placeholder="__('Search or choose tag...')"
                        :search-placeholder="__('Search tags...')"
                        icon="fa-light fa-tags"
                        :error="$errors->first('selectedTagId')"
                    />
                    <x-ui.button type="submit" class="mt-4 w-full justify-center"><i class="fa-light fa-plus"></i>{{ __('Add tag') }}</x-ui.button>
                </form>

                <form wire:submit="createAndAddTag" class="rounded-xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                    <p class="font-semibold">{{ __('Create new tag') }}</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Create a custom CRM tag and attach it to this customer.') }}</p>
                    <x-ui.input class="mt-4" wire:model="newTagName" name="newTagName" :label="__('Tag name')" :placeholder="__('High Value')" :error="$errors->first('newTagName')" />
                    <x-ui.color-picker class="mt-3" wire:model="newTagColor" name="newTagColor" :label="__('Color')" :value="$newTagColor" :error="$errors->first('newTagColor')" />
                    <x-ui.button type="submit" variant="outline" class="mt-4 w-full justify-center"><i class="fa-light fa-plus"></i>{{ __('Create and add') }}</x-ui.button>
                </form>
            </div>
        </div>
    </section>
</div>

