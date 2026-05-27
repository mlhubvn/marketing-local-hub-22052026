<div class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6" x-data="{ createOpen: false }" x-on:email-automation-saved.window="createOpen = false">
    @if ($statusMessage)
        <x-ui.alert variant="success" :title="__('Updated')" :description="$statusMessage" />
    @endif

    <section class="overflow-hidden rounded-[1.15rem] border px-5 py-6 sm:px-6 xl:px-7" style="border-color: rgba(var(--theme-border-color-rgb), .68); background: linear-gradient(135deg, rgba(var(--theme-accent-rgb), .12), transparent 36%), color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-md border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 80%, transparent);">
                    <i class="fa-light fa-bolt"></i>{{ __('Automation') }}
                </div>
                <h1 class="mt-4 text-[2.2rem] font-semibold leading-tight tracking-[-0.055em]" style="color: var(--theme-header-text-color);">{{ __('Email Automations') }}</h1>
                <p class="mt-3 max-w-2xl text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Send booking, coupon, lead, review, feedback, and customer follow-up emails from reusable rules.') }}</p>
            </div>
            <x-ui.button type="button" size="lg" x-on:click="createOpen = true">
                <i class="fa-light fa-plus"></i>{{ __('New automation') }}
            </x-ui.button>
        </div>
    </section>

    <section class="overflow-hidden rounded-[1.15rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Automation rules') }}</p>
            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Trigger, delay, template, recipient, and conditions.') }}</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead style="color: var(--theme-muted-text-color);">
                    <tr>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Automation') }}</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Trigger') }}</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Template') }}</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Status') }}</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em]">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                    @forelse ($automations as $automation)
                        <tr>
                            <td class="px-5 py-4">
                                <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $automation->name }}</p>
                                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $automation->business?->name ?: __('All businesses') }} · {{ $automation->send_to }}</p>
                            </td>
                            <td class="px-5 py-4" style="color: var(--theme-muted-text-color);">{{ $triggers[$automation->trigger_event] ?? $automation->trigger_event }}</td>
                            <td class="px-5 py-4" style="color: var(--theme-muted-text-color);">{{ $automation->template?->name ?: __('Template removed') }}</td>
                            <td class="px-5 py-4"><x-ui.badge :variant="$automation->status === 'active' ? 'success' : ($automation->status === 'draft' ? 'neutral' : 'warning')">{{ ucfirst($automation->status) }}</x-ui.badge></td>
                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button type="button" wire:click="toggle({{ $automation->id }})" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);" title="{{ __('Toggle') }}"><i class="fa-light fa-power-off"></i></button>
                                    <button type="button" wire:click="delete({{ $automation->id }})" wire:confirm="{{ __('Delete this automation?') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border" style="border-color: rgba(var(--theme-danger-color-rgb), .28); color: var(--theme-danger-color); background-color: var(--theme-surface-overlay);" title="{{ __('Delete') }}"><i class="fa-light fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-sm" style="color: var(--theme-muted-text-color);">{{ __('No email automations yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .68);">{{ $automations->links() }}</div>
    </section>

    <template x-teleport="body">
        <div x-cloak x-show="createOpen" class="fixed inset-0 z-[120] overflow-y-auto px-4 py-5 sm:px-6 sm:py-7" x-on:keydown.escape.window="createOpen = false">
            <div class="absolute inset-0 bg-white/55 backdrop-blur-[6px] dark:bg-slate-950/55" x-on:click="createOpen = false"></div>
            <div class="relative flex min-h-full items-start justify-center">
                <form wire:submit="save" x-show="createOpen" x-transition.opacity.scale.95 class="relative w-full max-w-4xl overflow-hidden rounded-[1.15rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .72); background-color: var(--theme-surface-overlay);">
                    <div class="flex items-start justify-between gap-4 border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                        <div><p class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Create email automation') }}</p><p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Trigger, timing, template, recipient.') }}</p></div>
                        <button type="button" x-on:click="createOpen = false" class="flex h-10 w-10 items-center justify-center rounded-xl" style="color: var(--theme-muted-text-color);"><i class="fa-light fa-xmark"></i></button>
                    </div>
                    <div class="grid gap-4 p-5 md:grid-cols-2">
                        <x-ui.input wire:model="name" name="name" :label="__('Name')" :placeholder="__('Booking confirmation')" :error="$errors->first('name')" />
                        <x-ui.select wire:model="business_id" name="business_id" :label="__('Business')">
                            <option value="">{{ __('All businesses') }}</option>
                            @foreach ($businesses as $business)<option value="{{ $business->id }}">{{ $business->name }}</option>@endforeach
                        </x-ui.select>
                        <x-ui.select wire:model="trigger_event" name="trigger_event" :label="__('Trigger')" :error="$errors->first('trigger_event')">
                            @foreach ($triggers as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach
                        </x-ui.select>
                        <x-ui.select wire:model="email_template_id" name="email_template_id" :label="__('Email template')" :error="$errors->first('email_template_id')">
                            @foreach ($templates as $template)<option value="{{ $template->id }}">{{ $template->name }}</option>@endforeach
                        </x-ui.select>
                        <x-ui.select wire:model.live="delay_type" name="delay_type" :label="__('Timing')">
                            <option value="immediate">{{ __('Immediately') }}</option>
                            <option value="after">{{ __('After delay') }}</option>
                        </x-ui.select>
                        <div class="grid grid-cols-2 gap-3">
                            <x-ui.input wire:model="delay_value" type="number" min="0" name="delay_value" :label="__('Delay value')" />
                            <x-ui.select wire:model="delay_unit" name="delay_unit" :label="__('Unit')"><option value="minutes">{{ __('Minutes') }}</option><option value="hours">{{ __('Hours') }}</option><option value="days">{{ __('Days') }}</option></x-ui.select>
                        </div>
                        <x-ui.select wire:model.live="send_to" name="send_to" :label="__('Recipient')">
                            <option value="customer">{{ __('Customer') }}</option>
                            <option value="business">{{ __('Business owner') }}</option>
                            <option value="custom">{{ __('Custom email') }}</option>
                        </x-ui.select>
                        <x-ui.input wire:model="custom_email" name="custom_email" :label="__('Custom email')" :placeholder="__('Optional')" :error="$errors->first('custom_email')" />
                        <x-ui.select wire:model="status" name="status" :label="__('Status')"><option value="draft">{{ __('Draft') }}</option><option value="active">{{ __('Active') }}</option><option value="inactive">{{ __('Inactive') }}</option></x-ui.select>
                        <x-ui.checkbox wire:model="require_customer_email" :checked="$require_customer_email" :label="__('Require customer email')" :description="__('Skip customer emails when no email exists.')" />
                        <div class="md:col-span-2 rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .62);">
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Condition') }}</p>
                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Optional rule that must match before this automation sends.') }}</p>
                            <div class="mt-4 grid gap-3 md:grid-cols-3">
                                <x-ui.select wire:model.live="condition_field" name="condition_field" :label="__('Field')">
                                    @foreach ($conditionFields as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach
                                </x-ui.select>
                                <x-ui.select wire:model.live="condition_operator" name="condition_operator" :label="__('Operator')">
                                    @foreach (['=' => '=', '!=' => '!=', '>' => '>', '<' => '<', '>=' => '>=', '<=' => '<=', 'contains' => __('Contains'), 'exists' => __('Exists'), 'not_exists' => __('Not exists')] as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach
                                </x-ui.select>
                                @if (! empty($conditionValues[$condition_field] ?? []))
                                    <x-ui.select wire:model="condition_value" name="condition_value" :label="__('Value')">
                                        @foreach ($conditionValues[$condition_field] as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </x-ui.select>
                                @elseif ($condition_field === 'customer.email')
                                    <x-ui.select wire:model="condition_value" name="condition_value" :label="__('Value')" disabled>
                                        <option value="">{{ __('No value needed') }}</option>
                                    </x-ui.select>
                                @else
                                    <x-ui.input wire:model="condition_value" name="condition_value" :label="__('Value')" :placeholder="__('Choose a field to see values')" />
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 border-t px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                        <x-ui.button type="button" variant="outline" x-on:click="createOpen = false">{{ __('Cancel') }}</x-ui.button>
                        <x-ui.button type="submit"><i class="fa-light fa-floppy-disk"></i>{{ __('Save automation') }}</x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
