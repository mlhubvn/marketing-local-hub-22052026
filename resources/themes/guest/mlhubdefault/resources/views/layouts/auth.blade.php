<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include(theme_view('partials.head', 'guest'))
        @livewireStyles
    </head>
    @php
        $brandOptions = app(\Modules\AdminSettings\Support\OptionStore::class);
        $authSiteTitle = trim((string) $brandOptions->get('website_title', config('app.name', 'MLHUB AI')));
        $authDarkLogoPath = (string) ($brandOptions->get('website_logo_brand_dark')
            ?: $brandOptions->get('website_logo_dark')
            ?: $brandOptions->get('website_logo')
            ?: 'img/logo-brand-dark.png');
        $authLightLogoPath = (string) ($brandOptions->get('website_logo_brand_light')
            ?: $brandOptions->get('website_logo_light')
            ?: $brandOptions->get('website_logo')
            ?: 'img/logo-brand-light.png');
        $resolvedAuthDarkLogo = url($authDarkLogoPath);
        $resolvedAuthLightLogo = url($authLightLogoPath);
    @endphp
    <body class="lb-auth-page min-h-screen antialiased">
        <div class="relative min-h-screen overflow-hidden px-4 py-6 sm:px-6 lg:px-8">
            <div class="pointer-events-none absolute -right-28 top-20 h-80 w-80 rounded-full opacity-40 blur-3xl" style="background: var(--lb-lime);"></div>

            <div class="lb-wrap relative grid min-h-[calc(100vh-3rem)] gap-8 lg:grid-cols-[0.92fr_1.08fr] lg:items-center">
                <section class="hidden lg:block">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-3" wire:navigate>
                        <img src="{{ $resolvedAuthDarkLogo }}" alt="{{ $authSiteTitle }}" class="theme-logo-dark h-9 w-auto max-w-[12rem] object-contain">
                        <img src="{{ $resolvedAuthLightLogo }}" alt="{{ $authSiteTitle }}" class="theme-logo-light h-9 w-auto max-w-[12rem] object-contain">
                    </a>

                    <div class="lb-reveal mt-14 max-w-xl">
                        <span class="lb-pill inline-flex items-center gap-2 rounded-full px-4 py-2 text-xs font-black uppercase tracking-[0.18em]">
                            <i class="fa-light fa-sparkles"></i>
                            {{ __('Local growth workspace') }}
                        </span>
                        <h1 class="lb-serif lb-heading mt-7">{{ __('Sign in and keep every local campaign moving') }}</h1>
                        <p class="lb-copy mt-5 text-lg">{{ __('Manage review boosters, booking pages, coupons, feedback forms, lead forms, QR campaign pages, AI copy, and reports from one calm workspace.') }}</p>
                    </div>

                    <div class="mt-10 grid max-w-xl gap-4">
                        @foreach ([['fa-star', __('Review clicks'), __('Turn happy customers into public reviews.')], ['fa-qrcode', __('QR campaign pages'), __('Share every offer and request offline.' )], ['fa-chart-line', __('Growth reports'), __('Track visits, conversions, leads, and bookings.')]] as $index => $item)
                            <div class="lb-card lb-hover lb-reveal rounded-xl p-4" style="--lb-delay: {{ 120 + ($index * 80) }}ms;">
                                <div class="flex gap-4">
                                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg" style="background: color-mix(in srgb, var(--lb-lime) 30%, #fff); color:#ff5f5f;">
                                        <i class="fa-light {{ $item[0] }}"></i>
                                    </span>
                                    <div>
                                        <p class="font-black">{{ $item[1] }}</p>
                                        <p class="lb-copy mt-1 text-sm">{{ $item[2] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="flex min-h-[calc(100vh-3rem)] items-center justify-center lg:min-h-0">
                    <div class="w-full max-w-[38rem]">
                        <div class="mb-6 flex items-center justify-between gap-4 lg:hidden">
                            <a href="{{ route('home') }}" class="inline-flex items-center gap-2" wire:navigate>
                                <img src="{{ $resolvedAuthDarkLogo }}" alt="{{ $authSiteTitle }}" class="theme-logo-dark h-9 w-auto max-w-[12rem] object-contain">
                                <img src="{{ $resolvedAuthLightLogo }}" alt="{{ $authSiteTitle }}" class="theme-logo-light h-9 w-auto max-w-[12rem] object-contain">
                            </a>
                            <div class="flex items-center gap-2">
                                @include(theme_view('partials.appearance-toggle', 'guest'))
                                @include(theme_view('partials.auth-language-switcher', 'guest'))
                            </div>
                        </div>

                        <div class="lb-window lb-reveal relative overflow-hidden rounded-xl p-6 sm:p-8 lg:p-10">
                            <div class="mb-7 hidden items-center justify-between gap-4 lg:flex">
                                <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-bold transition hover:opacity-70" style="color: var(--lb-muted);" wire:navigate>
                                    <i class="fa-light fa-arrow-left mr-2"></i>
                                    {{ __('Back to home') }}
                                </a>
                                <div class="flex items-center gap-2">
                                    @include(theme_view('partials.appearance-toggle', 'guest'))
                                    @include(theme_view('partials.auth-language-switcher', 'guest'))
                                </div>
                            </div>
                            <div class="mb-7 flex items-center gap-1.5">
                                <span class="lb-dot bg-red-400"></span>
                                <span class="lb-dot bg-amber-400"></span>
                                <span class="lb-dot bg-lime-500"></span>
                            </div>
                            {{ $slot }}
                        </div>
                        <div class="mt-5 flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-sm font-semibold" style="color: var(--lb-muted);">
                            <a href="{{ route('guest.privacy-policy') }}" class="transition hover:opacity-80" style="color: inherit;" wire:navigate>{{ __('Privacy Policy') }}</a>
                            <span class="h-1 w-1 rounded-full" style="background: color-mix(in srgb, var(--lb-red) 35%, var(--lb-line));"></span>
                            <a href="{{ route('guest.terms-of-use') }}" class="transition hover:opacity-80" style="color: inherit;" wire:navigate>{{ __('Terms of Use') }}</a>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include(theme_view('partials.embed-code-body', 'guest'))
        @livewireScripts
    </body>
</html>
