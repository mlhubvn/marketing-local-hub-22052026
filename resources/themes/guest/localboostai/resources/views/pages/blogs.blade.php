@component(theme_view('layouts.marketing', 'guest'), ['pageTitle' => $pageTitle])
    <style>
        .lb-blog-hero {
            position: relative;
            isolation: isolate;
        }

        .lb-blog-hero::before {
            content: "";
            position: absolute;
            inset: 2rem auto auto 45%;
            z-index: -1;
            width: 30rem;
            height: 30rem;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(184, 218, 22, .18), transparent 68%);
            filter: blur(12px);
        }

        .lb-blog-visual {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at 20% 20%, rgba(15, 118, 110, .16), transparent 14rem),
                radial-gradient(circle at 78% 76%, rgba(225, 235, 22, .22), transparent 14rem),
                rgba(255, 255, 252, .92);
        }

        .lb-blog-mini-card {
            box-shadow: 0 22px 56px -46px rgba(16, 37, 31, .62);
        }

        .lb-blog-cover {
            background:
                radial-gradient(circle at 20% 20%, rgba(15, 118, 110, .12), transparent 13rem),
                radial-gradient(circle at 80% 70%, rgba(225, 235, 22, .22), transparent 12rem),
                rgba(255, 255, 252, .9);
        }

        .lb-blog-cover-card {
            box-shadow: 0 24px 58px -46px rgba(16, 37, 31, .58);
        }

        .lb-blog-topic-panel {
            background:
                radial-gradient(circle at 16% 20%, rgba(15, 118, 110, .1), transparent 12rem),
                radial-gradient(circle at 88% 82%, rgba(184, 218, 22, .18), transparent 12rem),
                rgba(255, 255, 252, .9);
        }

        .lb-page-link {
            border: 1px solid var(--lb-line);
            background: #fffefb;
            color: var(--lb-ink);
            box-shadow: 0 16px 34px -30px rgba(16, 37, 31, .4);
        }

        .lb-page-link.is-active {
            background: var(--lb-red);
            border-color: var(--lb-red);
            color: #fff;
        }

        .lb-page-link.is-disabled {
            background: color-mix(in srgb, var(--lb-soft) 64%, #fff);
            color: color-mix(in srgb, var(--lb-muted) 70%, #fff);
            border-color: var(--lb-line);
            opacity: 1;
            cursor: not-allowed;
            box-shadow: none;
        }

        html[data-theme-resolved='dark'] .lb-blog-hero::before {
            opacity: .42;
            background: radial-gradient(circle, rgba(20, 163, 153, .18), transparent 70%);
        }

        html[data-theme-resolved='dark'] .lb-blog-visual,
        html[data-theme-resolved='dark'] .lb-blog-cover,
        html[data-theme-resolved='dark'] .lb-blog-topic-panel,
        html[data-theme-resolved='dark'] .lb-blog-cover-card,
        html[data-theme-resolved='dark'] .lb-blog-mini-card,
        html[data-theme-resolved='dark'] .lb-blog-topic-panel [class*="bg-white"],
        html[data-theme-resolved='dark'] .lb-blog-cover [class*="bg-white"],
        html[data-theme-resolved='dark'] .lb-blog-visual [class*="bg-white"] {
            border-color: rgba(96, 165, 250, .22) !important;
            background:
                linear-gradient(180deg, rgba(15, 23, 42, .92), rgba(11, 21, 38, .86)) !important;
            color: #e8eef7 !important;
            box-shadow: 0 28px 80px -58px rgba(0, 0, 0, .82) !important;
        }

        html[data-theme-resolved='dark'] .lb-page-link {
            border-color: rgba(96, 165, 250, .22) !important;
            background: rgba(15, 23, 42, .82) !important;
            color: #cbd5e1 !important;
        }

        html[data-theme-resolved='dark'] .lb-page-link.is-active {
            border-color: #ff5f5f !important;
            background: #0f766e !important;
            color: #fff !important;
        }

        html[data-theme-resolved='dark'] .lb-page-link.is-disabled {
            background: rgba(15, 23, 42, .42) !important;
            color: #64748b !important;
        }
    </style>

    <div class="lb-page">
        <section class="lb-wrap lb-blog-hero pb-20 pt-16 lg:pt-20">
            <div class="grid gap-10 xl:grid-cols-[0.72fr_1.28fr] xl:items-start">
                <aside class="lb-reveal xl:sticky xl:top-28">
                    <span class="lb-pill inline-flex items-center gap-2 rounded-full px-4 py-2 text-xs font-black uppercase tracking-[0.18em]">
                        <i class="fa-light fa-newspaper"></i>{{ __('Blog') }}
                    </span>
                    <h1 class="lb-serif lb-hero-title mt-7">{{ __('Playbooks for local campaign growth') }}</h1>
                    <p class="lb-copy mt-5 max-w-xl text-lg">{{ __('Read practical notes on review campaigns, booking pages, coupon claims, feedback flows, lead capture, QR pages, AI copy, and growth reports.') }}</p>

                    <form method="GET" action="{{ route('guest.blogs') }}" class="lb-card mt-8 rounded-2xl p-4">
                        <label for="blog-search" class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--lb-muted);">{{ __('Find a post') }}</label>
                        <div class="mt-3 flex gap-2">
                            <input id="blog-search" name="q" value="{{ $filters['q'] }}" placeholder="{{ __('Search articles...') }}" class="h-12 min-w-0 flex-1 rounded-full border bg-white px-4 text-sm font-bold outline-none" style="border-color: var(--lb-line);">
                            <button type="submit" class="lb-button inline-flex h-12 w-12 items-center justify-center"><i class="fa-light fa-magnifying-glass"></i></button>
                        </div>
                    </form>
                </aside>

                <div>
                    @if ($featuredPost)
                        <article class="lb-card lb-hover lb-reveal overflow-hidden rounded-3xl">
                            <div class="grid lg:grid-cols-[1.08fr_0.92fr]">
                                <a href="{{ route('guest.blog-show', $featuredPost->slug) }}" class="lb-blog-visual block min-h-[22rem] p-6">
                                    @if ($featuredPost->thumbnailUrl())
                                        <img src="{{ $featuredPost->thumbnailUrl() }}" alt="{{ $featuredPost->titleForLocale() }}" class="h-full min-h-[22rem] w-full rounded-2xl object-cover" loading="lazy" onerror="this.remove();">
                                    @else
                                        <div class="flex h-full min-h-[22rem] items-center justify-center">
                                            <div class="w-full max-w-sm rounded-3xl border bg-white p-5" style="border-color: var(--lb-line);">
                                                <div class="flex items-center justify-between gap-3">
                                                    <span class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--lb-muted);">{{ __('Campaign note') }}</span>
                                                    <span class="rounded-full px-3 py-1 text-xs font-black" style="background:#dcfce7; color:#047857;">{{ __('Published') }}</span>
                                                </div>
                                                <div class="mt-5 grid gap-3">
                                                    @foreach ([['fa-star', __('Reviews'), '4.9'], ['fa-qrcode', __('QR scans'), '284'], ['fa-user-plus', __('Leads'), '39']] as $item)
                                                        <div class="lb-blog-mini-card rounded-2xl border bg-white p-4" style="border-color: var(--lb-line);">
                                                            <div class="flex items-center justify-between gap-4">
                                                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl" style="background: color-mix(in srgb, var(--lb-red) 9%, #fff); color: var(--lb-red);">
                                                                    <i class="fa-light {{ $item[0] }}"></i>
                                                                </span>
                                                                <span class="text-2xl font-black">{{ $item[2] }}</span>
                                                            </div>
                                                            <p class="mt-2 text-xs font-bold" style="color: var(--lb-muted);">{{ $item[1] }}</p>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </a>
                                <div class="flex flex-col justify-between p-6 sm:p-8">
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-[0.16em]" style="color: var(--lb-muted);">{{ $featuredPost->publishedAtFormatted('Y-m-d') ?: $featuredPost->createdAtFormatted('Y-m-d') }}</p>
                                        <h2 class="lb-serif mt-4 text-4xl leading-none"><a href="{{ route('guest.blog-show', $featuredPost->slug) }}">{{ $featuredPost->titleForLocale() }}</a></h2>
                                        <p class="lb-copy mt-4 text-sm">{{ $featuredPost->contentPreview(240) }}</p>
                                    </div>
                                    <a href="{{ route('guest.blog-show', $featuredPost->slug) }}" class="lb-button-soft mt-6 inline-flex w-max items-center rounded-full px-5 py-3 text-sm font-black">
                                        {{ __('Read article') }}
                                        <i class="fa-light fa-arrow-right ml-2"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endif

                    <div class="lb-card lb-blog-topic-panel lb-reveal mt-6 rounded-3xl p-6" style="--lb-delay: 140ms;">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.18em]" style="color: var(--lb-muted);">{{ __('Popular topics') }}</p>
                                <h3 class="lb-serif mt-2 text-3xl leading-none">{{ __('Learn the workflows that move local growth') }}</h3>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-black" style="background: color-mix(in srgb, var(--lb-lime) 30%, #fff); color:#506807;">{{ $blogs->total() }} {{ __('posts') }}</span>
                        </div>
                        <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ([
                                ['fa-star', __('Review campaigns'), __('Route happy customers')],
                                ['fa-calendar-check', __('Booking pages'), __('Collect appointments')],
                                ['fa-ticket', __('Coupon claims'), __('Track redemptions')],
                                ['fa-qrcode', __('QR pages'), __('Offline to online')],
                                ['fa-sparkles', __('AI copy'), __('Write faster campaigns')],
                                ['fa-chart-line', __('Reports'), __('Measure conversion')],
                            ] as $topic)
                                <div class="rounded-2xl border bg-white/80 p-4" style="border-color: var(--lb-line);">
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl" style="background: color-mix(in srgb, var(--lb-red) 9%, #fff); color: var(--lb-red);">
                                        <i class="fa-light {{ $topic[0] }}"></i>
                                    </span>
                                    <p class="mt-4 text-sm font-black">{{ $topic[1] }}</p>
                                    <p class="mt-1 text-xs leading-5" style="color: var(--lb-muted);">{{ $topic[2] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-14 flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.2em]" style="color:#ff5f5f;">{{ __('Latest') }}</p>
                    <h2 class="lb-serif mt-3 text-4xl leading-none">{{ __('Recent articles and operating notes') }}</h2>
                </div>
                <span class="hidden rounded-full border bg-white px-4 py-2 text-sm font-bold md:inline-flex" style="border-color: var(--lb-line); color: var(--lb-muted);">{{ $blogs->total() }} {{ __('posts') }}</span>
            </div>

            <div class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($blogs as $blog)
                    <article class="lb-card lb-hover lb-reveal overflow-hidden rounded-2xl" style="--lb-delay: {{ $loop->index * 60 }}ms;">
                        <a href="{{ route('guest.blog-show', $blog->slug) }}" class="lb-blog-cover block h-52">
                            @if ($blog->thumbnailUrl())
                                <img src="{{ $blog->thumbnailUrl() }}" alt="{{ $blog->titleForLocale() }}" class="h-full w-full object-cover" loading="lazy" onerror="this.remove();">
                            @else
                                <div class="flex h-full items-center justify-center">
                                    <div class="lb-blog-cover-card w-[82%] rounded-3xl border bg-white/85 p-5" style="border-color: var(--lb-line);">
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl" style="background: color-mix(in srgb, var(--lb-red) 9%, #fff); color: var(--lb-red);">
                                                <i class="fa-light fa-chart-line text-xl"></i>
                                            </span>
                                            <span class="rounded-full px-3 py-1 text-xs font-black" style="background: color-mix(in srgb, var(--lb-lime) 28%, #fff); color:#506807;">{{ __('Guide') }}</span>
                                        </div>
                                        <div class="mt-5 grid grid-cols-3 gap-2">
                                            @foreach ([__('Reviews'), __('Leads'), __('Reports')] as $metric)
                                                <span class="rounded-full px-2 py-1 text-center text-[10px] font-black" style="background: var(--lb-soft); color: var(--lb-muted);">{{ $metric }}</span>
                                            @endforeach
                                        </div>
                                        <p class="mt-4 text-xs font-black uppercase tracking-[0.16em]" style="color: var(--lb-muted);">{{ __('Growth playbook') }}</p>
                                    </div>
                                </div>
                            @endif
                        </a>
                        <div class="p-6">
                            @php
                                $categoryName = $blog->category?->nameForLocale() ?: __('Article');
                                if (str_contains(strtolower($categoryName), 'smart link')) {
                                    $categoryName = __('Local growth guides');
                                }
                            @endphp
                            <p class="text-[10px] font-black uppercase tracking-[0.16em]" style="color: var(--lb-muted);">{{ $categoryName }}</p>
                            <h3 class="lb-serif mt-3 text-2xl leading-none"><a href="{{ route('guest.blog-show', $blog->slug) }}">{{ $blog->titleForLocale() }}</a></h3>
                            <p class="lb-copy mt-3 text-sm">{{ $blog->contentPreview(150) }}</p>
                        </div>
                    </article>
                @empty
                    <div class="lb-card rounded-xl border-dashed px-6 py-14 text-center text-sm xl:col-span-3" style="color: var(--lb-muted);">{{ __('No blog posts found.') }}</div>
                @endforelse
            </div>

            @if ($blogs->hasPages())
                <div class="mt-8 flex justify-end">
                    <nav class="inline-flex items-center gap-2" aria-label="Pagination">
                        @if ($blogs->onFirstPage())
                            <span class="lb-page-link is-disabled inline-flex h-11 w-11 items-center justify-center rounded-full">
                                <i class="fa-light fa-chevron-left"></i>
                            </span>
                        @else
                            <a href="{{ $blogs->previousPageUrl() }}" class="lb-page-link inline-flex h-11 w-11 items-center justify-center rounded-full transition hover:-translate-y-0.5">
                                <i class="fa-light fa-chevron-left"></i>
                            </a>
                        @endif

                        @foreach ($blogs->getUrlRange(1, $blogs->lastPage()) as $page => $url)
                            @if ($page === $blogs->currentPage())
                                <span class="lb-page-link is-active inline-flex h-11 min-w-11 items-center justify-center rounded-full px-4 text-sm font-black">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="lb-page-link inline-flex h-11 min-w-11 items-center justify-center rounded-full px-4 text-sm font-black transition hover:-translate-y-0.5">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if ($blogs->hasMorePages())
                            <a href="{{ $blogs->nextPageUrl() }}" class="lb-page-link inline-flex h-11 w-11 items-center justify-center rounded-full transition hover:-translate-y-0.5">
                                <i class="fa-light fa-chevron-right"></i>
                            </a>
                        @else
                            <span class="lb-page-link is-disabled inline-flex h-11 w-11 items-center justify-center rounded-full">
                                <i class="fa-light fa-chevron-right"></i>
                            </span>
                        @endif
                    </nav>
                </div>
            @endif
        </section>
    </div>
@endcomponent
