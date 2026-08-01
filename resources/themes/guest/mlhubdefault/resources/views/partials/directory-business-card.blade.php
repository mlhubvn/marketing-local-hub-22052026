@php
    $stats = $entry['stats'] ?? [];
    $statItems = [
        ['key' => 'campaigns', 'label' => __('Campaigns'), 'icon' => 'fa-bullhorn'],
        ['key' => 'qr_scans', 'label' => __('QR scans'), 'icon' => 'fa-qrcode'],
        ['key' => 'bookings', 'label' => __('Bookings'), 'icon' => 'fa-calendar-check'],
        ['key' => 'coupon_codes', 'label' => __('Coupon codes'), 'icon' => 'fa-ticket'],
    ];
@endphp

<article class="lb-card lb-hover lb-reveal rounded-xl p-6" style="--lb-delay: {{ $loop->index * 40 }}ms;">
    <div>
        <p class="text-[10px] font-black uppercase tracking-[0.16em]" style="color: var(--lb-muted);">{{ $entry['industry_group_label'] }}</p>
        <h2 class="lb-serif lb-subheading mt-2">{{ $entry['name'] }}</h2>
        @if (! empty($entry['industry_category_label']))
            <p class="mt-2 text-xs font-bold" style="color: var(--lb-muted);">{{ $entry['industry_category_label'] }}</p>
        @endif
    </div>

    @if ($stats !== [])
        <section class="mt-5 rounded-xl border px-4 py-4 sm:px-5" style="border-color: var(--lb-line); background: color-mix(in srgb, var(--lb-accent, #ff5f5f) 7%, #ffffff);">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="text-[10px] font-black uppercase tracking-[0.16em]" style="color: var(--lb-muted);">{{ __('MKT activity overview') }}</p>
                <span class="lb-pill inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.12em]">
                    <i class="fa-light fa-chart-line"></i>{{ __('Public totals') }}
                </span>
            </div>

            <dl class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
                @foreach ($statItems as $stat)
                    <div class="rounded-lg border bg-white/90 px-3 py-3 text-center" style="border-color: var(--lb-line);">
                        <dt class="flex flex-col items-center gap-1.5 text-[10px] font-black uppercase tracking-[0.12em]" style="color: var(--lb-muted);">
                            <i class="fa-light {{ $stat['icon'] }} text-sm"></i>
                            <span>{{ $stat['label'] }}</span>
                        </dt>
                        <dd class="lb-serif mt-2 text-2xl font-black leading-none">{{ format_number_locale((int) ($stats[$stat['key']] ?? 0)) }}</dd>
                    </div>
                @endforeach
            </dl>

            <p class="mt-3 text-[10px] font-semibold leading-relaxed" style="color: var(--lb-muted);">
                {{ __('Aggregate counts only. No personal customer data is shown on the public directory.') }}
            </p>
        </section>
    @endif

    <dl class="mt-5 grid gap-3 sm:grid-cols-2">
        <div class="flex flex-wrap items-end justify-between gap-3 sm:col-span-2">
            <div class="min-w-0">
                <dt class="text-[10px] font-black uppercase tracking-[0.14em]" style="color: var(--lb-muted);">{{ __('Managed by') }}</dt>
                <dd class="mt-1 text-sm font-bold">{{ $entry['owner_name'] !== '' ? $entry['owner_name'] : __('Unassigned') }}</dd>
            </div>
            <a href="{{ route('guest.contact') }}" class="lb-button-soft inline-flex shrink-0 items-center justify-center px-4 py-2.5 text-xs font-black">
                {{ __('Contact') }}
            </a>
        </div>

        @if ($entry['address'] !== '')
            <div class="sm:col-span-2">
                <dt class="text-[10px] font-black uppercase tracking-[0.14em]" style="color: var(--lb-muted);">{{ __('Address') }}</dt>
                <dd class="mt-1 text-sm font-semibold">{{ $entry['address'] }}</dd>
                @if (! empty($entry['google_maps_url']))
                    <dd class="mt-2">
                        <a href="{{ $entry['google_maps_url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-xs font-black underline decoration-dotted underline-offset-4 transition hover:opacity-80" style="color: var(--lb-accent, #ff5f5f);">
                            <i class="fa-light fa-diamond-turn-right"></i>
                            {{ __('Get directions on Google Maps') }}
                        </a>
                    </dd>
                @endif
            </div>
        @endif

        @if ($entry['phone_masked'])
            <div>
                <dt class="text-[10px] font-black uppercase tracking-[0.14em]" style="color: var(--lb-muted);">{{ __('Phone') }}</dt>
                <dd class="mt-1 font-mono text-sm font-bold">{{ $entry['phone_masked'] }}</dd>
            </div>
        @endif

        @if ($entry['email_masked'])
            <div>
                <dt class="text-[10px] font-black uppercase tracking-[0.14em]" style="color: var(--lb-muted);">{{ __('Email') }}</dt>
                <dd class="mt-1 break-all font-mono text-sm font-bold">{{ $entry['email_masked'] }}</dd>
            </div>
        @endif

        @if (! empty($entry['website_url']))
            <div class="sm:col-span-2">
                <dt class="text-[10px] font-black uppercase tracking-[0.14em]" style="color: var(--lb-muted);">{{ __('Website') }}</dt>
                <dd class="mt-1">
                    <a href="{{ $entry['website_url'] }}" target="_blank" rel="noopener noreferrer" class="break-all text-sm font-bold underline decoration-dotted underline-offset-4 transition hover:opacity-80" style="color: var(--lb-accent, #ff5f5f);">
                        {{ $entry['website_label'] ?? $entry['website_url'] }}
                    </a>
                </dd>
            </div>
        @endif
    </dl>
</article>
