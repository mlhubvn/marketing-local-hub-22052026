@php
    $homeUrl = route('home');
    $aboutUrl = route('guest.about');
    $homeNavActive = request()->routeIs('home');
    $aboutNavActive = request()->routeIs('guest.about');
    $homeNavSections = [
        ['label' => __('Platform overview'), 'hash' => '#hero'],
        ['label' => __('Growth workflow'), 'hash' => '#workflow'],
        ['label' => __('Core features'), 'hash' => '#features'],
        ['label' => __('How it works'), 'hash' => '#how-it-works'],
        ['label' => __('Real-world proof'), 'hash' => '#product-proof'],
        ['label' => __('Flexible expansion'), 'hash' => '#modules'],
        ['label' => __('Launch campaigns'), 'hash' => '#get-started'],
    ];
    $aboutNavSections = [
        ['label' => __('MLHUB (Making Local HUB)'), 'hash' => '#about-what'],
        ['label' => __('Vision & Mission'), 'hash' => '#about-vision'],
        ['label' => __('Market challenges'), 'hash' => '#about-pain'],
        ['label' => __('Comprehensive solutions'), 'hash' => '#about-solutions'],
        ['label' => __('Productivity digitization'), 'hash' => '#about-benefits'],
        ['label' => __('Revenue replication'), 'hash' => '#about-journey'],
    ];
    $resourceNavSections = [
        ['label' => __('Solutions'), 'href' => route('guest.solutions')],
        ['label' => __('Pricing'), 'href' => route('guest.pricing')],
        ['label' => __('Blog'), 'href' => route('guest.blogs')],
        ['label' => __('FAQs (FAQ)'), 'href' => route('guest.faqs')],
    ];
    $resourcesNavActive = request()->routeIs('guest.solutions')
        || request()->routeIs('guest.pricing')
        || request()->routeIs('guest.blogs')
        || request()->routeIs('guest.blog*')
        || request()->routeIs('guest.faqs');
    $contactNavActive = request()->routeIs('guest.contact');
@endphp

<nav class="grid gap-1" x-data="{ homeOpen: false, aboutOpen: false, resourcesOpen: false }">
    <div>
        <button
            type="button"
            x-on:click="homeOpen = ! homeOpen"
            class="flex w-full items-center justify-between rounded-[0.95rem] px-3 py-2.5 text-sm font-bold {{ $homeNavActive ? 'text-white' : 'text-slate-700' }}"
            @if($homeNavActive) style="background:#ff5f5f;" @endif
        >
            <span>{{ __('Home') }}</span>
            <i class="fa-light text-xs" x-bind:class="homeOpen ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
        </button>
        <div x-cloak x-show="homeOpen" x-transition class="mt-1 grid gap-0.5 pl-2">
            @foreach ($homeNavSections as $section)
                <a href="{{ $homeUrl }}{{ $section['hash'] }}" class="rounded-[0.85rem] px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ $section['label'] }}</a>
            @endforeach
        </div>
    </div>

    <div>
        <button
            type="button"
            x-on:click="aboutOpen = ! aboutOpen"
            class="flex w-full items-center justify-between rounded-[0.95rem] px-3 py-2.5 text-sm font-bold {{ $aboutNavActive ? 'text-white' : 'text-slate-700 hover:bg-slate-50' }}"
            @if($aboutNavActive) style="background:#ff5f5f;" @endif
        >
            <span>{{ __('About') }}</span>
            <i class="fa-light text-xs" x-bind:class="aboutOpen ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
        </button>
        <div x-cloak x-show="aboutOpen" x-transition class="mt-1 grid gap-0.5 pl-2">
            @foreach ($aboutNavSections as $section)
                <a href="{{ $aboutUrl }}{{ $section['hash'] }}" class="rounded-[0.85rem] px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ $section['label'] }}</a>
            @endforeach
        </div>
    </div>

    <div>
        <button
            type="button"
            x-on:click="resourcesOpen = ! resourcesOpen"
            class="flex w-full items-center justify-between rounded-[0.95rem] px-3 py-2.5 text-sm font-bold {{ $resourcesNavActive ? 'text-white' : 'text-slate-700 hover:bg-slate-50' }}"
            @if($resourcesNavActive) style="background:#ff5f5f;" @endif
        >
            <span>{{ __('Resources') }}</span>
            <i class="fa-light text-xs" x-bind:class="resourcesOpen ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
        </button>
        <div x-cloak x-show="resourcesOpen" x-transition class="mt-1 grid gap-0.5 pl-2">
            @foreach ($resourceNavSections as $section)
                <a href="{{ $section['href'] }}" class="rounded-[0.85rem] px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ $section['label'] }}</a>
            @endforeach
        </div>
    </div>

    <a
        href="{{ route('guest.contact') }}"
        class="rounded-[0.95rem] px-3 py-2.5 text-sm font-bold {{ $contactNavActive ? 'text-white' : 'text-slate-600 hover:bg-slate-50' }}"
        @if($contactNavActive) style="background:#ff5f5f;" @endif
    >{{ __('Contact') }}</a>
</nav>
