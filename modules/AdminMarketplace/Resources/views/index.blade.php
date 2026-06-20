@php
    $metricCards = [
        ['label' => __('Installed packages'), 'value' => format_number_locale($summary['total']), 'description' => __('Modules currently managed by Marketplace.'), 'icon' => 'fa-light fa-boxes-stacked', 'tone' => 'var(--theme-accent)'],
        ['label' => __('Active packages'), 'value' => format_number_locale($summary['active']), 'description' => __('Packages currently booted through the marketplace provider bootstrap file.'), 'icon' => 'fa-light fa-toggle-on', 'tone' => 'rgb(5 150 105)'],
        ['label' => __('Inactive packages'), 'value' => format_number_locale($summary['inactive']), 'description' => __('Installed packages that are not currently active.'), 'icon' => 'fa-light fa-toggle-off', 'tone' => 'rgb(100 116 139)'],
        ['label' => __('Manual ZIP packages'), 'value' => format_number_locale($summary['zip']), 'description' => __('Packages installed manually from a ZIP upload.'), 'icon' => 'fa-light fa-file-zipper', 'tone' => 'rgb(59 130 246)'],
    ];
    $openInstallModal = request()->boolean('install') || $errors->has('module_zip') || $errors->has('purchase_code');
    $installMethod = old('install_method', $errors->has('module_zip') ? 'zip' : 'purchase');

    $sourceMap = [
        'purchase' => [
            'label' => __('Marketplace'),
            'hint' => __('Purchase code'),
        ],
        'zip' => [
            'label' => __('Manual'),
            'hint' => __('ZIP package'),
        ],
        'local' => [
            'label' => __('Directory'),
            'hint' => __('Local module'),
        ],
    ];
@endphp

<div class="space-y-6">
    @if (session('status'))
        <x-ui.alert variant="success" :title="__('Updated')" :description="session('status')" dismissible />
    @endif

    @if ($errors->has('marketplace'))
        <x-ui.alert :title="__('Marketplace error')" :description="$errors->first('marketplace')" variant="danger" dismissible />
    @endif

    <x-ui.page-hero
        :eyebrow="__('Settings')"
        :title="__('Marketplace')"
        :description="__('Manage the module packages installed in this app: install, activate, and uninstall.')"
        :count="$summary['total']"
        icon="fa-light fa-store"
    >
        <x-slot:actions>
            <form method="POST" action="{{ route('admin-marketplace.lock.engage') }}">
                @csrf
                <x-ui.button type="submit" variant="outline">
                    <i class="fa-light fa-lock text-sm"></i>
                    {{ __('Lock') }}
                </x-ui.button>
            </form>

            <form method="POST" action="{{ route('admin-marketplace.rescan') }}">
                @csrf
                <x-ui.button type="submit" variant="outline">
                    <i class="fa-light fa-arrows-rotate text-sm"></i>
                    {{ __('Rescan') }}
                </x-ui.button>
            </form>

            <x-ui.button :href="route('admin-marketplace.packages.index')" variant="outline" wire:navigate>
                <i class="fa-light fa-boxes-stacked text-sm"></i>
                {{ __('Packages') }}
            </x-ui.button>

            <x-ui.modal
                width="lg"
                :title="__('Install package')"
                :description="__('Install with a purchase code or upload a ZIP package manually.')"
                :initially-open="$openInstallModal"
            >
                <x-slot:trigger>
                    <x-ui.button type="button">
                        <i class="fa-light fa-cloud-arrow-down text-sm"></i>
                        {{ __('Install') }}
                    </x-ui.button>
                </x-slot:trigger>

                <div x-data="{ method: '{{ $installMethod }}', submitting: false }">
                    <form id="marketplace-install-form-inline" method="POST" action="{{ route('admin-marketplace.install') }}" enctype="multipart/form-data" class="space-y-5" x-on:submit="submitting = true">
                        @csrf
                        <input type="hidden" name="install_method" x-model="method">

                        <div class="grid grid-cols-2 gap-3 rounded-2xl border p-2" style="border-color: var(--theme-border-color); background: rgba(15,23,42,0.02);">
                            <button
                                type="button"
                                x-on:click="method = 'purchase'"
                                class="rounded-2xl px-4 py-3 text-sm font-medium transition"
                                :style="method === 'purchase'
                                    ? 'background: var(--theme-accent); color: white;'
                                    : 'background: transparent; color: var(--theme-header-text-color);'"
                            >
                                {{ __('Purchase code') }}
                            </button>
                            <button
                                type="button"
                                x-on:click="method = 'zip'"
                                class="rounded-2xl px-4 py-3 text-sm font-medium transition"
                                :style="method === 'zip'
                                    ? 'background: var(--theme-accent); color: white;'
                                    : 'background: transparent; color: var(--theme-header-text-color);'"
                            >
                                {{ __('ZIP file') }}
                            </button>
                        </div>

                        <div x-show="method === 'purchase'" x-cloak class="space-y-3">
                            <x-ui.input
                                id="purchase_code"
                                name="purchase_code"
                                :label="__('Purchase code')"
                                :placeholder="__('Enter your Envato purchase code')"
                                :value="old('purchase_code')"
                                :error="$errors->first('purchase_code')"
                            />
                            <div class="rounded-2xl border px-4 py-3 text-sm" style="border-color: var(--theme-border-color); background: rgba(59,130,246,0.04); color: var(--theme-muted-text-color);">
                                {{ __('Use this when you want to verify a purchase code and install the package directly from the marketplace.') }}
                            </div>
                        </div>

                        <div x-show="method === 'zip'" x-cloak class="space-y-3">
                            <div class="space-y-2">
                                <label for="module_zip" class="text-sm font-medium" style="color: var(--theme-header-text-color);">{{ __('Module ZIP') }}</label>
                                <input
                                    id="module_zip"
                                    name="module_zip"
                                    type="file"
                                    accept=".zip"
                                    class="block w-full rounded-2xl border px-4 py-3 text-sm"
                                    style="border-color: var(--theme-border-color); background: var(--theme-card-background); color: var(--theme-header-text-color);"
                                >
                                <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ __('The ZIP must extract into modules/YourModuleName and include module.json at the root of that folder.') }}</p>
                                @error('module_zip')
                                    <p class="text-sm text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 border-t pt-5" style="border-color: var(--theme-border-color);">
                            <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                            <x-ui.button type="submit" x-bind:disabled="submitting">
                                <span x-show="!submitting">{{ __('Install package') }}</span>
                                <span x-show="submitting" x-cloak>{{ __('Installing...') }}</span>
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            </x-ui.modal>
        </x-slot:actions>
    </x-ui.page-hero>

    <x-ui.metric-strip :items="$metricCards" :show-progress="false" columns="md:grid-cols-2 xl:grid-cols-4" />

    <x-ui.card>
        <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_220px_auto]">
            <label class="space-y-2">
                <span class="text-sm font-medium" style="color: var(--theme-header-text-color);">{{ __('Search installed packages') }}</span>
                <input
                    type="text"
                    wire:model.live.debounce.250ms="search"
                    placeholder="{{ __('Package name, module, version...') }}"
                    class="block w-full rounded-2xl border px-4 py-3 text-sm"
                    style="border-color: var(--theme-border-color); background: var(--theme-card-background); color: var(--theme-header-text-color);"
                >
            </label>

            <label class="space-y-2">
                <span class="text-sm font-medium" style="color: var(--theme-header-text-color);">{{ __('Status') }}</span>
                <select
                    wire:model.live="statusFilter"
                    class="block w-full rounded-2xl border px-4 py-3 text-sm"
                    style="border-color: var(--theme-border-color); background: var(--theme-card-background); color: var(--theme-header-text-color);"
                >
                    <option value="all">{{ __('All') }}</option>
                    <option value="1">{{ __('Active') }}</option>
                    <option value="0">{{ __('Inactive') }}</option>
                </select>
            </label>

            <div class="flex flex-wrap items-end gap-3">
                <x-ui.button type="button" variant="outline" wire:click="resetPackageFilters">{{ __('Clear') }}</x-ui.button>
            </div>
        </div>
    </x-ui.card>

    <x-ui.datatable-shell :title="__('Installed packages')" :info="__('Module packages actually present in this app.')">
        <x-ui.table class="rounded-none border-0 shadow-none">
            <x-ui.table-head>
                <x-ui.table-cell head>{{ __('Package') }}</x-ui.table-cell>
                <x-ui.table-cell head>{{ __('Version') }}</x-ui.table-cell>
                <x-ui.table-cell head>{{ __('Status') }}</x-ui.table-cell>
            </x-ui.table-head>
            <x-ui.table-body>
                @forelse ($packages as $package)
                    @php
                        $sourceMeta = $sourceMap[$package->source_type] ?? $sourceMap['local'];
                    @endphp
                    <x-ui.table-row>
                        <x-ui.table-cell>
                            <div class="space-y-2">
                                <p class="font-medium" style="color: var(--theme-header-text-color);">{{ $package->title }}</p>
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-2 text-xs" style="color: var(--theme-muted-text-color);">
                                    @if ($package->module_name)
                                        <span>
                                            <span class="font-semibold">{{ __('Module') }}:</span>
                                            <span class="font-mono">{{ $package->module_name }}</span>
                                        </span>
                                    @endif

                                    <span class="inline-flex items-center gap-2">
                                        <span class="h-2 w-2 rounded-full" style="{{ $package->source_type === 'purchase' ? 'background: rgb(16,185,129);' : ($package->source_type === 'zip' ? 'background: rgb(59,130,246);' : 'background: rgb(148,163,184);') }}"></span>
                                        <span>{{ $sourceMeta['label'] }}</span>
                                    </span>
                                </div>
                                @if ($package->description)
                                    <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ \Illuminate\Support\Str::limit($package->description, 120) }}</p>
                                @endif
                            </div>
                        </x-ui.table-cell>
                        <x-ui.table-cell>
                            <span class="text-sm" style="color: var(--theme-header-text-color);">{{ $package->version ?: __('Unknown') }}</span>
                        </x-ui.table-cell>
                        <x-ui.table-cell>
                            <span class="inline-flex items-center gap-2 text-sm font-medium" style="color: {{ $package->is_active ? 'rgb(6,95,70)' : 'var(--theme-muted-text-color)' }};">
                                <span class="h-2 w-2 rounded-full" style="{{ $package->is_active ? 'background: rgb(16,185,129);' : 'background: rgb(148,163,184);' }}"></span>
                                {{ $package->is_active ? __('Active') : __('Inactive') }}
                            </span>
                        </x-ui.table-cell>
                    </x-ui.table-row>
                @empty
                    <x-ui.table-row>
                        <x-ui.table-cell colspan="3" class="py-10">
                            <x-ui.empty icon="fa-light fa-box-open" :title="__('No installed marketplace packages found.')" :description="__('Upload the first module ZIP or rescan after you copy a package into the modules directory.')" />
                        </x-ui.table-cell>
                    </x-ui.table-row>
                @endforelse
            </x-ui.table-body>
        </x-ui.table>
    </x-ui.datatable-shell>
</div>
