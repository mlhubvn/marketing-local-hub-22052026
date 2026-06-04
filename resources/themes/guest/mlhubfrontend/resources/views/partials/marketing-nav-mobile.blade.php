@php
    $homeUrl = route('home');
    $aboutUrl = route('guest.about');
    $homeNavActive = request()->routeIs('home');
    $aboutNavActive = request()->routeIs('guest.about');
    $homeNavSections = [
        ['label' => __('Overview'), 'hash' => '#hero'],
        ['label' => __('Connected workflow'), 'hash' => '#workflow'],
        ['label' => __('Features'), 'hash' => '#features'],
        ['label' => __('How it works'), 'hash' => '#how-it-works'],
        ['label' => __('Public campaign pages'), 'hash' => '#product-proof'],
        ['label' => __('Platform modules'), 'hash' => '#modules'],
        ['label' => __('Campaign workflow'), 'hash' => '#growth-flow'],
        ['label' => __('Get started'), 'hash' => '#get-started'],
    ];
    $aboutNavSections = [
        ['label' => __('What is MLHUB?'), 'hash' => '#about-what'],
        ['label' => __('Vision & Mission'), 'hash' => '#about-vision'],
        ['label' => __('Market challenges'), 'hash' => '#about-pain'],
        ['label' => __('MLHUB solutions'), 'hash' => '#about-solutions'],
        ['label' => __('Core benefits'), 'hash' => '#about-benefits'],
        ['label' => __('Your MLHUB journey'), 'hash' => '#about-journey'],
    ];
    $resourceNavSections = [
        ['label' => __('Pricing'), 'href' => route('guest.pricing')],
        ['label' => __('Blog'), 'href' => route('guest.blogs')],
        ['label' => __('FAQs'), 'href' => route('guest.faqs')],
    ];
    $resourcesNavActive = request()->routeIs('guest.pricing')
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
            <p class="px-3 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">{{ __('Solutions') }}</p>
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
