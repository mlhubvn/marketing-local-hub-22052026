@php
    $homeUrl = route('home');
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
    $simpleNavItems = [
        ['label' => __('Pricing'), 'href' => route('guest.pricing')],
        ['label' => __('Blog'), 'href' => route('guest.blogs')],
        ['label' => __('FAQs'), 'href' => route('guest.faqs')],
        ['label' => __('Contact'), 'href' => route('guest.contact')],
    ];
@endphp

<nav class="grid gap-1" x-data="{ homeOpen: false, aboutOpen: false }">
    <div>
        <button
            type="button"
            x-on:click="homeOpen = ! homeOpen"
            class="flex w-full items-center justify-between rounded-[0.95rem] px-3 py-2.5 text-sm font-bold text-white"
            style="background:#ff5f5f;"
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
            class="flex w-full items-center justify-between rounded-[0.95rem] px-3 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50"
        >
            <span>{{ __('About') }}</span>
            <i class="fa-light text-xs" x-bind:class="aboutOpen ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
        </button>
        <div x-cloak x-show="aboutOpen" x-transition class="mt-1 grid gap-0.5 pl-2">
            @foreach ($aboutNavSections as $section)
                <a href="{{ $homeUrl }}{{ $section['hash'] }}" class="rounded-[0.85rem] px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ $section['label'] }}</a>
            @endforeach
        </div>
    </div>

    @foreach ($simpleNavItems as $item)
        <a href="{{ $item['href'] }}" class="rounded-[0.95rem] px-3 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50">{{ $item['label'] }}</a>
    @endforeach
</nav>
