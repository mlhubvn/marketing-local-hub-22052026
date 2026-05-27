@component(theme_view('layouts.marketing', 'guest'), ['pageTitle' => $pageTitle])
    <div class="lb-page">
        <section class="lb-wrap pb-20 pt-16 lg:pt-20">
            <div class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr] lg:items-start">
                <aside class="lb-card lb-reveal rounded-xl p-6 lg:sticky lg:top-28">
                    <span class="lb-pill inline-flex items-center gap-2 rounded-full px-4 py-2 text-xs font-black uppercase tracking-[0.18em]">
                        <i class="fa-light fa-circle-question"></i>{{ __('FAQs') }}
                    </span>
                    <h1 class="lb-serif lb-heading mt-7">{{ __('Answers for local growth SaaS buyers') }}</h1>
                    <p class="lb-copy mt-5">{{ __('Search setup, plan limits, campaign pages, QR codes, AI credits, teams, reports, and selling the script as SaaS.') }}</p>

                    <form method="GET" action="{{ route('guest.faqs') }}" class="mt-7 space-y-3">
                        <label for="faq-search" class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--lb-muted);">{{ __('Search answers') }}</label>
                        <div class="flex gap-2">
                            <input id="faq-search" name="q" value="{{ $filters['q'] }}" placeholder="{{ __('Search campaign pages, QR, AI...') }}" class="h-12 min-w-0 flex-1 rounded-full border bg-white px-4 text-sm font-bold outline-none" style="border-color: var(--lb-line);">
                            <button type="submit" class="lb-button inline-flex h-12 w-12 items-center justify-center"><i class="fa-light fa-magnifying-glass"></i></button>
                        </div>
                    </form>
                </aside>

                <div x-data="{ openFaq: 0 }" class="space-y-4">
                    @forelse ($faqs as $faq)
                        <article class="lb-card lb-hover lb-reveal rounded-xl p-5" style="--lb-delay: {{ $loop->index * 60 }}ms;">
                            <button type="button" x-on:click="openFaq = openFaq === {{ $loop->index }} ? -1 : {{ $loop->index }}" class="flex w-full items-start justify-between gap-4 text-left">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.16em]" style="color: var(--lb-muted);">{{ __('Question') }} {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                                    <h2 class="lb-serif mt-2 text-3xl leading-none">{{ $faq->titleForLocale() }}</h2>
                                </div>
                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full transition" style="background: color-mix(in srgb, var(--lb-lime) 28%, #fff); color:#5f7f07;" x-bind:class="openFaq === {{ $loop->index }} ? 'rotate-45' : ''">
                                    <i class="fa-light fa-plus"></i>
                                </span>
                            </button>
                            <div class="grid overflow-hidden transition-all duration-300" x-bind:style="openFaq === {{ $loop->index }} ? 'grid-template-rows: 1fr; opacity: 1; margin-top: 1.25rem;' : 'grid-template-rows: 0fr; opacity: 0; margin-top: 0;'">
                                <div class="lb-copy min-h-0 overflow-hidden rounded-lg px-5 py-4 text-sm whitespace-pre-line" style="background: var(--lb-soft);">
                                    {{ $faq->contentPreview(420) !== '' ? $faq->contentPreview(420) : $faq->contentForLocale() }}
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="lb-card rounded-xl border-dashed px-6 py-14 text-center text-sm" style="color: var(--lb-muted);">{{ __('No FAQs found.') }}</div>
                    @endforelse

                    @if ($faqs->hasPages())
                        <div class="pt-4">{{ $faqs->links() }}</div>
                    @endif
                </div>
            </div>
        </section>
    </div>
@endcomponent
