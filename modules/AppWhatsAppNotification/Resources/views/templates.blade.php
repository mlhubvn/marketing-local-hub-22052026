<div class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6" x-data="{ createOpen: false, previewOpen: false }" x-on:whatsapp-template-saved.window="createOpen = false" x-on:whatsapp-template-editing.window="createOpen = true" x-on:whatsapp-template-previewing.window="previewOpen = true">
    @if ($statusMessage)
        <x-ui.alert variant="success" :title="__('Updated')" :description="$statusMessage" />
    @endif

    <section class="overflow-hidden rounded-[1.15rem] border px-5 py-6 sm:px-6 xl:px-7" style="border-color: rgba(var(--theme-border-color-rgb), .68); background: linear-gradient(135deg, rgba(var(--theme-accent-rgb), .12), transparent 36%), color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-md border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-base) 80%, transparent);"><i class="fa-light fa-envelope-open-text"></i>{{ __('Automation') }}</div>
                <h1 class="mt-4 text-[2.2rem] font-semibold leading-tight tracking-[-0.055em]" style="color: var(--theme-header-text-color);">{{ __('WhatsApp Templates') }}</h1>
                <p class="mt-3 max-w-2xl text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Reusable WhatsApp copy with LocalBoost variables for booking, coupon, lead, review, and feedback follow-up.') }}</p>
            </div>
            <x-ui.button type="button" size="lg" x-on:click="createOpen = true"><i class="fa-light fa-plus"></i>{{ __('New template') }}</x-ui.button>
        </div>
    </section>

    <section
        class="overflow-hidden rounded-[1rem] border"
        style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);"
        x-data="{ copied: null, copyToken(token) { navigator.clipboard?.writeText(token); this.copied = token; setTimeout(() => { if (this.copied === token) this.copied = null }, 1400) } }"
    >
        <div class="flex flex-col gap-3 border-b px-4 py-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
            <div>
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Template variables') }}</p>
                <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Click a variable to copy it, then paste it into the message body.') }}</p>
            </div>
            <x-ui.badge variant="neutral">{{ count($variables) }} {{ __('tokens') }}</x-ui.badge>
        </div>

        <div class="grid gap-2 p-4 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
            @foreach ($variables as $variable)
                <button
                    type="button"
                    x-on:click="copyToken(@js($variable))"
                    class="group flex min-h-11 items-center justify-between gap-3 rounded-xl border px-3 py-2 text-left text-xs font-semibold transition hover:-translate-y-0.5 hover:shadow-sm"
                    style="border-color: rgba(var(--theme-border-color-rgb), .58); color: var(--theme-header-text-color); background-color: var(--theme-surface-base);"
                >
                    <code class="truncate font-mono text-[12px]" style="color: var(--theme-header-text-color);">{{ $variable }}</code>
                    <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-lg border px-2 text-[11px] font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .58); color: var(--theme-muted-text-color); background-color: color-mix(in srgb, var(--theme-surface-overlay) 88%, transparent);">
                        <span x-show="copied !== @js($variable)" class="inline-flex items-center gap-1"><i class="fa-light fa-copy"></i><span class="hidden xl:inline">{{ __('Copy') }}</span></span>
                        <span x-cloak x-show="copied === @js($variable)" class="inline-flex items-center gap-1" style="color: var(--theme-success-color);"><i class="fa-light fa-check"></i>{{ __('Copied') }}</span>
                    </span>
                </button>
            @endforeach
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($templates as $template)
            <article class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), .62); background-color: color-mix(in srgb, var(--theme-surface-overlay) 98%, transparent);">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate font-semibold" style="color: var(--theme-header-text-color);">{{ $template->name }}</p>
                        <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $template->template_name ?: $template->language }}</p>
                    </div>
                    <x-ui.badge :variant="$template->is_system ? 'primary' : 'neutral'">{{ $template->is_system ? __('System') : __('Custom') }}</x-ui.badge>
                </div>
                <p class="mt-4 line-clamp-4 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $template->body }}</p>
                <div class="mt-4 flex items-center justify-end gap-2 border-t pt-3" style="border-color: rgba(var(--theme-border-color-rgb), .58);">
                    <button type="button" wire:click="preview({{ $template->id }})" class="inline-flex h-9 items-center gap-2 rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);"><i class="fa-light fa-eye"></i>{{ __('Preview') }}</button>
                    @unless ($template->is_system)
                        <button type="button" wire:click="edit({{ $template->id }})" class="inline-flex h-9 items-center gap-2 rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);"><i class="fa-light fa-pen"></i>{{ __('Edit') }}</button>
                    @endunless
                    <button type="button" wire:click="duplicate({{ $template->id }})" class="inline-flex h-9 items-center gap-2 rounded-xl border px-3 text-sm font-semibold" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);"><i class="fa-light fa-copy"></i>{{ __('Duplicate') }}</button>
                    @unless ($template->is_system)
                        <button type="button" wire:click="delete({{ $template->id }})" wire:confirm="{{ __('Delete this template?') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border" style="border-color: rgba(var(--theme-danger-color-rgb), .28); color: var(--theme-danger-color); background-color: var(--theme-surface-overlay);"><i class="fa-light fa-trash"></i></button>
                    @endunless
                </div>
            </article>
        @empty
            <div class="rounded-xl border p-6 text-sm" style="border-color: rgba(var(--theme-border-color-rgb), .58); color: var(--theme-muted-text-color);">{{ __('No templates yet.') }}</div>
        @endforelse
    </section>

    {{ $templates->links() }}

    <template x-teleport="body">
        <div x-cloak x-show="createOpen" class="fixed inset-0 z-[120] overflow-y-auto px-4 py-5 sm:px-6 sm:py-7" x-on:keydown.escape.window="createOpen = false">
            <div class="absolute inset-0 bg-white/55 backdrop-blur-[6px] dark:bg-slate-950/55" x-on:click="createOpen = false"></div>
            <div class="relative flex min-h-full items-start justify-center">
                <form wire:submit="save" x-show="createOpen" x-transition.opacity.scale.95 class="relative w-full max-w-4xl overflow-hidden rounded-[1.15rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .72); background-color: var(--theme-surface-overlay);">
                    <div class="flex items-start justify-between gap-4 border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                        <div><p class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ $editingTemplateId ? __('Edit WhatsApp template') : __('Create WhatsApp template') }}</p><p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Use variables like {customer_name} and {business_name}.') }}</p></div>
                        <button type="button" x-on:click="createOpen = false" class="flex h-10 w-10 items-center justify-center rounded-xl" style="color: var(--theme-muted-text-color);"><i class="fa-light fa-xmark"></i></button>
                    </div>
                    <div class="grid gap-4 p-5 md:grid-cols-2">
                        <x-ui.input wire:model="name" name="name" :label="__('Name')" :error="$errors->first('name')" />
                        <x-ui.select wire:model="business_id" name="business_id" :label="__('Business')"><option value="">{{ __('All businesses') }}</option>@foreach ($businesses as $business)<option value="{{ $business->id }}">{{ $business->name }}</option>@endforeach</x-ui.select>
                        <x-ui.input wire:model="type" name="type" :label="__('Type')" />
                        <x-ui.input wire:model="language" name="language" :label="__('Language')" />
                        <div class="md:col-span-2"><x-ui.input wire:model="template_name" name="template_name" :label="__('Approved template name')" :placeholder="__('Optional')" :error="$errors->first('template_name')" /></div>
                        <div class="md:col-span-2"><x-ui.textarea wire:model="body" name="body" rows="9" :label="__('Body')" :error="$errors->first('body')">{{ $body }}</x-ui.textarea></div>
                        <x-ui.select wire:model="status" name="status" :label="__('Status')"><option value="active">{{ __('Active') }}</option><option value="inactive">{{ __('Inactive') }}</option></x-ui.select>
                    </div>
                    <div class="flex justify-end gap-3 border-t px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                        <x-ui.button type="button" variant="outline" x-on:click="createOpen = false">{{ __('Cancel') }}</x-ui.button>
                        <x-ui.button type="submit"><i class="fa-light fa-floppy-disk"></i>{{ __('Save template') }}</x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div x-cloak x-show="previewOpen" class="fixed inset-0 z-[120] overflow-y-auto px-4 py-5 sm:px-6 sm:py-7" x-on:keydown.escape.window="previewOpen = false">
            <div class="absolute inset-0 bg-white/55 backdrop-blur-[6px] dark:bg-slate-950/55" x-on:click="previewOpen = false"></div>
            <div class="relative flex min-h-full items-start justify-center">
                <section x-show="previewOpen" x-transition.opacity.scale.95 class="relative w-full max-w-3xl overflow-hidden rounded-[1.15rem] border" style="border-color: rgba(var(--theme-border-color-rgb), .72); background-color: var(--theme-surface-overlay);">
                    <div class="flex items-start justify-between gap-4 border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb), .68);">
                        <div><p class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ $previewTemplate?->name ?: __('Template preview') }}</p><p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ $previewTemplate?->template_name ?: $previewTemplate?->language }}</p></div>
                        <button type="button" x-on:click="previewOpen = false" class="flex h-10 w-10 items-center justify-center rounded-xl" style="color: var(--theme-muted-text-color);"><i class="fa-light fa-xmark"></i></button>
                    </div>
                    <div class="p-5">
                        <div class="rounded-[1rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), .62); background-color: var(--theme-surface-base);">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('WhatsApp message') }}</p>
                            <div class="mt-5 whitespace-pre-line text-sm leading-7" style="color: var(--theme-header-text-color);">{{ $previewTemplate?->body }}</div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </template>
</div>
