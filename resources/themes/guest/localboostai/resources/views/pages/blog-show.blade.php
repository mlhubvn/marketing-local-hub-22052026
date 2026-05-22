@component(theme_view('layouts.marketing', 'guest'), ['pageTitle' => $pageTitle])
    <article class="localboost-shell localboost-section pt-10">
        <div class="mx-auto max-w-5xl">
            <a href="{{ route('guest.blogs') }}" class="inline-flex items-center rounded-full border bg-white px-4 py-2 text-sm font-bold text-slate-600 transition hover:text-blue-700" style="border-color: rgba(var(--theme-border-color-rgb),0.82);">
                <i class="fa-light fa-arrow-left mr-2"></i>
                {{ __('Back to blog') }}
            </a>

            <header class="mt-8">
                <div class="flex flex-wrap items-center gap-3 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">
                    @if ($blog->category)
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-blue-700">{{ $blog->category->nameForLocale() }}</span>
                    @endif
                    <span>{{ $blog->publishedAtFormatted('M d, Y') ?: $blog->createdAtFormatted('M d, Y') }}</span>
                </div>
                <h1 class="mt-5 max-w-4xl text-5xl font-extrabold leading-[1.02] tracking-[-0.07em] text-slate-950 md:text-6xl">
                    {{ $blog->titleForLocale() }}
                </h1>
                <p class="mt-5 max-w-3xl text-base leading-8 text-slate-600">{{ $blog->contentPreview(220) }}</p>
            </header>

            <div class="localboost-card mt-10 overflow-hidden rounded-[1.7rem]">
                <div class="h-[26rem]">
                    @if ($blog->thumbnailUrl())
                        <span class="localboost-image-frame block h-full">
                            <img src="{{ $blog->thumbnailUrl() }}" alt="{{ $blog->titleForLocale() }}" loading="lazy" onerror="this.remove();">
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

            <div class="mt-10 grid gap-8 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-start">
                <div class="localboost-card rounded-[1.5rem] p-6 sm:p-8">
                    <div class="prose prose-slate max-w-none prose-headings:tracking-[-0.04em] prose-a:text-blue-700">
                        {!! $blog->normalizedContentForLocale() !!}
                    </div>
                </div>

                <aside class="space-y-4 lg:sticky lg:top-28">
                    <div class="localboost-card rounded-[1.25rem] p-5">
                        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('Next step') }}</p>
                        <h2 class="mt-3 text-xl font-extrabold tracking-[-0.035em] text-slate-950">{{ __('Build your campaign workspace') }}</h2>
                        <p class="mt-3 text-sm leading-7 text-slate-600">{{ __('Use the product to turn QR scans and Bio clicks into measurable campaign signals.') }}</p>
                        <a href="{{ route('guest.pricing') }}" class="localboost-button-primary mt-5 inline-flex w-full items-center justify-center rounded-[var(--theme-button-radius)] px-5 py-3 text-sm font-bold">{{ __('View pricing') }}</a>
                    </div>
                    <div class="localboost-card rounded-[1.25rem] p-5">
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
        <section class="localboost-shell pb-14">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-700">{{ __('Related') }}</p>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-[-0.05em] text-slate-950">{{ __('More operating notes') }}</h2>
                </div>
                <a href="{{ route('guest.blogs') }}" class="localboost-button-secondary hidden rounded-[var(--theme-button-radius)] px-5 py-3 text-sm font-bold md:inline-flex">{{ __('All posts') }}</a>
            </div>
            <div class="mt-8 grid gap-5 md:grid-cols-3">
                @foreach ($relatedBlogs as $related)
                    <article class="localboost-card localboost-hover-lift rounded-[1.2rem] p-5">
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
