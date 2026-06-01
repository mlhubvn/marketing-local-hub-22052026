@php
    $totalDomains = $domains->count();
    $verifiedDomains = $domains->where('status', 'verified')->count();
    $pendingDomains = max(0, $totalDomains - $verifiedDomains);
    $domainLimit = (int) ($domainLimit ?? -1);
    $domainLimitLabel = $domainLimit < 0 ? __('Unlimited') : format_number_locale($domainLimit);
    $routingHost = parse_url((string) config('app.url'), PHP_URL_HOST) ?: request()->getHost();
    $routingHost = in_array($routingHost, ['127.0.0.1', 'localhost', ''], true) ? request()->getHost() : $routingHost;
@endphp

<div class="px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <div class="mx-auto max-w-[92rem] space-y-5">
        <section class="overflow-hidden rounded-[1.45rem] border" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
            <div class="grid gap-0 xl:grid-cols-[minmax(0,1fr)_minmax(28rem,0.72fr)]">
                <div class="p-5 sm:p-7">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.14em]" style="background-color: rgba(var(--theme-accent-rgb),0.09); color: var(--theme-accent);">
                            <i class="fa-light fa-globe-pointer"></i>
                            {{ __('Brand routing') }}
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-[11px] font-semibold text-emerald-700">
                            <i class="fa-light fa-shield-check"></i>
                            {{ __('DNS verified links') }}
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.14em]" style="background-color: rgba(var(--theme-border-color-rgb),0.12); color: var(--theme-muted-text-color);">
                            {{ __('Limit') }} {{ $totalDomains }} / {{ $domainLimitLabel }}
                        </span>
                    </div>
                    <h1 class="mt-5 text-[2.25rem] font-semibold leading-tight tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ __('Custom Domains') }}</h1>
                    <p class="mt-3 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Connect branded subdomains for QR redirects, short links, and Link Bio pages. Keep campaign URLs owned by your brand instead of exposing the application host.') }}</p>

                    <div class="mt-6 grid gap-3 sm:grid-cols-3">
                        @foreach ([
                            ['label' => __('Domains'), 'value' => $totalDomains, 'icon' => 'fa-globe-pointer', 'color' => '#2563eb'],
                            ['label' => __('Verified'), 'value' => $verifiedDomains, 'icon' => 'fa-circle-check', 'color' => '#059669'],
                            ['label' => __('Pending DNS'), 'value' => $pendingDomains, 'icon' => 'fa-clock', 'color' => '#ea580c'],
                        ] as $stat)
                            <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-soft);">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ $stat['label'] }}</p>
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-[0.75rem]" style="background-color: {{ $stat['color'] }}14; color: {{ $stat['color'] }};">
                                        <i class="fa-light {{ $stat['icon'] }}"></i>
                                    </span>
                                </div>
                                <p class="mt-3 text-3xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ format_number_locale($stat['value']) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="border-t p-5 xl:border-l xl:border-t-0" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background: linear-gradient(135deg, rgba(37,99,235,0.10), rgba(16,185,129,0.12) 48%, rgba(245,158,11,0.12));">
                    <div class="rounded-[1.25rem] border p-4 shadow-[0_28px_70px_-46px_rgba(15,23,42,0.5)]" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.84);">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Public URL flow') }}</p>
                                <p class="mt-1 text-sm font-semibold" style="color: var(--theme-header-text-color);">your-domain.com/lp/spring</p>
                            </div>
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full shadow-sm" style="background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.88); color: var(--theme-accent);">
                                <i class="fa-light fa-route"></i>
                            </span>
                        </div>

                        <div class="mt-4 grid gap-3">
                            @foreach ([
                                ['label' => __('QR campaign'), 'value' => __('Dynamic redirect'), 'icon' => 'fa-qrcode', 'color' => '#2563eb'],
                                ['label' => __('Link Bio page'), 'value' => __('Creator landing URL'), 'icon' => 'fa-link-horizontal', 'color' => '#10b981'],
                                ['label' => __('Analytics'), 'value' => __('Scans, clicks, source'), 'icon' => 'fa-chart-line', 'color' => '#f59e0b'],
                            ] as $route)
                                <div class="flex items-center gap-3 rounded-[0.95rem] border px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.78);">
                                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-[0.75rem]" style="background-color: {{ $route['color'] }}14; color: {{ $route['color'] }};">
                                        <i class="fa-light {{ $route['icon'] }}"></i>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $route['label'] }}</p>
                                        <p class="mt-0.5 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $route['value'] }}</p>
                                    </div>
                                    <i class="fa-light fa-arrow-right text-xs" style="color: var(--theme-muted-text-color);"></i>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-5 xl:grid-cols-[minmax(25rem,0.42fr)_minmax(0,0.58fr)]">
            <section class="rounded-[1.25rem] border p-5 shadow-[0_24px_65px_-52px_rgba(15,23,42,0.45)]" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">{{ __('Add branded domain') }}</h2>
                        <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Enter the exact domain customers will open. Use a root domain or any subdomain you own.') }}</p>
                    </div>
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-[1rem]" style="background-color: rgba(var(--theme-accent-rgb),0.09); color: var(--theme-accent);">
                        <i class="fa-light fa-plus"></i>
                    </span>
                </div>

                <div class="mt-5 space-y-4">
                    <div>
                        <x-ui.input wire:model.defer="domain" name="domain" :label="__('Domain')" :placeholder="__('your-domain.com or sub.your-domain.com')" />
                        @error('domain')
                            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="rounded-[0.95rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb),0.6); background-color: var(--theme-surface-soft);">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-[0.8rem]" style="background-color: rgba(var(--theme-accent-rgb),0.1); color: var(--theme-accent);">
                                <i class="fa-light fa-link-horizontal"></i>
                            </span>
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Shared domain') }}</p>
                                <p class="mt-0.5 text-xs" style="color: var(--theme-muted-text-color);">{{ __('One verified domain works for Link Bio pages, QR redirects, and campaign analytics.') }}</p>
                            </div>
                        </div>
                    </div>

                    <x-ui.button type="button" wire:click="addDomain" wire:loading.attr="disabled" wire:target="addDomain" class="w-full justify-center">
                        <i class="fa-light fa-plus" wire:loading.remove wire:target="addDomain"></i>
                        <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="addDomain"></i>
                        <span wire:loading.remove wire:target="addDomain">{{ __('Add domain') }}</span>
                        <span wire:loading wire:target="addDomain">{{ __('Adding...') }}</span>
                    </x-ui.button>
                </div>

                <div class="mt-5 rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.66); background-color: var(--theme-surface-soft);">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-[0.85rem] bg-emerald-50 text-emerald-700">
                            <i class="fa-light fa-shield-check"></i>
                        </span>
                        <div>
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('DNS setup') }}</p>
                            <p class="mt-0.5 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Add these records at your DNS provider before verifying.') }}</p>
                        </div>
                    </div>

                    <div class="mt-4 rounded-[0.9rem] border px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: var(--theme-surface-base);">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('App routing host') }}</p>
                        <p class="mt-1 break-all font-mono text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $routingHost }}</p>
                        <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Use only this hostname as the DNS target.') }}</p>
                    </div>

                    <div class="mt-3 space-y-2">
                        @foreach ([
                            [
                                'name' => __('CNAME'),
                                'value' => __('If you enter a subdomain, use only its left part as the DNS Name and point it to :host.', ['host' => $routingHost]),
                                'code' => '{subdomain}  CNAME  '.$routingHost,
                            ],
                            [
                                'name' => __('Root domain'),
                                'value' => __('If you enter the root domain, use @ as the DNS Name and point it to :host.', ['host' => $routingHost]),
                                'code' => '@  CNAME  '.$routingHost,
                            ],
                            [
                                'name' => __('TXT'),
                                'value' => __('Use @ for root domains, or the same subdomain name used above. Paste the generated token as TXT content.'),
                                'code' => '{name}  TXT  qr-verify-...',
                            ],
                        ] as $index => $step)
                            <div class="flex items-start gap-3 rounded-[0.85rem] border px-3 py-2.5" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: var(--theme-surface-base);">
                                <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.1); color: var(--theme-accent);">{{ $index + 1 }}</span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $step['name'] }}</p>
                                    <p class="text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $step['value'] }}</p>
                                    <p class="mt-1 break-all rounded-[0.5rem] px-2 py-1 font-mono text-[11px]" style="background-color: rgba(var(--theme-border-color-rgb),0.08); color: var(--theme-header-text-color);">{{ $step['code'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="rounded-[1.25rem] border p-5 shadow-[0_24px_65px_-52px_rgba(15,23,42,0.45)]" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">{{ __('Connected domains') }}</h2>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Manage DNS token, verification status, and the shared default domain.') }}</p>
                    </div>
                    <span class="inline-flex w-fit items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">
                        <i class="fa-light fa-server"></i>
                        {{ __('Routing table') }}
                    </span>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($domains as $domain)
                        @php($domainErrorKey = $domain['scope'].'-'.$domain['id'])
                        <div class="rounded-[1.05rem] border p-4 transition hover:-translate-y-0.5 hover:shadow-[0_20px_42px_-34px_rgba(15,23,42,0.42)]" style="border-color: rgba(var(--theme-border-color-rgb),0.7); background: linear-gradient(180deg, var(--theme-surface-base), var(--theme-surface-soft));">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-[0.95rem]" style="background-color: rgba(var(--theme-accent-rgb),0.09); color: var(--theme-accent);">
                                        <i class="fa-light fa-globe-pointer"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-base font-semibold" style="color: var(--theme-header-text-color);">{{ $domain['domain'] }}</p>
                                        <p class="mt-0.5 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Created') }} {{ format_date_locale($domain['created_at'] ?? null) }}</p>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <x-ui.badge :variant="$domain['status'] === 'verified' ? 'success' : 'neutral'">{{ ucfirst($domain['status']) }}</x-ui.badge>
                                    @if (! empty($domain['is_default']))
                                        <x-ui.badge variant="info">{{ __('Default') }}</x-ui.badge>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-4 grid gap-3 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                                <div class="rounded-[0.85rem] border px-3 py-2" style="border-color: rgba(var(--theme-border-color-rgb),0.6); background-color: var(--theme-surface-base);">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('TXT verification record') }}</p>
                                    <p class="mt-1 break-all font-mono text-xs" style="color: var(--theme-header-text-color);">{{ $domain['verification_token'] }}</p>
                                </div>

                                <div class="flex flex-wrap gap-2 lg:justify-end">
                                    @if ($domain['status'] !== 'verified')
                                        <x-ui.button type="button" wire:click="markVerified('{{ $domain['scope'] }}', {{ $domain['id'] }})" wire:loading.attr="disabled" wire:target="markVerified('{{ $domain['scope'] }}', {{ $domain['id'] }})" variant="outline" size="sm">
                                            <i class="fa-light fa-circle-check"></i>
                                            {{ __('Verify DNS') }}
                                        </x-ui.button>
                                    @elseif (! empty($domain['is_default']))
                                        <x-ui.button type="button" wire:click="unsetDefault('{{ $domain['scope'] }}', {{ $domain['id'] }})" variant="outline" size="sm">
                                            <i class="fa-light fa-star-half-stroke"></i>
                                            {{ __('Unset default') }}
                                        </x-ui.button>
                                    @else
                                        <x-ui.button type="button" wire:click="setDefault('{{ $domain['scope'] }}', {{ $domain['id'] }})" variant="outline" size="sm">
                                            <i class="fa-light fa-star"></i>
                                            {{ __('Set default') }}
                                        </x-ui.button>
                                    @endif
                                    <x-ui.dialog wire:key="domain-delete-dialog-{{ $domain['scope'] }}-{{ $domain['id'] }}" :title="__('Delete this custom domain?')" :description="__('This removes the shared domain. Existing public QR and Link Bio URLs using it may stop working.')" width="sm" dismissible>
                                        <x-slot:trigger>
                                            <x-ui.button type="button" variant="outline" size="sm" class="hover:!border-red-200 hover:!text-red-600">
                                                <i class="fa-light fa-trash-can"></i>
                                                {{ __('Delete') }}
                                            </x-ui.button>
                                        </x-slot:trigger>
                                        <x-slot:footer>
                                            <div class="flex justify-end gap-3">
                                                <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                                                <x-ui.button type="button" variant="danger" wire:click="deleteDomain('{{ $domain['scope'] }}', {{ $domain['id'] }})" x-on:click="open = false">
                                                    <i class="fa-light fa-trash-can"></i>
                                                    {{ __('Delete domain') }}
                                                </x-ui.button>
                                            </div>
                                        </x-slot:footer>
                                    </x-ui.dialog>
                                </div>
                            </div>
                            @php($health = (array) ($domain['health'] ?? []))
                            <div class="mt-3 grid gap-2 sm:grid-cols-3">
                                <div class="rounded-[0.8rem] border px-3 py-2" style="border-color: rgba(var(--theme-border-color-rgb),0.56); background-color: var(--theme-surface-base);">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">{{ __('TXT') }}</p>
                                    <p class="mt-1 text-xs font-semibold" style="color: {{ ! empty($health['txt_verified']) ? '#059669' : '#ea580c' }};">{{ ! empty($health['txt_verified']) ? __('Found') : __('Waiting') }}</p>
                                </div>
                                <div class="rounded-[0.8rem] border px-3 py-2" style="border-color: rgba(var(--theme-border-color-rgb),0.56); background-color: var(--theme-surface-base);">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">{{ __('CNAME') }}</p>
                                    <p class="mt-1 truncate text-xs font-semibold" style="color: {{ ! empty($health['has_cname']) ? '#059669' : '#ea580c' }};">{{ ! empty($health['has_cname']) ? implode(', ', (array) ($health['cname_targets'] ?? [])) : __('Not found') }}</p>
                                </div>
                                <div class="rounded-[0.8rem] border px-3 py-2" style="border-color: rgba(var(--theme-border-color-rgb),0.56); background-color: var(--theme-surface-base);">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">{{ __('SSL') }}</p>
                                    <p class="mt-1 text-xs font-semibold" style="color: {{ ! empty($health['ssl_ready']) ? '#059669' : '#ea580c' }};">{{ ! empty($health['ssl_ready']) ? __('Ready') : __('Pending') }}</p>
                                </div>
                            </div>

                            @if (! empty($domainErrors[$domainErrorKey] ?? null))
                                <div class="mt-3 rounded-[0.85rem] border px-3 py-2 text-sm font-medium text-red-700" style="border-color: rgba(248,113,113,0.4); background-color: rgba(254,226,226,0.72);">
                                    {{ $domainErrors[$domainErrorKey] }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="rounded-[1.15rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background: linear-gradient(135deg, rgba(37,99,235,0.06), rgba(16,185,129,0.08));">
                            <div class="grid gap-4 lg:grid-cols-[minmax(0,0.58fr)_minmax(0,0.42fr)] lg:items-center">
                                <div>
                                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-[1rem] shadow-sm" style="background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.88); color: var(--theme-accent);">
                                        <i class="fa-light fa-globe-pointer text-lg"></i>
                                    </span>
                                    <h3 class="mt-4 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('No custom domains yet') }}</h3>
                                    <p class="mt-2 max-w-lg text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Add a branded subdomain to turn public campaign URLs into owned brand links.') }}</p>
                                </div>
                                <div class="space-y-2 rounded-[1rem] p-3 shadow-sm" style="background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.78);">
                                    @foreach ([__('Add domain'), __('Copy TXT token'), __('Verify DNS')] as $index => $label)
                                        <div class="flex items-center gap-3 rounded-[0.8rem] px-3 py-2" style="background-color: rgba(var(--theme-border-color-rgb),0.06);">
                                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full text-xs font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.1); color: var(--theme-accent);">{{ $index + 1 }}</span>
                                            <span class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $label }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</div>
