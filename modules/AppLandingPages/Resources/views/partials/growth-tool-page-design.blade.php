@php
    $type = $type ?? 'growth';
    $templates = $templates ?? [];
@endphp

<div
    class="rounded-[1rem] border"
    style="border-color: rgba(var(--theme-border-color-rgb), .58); background-color: color-mix(in srgb, var(--theme-surface-base) 78%, transparent);"
    x-data="{
        designOpen: false,
        businessId: @entangle('business_id').live,
        template: @entangle('landing_template').live,
        primaryColor: @entangle('primary_color').live,
        backgroundType: @entangle('background_type').live,
        fontStyle: @entangle('font_style').live,
        buttonStyle: @entangle('button_style').live,
        cardStyle: @entangle('card_style').live,
        logoUrl: @entangle('logo_url').live,
        coverImage: @entangle('cover_image').live,
        previewOpen: false,
        previewFrameUrl: '',
        previewTemplateKey: '',
        previewTemplateName: '',
        previewUrl() {
            const params = new URLSearchParams({
                type: @js($type),
                business_id: this.businessId || '',
                template: this.template || '',
                primary_color: this.primaryColor || '',
                background_type: this.backgroundType || '',
                font_style: this.fontStyle || '',
                button_style: this.buttonStyle || '',
                card_style: this.cardStyle || '',
                logo_url: this.logoUrl || '',
                cover_image: this.coverImage || '',
            });

            return @js(route('landing-pages.preview')) + '?' + params.toString();
        },
        templatePreviewUrl(templateKey) {
            const params = new URLSearchParams({
                type: @js($type),
                business_id: this.businessId || '',
                template: templateKey || '',
            });

            return @js(route('landing-pages.preview')) + '?' + params.toString();
        },
        openPreview() {
            this.previewTemplateKey = '';
            this.previewTemplateName = @js(__('Current design'));
            this.previewFrameUrl = this.previewUrl();
            this.previewOpen = true;
        },
        previewTemplate(templateKey, templateName) {
            this.previewTemplateKey = templateKey;
            this.previewTemplateName = templateName || @js(__('Template preview'));
            this.previewFrameUrl = this.templatePreviewUrl(templateKey);
            this.previewOpen = true;
        },
        usePreviewTemplate() {
            if (this.previewTemplateKey) {
                this.template = this.previewTemplateKey;
            }

            this.previewOpen = false;
        },
    }"
>
    <div class="flex items-start justify-between gap-3 p-4">
        <div class="min-w-0">
            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Public Page Preview') }}</p>
            <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('A public page and QR code are created automatically. Customize only when needed.') }}</p>
        </div>
        <div class="flex shrink-0 items-center gap-2">
            <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border text-sm transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-header-text-color); background-color: var(--theme-surface-overlay);" x-on:click="openPreview()" title="{{ __('Live preview') }}" aria-label="{{ __('Live preview') }}">
                <i class="fa-light fa-arrow-up-right"></i>
            </button>
            <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border text-sm transition hover:-translate-y-0.5" style="border-color: rgba(var(--theme-accent-rgb), .30); color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb), .06);" x-on:click="designOpen = ! designOpen" x-bind:title="designOpen ? @js(__('Hide design')) : @js(__('Customize design'))" x-bind:aria-label="designOpen ? @js(__('Hide design')) : @js(__('Customize design'))">
                <i class="fa-light fa-sliders"></i>
            </button>
        </div>
    </div>

    <div class="grid gap-3 border-t p-4 sm:grid-cols-3" style="border-color: rgba(var(--theme-border-color-rgb), .50);">
        <x-ui.checkbox
            wire:model="create_public_page"
            name="{{ $type }}_create_public_page"
            :checked="$create_public_page"
            :label="__('Create public page')"
            :description="__('Enabled by default')"
        />
        <x-ui.checkbox
            wire:model="generate_qr_code"
            name="{{ $type }}_generate_qr_code"
            :checked="$generate_qr_code"
            :label="__('Generate QR code')"
            :description="__('Included in campaign')"
        />
        <div class="text-sm">
            <span class="block font-semibold" style="color: var(--theme-header-text-color);">{{ __('Style') }}</span>
            <span class="text-xs" style="color: var(--theme-muted-text-color);">{{ __('Template + brand color') }}</span>
        </div>
    </div>

    <div x-cloak x-show="designOpen" class="grid gap-4 border-t p-4" style="border-color: rgba(var(--theme-border-color-rgb), .50);">
        <x-ui.select wire:model="landing_template" name="{{ $type }}_landing_template" :label="__('Template')" :error="$errors->first('landing_template')">
            @foreach ($templates as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </x-ui.select>

        <div>
            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Template gallery') }}</p>
            <div class="grid gap-2 sm:grid-cols-2">
                @foreach ($templates as $value => $label)
                    <div class="flex items-center justify-between gap-3 rounded-xl border px-3 py-2" style="border-color: rgba(var(--theme-border-color-rgb), .52); background-color: var(--theme-surface-overlay);">
                        <button type="button" class="min-w-0 flex-1 truncate text-left text-sm font-semibold" style="color: var(--theme-header-text-color);" x-on:click="template = @js($value)">
                            {{ $label }}
                        </button>
                        <div class="flex shrink-0 items-center gap-1.5">
                            <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border text-xs transition" style="border-color: rgba(var(--theme-border-color-rgb), .58); color: var(--theme-muted-text-color);" title="{{ __('Preview') }}" x-on:click="previewTemplate(@js($value), @js($label))">
                                <i class="fa-light fa-eye"></i>
                            </button>
                            <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border text-xs transition" style="border-color: rgba(var(--theme-accent-rgb), .28); color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb), .08);" title="{{ __('Use template') }}" x-on:click="template = @js($value)">
                                <i class="fa-light fa-check"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-ui.color-picker
                wire:model="primary_color"
                name="{{ $type }}_primary_color"
                :label="__('Primary color')"
                :value="$primary_color"
                :error="$errors->first('primary_color')"
                :presets="['#0f766e', '#128a7f', '#5f7f07', '#ff8c42', '#2563eb', '#7c3aed', '#d97706', '#0f172a']"
            />
            <x-ui.select wire:model="background_type" name="{{ $type }}_background_type" :label="__('Background')" :error="$errors->first('background_type')">
                <option value="gradient">{{ __('Gradient') }}</option>
                <option value="solid">{{ __('Solid') }}</option>
                <option value="soft">{{ __('Soft') }}</option>
            </x-ui.select>
            <x-ui.select wire:model="button_style" name="{{ $type }}_button_style" :label="__('Button style')" :error="$errors->first('button_style')">
                <option value="pill">{{ __('Pill') }}</option>
                <option value="rounded">{{ __('Rounded') }}</option>
                <option value="square">{{ __('Square') }}</option>
            </x-ui.select>
            <x-ui.select wire:model="card_style" name="{{ $type }}_card_style" :label="__('Card style')" :error="$errors->first('card_style')">
                <option value="soft">{{ __('Soft card') }}</option>
                <option value="bordered">{{ __('Bordered') }}</option>
                <option value="flat">{{ __('Flat') }}</option>
            </x-ui.select>
            <x-ui.select wire:model="font_style" name="{{ $type }}_font_style" :label="__('Font style')" :error="$errors->first('font_style')">
                <option value="modern">{{ __('Modern') }}</option>
                <option value="classic">{{ __('Classic') }}</option>
                <option value="elegant">{{ __('Elegant') }}</option>
                <option value="friendly">{{ __('Friendly') }}</option>
            </x-ui.select>
        </div>
        <x-ui.image-picker
            wire:model.change="logo_url"
            name="logo_url"
            :label="__('Logo')"
            :value="$logo_url"
            :preview="$logo_url"
            context="portal"
            layout="compact"
            :button-label="__('Choose logo')"
            :empty-label="__('No logo selected')"
            :dialog-title="__('Choose logo image')"
            :dialog-description="__('Select or upload a logo from your file library.')"
            :error="$errors->first('logo_url')"
        />
        <x-ui.image-picker
            wire:model.change="cover_image"
            name="cover_image"
            :label="__('Cover image')"
            :value="$cover_image"
            :preview="$cover_image"
            context="portal"
            layout="compact"
            :button-label="__('Choose cover')"
            :empty-label="__('No cover selected')"
            :dialog-title="__('Choose cover image')"
            :dialog-description="__('Select or upload a cover image for the public page.')"
            :error="$errors->first('cover_image')"
        />
    </div>

    <div
        x-cloak
        x-show="previewOpen"
        class="fixed inset-0 z-[220] overflow-hidden px-4 py-5 sm:px-6 sm:py-7"
        x-on:keydown.escape.window="previewOpen = false"
    >
        <div class="absolute inset-0 bg-slate-950/55 backdrop-blur-[4px]" x-on:click="previewOpen = false"></div>
        <div class="relative mx-auto flex h-full max-w-6xl flex-col overflow-hidden rounded-[1.1rem] border shadow-[0_34px_100px_-36px_rgba(15,23,42,.55)]" style="border-color: rgba(var(--theme-border-color-rgb), .72); background-color: var(--theme-surface-overlay);">
            <div class="flex shrink-0 items-center justify-between gap-4 border-b px-4 py-3 sm:px-5" style="border-color: rgba(var(--theme-border-color-rgb), .62);">
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);" x-text="previewTemplateName"></p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Preview before applying this template.') }}</p>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <button
                        type="button"
                        x-show="previewTemplateKey"
                        x-on:click="usePreviewTemplate()"
                        class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-sm font-semibold"
                        style="border-color: rgba(var(--theme-accent-rgb), .30); color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb), .08);"
                    >
                        <i class="fa-light fa-check"></i>{{ __('Use template') }}
                    </button>
                    <button type="button" x-on:click="previewOpen = false" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border" style="border-color: rgba(var(--theme-border-color-rgb), .62); color: var(--theme-muted-text-color); background-color: var(--theme-surface-overlay);" title="{{ __('Close') }}">
                        <i class="fa-light fa-xmark"></i>
                    </button>
                </div>
            </div>
            <div class="min-h-0 flex-1 bg-slate-100">
                <iframe x-bind:src="previewFrameUrl" class="h-full w-full border-0 bg-white" title="{{ __('Template preview') }}"></iframe>
            </div>
        </div>
    </div>
</div>
