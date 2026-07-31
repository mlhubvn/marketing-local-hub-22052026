<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ current_locale_direction() }}">
    <head>
        {{-- Reuses the exact same head partial (fonts, Tailwind/Alpine bundle via
        theme_vite, FontAwesome, Highcharts bootstrap, CSS variable tokens) as the rest of
        MLHUB — this portal is intentionally NOT wrapped in <x-ui.shell>, because that shell
        always renders the full admin/portal sidebar for the current user, which would leak
        unrelated MLHUB navigation (and possibly admin-only links) into a domain that is
        supposed to expose only this read-only FizaHUB report. --}}
        @include(theme_view('partials.head', 'app'), ['title' => $title ?? null])
    </head>
    <body class="min-h-screen bg-[#f8faff] text-slate-900 antialiased dark:bg-[#0f172a] dark:text-slate-100" style="font-family: var(--theme-font-sans);">
        <div class="min-h-screen">
            <header class="sticky top-0 z-40 border-b" style="border-color: var(--theme-border-color); background: color-mix(in srgb, var(--theme-surface-overlay) 96%, transparent);">
                <div class="mx-auto flex max-w-[84rem] flex-wrap items-center justify-between gap-3 px-5 py-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl text-sm font-bold text-white" style="background: var(--theme-accent, #4f46e5);">FZ</span>
                        <div>
                            <p class="text-sm font-semibold leading-tight" style="color: var(--theme-header-text-color);">{{ __('FizaHUB Partner Reporting') }}</p>
                            <p class="text-xs leading-tight" style="color: var(--theme-muted-text-color);">{{ __('Cổng báo cáo onboarding HKD — chỉ xem, không chỉnh sửa') }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="hidden text-sm sm:inline" style="color: var(--theme-muted-text-color);">{{ auth()->user()?->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-ui.button type="submit" size="sm" variant="outline">{{ __('Đăng xuất') }}</x-ui.button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="mx-auto max-w-[84rem] px-5 py-6">
                {{ $slot }}
            </main>
        </div>

        @livewireScripts
    </body>
</html>
