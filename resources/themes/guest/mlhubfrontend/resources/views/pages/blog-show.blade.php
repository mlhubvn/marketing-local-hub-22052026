@component(theme_view('layouts.marketing', 'guest'), ['pageTitle' => $pageTitle])
    @php
        $articleContent = $blog->normalizedContentForLocale();
        $articleContentIsHtml = str_contains($articleContent, '<') && str_contains($articleContent, '>');
        $publishedLabel = $blog->publishedAtFormatted('Y-m-d') ?: $blog->createdAtFormatted('Y-m-d');
        $publishedTimestamp = (int) ($blog->published_at ?: $blog->created ?: 0);
        $publishedIso = $publishedTimestamp > 0
            ? \Carbon\Carbon::createFromTimestamp($publishedTimestamp)->toIso8601String()
            : null;
    @endphp

    <article class="mlhub-shell mlhub-section pt-10" itemscope itemtype="https://schema.org/BlogPosting">
        <div class="mx-auto max-w-5xl">
            <a href="{{ route('guest.blogs') }}" class="inline-flex items-center rounded-full border bg-white px-4 py-2 text-sm font-bold text-slate-600 transition hover:text-blue-700" style="border-color: rgba(var(--theme-border-color-rgb),0.82);">
                <i class="fa-light fa-arrow-left mr-2"></i>
                {{ __('Back to blog') }}
            </a>

            <header class="mt-8">
                <div class="flex flex-wrap items-center gap-3 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">
                    @if ($blog->category)
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-blue-700" itemprop="articleSection">{{ $blog->category->nameForLocale() }}</span>
                    @endif
                    @if ($publishedLabel)
                        <time @if ($publishedIso) datetime="{{ $publishedIso }}" @endif itemprop="datePublished">{{ $publishedLabel }}</time>
                    @endif
                </div>
                <h1 class="mt-5 max-w-4xl text-5xl font-extrabold leading-[1.02] tracking-[-0.07em] text-slate-950 md:text-6xl" itemprop="headline">
                    {{ $blog->titleForLocale() }}
                </h1>
                <p class="mt-5 max-w-3xl text-base leading-8 text-slate-600" itemprop="description">{{ $blog->contentPreview(220) }}</p>
            </header>

            <div class="mlhub-card mt-10 overflow-hidden rounded-[1.7rem]">
                <div class="h-[26rem]">
                    @if ($blog->thumbnailUrl())
                        <span class="mlhub-image-frame block h-full">
                            <img src="{{ $blog->thumbnailUrl() }}" alt="{{ $blog->titleForLocale() }}" itemprop="image" loading="lazy" onerror="this.remove();">
                        </span>
                    @else
                        @include(theme_view('partials.visual-card', 'guest'), [
                            'type' => 'analytics',
                            'icon' => 'fa-light fa-newspaper',
                            'label' => $blog->category?->nameForLocale() ?: __('Article'),
                        ])
                    @endif
                </div>
            </div>

            <div class="mt-10 grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_18rem]">
                <div class="min-w-0 mlhub-card rounded-[1.5rem] p-6 sm:p-8">
                    <div class="lb-rich-content" itemprop="articleBody">
                        @if ($articleContentIsHtml)
                            {!! $articleContent !!}
                        @else
                            <p class="whitespace-pre-line">{{ $articleContent }}</p>
                        @endif
                    </div>
                </div>

                <aside class="lb-sidebar-panel w-full shrink-0 space-y-4 lg:w-[18rem]">
                    <div class="mlhub-card rounded-[1.25rem] p-5">
                        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('Next step') }}</p>
                        <h2 class="mt-3 text-xl font-extrabold tracking-[-0.035em] text-slate-950">{{ __('Build your campaign workspace') }}</h2>
                        <p class="mt-3 text-sm leading-7 text-slate-600">{{ __('Use the product to turn QR scans and Bio clicks into measurable campaign signals.') }}</p>
                        <a href="{{ route('guest.pricing') }}" class="mlhub-button-primary mt-5 inline-flex w-full items-center justify-center rounded-[var(--theme-button-radius)] px-5 py-3 text-sm font-bold">{{ __('View pricing') }}</a>
                    </div>
                    <div class="mlhub-card rounded-[1.25rem] p-5">
                        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('Explore') }}</p>
                        <div class="mt-4 grid gap-2">
                            <a href="{{ route('guest.faqs') }}" class="rounded-[0.9rem] bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 hover:text-blue-700">{{ __('FAQs') }}</a>
                            <a href="{{ route('guest.contact') }}" class="rounded-[0.9rem] bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 hover:text-blue-700">{{ __('Contact') }}</a>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </article>

    @if ($relatedBlogs->isNotEmpty())
        <section class="mlhub-shell pb-14">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-700">{{ __('Related') }}</p>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-[-0.05em] text-slate-950">{{ __('More operating notes') }}</h2>
                </div>
                <a href="{{ route('guest.blogs') }}" class="mlhub-button-secondary hidden rounded-[var(--theme-button-radius)] px-5 py-3 text-sm font-bold md:inline-flex">{{ __('All posts') }}</a>
            </div>
            <div class="mt-8 grid gap-5 md:grid-cols-3">
                @foreach ($relatedBlogs as $related)
                    <article class="mlhub-card mlhub-hover-lift rounded-[1.2rem] p-5">
                        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ $related->category?->nameForLocale() ?: __('Article') }}</p>
                        <h3 class="mt-3 text-lg font-extrabold tracking-[-0.035em] text-slate-950">
                            <a href="{{ route('guest.blog-show', $related->slug) }}">{{ $related->titleForLocale() }}</a>
                        </h3>
                        <p class="mt-3 text-sm leading-7 text-slate-600">{{ $related->contentPreview(120) }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
@endcomponent
