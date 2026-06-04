@php
    $homeUrl = route('home');
    $aboutUrl = route('guest.about');
    $homeNavSections = [
        ['label' => __('Overview'), 'hash' => '#hero', 'icon' => 'fa-house'],
        ['label' => __('Connected workflow'), 'hash' => '#workflow', 'icon' => 'fa-diagram-project'],
        ['label' => __('Features'), 'hash' => '#features', 'icon' => 'fa-grid-2'],
        ['label' => __('How it works'), 'hash' => '#how-it-works', 'icon' => 'fa-route'],
        ['label' => __('Public campaign pages'), 'hash' => '#product-proof', 'icon' => 'fa-browser'],
        ['label' => __('Platform modules'), 'hash' => '#modules', 'icon' => 'fa-layer-group'],
        ['label' => __('Campaign workflow'), 'hash' => '#growth-flow', 'icon' => 'fa-bullhorn'],
        ['label' => __('Get started'), 'hash' => '#get-started', 'icon' => 'fa-rocket-launch'],
    ];
    $aboutNavSections = [
        ['label' => __('What is MLHUB?'), 'hash' => '#about-what', 'icon' => 'fa-circle-info'],
        ['label' => __('Vision & Mission'), 'hash' => '#about-vision', 'icon' => 'fa-compass'],
        ['label' => __('Market challenges'), 'hash' => '#about-pain', 'icon' => 'fa-triangle-exclamation'],
        ['label' => __('MLHUB solutions'), 'hash' => '#about-solutions', 'icon' => 'fa-lightbulb'],
        ['label' => __('Core benefits'), 'hash' => '#about-benefits', 'icon' => 'fa-gem'],
        ['label' => __('Your MLHUB journey'), 'hash' => '#about-journey', 'icon' => 'fa-road'],
    ];
    $resourceNavSections = [
        ['label' => __('Pricing'), 'href' => route('guest.pricing'), 'icon' => 'fa-credit-card'],
        ['label' => __('Blog'), 'href' => route('guest.blogs'), 'icon' => 'fa-newspaper'],
        ['label' => __('FAQs'), 'href' => route('guest.faqs'), 'icon' => 'fa-circle-question'],
    ];
    $homeNavActive = request()->routeIs('home');
    $aboutNavActive = request()->routeIs('guest.about');
    $resourcesNavActive = request()->routeIs('guest.pricing')
        || request()->routeIs('guest.blogs')
        || request()->routeIs('guest.blog*')
        || request()->routeIs('guest.faqs');
    $contactNavActive = request()->routeIs('guest.contact');
@endphp

<nav class="hidden items-center gap-2 lg:flex">
    <div
        x-data="{ open: false }"
        class="relative"
        x-on:mouseenter="open = true"
        x-on:mouseleave="open = false"
    >
        <a
            href="{{ $homeUrl }}#hero"
            class="inline-flex items-center gap-1.5 rounded-full px-4 py-2.5 text-sm font-bold transition {{ $homeNavActive ? 'bg-neutral-100 text-neutral-950' : 'text-neutral-500 hover:bg-neutral-50 hover:text-neutral-950' }}"
        >
            {{ __('Home') }}
            <i class="fa-light fa-chevron-down text-[10px] opacity-70"></i>
        </a>
        <div
            x-cloak
            x-show="open"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
            class="absolute left-0 z-40 mt-2 w-[17.5rem] overflow-hidden rounded-[1.1rem] border bg-white p-2 shadow-xl"
            style="border-color: rgba(232,229,220,0.95);"
        >
            @foreach ($homeNavSections as $section)
                <a
                    href="{{ $homeUrl }}{{ $section['hash'] }}"
                    class="flex items-center gap-3 rounded-[0.85rem] px-3 py-2.5 text-sm font-semibold text-neutral-600 transition hover:bg-neutral-50 hover:text-neutral-950"
                >
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg" style="background: color-mix(in srgb, #ff5f5f 10%, #fff); color: #ff5f5f;">
                        <i class="fa-light {{ $section['icon'] }} text-sm"></i>
                    </span>
                    <span>{{ $section['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <div
        x-data="{ open: false }"
        class="relative"
        x-on:mouseenter="open = true"
        x-on:mouseleave="open = false"
    >
        <a
            href="{{ $aboutUrl }}#about-what"
            class="inline-flex items-center gap-1.5 rounded-full px-4 py-2.5 text-sm font-bold transition {{ $aboutNavActive ? 'bg-neutral-100 text-neutral-950' : 'text-neutral-500 hover:bg-neutral-50 hover:text-neutral-950' }}"
        >
            {{ __('About') }}
            <i class="fa-light fa-chevron-down text-[10px] opacity-70"></i>
        </a>
        <div
            x-cloak
            x-show="open"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
            class="absolute left-0 z-40 mt-2 w-[17.5rem] overflow-hidden rounded-[1.1rem] border bg-white p-2 shadow-xl"
            style="border-color: rgba(232,229,220,0.95);"
        >
            @foreach ($aboutNavSections as $section)
                <a
                    href="{{ $aboutUrl }}{{ $section['hash'] }}"
                    class="flex items-center gap-3 rounded-[0.85rem] px-3 py-2.5 text-sm font-semibold text-neutral-600 transition hover:bg-neutral-50 hover:text-neutral-950"
                >
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg" style="background: color-mix(in srgb, #ff5f5f 10%, #fff); color: #ff5f5f;">
                        <i class="fa-light {{ $section['icon'] }} text-sm"></i>
                    </span>
                    <span>{{ $section['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <div
        x-data="{ open: false }"
        class="relative"
        x-on:mouseenter="open = true"
        x-on:mouseleave="open = false"
    >
        <a
            href="{{ route('guest.pricing') }}"
            class="inline-flex items-center gap-1.5 rounded-full px-4 py-2.5 text-sm font-bold transition {{ $resourcesNavActive ? 'bg-neutral-100 text-neutral-950' : 'text-neutral-500 hover:bg-neutral-50 hover:text-neutral-950' }}"
        >
            {{ __('Resources') }}
            <i class="fa-light fa-chevron-down text-[10px] opacity-70"></i>
        </a>
        <div
            x-cloak
            x-show="open"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
            class="absolute left-0 z-40 mt-2 w-[15.5rem] overflow-hidden rounded-[1.1rem] border bg-white p-2 shadow-xl"
            style="border-color: rgba(232,229,220,0.95);"
        >
            <p class="px-3 pb-1 pt-2 text-[10px] font-black uppercase tracking-[0.14em] text-neutral-400">{{ __('Solutions') }}</p>
            @foreach ($resourceNavSections as $section)
                <a
                    href="{{ $section['href'] }}"
                    class="flex items-center gap-3 rounded-[0.85rem] px-3 py-2.5 text-sm font-semibold text-neutral-600 transition hover:bg-neutral-50 hover:text-neutral-950"
                >
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg" style="background: color-mix(in srgb, #ff5f5f 10%, #fff); color: #ff5f5f;">
                        <i class="fa-light {{ $section['icon'] }} text-sm"></i>
                    </span>
                    <span>{{ $section['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <a href="{{ route('guest.contact') }}" class="rounded-full px-4 py-2.5 text-sm font-bold transition {{ $contactNavActive ? 'bg-neutral-100 text-neutral-950' : 'text-neutral-500 hover:bg-neutral-50 hover:text-neutral-950' }}">
        {{ __('Contact') }}
    </a>
</nav>
