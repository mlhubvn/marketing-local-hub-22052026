<section class="w-full" wire:key="admin-cache-index-{{ config('livewire.release_token', 'default') }}">
    <x-settings.layout :heading="__('Cache & session')" :subheading="__('Clear application caches and manage sessions safely.')">
        <div class="space-y-6">
            @if ($statusMessage)
                <x-ui.alert wire:key="cache-status-{{ md5($statusMessage.$statusVariant) }}" :variant="$statusVariant" :title="$statusVariant === 'success' ? __('Completed') : __('Action failed')" :description="$statusMessage" />
            @endif

            @if (($redisDiagnostics['session_driver'] ?? '') === 'redis' || ($redisDiagnostics['cache_store'] ?? '') === 'redis')
                <x-ui.alert
                    wire:key="cache-redis-diagnostics"
                    variant="neutral"
                    :title="__('Redis status')"
                    :description="__('Session and cache use separate Redis databases. Clear sessions targets the session database; application cache clear targets the cache database.')"
                >
                    <dl class="mt-3 grid gap-2 text-xs sm:grid-cols-2" style="color: var(--theme-muted-text-color);">
                        @if (($redisDiagnostics['session_driver'] ?? '') === 'redis')
                            <div>
                                <dt class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('Session Redis') }}</dt>
                                <dd>{{ $redisDiagnostics['session_redis_connection'] ?? '—' }} / DB {{ $redisDiagnostics['session_redis_database'] ?? '—' }}
                                    @if (! empty($redisDiagnostics['session_redis_ping_error']))
                                        <span style="color: var(--theme-danger-color);"> — {{ $redisDiagnostics['session_redis_ping_error'] }}</span>
                                    @else
                                        <span style="color: var(--theme-success-color);"> — {{ __('Connected') }}</span>
                                    @endif
                                </dd>
                            </div>
                        @endif
                        @if (($redisDiagnostics['cache_store'] ?? '') === 'redis')
                            <div>
                                <dt class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('Cache Redis') }}</dt>
                                <dd>{{ $redisDiagnostics['cache_redis_connection'] ?? '—' }} / DB {{ $redisDiagnostics['cache_redis_database'] ?? '—' }}
                                    @if (! empty($redisDiagnostics['cache_redis_ping_error']))
                                        <span style="color: var(--theme-danger-color);"> — {{ $redisDiagnostics['cache_redis_ping_error'] }}</span>
                                    @else
                                        <span style="color: var(--theme-success-color);"> — {{ __('Connected') }}</span>
                                    @endif
                                </dd>
                            </div>
                        @endif
                    </dl>
                </x-ui.alert>
            @endif

            <div class="grid gap-6 md:grid-cols-2">
                @foreach ($cacheActions as $action)
                    <div wire:key="cache-action-card-{{ $action['key'] }}">
                        <x-theme.section-card
                            :title="__($action['title'])"
                            :description="__($action['description'])"
                            body-class="p-6"
                        >
                            <x-ui.button
                                type="button"
                                :variant="$action['variant']"
                                block
                                wire:click="runAction('{{ $action['key'] }}')"
                                wire:confirm="{{ __($action['confirm_title']).'|'.__($action['confirm']) }}"
                                wire:loading.attr="disabled"
                                wire:target="runAction"
                            >
                                <i class="fa-light {{ $action['icon'] }}"></i>
                                <span>{{ __($action['button']) }}</span>
                            </x-ui.button>
                        </x-theme.section-card>
                    </div>
                @endforeach
            </div>

            <x-theme.section-card
                wire:key="cache-action-card-{{ $optimizeAction['key'] }}"
                :title="__($optimizeAction['title'])"
                :description="__($optimizeAction['description'])"
                body-class="p-6"
            >
                <x-ui.button
                    type="button"
                    :variant="$optimizeAction['variant']"
                    block
                    wire:click="runAction('{{ $optimizeAction['key'] }}')"
                    wire:confirm="{{ __($optimizeAction['confirm_title']).'|'.__($optimizeAction['confirm']) }}"
                    wire:loading.attr="disabled"
                    wire:target="runAction"
                >
                    <i class="fa-light {{ $optimizeAction['icon'] }}"></i>
                    <span>{{ __($optimizeAction['button']) }}</span>
                </x-ui.button>
            </x-theme.section-card>

            <x-ui.alert
                wire:key="cache-action-card-{{ $sessionAction['key'] }}"
                variant="danger"
                inline
                :title="__($sessionAction['title'])"
                :description="__($sessionAction['description'])"
            >
                <div class="mt-4">
                    <x-ui.button
                        type="button"
                        :variant="$sessionAction['variant']"
                        block
                        wire:click="runAction('{{ $sessionAction['key'] }}')"
                        wire:confirm="{{ __($sessionAction['confirm_title']).'|'.__($sessionAction['confirm']) }}"
                        wire:loading.attr="disabled"
                        wire:target="runAction"
                    >
                        <i class="fa-light {{ $sessionAction['icon'] }}"></i>
                        <span>{{ __($sessionAction['button']) }}</span>
                    </x-ui.button>
                </div>
            </x-ui.alert>
        </div>
    </x-settings.layout>
</section>
