@php
    $options = app(\Modules\AdminSettings\Support\OptionStore::class);
    $companyName = trim((string) $options->get('contact_company_name', config('app.name', 'LocalBoost AI')));
    $companyWebsite = trim((string) $options->get('contact_company_website', 'https://yourcompany.com'));
    $contactEmail = trim((string) $options->get('contact_email', 'support@yourcompany.com'));
    $contactPhone = trim((string) $options->get('contact_phone_number', '+1 234 567 890'));
    $workingHours = trim((string) $options->get('contact_working_hours', 'Mon - Fri: 09:00 AM - 06:00 PM'));
    $contactLocation = trim((string) $options->get('contact_location', '123 Main Street, City, Country'));
@endphp

@component(theme_view('layouts.marketing', 'guest'), ['pageTitle' => $pageTitle])
    <style>
        .lb-contact-hero {
            position: relative;
            isolation: isolate;
        }

        .lb-contact-hero::before {
            content: "";
            position: absolute;
            inset: 2rem -4rem auto auto;
            z-index: -1;
            width: 28rem;
            height: 28rem;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(184, 218, 22, .22), transparent 68%);
            filter: blur(12px);
        }

        .lb-contact-panel {
            background:
                radial-gradient(circle at 16% 12%, rgba(255, 95, 95, .12), transparent 16rem),
                radial-gradient(circle at 86% 86%, rgba(184, 218, 22, .2), transparent 18rem),
                rgba(255, 255, 252, .94);
        }

        .lb-contact-row {
            border-bottom: 1px solid var(--lb-line);
        }

        .lb-contact-row:last-child {
            border-bottom: 0;
        }

        html[data-theme-resolved='dark'] .lb-contact-hero::before {
            opacity: .42;
            background: radial-gradient(circle, rgba(20, 163, 153, .18), transparent 70%);
        }

        html[data-theme-resolved='dark'] .lb-contact-panel,
        html[data-theme-resolved='dark'] .lb-contact-panel [class*="bg-white"],
        html[data-theme-resolved='dark'] .lb-contact-panel [style*="#fff"],
        html[data-theme-resolved='dark'] .lb-contact-panel [style*="255, 255, 255"] {
            border-color: rgba(96, 165, 250, .22) !important;
            background:
                linear-gradient(180deg, rgba(15, 23, 42, .92), rgba(11, 21, 38, .86)) !important;
            color: #e8eef7 !important;
            box-shadow: 0 28px 80px -58px rgba(0, 0, 0, .82) !important;
        }

        html[data-theme-resolved='dark'] .lb-contact-row {
            border-color: rgba(96, 165, 250, .14) !important;
        }
    </style>

    <div class="lb-page">
        <section class="lb-wrap lb-contact-hero pb-20 pt-16 lg:pt-20">
            <div class="grid gap-12 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
                <div class="lb-reveal">
                    <span class="lb-pill inline-flex items-center gap-2 rounded-full px-4 py-2 text-xs font-black uppercase tracking-[0.18em]">
                        <i class="fa-light fa-message-lines"></i>{{ __('Contact') }}
                    </span>
                    <h1 class="lb-serif lb-hero-title mt-7">{{ __('Talk to LocalBoost AI') }}</h1>
                    <p class="lb-copy mt-6 max-w-2xl text-lg">{{ __('Need help with campaigns, QR pages, reviews, bookings, AI credits, reports, billing or your team workspace? Send us a message and we will help you choose the right path.') }}</p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="mailto:{{ $contactEmail }}" class="lb-button inline-flex items-center gap-2 px-6 py-4 text-sm font-black"><i class="fa-light fa-envelope"></i>{{ __('Email support') }}</a>
                        <a href="{{ route('guest.pricing') }}" class="lb-button-soft inline-flex items-center gap-2 px-6 py-4 text-sm font-black">{{ __('View pricing') }}</a>
                    </div>

                    <div class="mt-10 grid gap-3 sm:grid-cols-3">
                        @foreach ([
                            ['fa-sparkles', __('Product questions'), __('Campaign pages, AI tools and reports')],
                            ['fa-credit-card', __('Billing help'), __('Plans, invoices and AI credits')],
                            ['fa-users', __('Team setup'), __('Workspaces, members and permissions')],
                        ] as $card)
                            <div class="lb-card rounded-2xl p-4">
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl" style="background: color-mix(in srgb, var(--lb-green) 9%, #fff); color: var(--lb-green);">
                                    <i class="fa-light {{ $card[0] }}"></i>
                                </span>
                                <p class="mt-4 text-sm font-black">{{ $card[1] }}</p>
                                <p class="mt-2 text-xs leading-5" style="color: var(--lb-muted);">{{ $card[2] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="lb-window lb-contact-panel lb-float rounded-3xl p-6">
                    <div class="flex items-center gap-1.5"><span class="lb-dot bg-red-400"></span><span class="lb-dot bg-amber-400"></span><span class="lb-dot bg-lime-500"></span></div>
                    <div class="mt-8 flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.18em]" style="color: var(--lb-muted);">{{ __('Contact details') }}</p>
                            <h2 class="lb-serif mt-2 text-4xl">{{ $companyName }}</h2>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-black" style="background:#dcfce7; color:#047857;">{{ __('Online') }}</span>
                    </div>
                    <div class="mt-8 rounded-2xl border bg-white/80 p-5" style="border-color: var(--lb-line);">
                        @foreach ([
                            ['fa-envelope', __('Email'), $contactEmail, 'mailto:'.$contactEmail],
                            ['fa-phone', __('Phone'), $contactPhone, 'tel:'.preg_replace('/\s+/', '', $contactPhone)],
                            ['fa-globe', __('Website'), $companyWebsite, $companyWebsite],
                            ['fa-clock', __('Working hours'), $workingHours, null],
                            ['fa-location-dot', __('Address'), $contactLocation, null],
                        ] as $item)
                            @if ($item[3])
                                <a href="{{ $item[3] }}" class="lb-contact-row lb-hover flex gap-4 py-4 first:pt-0 last:pb-0">
                                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background: color-mix(in srgb, var(--lb-green) 9%, #fff); color: var(--lb-green);">
                                        <i class="fa-light {{ $item[0] }}"></i>
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block text-xs font-black uppercase tracking-[0.14em]" style="color: var(--lb-muted);">{{ $item[1] }}</span>
                                        <span class="mt-1 block break-words font-bold">{{ $item[2] }}</span>
                                    </span>
                                </a>
                            @else
                                <div class="lb-contact-row flex gap-4 py-4 first:pt-0 last:pb-0">
                                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" style="background: color-mix(in srgb, var(--lb-green) 9%, #fff); color: var(--lb-green);">
                                        <i class="fa-light {{ $item[0] }}"></i>
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block text-xs font-black uppercase tracking-[0.14em]" style="color: var(--lb-muted);">{{ $item[1] }}</span>
                                        <span class="mt-1 block break-words font-bold">{{ $item[2] }}</span>
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    </div>
@endcomponent
