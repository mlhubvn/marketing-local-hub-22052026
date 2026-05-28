<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ current_locale_direction() }}">
<head>
    @include(theme_view('partials.head', 'guest'), ['title' => $pageTitle ?? null])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
</head>
@php
    $languages = available_languages();
    $options = app(\Modules\AdminSettings\Support\OptionStore::class);
    $settingsTitle = trim((string) $options->get('website_title', ''));
    $siteTitle = $settingsTitle !== '' && ! str_contains(strtolower($settingsTitle), 'stackposts') ? $settingsTitle : 'MLHUB';
    $siteLogoDark = trim((string) ($options->get('website_logo_brand_dark')
        ?: $options->get('website_logo_dark')
        ?: $options->get('website_logo')
        ?: 'img/logo-brand-dark.png'));
    $siteLogoLight = trim((string) ($options->get('website_logo_brand_light')
        ?: $options->get('website_logo_light')
        ?: $options->get('website_logo')
        ?: 'img/logo-brand-light.png'));
    $siteLogoDarkUrl = url($siteLogoDark);
    $siteLogoLightUrl = url($siteLogoLight);
    $siteDescription = trim((string) $options->get('website_description', ''));
    $siteDescription = $siteDescription !== '' ? $siteDescription : __('AI-powered local marketing tools for reviews, bookings, leads, coupons, feedback, QR campaign pages, and reports.');
    $signupEnabled = (string) $options->get('auth_signup_page_status', '1') === '1';
    $contactEmail = trim((string) $options->get('contact_email', ''));
    $navItems = [
        ['key' => 'home', 'label' => __('Home'), 'href' => route('home'), 'active' => request()->routeIs('home')],
        ['key' => 'features', 'label' => __('Features'), 'href' => route('home').'#features', 'active' => false],
        ['key' => 'pricing', 'label' => __('Pricing'), 'href' => route('guest.pricing'), 'active' => request()->routeIs('guest.pricing')],
        ['key' => 'blog', 'label' => __('Blog'), 'href' => route('guest.blogs'), 'active' => request()->routeIs('guest.blogs') || request()->routeIs('guest.blog*')],
        ['key' => 'faqs', 'label' => __('FAQs'), 'href' => route('guest.faqs'), 'active' => request()->routeIs('guest.faqs')],
        ['key' => 'contact', 'label' => __('Contact'), 'href' => route('guest.contact'), 'active' => request()->routeIs('guest.contact')],
    ];
    $footerLegal = [
        ['label' => __('Privacy Policy'), 'href' => route('guest.privacy-policy')],
        ['label' => __('Terms of Use'), 'href' => route('guest.terms-of-use')],
    ];
@endphp
<body class="min-h-screen antialiased" style="font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; color: #2d1810; background: #fffbf8;">
    <div class="relative isolate min-h-screen overflow-hidden">
        <header
            x-data="{
                open: false,
                scrolled: false,
                mode: window.themeMode?.getMode?.() || 'dark',
                resolved: window.themeMode?.getResolved?.() || 'dark',
                syncTheme(state = null) {
                    this.mode = state?.mode || window.themeMode?.getMode?.() || 'dark';
                    this.resolved = state?.resolved || window.themeMode?.getResolved?.() || 'dark';
                },
                toggleTheme() {
                    this.syncTheme(window.themeMode?.toggle?.());
                }
            }"
            x-init="
                scrolled = window.scrollY > 12;
                syncTheme();
                window.addEventListener('scroll', () => { scrolled = window.scrollY > 12 }, { passive: true });
                window.addEventListener('theme-mode-changed', (event) => syncTheme(event.detail));
            "
            class="fixed left-0 right-0 top-3 z-50"
        >
            <div
                class="mx-auto rounded-[1.45rem] border bg-white/82 px-3.5 shadow-[0_18px_52px_-46px_rgba(36,35,32,0.55)] backdrop-blur-2xl transition"
                style="width: min(1160px, calc(100% - 40px)); border-color: rgba(255,229,220,0.86);"
                x-bind:class="scrolled ? 'bg-white/94 shadow-[0_18px_50px_-40px_rgba(36,35,32,0.42)]' : ''"
            >
                <div>
                    <div class="flex min-h-[3.5rem] items-center justify-between gap-6">
                        <a href="{{ route('home') }}" class="group flex min-w-0 items-center no-theme-link">
                            <span class="relative inline-flex h-10 shrink-0 items-center justify-center overflow-hidden">
                                <img class="theme-logo-dark h-9 w-auto max-w-[11rem] object-contain" src="{{ $siteLogoDarkUrl }}" alt="{{ $siteTitle }}">
                                <img class="theme-logo-light h-9 w-auto max-w-[11rem] object-contain" src="{{ $siteLogoLightUrl }}" alt="{{ $siteTitle }}">
                            </span>
                        </a>

                        <nav class="hidden items-center gap-2 lg:flex">
                            @foreach ($navItems as $item)
                                <a href="{{ $item['href'] }}" class="rounded-full px-4 py-2.5 text-sm font-bold transition {{ $item['active'] ? 'bg-neutral-100 text-neutral-950' : 'text-neutral-500 hover:bg-neutral-50 hover:text-neutral-950' }}">
                                    {{ $item['label'] }}
                                </a>
                            @endforeach
                        </nav>

                        <div class="hidden items-center gap-2 lg:flex">
                            <button
                                type="button"
                                x-on:click="toggleTheme()"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-full border bg-white/74 text-neutral-600 shadow-sm transition hover:bg-neutral-50"
                                style="border-color: #ffe5dc;"
                                x-bind:aria-label="resolved === 'dark' ? @js(__('Switch to light mode')) : @js(__('Switch to dark mode'))"
                                x-bind:title="resolved === 'dark' ? @js(__('Switch to light mode')) : @js(__('Switch to dark mode'))"
                            >
                                <i class="fa-light text-sm" x-bind:class="resolved === 'dark' ? 'fa-sun-bright' : 'fa-moon-stars'"></i>
                            </button>

                            @if ($languages->isNotEmpty())
                                @php $activeLanguage = $languages->firstWhere('code', app()->getLocale()) ?? $languages->first(); @endphp
                                <div x-data="{ openLanguage: false }" class="relative">
                                    <button type="button" x-on:click="openLanguage = ! openLanguage" x-on:click.outside="openLanguage = false" class="inline-flex h-10 items-center gap-2 rounded-full border bg-white/74 px-3 text-sm font-bold text-neutral-600 shadow-sm" style="border-color: #ffe5dc;">
                                        <span class="{{ language_flag_class($activeLanguage ?? app()->getLocale()) }} rounded-sm text-[17px]"></span>
                                    </button>
                                    <div x-cloak x-show="openLanguage" x-transition.origin.top.right class="absolute right-0 z-30 mt-3 w-56 overflow-hidden rounded-[1rem] border bg-white p-2 shadow-xl" style="border-color: rgba(var(--theme-border-color-rgb),0.85);">
                                        @foreach ($languages as $language)
                                            @php $isActiveLanguage = app()->getLocale() === $language->code; @endphp
                                            <a href="{{ route('language.switch', $language->code) }}" class="flex items-center gap-3 rounded-[0.8rem] px-3 py-2.5 text-sm font-semibold transition {{ $isActiveLanguage ? 'text-white' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }}" @if($isActiveLanguage) style="background: linear-gradient(135deg, #ff5f5f 0%, #ff8c42 100%);" @endif>
                                                <span class="{{ language_flag_class($language) }} rounded-sm text-[17px]"></span>
                                                <span class="flex-1">{{ $language->name ?? strtoupper((string) $language->code) }}</span>
                                                @if ($isActiveLanguage)
                                                    <i class="fa-light fa-check text-xs"></i>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @auth
                                <a href="{{ route('portal.dashboard') }}" class="inline-flex h-10 items-center justify-center rounded-full px-5 text-sm font-black text-white shadow-[0_14px_28px_-20px_rgba(255,95,95,0.62)]" style="background: linear-gradient(135deg, #ff5f5f 0%, #ff8c42 100%);">{{ __('Dashboard') }}</a>
                            @else
                                <a href="{{ route('login') }}" class="inline-flex h-10 items-center justify-center rounded-full px-4 text-sm font-bold text-neutral-500 transition hover:bg-neutral-100 hover:text-neutral-950">{{ __('Log in') }}</a>
                                <a href="{{ $signupEnabled ? route('register') : route('login') }}" class="inline-flex h-10 items-center justify-center rounded-full px-5 text-sm font-black text-white shadow-[0_14px_28px_-20px_rgba(255,95,95,0.62)]" style="background: linear-gradient(135deg, #ff5f5f 0%, #ff8c42 100%);">{{ __('Sign up') }}</a>
                            @endauth
                        </div>

                        <button type="button" x-on:click="open = ! open" class="inline-flex h-9 w-9 items-center justify-center rounded-full border bg-white text-neutral-700 shadow-sm lg:hidden" style="border-color: #ffe5dc;">
                            <i class="fa-light fa-bars"></i>
                        </button>
                    </div>

                    <div x-cloak x-show="open" x-transition class="border-t pb-4 pt-3 lg:hidden" style="border-color: rgba(var(--theme-border-color-rgb),0.75);">
                        <nav class="grid gap-1">
                            @foreach ($navItems as $item)
                                <a href="{{ $item['href'] }}" class="rounded-[0.95rem] px-3 py-2.5 text-sm font-bold {{ $item['active'] ? 'text-white' : 'text-slate-600 hover:bg-slate-50' }}" @if($item['active']) style="background: linear-gradient(135deg, #ff5f5f 0%, #ff8c42 100%);" @endif>{{ $item['label'] }}</a>
                            @endforeach
                        </nav>
                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                            <button
                                type="button"
                                x-on:click="toggleTheme()"
                                class="localboost-button-secondary inline-flex items-center justify-center rounded-[var(--theme-button-radius)] px-4 py-3 text-sm font-semibold sm:col-span-2"
                            >
                                <i class="fa-light mr-2" x-bind:class="resolved === 'dark' ? 'fa-sun-bright' : 'fa-moon-stars'"></i>
                                <span x-text="resolved === 'dark' ? @js(__('Light mode')) : @js(__('Dark mode'))"></span>
                            </button>
                            @auth
                                <a href="{{ route('portal.dashboard') }}" class="localboost-button-primary inline-flex items-center justify-center rounded-[var(--theme-button-radius)] px-4 py-3 text-sm font-bold">{{ __('Dashboard') }}</a>
                            @else
                                <a href="{{ route('login') }}" class="localboost-button-secondary inline-flex items-center justify-center rounded-[var(--theme-button-radius)] px-4 py-3 text-sm font-semibold">{{ __('Log in') }}</a>
                                <a href="{{ $signupEnabled ? route('register') : route('login') }}" class="localboost-button-primary inline-flex items-center justify-center rounded-[var(--theme-button-radius)] px-4 py-3 text-sm font-bold">{{ __('Sign up') }}</a>
                            @endauth
                        </div>
                    </div>
                    </div>
            </div>
        </header>

        <main class="pt-20">
            @hasSection('content')
                @yield('content')
            @else
                {{ $slot ?? '' }}
            @endif
        </main>

        <footer class="border-t bg-[#fffbf8]" style="border-color: #ffe5dc;">
            <div class="mx-auto py-14" style="width: min(1120px, calc(100% - 40px));">
                <div class="grid gap-10 lg:grid-cols-[1.1fr_0.8fr_0.8fr_0.8fr]">
                    <div>
                        <a href="{{ route('home') }}" class="inline-flex items-center no-theme-link">
                            <span class="inline-flex h-10 shrink-0 items-center justify-center overflow-hidden">
                                <img class="theme-logo-dark h-9 w-auto max-w-[11rem] object-contain" src="{{ $siteLogoDarkUrl }}" alt="{{ $siteTitle }}">
                                <img class="theme-logo-light h-9 w-auto max-w-[11rem] object-contain" src="{{ $siteLogoLightUrl }}" alt="{{ $siteTitle }}">
                            </span>
                        </a>
                        <p class="mt-4 max-w-md text-sm leading-7 text-neutral-500">{{ __('AI-powered local marketing tools for reviews, bookings, leads, coupons, feedback, QR campaign pages, and reports.') }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-neutral-400">{{ __('Product') }}</p>
                        <div class="mt-4 grid gap-3 text-sm font-bold text-neutral-600">
                            <a href="{{ route('home') }}#features" class="hover:text-[#e84a3a]">{{ __('Features') }}</a>
                            <a href="{{ route('guest.pricing') }}" class="hover:text-[#e84a3a]">{{ __('Pricing') }}</a>
                            <a href="{{ route('home') }}#how-it-works" class="hover:text-[#e84a3a]">{{ __('How it works') }}</a>
                            <a href="{{ route('guest.blogs') }}" class="hover:text-[#e84a3a]">{{ __('Blog') }}</a>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-neutral-400">{{ __('Company') }}</p>
                        <div class="mt-4 grid gap-3 text-sm font-bold text-neutral-600">
                            <a href="{{ route('guest.contact') }}" class="hover:text-[#e84a3a]">{{ __('Contact') }}</a>
                            @foreach ($footerLegal as $item)
                                <a href="{{ $item['href'] }}" class="hover:text-[#e84a3a]">{{ $item['label'] }}</a>
                            @endforeach
                            @if ($contactEmail !== '')
                                <a href="mailto:{{ $contactEmail }}" class="hover:text-[#e84a3a]">{{ $contactEmail }}</a>
                            @endif
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-neutral-400">{{ __('Built for') }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ([__('Reviews'), __('Bookings'), __('Leads'), __('QR campaigns'), __('Reports')] as $badge)
                                <span class="rounded-full border bg-white px-3 py-1.5 text-[11px] font-bold text-neutral-600 shadow-sm" style="border-color: #ffe5dc;">{{ $badge }}</span>
                            @endforeach
                        </div>
                        <a href="{{ route('login') }}" class="mt-5 inline-flex h-11 items-center justify-center rounded-full px-5 text-sm font-bold text-white shadow-[0_14px_28px_-20px_rgba(255,95,95,0.62)]" style="background: linear-gradient(135deg, #ff5f5f 0%, #ff8c42 100%);">
                            {{ __('Start Free Trial') }}
                        </a>
                    </div>
                </div>
            </div>
            <div class="relative border-t overflow-hidden" style="border-color: #ffe5dc;">
                <div class="pointer-events-none absolute -bottom-20 left-10 select-none font-serif font-black leading-none text-neutral-100" style="font-size: clamp(7rem, 18vw, 15rem);">MLHUB</div>
                <div class="relative mx-auto flex flex-col gap-3 py-6 text-xs font-bold text-neutral-400 sm:flex-row sm:items-center sm:justify-between" style="width: min(1120px, calc(100% - 40px));">
                    <p>&copy; {{ date('Y') }} {{ $siteTitle }}. {{ __('All rights reserved.') }}</p>
                    <p>{{ __('AI-powered local marketing tools in one SaaS.') }}</p>
                </div>
            </div>
        </footer>
    </div>
    @include(theme_view('partials.gdpr-consent', 'guest'))
    @include(theme_view('partials.embed-code-body', 'guest'))
</body>
</html>
