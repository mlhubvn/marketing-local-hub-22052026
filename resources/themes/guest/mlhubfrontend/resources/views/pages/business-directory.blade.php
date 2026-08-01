@component(theme_view('layouts.marketing', 'guest'), ['pageTitle' => $pageTitle])
    <div class="lb-page">
        <section class="lb-wrap pb-20 pt-16 lg:pt-20">
            <div class="grid gap-10 lg:grid-cols-[0.85fr_1.15fr] lg:items-start">
                <aside class="lb-card lb-reveal lb-sidebar-panel rounded-xl p-6">
                    <span class="lb-pill inline-flex items-center gap-2 rounded-full px-4 py-2 text-xs font-black uppercase tracking-[0.18em]">
                        <i class="fa-light fa-store"></i>{{ __('Business directory') }}
                    </span>
                    <h1 class="lb-serif lb-hero-title lb-reveal mt-7">{{ __('Look up local businesses on MKT') }}</h1>
                    <p class="lb-copy mt-5">{{ __('Search registered businesses, see which industry group they belong to, and find out which MKT account manages each profile.') }}</p>
                    <p class="lb-copy mt-4 text-sm" style="color: var(--lb-muted);">{{ __('Contact details are partially masked so owners can recognize their own phone or email without exposing full records publicly.') }}</p>

                    <form method="GET" action="{{ route('guest.directory') }}" class="mt-7 space-y-4">
                        <div class="space-y-2">
                            <label for="directory-search" class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--lb-muted);">{{ __('Search businesses') }}</label>
                            <div class="flex gap-2">
                                <input id="directory-search" name="q" value="{{ $filters['q'] }}" placeholder="{{ __('Business name, address, or manager...') }}" class="h-12 min-w-0 flex-1 rounded-full border bg-white px-4 text-sm font-bold outline-none" style="border-color: var(--lb-line);">
                                <button type="submit" class="lb-button inline-flex h-12 w-12 items-center justify-center"><i class="fa-light fa-magnifying-glass"></i></button>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="directory-industry" class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--lb-muted);">{{ __('Industry group') }}</label>
                            <select id="directory-industry" name="industry" class="h-12 w-full rounded-full border bg-white px-4 text-sm font-bold outline-none" style="border-color: var(--lb-line);">
                                <option value="">{{ __('All industries') }}</option>
                                @foreach ($industryOptions as $option)
                                    <option value="{{ $option['code'] }}" @selected($filters['industry'] === $option['code'])>{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        @include(theme_view('partials.directory-industry-picker', 'guest'))

                        <button type="submit" class="lb-button-soft inline-flex w-full items-center justify-center px-5 py-3.5 text-sm font-black">
                            {{ __('Apply filters') }}
                        </button>
                    </form>
                </aside>

                <div class="space-y-4">
                    <div class="lb-card lb-reveal rounded-xl border-dashed px-5 py-4 text-sm" style="color: var(--lb-muted);">
                        {{ __('This directory helps household businesses check whether a profile already exists on MKT and which account currently manages it.') }}
                    </div>

                    @forelse ($businesses as $entry)
                        @include(theme_view('partials.directory-business-card', 'guest'), ['entry' => $entry])
                    @empty
                        <div class="lb-card rounded-xl border-dashed px-6 py-14 text-center text-sm" style="color: var(--lb-muted);">{{ __('No businesses found for this search.') }}</div>
                    @endforelse

                    @if ($businesses->hasPages())
                        <div class="pt-2">
                            {{ $businesses->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
@endcomponent
