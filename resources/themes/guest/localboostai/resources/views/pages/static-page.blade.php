@component(theme_view('layouts.marketing', 'guest'), ['pageTitle' => $pageTitle])
    @php
        $hasHtml = str_contains($pageContent, '<') && str_contains($pageContent, '>');
        $pageType = $pageType ?? 'html';
        $socialLinks = $socialLinks ?? [];
        $eyebrow = $pageType === 'social'
            ? __('Social directory')
            : (str_contains(strtolower($pageTitle), 'privacy')
                ? __('Privacy')
                : (str_contains(strtolower($pageTitle), 'term') ? __('Legal') : __('Information')));
        $pageTabs = [
            ['label' => __('Privacy Policy'), 'route' => 'guest.privacy-policy'],
            ['label' => __('Terms of Use'), 'route' => 'guest.terms-of-use'],
            ['label' => __('Social Pages'), 'route' => 'guest.social-pages'],
        ];
    @endphp

    <style>
        .lb-static-eyebrow {
            border-color: color-mix(in srgb, var(--theme-border-color, #dfe9df) 88%, transparent);
            background: color-mix(in srgb, #b8da16 18%, #fff);
            color: #4f6907;
            box-shadow: 0 12px 34px -28px rgba(15, 118, 110, .45);
        }

        .lb-static-tabs {
            border-color: color-mix(in srgb, var(--theme-border-color, #dfe9df) 88%, transparent);
            background: rgba(255, 255, 252, .86);
            box-shadow: 0 16px 42px -32px rgba(16, 37, 31, .42);
        }

        .lb-static-tab {
            color: #66746d;
        }

        .lb-static-tab:hover {
            background: color-mix(in srgb, #0f766e 8%, #fff);
            color: #0f766e;
        }

        .lb-static-tab.is-active {
            background: #0f766e;
            color: #fff;
            box-shadow: 0 12px 28px -20px rgba(15, 118, 110, .72);
        }

        .lb-static-content a,
        .lb-static-content.prose a {
            color: #0f766e;
        }

        html[data-theme-resolved='dark'] .lb-static-eyebrow {
            border-color: rgba(184, 218, 22, .22) !important;
            background: rgba(184, 218, 22, .12) !important;
            color: #d9f75d !important;
        }

        html[data-theme-resolved='dark'] .lb-static-tabs,
        html[data-theme-resolved='dark'] .lb-static-tab:hover,
        html[data-theme-resolved='dark'] .lb-static-content [class*="bg-white"],
        html[data-theme-resolved='dark'] .guest-static-content [style*="#fff"],
        html[data-theme-resolved='dark'] .guest-static-content [style*="255, 255, 255"] {
            border-color: rgba(96, 165, 250, .22) !important;
            background: rgba(15, 23, 42, .82) !important;
            color: #e8eef7 !important;
        }

        html[data-theme-resolved='dark'] .lb-static-tab {
            color: #cbd5e1 !important;
        }

        html[data-theme-resolved='dark'] .lb-static-tab.is-active {
            background: #0f766e !important;
            color: #fff !important;
        }

        html[data-theme-resolved='dark'] .lb-static-content,
        html[data-theme-resolved='dark'] .guest-static-content {
            color: #cbd5e1 !important;
        }

        html[data-theme-resolved='dark'] .lb-static-content a,
        html[data-theme-resolved='dark'] .lb-static-content.prose a {
            color: #5eead4 !important;
        }
    </style>

    <section class="localboost-shell localboost-section pt-10">
        <div class="mx-auto max-w-5xl">
            <span class="lb-static-eyebrow inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-bold uppercase tracking-[0.18em]">
                <i class="fa-light fa-file-lines"></i>
                {{ $eyebrow }}
            </span>
            <h1 class="mt-6 max-w-4xl text-5xl font-extrabold leading-[1.02] tracking-[-0.07em] text-slate-950 md:text-6xl">{{ $pageTitle }}</h1>
            <p class="mt-5 max-w-3xl text-base leading-8 text-slate-600">
                {{ $pageType === 'social'
                    ? __('Public destinations connected to the LocalBoostAI brand.')
                    : __('Clear public information for customers, buyers, and operators reviewing the platform.') }}
            </p>

            <div class="lb-static-tabs mt-8 flex flex-wrap gap-2 rounded-[1.25rem] border p-2">
                @foreach ($pageTabs as $tab)
                    <a href="{{ route($tab['route']) }}" class="lb-static-tab {{ $routeName === $tab['route'] ? 'is-active' : '' }} rounded-full px-4 py-2 text-sm font-bold transition">
                        {{ $tab['label'] }}
                    </a>
                @endforeach
            </div>

            <div class="localboost-card mt-6 overflow-hidden rounded-[1.6rem]">
                <div class="border-b px-7 py-5 md:px-9" style="border-color: rgba(var(--theme-border-color-rgb),0.82);">
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400">{{ __('Page content') }}</p>
                </div>
                <div class="px-7 py-7 md:px-9 md:py-8">
                    @if ($pageType === 'social')
                        @if (trim($pageContent) !== '')
                            <div class="mb-8 max-w-3xl text-base leading-8 text-slate-600 whitespace-pre-line">{{ $pageContent }}</div>
                        @endif

                        @if ($socialLinks !== [])
                            <div class="grid gap-4 md:grid-cols-2">
                                @foreach ($socialLinks as $link)
                                    <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" class="localboost-card localboost-hover-lift rounded-[1.2rem] p-5">
                                        <div class="flex items-center gap-4">
                                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[0.9rem]" style="background: color-mix(in srgb, #0f766e 9%, #fff); color: #0f766e;">
                                                <i class="{{ $link['icon'] }} text-xl"></i>
                                            </span>
                                            <div class="min-w-0">
                                                <p class="font-extrabold text-slate-950">{{ $link['label'] }}</p>
                                                <p class="mt-1 truncate text-sm text-slate-500">{{ $link['url'] }}</p>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-[1.25rem] border border-dashed px-6 py-12 text-center text-sm text-slate-500" style="border-color: rgba(var(--theme-border-color-rgb),0.9);">{{ __('No social links have been configured yet.') }}</div>
                        @endif
                    @elseif (trim($pageContent) === '')
                        <div class="rounded-[1.25rem] border border-dashed px-6 py-12 text-center text-sm text-slate-500" style="border-color: rgba(var(--theme-border-color-rgb),0.9);">{{ __('This page has not been configured yet.') }}</div>
                    @elseif ($hasHtml)
                        <div class="lb-static-content guest-static-content prose prose-slate max-w-none">
                            {!! $pageContent !!}
                        </div>
                    @else
                        <div class="lb-static-content guest-static-content whitespace-pre-line text-sm leading-8 text-slate-600">
                            {{ $pageContent }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endcomponent
