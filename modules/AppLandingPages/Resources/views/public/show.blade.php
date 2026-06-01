@php
    $content = $landingPage->content ?: [];
    $settings = $landingPage->settings ?: [];
    $bookingServices = collect($bookingServices ?? []);

    if ($landingPage->type === 'booking' && $bookingServices->isEmpty() && $landingPage->business_id) {
        $bookingServices = \Modules\AppBookingPages\Models\BookingService::query()
            ->where('business_id', $landingPage->business_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
    $bookingAvailability = (array) ($bookingAvailability ?? []);
    if ($landingPage->type === 'booking' && $bookingServices->isNotEmpty() && $bookingAvailability === []) {
        $bookingAvailability = app(\Modules\AppBookingPages\Support\BookingAvailability::class)->availabilityMap($bookingServices, 120);
    }
    $design = array_merge([
        'primary_color' => '#0f766e',
        'layout_style' => 'split',
        'background_color' => '#f4fbf8',
        'accent_color' => '#ccfbf1',
        'background_type' => 'gradient',
        'font_style' => 'modern',
        'button_style' => 'pill',
        'card_style' => 'soft',
        'logo_url' => '',
        'logo_shape' => 'circle',
        'cover_image' => '',
        'show_logo' => true,
        'show_business_info' => true,
        'show_social_links' => true,
        'show_benefits' => true,
        'show_terms' => true,
        'show_faq' => true,
    ], (array) data_get($settings, 'design', []));
    $initialBookingServiceId = (string) old('service_id', $bookingServices->count() === 1 ? $bookingServices->first()?->id : '');
    $benefits = (array) data_get($content, 'benefits', []);
    $showBusinessInfo = (bool) $design['show_business_info'];
    $showFaq = $landingPage->type === 'review' ? false : (bool) $design['show_faq'];
    $businessInfoBlockConfigured = false;
    $faqBlockConfigured = false;
    $faqBlock = null;
    $extraBlocks = collect();
    $slots = (array) data_get($settings, 'available_slots', []);
    $business = $landingPage->business;
    $primary = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $design['primary_color']) ? $design['primary_color'] : '#0f766e';
    $background = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $design['background_color']) ? $design['background_color'] : '#f4fbf8';
    $accent = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $design['accent_color']) ? $design['accent_color'] : '#ccfbf1';
    $primaryRgbParts = sscanf($primary, '#%02x%02x%02x') ?: [15, 118, 110];
    $primaryRgb = implode(', ', $primaryRgbParts);
    $buttonRadius = match ((string) $design['button_style']) {
        'square' => '8px',
        'rounded' => '14px',
        default => '999px',
    };
    $cardRadius = match ((string) $design['card_style']) {
        'flat' => '12px',
        'bordered' => '18px',
        default => '24px',
    };
    $fontFamily = match ((string) $design['font_style']) {
        'classic' => '"Lora", Georgia, Cambria, "Times New Roman", serif',
        'elegant' => '"Manrope", "Be Vietnam Pro", ui-sans-serif, system-ui, sans-serif',
        'friendly' => '"Nunito", "Be Vietnam Pro", ui-sans-serif, system-ui, sans-serif',
        default => '"Be Vietnam Pro", Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
    };
    $logoShape = in_array((string) $design['logo_shape'], ['circle', 'square'], true) ? (string) $design['logo_shape'] : 'circle';
    $submitRoute = route('landing-pages.submit', ['landingPage' => $landingPage->slug]);
    $formTitle = match ($landingPage->type) {
        'coupon', 'promotion' => __('Claim your offer'),
        'review' => __('Rate your visit'),
        'feedback' => __('Share private feedback'),
        default => __('Send your details'),
    };
    $formBadge = match ($landingPage->type) {
        'review' => __('Review flow'),
        'feedback' => __('Private'),
        'booking' => __('Secure'),
        'coupon', 'promotion' => __('Offer'),
        default => __('Secure'),
    };
    $formNote = match ($landingPage->type) {
        'review' => __('Your rating helps improve local service and helps other customers choose confidently.'),
        'feedback' => __('Tell us what went well or what we can improve. Your response stays with the local team.'),
        default => '',
    };
    $submitText = match ($landingPage->type) {
        'review' => (data_get($content, 'cta') === 'Continue' ? __('Submit feedback') : data_get($content, 'cta', __('Submit feedback'))),
        'feedback' => (data_get($content, 'cta') ?: __('Send feedback')),
        default => data_get($content, 'cta', __('Submit')),
    };
    $ratingLabels = [
        1 => __('Poor'),
        2 => __('Okay'),
        3 => __('Good'),
        4 => __('Great'),
        5 => __('Excellent'),
    ];
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $landingPage->title }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=be-vietnam-pro:400,500,600,700,800|manrope:400,500,600,700,800|nunito:400,500,600,700,800|lora:400,500,600,700" rel="stylesheet">
    {!! theme_vite('app', ['assets/js/app.js']) !!}
    <style>
        :root{--primary:{{ $primary }};--bg:{{ $background }};--accent:{{ $accent }};--button-radius:{{ $buttonRadius }};--card-radius:{{ $cardRadius }};--font:{!! $fontFamily !!};--theme-accent:{{ $primary }};--theme-accent-rgb:{{ $primaryRgb }};--theme-border-color:#dbe1ea;--theme-input-surface:#fff;--theme-input-text:#111827;--theme-input-placeholder:#94a3b8;--theme-header-text-color:#111827;--theme-muted-text-color:#64748b;--theme-input-radius:14px;--theme-button-radius:{{ $buttonRadius }};}
        *{box-sizing:border-box}body{margin:0;font-family:var(--font);background:var(--bg);color:#111827}.shell{min-height:100vh;padding:24px;background:linear-gradient(135deg,color-mix(in srgb,var(--primary) 16%,transparent),transparent 34%),radial-gradient(circle at 86% 12%,color-mix(in srgb,var(--accent) 62%,transparent),transparent 28%),var(--bg)}.page{width:min(1120px,100%);margin:0 auto;display:grid;grid-template-columns:minmax(0,1.08fr) minmax(340px,.92fr);gap:22px;align-items:start}.page.centered{width:min(820px,100%);grid-template-columns:1fr}.page.poster{grid-template-columns:minmax(340px,.86fr) minmax(0,1.14fr)}.page.sidebar{grid-template-columns:minmax(340px,.78fr) minmax(0,1.22fr)}.page.stacked{grid-template-columns:1fr}.page.editorial{width:min(1180px,100%);grid-template-columns:minmax(0,1.3fr) minmax(340px,.7fr)}.card{border:1px solid rgba(17,24,39,.09);border-radius:var(--card-radius);background:rgba(255,255,255,.88);box-shadow:0 26px 80px rgba(15,23,42,.10);backdrop-filter:blur(12px)}.hero{overflow:hidden}.cover{min-height:190px;background:linear-gradient(135deg,color-mix(in srgb,var(--primary) 82%,#111827),color-mix(in srgb,var(--accent) 72%,white));position:relative}.cover.has-image{background-size:cover;background-position:center}.cover:after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,transparent,rgba(0,0,0,.18))}.hero-body{padding:30px}.centered .hero{text-align:center}.centered .brand{justify-content:center}.centered .brand-copy{align-items:center}.centered .benefits{max-width:420px;margin-left:auto;margin-right:auto}.centered .cover{min-height:145px}.editorial .hero-body{padding:56px}.editorial h1{font-size:clamp(46px,6vw,82px)}.poster .hero{order:2}.poster .side{order:1}.sidebar .hero{order:2}.sidebar .side{order:1}.stacked .hero{display:grid;grid-template-columns:360px minmax(0,1fr)}.stacked .cover{min-height:100%}.stacked .side{position:static;display:grid;grid-template-columns:minmax(0,1fr) minmax(260px,.45fr);gap:22px}.brand{display:flex;align-items:center;gap:16px}.brand-copy{display:flex;min-width:0;flex-direction:column;gap:6px}.logo{display:grid;place-items:center;width:52px;height:52px;border-radius:18px;background:color-mix(in srgb,var(--primary) 12%,white);color:var(--primary);font-weight:900;overflow:hidden;padding:6px;flex:0 0 auto}.logo.has-image{width:92px;height:48px;border-radius:16px;padding:6px 10px;background:#fff;border:1px solid rgba(148,163,184,.28);box-shadow:0 12px 24px rgba(15,23,42,.08)}.logo img{width:100%;height:100%;object-fit:contain;object-position:center}.eyebrow{font-size:13px;font-weight:800;letter-spacing:.04em;line-height:1.45;color:#64748b;max-width:34ch;word-break:break-word}.type{display:inline-flex;align-self:flex-start;border-radius:999px;background:color-mix(in srgb,var(--primary) 10%,white);color:var(--primary);padding:5px 10px;letter-spacing:.08em;font-size:11px;font-weight:850;text-transform:uppercase}h1{font-size:clamp(36px,5vw,64px);line-height:1;margin:24px 0 0;letter-spacing:-.055em}p{font-size:16px;line-height:1.72;color:#475569}.lead{font-size:18px;margin-top:18px}.benefits{display:grid;gap:10px;margin:26px 0 0;padding:0;list-style:none}.benefits li{display:flex;gap:10px;align-items:flex-start;color:#334155;font-weight:700}.benefits span{display:grid;place-items:center;width:24px;height:24px;border-radius:999px;background:color-mix(in srgb,var(--primary) 12%,white);color:var(--primary);flex:0 0 24px}.side{position:sticky;top:18px}.form-card{padding:24px}.form-title{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:18px}.form-title h2{font-size:22px;letter-spacing:-.035em;margin:0}.badge{display:inline-flex;align-items:center;border-radius:999px;background:color-mix(in srgb,var(--accent) 58%,white);color:var(--primary);padding:7px 11px;font-size:12px;font-weight:850}.success{margin-bottom:18px;border-radius:18px;background:#ecfdf5;color:#047857;padding:16px;font-weight:800}.meta{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin:18px 0}.meta div{border:1px solid #e5e7eb;border-radius:16px;padding:14px;color:#475569;background:#fff}.meta strong{display:block;color:#111827;font-size:18px}.field{margin-bottom:14px}.field label{display:block;margin-bottom:7px;color:#334155;font-size:13px;font-weight:850}.field input:not([type=hidden]),.field textarea,.field select{width:100%;min-height:50px;border:1px solid #dbe1ea;border-radius:14px;padding:13px 14px;font:inherit;color:#111827;background:#fff;outline:0}.field input:not([type=hidden]):focus,.field textarea:focus,.field select:focus{border-color:var(--primary);box-shadow:0 0 0 4px color-mix(in srgb,var(--primary) 12%,transparent)}.field textarea{min-height:112px;resize:vertical}.button{width:100%;border:0;border-radius:var(--button-radius);background:var(--primary);color:#fff;padding:15px 18px;font-weight:900;font-size:15px;cursor:pointer;box-shadow:0 18px 34px color-mix(in srgb,var(--primary) 25%,transparent)}.stars{display:grid;grid-template-columns:repeat(5,1fr);gap:8px}.stars label{display:grid;place-items:center;height:48px;border:1px solid #dbe1ea;border-radius:14px;cursor:pointer;font-weight:950;color:#ca8a04;background:#fff}.stars input{display:none}.stars label:has(input:checked){background:#fef3c7;border-color:#facc15}.section-card{margin-top:18px;padding:22px;background:linear-gradient(145deg,rgba(255,255,255,.95),color-mix(in srgb,var(--accent) 18%,white));box-shadow:0 18px 48px rgba(15,23,42,.07)}.section-head{display:flex;align-items:flex-start;gap:12px}.section-icon{display:grid;place-items:center;width:38px;height:38px;border-radius:14px;background:color-mix(in srgb,var(--primary) 12%,white);color:var(--primary);font-weight:900;flex:0 0 38px}.section-kicker{margin:0 0 4px;text-transform:uppercase;letter-spacing:.13em;font-size:11px;font-weight:900;color:#64748b}.section-title{margin:0;color:#111827;font-size:18px;letter-spacing:-.02em}.section-body{margin:10px 0 0;color:#475569;font-size:14px;line-height:1.65}.info-grid{display:grid;gap:10px;margin-top:14px}.info-row{display:grid;grid-template-columns:86px minmax(0,1fr);gap:10px;border-top:1px solid #e7eef6;padding-top:10px;color:#475569;font-size:14px}.info-row strong{color:#111827}.terms{margin-top:14px;border-top:1px solid #e5e7eb;padding-top:14px;font-size:14px}.thank-code{display:grid;gap:10px;text-align:center}.thank-code strong{font-size:28px;letter-spacing:.04em;color:var(--primary)}@media(max-width:860px){.shell{padding:12px}.page,.page.centered,.page.poster,.page.sidebar,.page.stacked,.page.editorial{grid-template-columns:1fr}.poster .hero,.poster .side,.sidebar .hero,.sidebar .side{order:initial}.stacked .hero,.stacked .side{display:block}.side{position:static}.hero-body{padding:24px}h1,.editorial h1{font-size:38px}.cover{min-height:150px}.brand{align-items:flex-start}.eyebrow{max-width:none}.info-row{grid-template-columns:1fr}}
        .slot-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
        .brand .logo{width:56px;height:56px;flex:0 0 56px;padding:0;background:color-mix(in srgb,var(--primary) 12%,white);background-size:cover;background-position:center;background-repeat:no-repeat;border:3px solid rgba(255,255,255,.92);box-shadow:0 14px 30px rgba(15,23,42,.14)}
        .brand .logo.has-image{width:56px;height:56px;padding:0;background-size:cover;background-position:center;background-repeat:no-repeat}
        .brand .logo.is-circle{border-radius:999px}
        .brand .logo.is-square{border-radius:16px}
        .brand .logo img{display:none}
        @media(min-width:560px){.slot-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
        @media(min-width:980px){.slot-grid{grid-template-columns:repeat(4,minmax(0,1fr))}}
        .slot-option{display:block;margin:0!important}
        .slot-grid input,.slot-option input{position:absolute!important;opacity:0!important;pointer-events:none!important;width:1px!important;height:1px!important;min-height:0!important;border:0!important;padding:0!important;margin:0!important}
        .slot-option span{display:flex;min-height:48px;align-items:center;justify-content:center;border:1px solid #dbe1ea;border-radius:14px;background:#fff;color:#111827;font-weight:850;cursor:pointer;transition:border-color .18s ease,background .18s ease,color .18s ease,box-shadow .18s ease}
        .slot-option input:focus-visible+span{box-shadow:0 0 0 4px color-mix(in srgb,var(--primary) 12%,transparent);border-color:var(--primary)}
        .slot-option input:checked+span{border-color:var(--primary);background:color-mix(in srgb,var(--primary) 12%,white);color:var(--primary)}
        .slot-empty{margin:0;border:1px dashed #dbe1ea;border-radius:14px;padding:13px 14px;background:#fff;color:#64748b;font-size:14px;font-weight:700}
        .field-error{display:block;margin-top:6px;color:#b91c1c;font-size:13px;font-weight:750}
        .field [x-ref=panel]{z-index:9999!important;background:#fff!important;border-color:#dbe1ea!important}
        .field:has([x-ref=panel]){position:relative;z-index:30}
        .page-blocks{display:grid;gap:12px;margin-bottom:18px}.page-block{padding:18px;background:rgba(255,255,255,.94)}.page-block small{display:block;margin-bottom:7px;text-transform:uppercase;letter-spacing:.14em;font-size:11px;font-weight:850;color:#64748b}.page-block h3{margin:0;color:#111827;font-size:18px}.page-block p{margin:8px 0 0;font-size:14px;line-height:1.6}.block-cta{display:inline-flex;margin-top:12px;border-radius:999px;background:color-mix(in srgb,var(--primary) 12%,white);color:var(--primary);padding:8px 12px;font-size:12px;font-weight:850;text-decoration:none}
        .cover{min-height:155px}.hero-body{padding:26px 28px 30px}h1{font-size:clamp(34px,4.35vw,54px)}.lead{font-size:16px}.form-card{background:#fff;padding:28px;box-shadow:0 30px 90px rgba(15,23,42,.16);border-color:rgba(15,23,42,.12)}.form-title{align-items:flex-start;margin-bottom:8px}.form-title h2{font-size:24px}.form-note{margin:0 0 18px;color:#475569;font-size:14px;line-height:1.55}.badge{background:color-mix(in srgb,var(--primary) 10%,white);color:var(--primary)}.button{min-height:56px;font-size:16px;letter-spacing:0}.stars.rating-cards{grid-template-columns:1fr;gap:10px}.stars.rating-cards label{display:flex;min-height:62px;align-items:center;justify-content:flex-start;gap:12px;padding:12px 15px;color:#111827;text-align:left;border-color:rgba(148,163,184,.28);border-radius:20px;box-shadow:0 1px 2px rgba(15,23,42,.035);transition:border-color .18s ease,background .18s ease,box-shadow .18s ease,transform .18s ease}.stars.rating-cards label:hover{border-color:color-mix(in srgb,var(--primary) 42%,#cbd5e1);box-shadow:0 14px 30px color-mix(in srgb,var(--primary) 10%,transparent);transform:translateY(-1px)}.stars.rating-cards label:has(input:checked){border-color:color-mix(in srgb,var(--primary) 62%,#cbd5e1);background:linear-gradient(180deg,color-mix(in srgb,var(--primary) 11%,white),#fff);box-shadow:0 0 0 3px color-mix(in srgb,var(--primary) 11%,transparent),0 16px 34px rgba(15,23,42,.09)}.rating-stars{min-width:92px;color:#ca8a04;letter-spacing:.03em}.rating-copy{font-weight:850;color:#111827}.review-page{grid-template-columns:minmax(0,.96fr) minmax(390px,1.04fr)}.review-page .cover{min-height:138px}.review-page .hero-body{padding:24px 28px 24px}.review-page h1{font-size:clamp(30px,3.55vw,44px);line-height:.98}.review-page .form-card{border:1px solid rgba(148,163,184,.22);box-shadow:0 34px 90px rgba(15,23,42,.16),0 0 0 5px rgba(255,255,255,.52)}.review-page .field{margin-bottom:19px}.review-page .field label{margin-bottom:9px}.review-page .field textarea::placeholder{color:#64748b}.review-page .button{display:block;width:min(82%,430px);margin:4px auto 0;background:linear-gradient(135deg,var(--primary),color-mix(in srgb,var(--primary) 78%,#111827));box-shadow:0 18px 38px color-mix(in srgb,var(--primary) 20%,transparent)}.review-page .business-card{display:none}@media(min-width:720px){.stars.rating-cards{grid-template-columns:repeat(5,minmax(0,1fr))}.stars.rating-cards label{display:grid;min-height:82px;justify-items:center;gap:5px;padding:12px 8px;text-align:center}.rating-stars{min-width:0;font-size:14px}.rating-copy{font-size:12px}}@media(max-width:860px){.page.review-page{display:flex;flex-direction:column}.review-page .side{order:-1}.cover{min-height:126px}h1,.editorial h1,.review-page h1{font-size:32px}.form-card{padding:22px}.review-page .button{width:100%;position:sticky;bottom:10px;z-index:5}}
    </style>
    <script>
        window.localBoostBookingSlots = function (slotMap, initialServiceId, initialDate) {
            return {
                slotMap: slotMap || {},
                serviceId: String(initialServiceId || ''),
                date: String(initialDate || ''),
                selectedTime: '',
                get slots() {
                    const serviceSlots = this.slotMap[this.serviceId] || {};

                    return this.date ? (serviceSlots[this.date] || []) : [];
                },
                chooseService(event) {
                    this.serviceId = String(event.target.value || '');
                    this.selectedTime = '';
                },
                chooseDate(event) {
                    this.date = String(event.target.value || '');
                    this.selectedTime = '';
                },
            };
        };
    </script>
</head>
<body>
    <main class="shell">
        <section class="page {{ $design['layout_style'] ?? 'split' }} {{ $landingPage->type === 'review' ? 'review-page' : '' }}">
            <article class="card hero">
                <div class="cover {{ filled($design['cover_image']) ? 'has-image' : '' }}" @if(filled($design['cover_image'])) style="background-image:url('{{ $design['cover_image'] }}')" @endif></div>
                <div class="hero-body">
                    <div class="brand">
                        @if($design['show_logo'])
                            <div
                                class="logo {{ filled($design['logo_url']) ? 'has-image' : '' }} is-{{ $logoShape }}"
                                @if(filled($design['logo_url'])) style="background-image:url('{{ $design['logo_url'] }}')" @endif
                            >
                                @unless(filled($design['logo_url']))
                                    {{ str($business?->name ?: 'LB')->substr(0, 2)->upper() }}
                                @endunless
                            </div>
                        @endif
                        <div class="brand-copy">
                            <div class="eyebrow">{{ $business?->name ?: __('Local business') }}</div>
                            <span class="type">{{ str($landingPage->type)->headline() }}</span>
                        </div>
                    </div>

                    <h1>{{ data_get($content, 'headline', $landingPage->title) }}</h1>
                    @if(data_get($content, 'subheadline'))
                        <p class="lead">{{ data_get($content, 'subheadline') }}</p>
                    @endif
                    @if(data_get($content, 'description'))
                        <p>{{ data_get($content, 'description') }}</p>
                    @endif
                    @if($design['show_benefits'] && $benefits)
                        <ul class="benefits">
                            @foreach($benefits as $benefit)
                                <li><span>&#10003;</span>{{ $benefit }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </article>

            <aside class="side">
                @if($extraBlocks->isNotEmpty())
                    <div class="page-blocks">
                        @foreach($extraBlocks as $block)
                            <div class="card page-block">
                                @php
                                    $blockSettings = (array) ($block['settings'] ?? []);
                                    $blockType = (string) ($block['type'] ?? 'custom_html');
                                    $blockTitle = trim((string) ($block['title'] ?? ''));
                                    $blockKicker = $blockTitle !== ''
                                        ? $blockTitle
                                        : ($blockLabels[$blockType] ?? __('Section'));
                                @endphp
                                <small>{{ $blockKicker }}</small>

                                @switch($blockType)
                                    @case('benefits')
                                        <h3>{{ $blockSettings['headline'] ?? ($block['title'] ?: __('Why choose us')) }}</h3>
                                        @php
                                            $benefitItems = collect(preg_split('/\r\n|\r|\n/', (string) ($blockSettings['items'] ?? $blockSettings['body'] ?? '')))->map(fn ($line) => trim($line))->filter();
                                        @endphp
                                        @if($benefitItems->isNotEmpty())
                                            <ul class="benefits" style="margin-top:12px;">
                                                @foreach($benefitItems as $benefit)
                                                    <li><span>&#10003;</span>{{ $benefit }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                        @break

                                    @case('form')
                                        <h3>{{ $blockSettings['form_title'] ?? ($block['title'] ?: __('Send your details')) }}</h3>
                                        @if(filled($blockSettings['success_message'] ?? ''))
                                            <p>{{ $blockSettings['success_message'] }}</p>
                                        @endif
                                        @if(filled($blockSettings['submit_button'] ?? ''))
                                            <a class="block-cta" href="#">{{ $blockSettings['submit_button'] }}</a>
                                        @endif
                                        @break

                                    @case('offer')
                                    @case('coupon_details')
                                        <h3>{{ $blockSettings['offer_title'] ?? $blockSettings['headline'] ?? ($block['title'] ?: __('Offer details')) }}</h3>
                                        <div class="meta" style="margin-bottom:0;">
                                            @if(filled($blockSettings['discount'] ?? ''))
                                                <div><strong>{{ $blockSettings['discount'] }}</strong>{{ __('Discount / value') }}</div>
                                            @endif
                                            @if(filled($blockSettings['expiry'] ?? ''))
                                                <div><strong>{{ __('Valid until') }}</strong>{{ $blockSettings['expiry'] }}</div>
                                            @endif
                                        </div>
                                        @if(filled($blockSettings['terms'] ?? ''))
                                            <p>{{ $blockSettings['terms'] }}</p>
                                        @endif
                                        @if(filled($blockSettings['redemption_instructions'] ?? ''))
                                            <p>{{ $blockSettings['redemption_instructions'] }}</p>
                                        @endif
                                        @break

                                    @case('booking_services')
                                        <h3>{{ $blockSettings['service_title'] ?? ($block['title'] ?: __('Booking services')) }}</h3>
                                        <div class="meta" style="margin-bottom:0;">
                                            @if(filled($blockSettings['duration'] ?? ''))
                                                <div><strong>{{ __('Duration') }}</strong>{{ $blockSettings['duration'] }}</div>
                                            @endif
                                            @if(filled($blockSettings['price'] ?? ''))
                                                <div><strong>{{ __('Price') }}</strong>{{ $blockSettings['price'] }}</div>
                                            @endif
                                        </div>
                                        @if(filled($blockSettings['description'] ?? ''))
                                            <p>{{ $blockSettings['description'] }}</p>
                                        @endif
                                        @break

                                    @case('business_info')
                                        <h3>{{ $blockSettings['headline'] ?? __('Business info') }}</h3>
                                        @if($business)
                                            <div class="info-grid">
                                                @if(($blockSettings['show_address'] ?? true) && $business->address)<div class="info-row"><strong>{{ __('Address') }}:</strong><span>{{ $business->address }}</span></div>@endif
                                                @if(($blockSettings['show_phone'] ?? true) && $business->phone)<div class="info-row"><strong>{{ __('Phone') }}:</strong><span>{{ $business->phone }}</span></div>@endif
                                                @if(($blockSettings['show_website'] ?? true) && $business->website)<div class="info-row"><strong>{{ __('Website') }}:</strong><a href="{{ $business->website }}" target="_blank">{{ parse_url($business->website, PHP_URL_HOST) ?: $business->website }}</a></div>@endif
                                            </div>
                                        @endif
                                        @break

                                    @case('social_links')
                                        <h3>{{ $blockSettings['headline'] ?? __('Follow us') }}</h3>
                                        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;">
                                            @foreach(['facebook_url' => 'Facebook', 'instagram_url' => 'Instagram', 'website_url' => 'Website'] as $key => $label)
                                                @if(filled($blockSettings[$key] ?? ''))
                                                    <a class="block-cta" href="{{ $blockSettings[$key] }}" target="_blank">{{ $label }}</a>
                                                @endif
                                            @endforeach
                                        </div>
                                        @break

                                    @case('faq')
                                        <h3>{{ $blockSettings['question'] ?? $blockSettings['headline'] ?? ($block['title'] ?: __('FAQ')) }}</h3>
                                        @if(filled($blockSettings['answer'] ?? $blockSettings['body'] ?? ''))
                                            <p>{{ $blockSettings['answer'] ?? $blockSettings['body'] }}</p>
                                        @endif
                                        @break

                                    @case('testimonials')
                                        @if(filled($blockSettings['quote'] ?? ''))
                                            <p>"{{ $blockSettings['quote'] }}"</p>
                                        @endif
                                        <h3>{{ $blockSettings['author'] ?? ($block['title'] ?: __('Local customer')) }}</h3>
                                        @break

                                    @case('map')
                                        <h3>{{ $blockSettings['map_title'] ?? ($block['title'] ?: __('Location')) }}</h3>
                                        <p>{{ $blockSettings['map_text'] ?? ($business?->address ?: __('Location details')) }}</p>
                                        @break

                                    @case('opening_hours')
                                        <h3>{{ $block['title'] ?: __('Opening hours') }}</h3>
                                        @if(filled($blockSettings['hours_text'] ?? ''))
                                            <p style="white-space:pre-line;">{{ $blockSettings['hours_text'] }}</p>
                                        @endif
                                        @break

                                    @case('thank_you')
                                        @if(filled($blockSettings['message'] ?? ''))
                                            <p>{{ $blockSettings['message'] }}</p>
                                        @endif
                                        @if(filled($blockSettings['cta'] ?? ''))
                                            <a class="block-cta" href="#">{{ $blockSettings['cta'] }}</a>
                                        @endif
                                        @break

                                    @case('custom_html')
                                        <div>{!! $blockSettings['html'] ?? $blockSettings['body'] ?? '' !!}</div>
                                        @break

                                    @default
                                        @if(filled(data_get($block, 'settings.headline')))
                                            <h3>{{ data_get($block, 'settings.headline') }}</h3>
                                        @endif
                                        @if(filled(data_get($block, 'settings.body')))
                                            <p>{{ data_get($block, 'settings.body') }}</p>
                                        @endif
                                        @if(filled(data_get($block, 'settings.cta')))
                                            <a class="block-cta" href="#">{{ data_get($block, 'settings.cta') }}</a>
                                        @endif
                                @endswitch
                            </div>
                        @endforeach
                    </div>
                @endif

                <div
                    class="card form-card"
                    @if($landingPage->type === 'review')
                        x-data="{ rating: '' }"
                    @endif
                >
                    @if(session('landing_page_converted'))
                        <div class="success">
                            <div class="thank-code">
                                <span>{{ data_get($content, 'thank_you_message', __('Thank you.')) }}</span>
                                @if(in_array($landingPage->type, ['coupon', 'promotion'], true))
                                    <strong>{{ data_get($settings, 'coupon_title', __('CLAIMED')) }}</strong>
                                    <small>{{ __('Show this confirmation when you visit.') }}</small>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="form-title">
                        <h2>{{ $formTitle }}</h2>
                        @if($landingPage->type === 'review')
                            <span class="badge" x-text="rating === '' ? @js(__('Review flow')) : (Number(rating) >= 4 ? @js(__('Public review')) : @js(__('Internal feedback')))">{{ $formBadge }}</span>
                        @else
                            <span class="badge">{{ $formBadge }}</span>
                        @endif
                    </div>
                    @if($formNote !== '')
                        <p class="form-note">{{ $formNote }}</p>
                    @endif

                    @if(in_array($landingPage->type, ['coupon', 'promotion'], true))
                        @php
                            $discountValue = trim((string) data_get($settings, 'discount', ''));
                            $couponTitleValue = trim((string) data_get($settings, 'coupon_title', ''));
                            $expiryValue = trim((string) data_get($settings, 'expiry', ''));
                        @endphp
                        @if($discountValue !== '' || $couponTitleValue !== '' || $expiryValue !== '')
                            <div class="meta">
                                @if($discountValue !== '' || $couponTitleValue !== '')
                                    <div>
                                        @if($discountValue !== '')<strong>{{ $discountValue }}</strong>@endif
                                        {{ $couponTitleValue !== '' ? $couponTitleValue : __('Limited-time local offer') }}
                                    </div>
                                @endif
                                @if($expiryValue !== '')
                                    <div><strong>{{ __('Valid until') }}</strong>{{ $expiryValue }}</div>
                                @endif
                            </div>
                        @endif
                        @if($design['show_terms'] && data_get($settings, 'terms'))
                            <p class="terms">{{ data_get($settings, 'terms') }}</p>
                        @endif
                    @endif

                    <form
                        method="post"
                        action="{{ $submitRoute }}"
                        @if($landingPage->type === 'booking')
                            x-data="localBoostBookingSlots(@js($bookingAvailability), @js($initialBookingServiceId), @js(old('date', '')))"
                        @endif
                    >
                        @csrf
                        @if($errors->any())
                            <div class="success" style="background:#fef2f2;color:#b91c1c;">
                                {{ $errors->first() }}
                            </div>
                        @endif
                        @if($landingPage->type === 'review')
                            <div class="field">
                                <label>{{ __('Choose your rating') }}</label>
                                <div class="stars rating-cards">
                                    @for($i = 1; $i <= 5; $i++)
                                        <label>
                                            <input type="radio" name="rating" value="{{ $i }}" required x-model="rating">
                                            <span class="rating-stars" aria-hidden="true">@for($star = 1; $star <= $i; $star++)&#9733;@endfor</span>
                                            <span class="rating-copy">{{ $ratingLabels[$i] }}</span>
                                        </label>
                                    @endfor
                                </div>
                            </div>
                            <x-ui.textarea class="field" name="feedback" :label="__('What went well or what can we improve?')" :placeholder="__('Your feedback helps improve local service.')"></x-ui.textarea>
                            <x-ui.input class="field" name="name" :label="__('Name')" />
                            <x-ui.input class="field" type="email" name="email" :label="__('Email')" />
                        @elseif($landingPage->type === 'booking')
                            <div class="meta">
                                @if($bookingServices->isNotEmpty())
                                    <div><strong>{{ format_number_locale($bookingServices->count()) }}</strong>{{ __('services available') }}</div>
                                    <div><strong>{{ __('Price') }}</strong>{{ $bookingServices->whereNotNull('price')->isNotEmpty() ? __('Shown per service') : __('Ask us') }}</div>
                                @else
                                    <div><strong>{{ data_get($settings, 'service', __('Appointment')) }}</strong>{{ data_get($settings, 'duration', __('Duration varies')) }}</div>
                                    <div><strong>{{ __('Price') }}</strong>{{ data_get($settings, 'price', __('Ask us')) }}</div>
                                @endif
                            </div>
                            @if($bookingServices->isNotEmpty())
                                <x-ui.select class="field" name="service_id" :label="__('Service')" x-on:change="chooseService($event)" required>
                                    <option value="">{{ __('Choose a service') }}</option>
                                    @foreach($bookingServices as $service)
                                        <option value="{{ $service->id }}" @selected((string) $service->id === $initialBookingServiceId)>
                                            {{ $service->name }}
                                            - {{ $service->duration_minutes }} {{ __('min') }}
                                            @if($service->price !== null)
                                                - {{ format_price_locale((float) $service->price) }}
                                            @endif
                                        </option>
                                    @endforeach
                                </x-ui.select>
                                @error('service_id')<span class="field-error">{{ $message }}</span>@enderror
                            @else
                                <input type="hidden" name="service" value="{{ data_get($settings, 'service', $landingPage->title) }}">
                            @endif
                            <x-ui.date-picker class="field" name="date" :label="__('Date')" :value="old('date')" :placeholder="__('Choose date')" placement="bottom" x-on:change="chooseDate($event)" required />
                            @error('date')<span class="field-error">{{ $message }}</span>@enderror
                            <x-ui.field class="field" :label="__('Available time')">
                                @if($bookingServices->isNotEmpty())
                                    <template x-if="! serviceId">
                                        <p class="slot-empty">{{ __('Choose a service first.') }}</p>
                                    </template>
                                    <template x-if="serviceId && ! date">
                                        <p class="slot-empty">{{ __('Choose a date to see available times.') }}</p>
                                    </template>
                                    <template x-if="serviceId && date && slots.length === 0">
                                        <p class="slot-empty">{{ __('No available time slots for this date. Please choose another date.') }}</p>
                                    </template>
                                    <div class="slot-grid" x-show="serviceId && date && slots.length">
                                        <template x-for="slot in slots" :key="slot">
                                            <label class="slot-option">
                                                <input type="radio" name="time" :value="slot" required x-model="selectedTime">
                                                <span x-text="slot"></span>
                                            </label>
                                        </template>
                                    </div>
                                @else
                                    <div class="slot-grid">
                                        @foreach($slots ?: ['09:00','10:00','14:00','15:00'] as $slot)
                                            <label class="slot-option">
                                                <input type="radio" name="time" value="{{ $slot }}" required @checked($loop->first)>
                                                <span>{{ $slot }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                            </x-ui.field>
                            @error('time')<span class="field-error">{{ $message }}</span>@enderror
                            <x-ui.input class="field" name="name" :label="__('Name *')" :placeholder="__('Your name')" :value="old('name')" required />
                            @error('name')<span class="field-error">{{ $message }}</span>@enderror
                            <x-ui.input class="field" name="phone" :label="__('Phone *')" :placeholder="__('Phone number')" :value="old('phone')" minlength="6" maxlength="30" required />
                            @error('phone')<span class="field-error">{{ $message }}</span>@enderror
                            <x-ui.input class="field" type="email" name="email" :label="__('Email')" :placeholder="__('Email address optional')" :value="old('email')" />
                            @error('email')<span class="field-error">{{ $message }}</span>@enderror
                        @elseif($landingPage->type === 'feedback')
                            <x-ui.select class="field" name="rating" :label="__('Rating')">
                                <option value="">{{ __('Choose rating') }}</option>
                                @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </x-ui.select>
                            <x-ui.input class="field" name="topic" :label="__('Topic')" />
                            <x-ui.textarea class="field" name="feedback" :label="__('Feedback')" required></x-ui.textarea>
                            <x-ui.input class="field" name="name" :label="__('Name')" />
                            <x-ui.input class="field" type="email" name="email" :label="__('Email')" />
                        @else
                            <x-ui.input class="field" name="name" :label="__('Name')" required />
                            <x-ui.input class="field" type="email" name="email" :label="__('Email')" />
                            <x-ui.input class="field" name="phone" :label="__('Phone')" />
                            <x-ui.input class="field" name="interested_service" :label="__('Interested service')" />
                            <x-ui.textarea class="field" name="message" :label="__('Message')"></x-ui.textarea>
                        @endif
                        <x-ui.button class="button" type="submit">{{ $submitText }}</x-ui.button>
                    </form>
                </div>

                @if($showBusinessInfo && $business && ! $businessInfoBlockConfigured && $landingPage->type !== 'review')
                    <div class="card section-card business-card">
                        <div class="section-head">
                            <span class="section-icon">&#8962;</span>
                            <div>
                                <p class="section-kicker">{{ __('Business') }}</p>
                                <h3 class="section-title">{{ __('Business info') }}</h3>
                            </div>
                        </div>
                        <div class="info-grid">
                            @if($business->address)<div class="info-row"><strong>{{ __('Address') }}:</strong><span>{{ $business->address }}</span></div>@endif
                            @if($business->phone)<div class="info-row"><strong>{{ __('Phone') }}:</strong><span>{{ $business->phone }}</span></div>@endif
                            @if($business->website)<div class="info-row"><strong>{{ __('Website') }}:</strong><a href="{{ $business->website }}" target="_blank">{{ parse_url($business->website, PHP_URL_HOST) ?: $business->website }}</a></div>@endif
                        </div>
                    </div>
                @endif

                @if($showFaq && ! $faqBlockConfigured)
                    <div class="card section-card">
                        <div class="section-head">
                            <span class="section-icon">?</span>
                            <div>
                                <p class="section-kicker">{{ __('Next step') }}</p>
                                <h3 class="section-title">{{ data_get($faqBlock, 'settings.headline') ?: __('What happens next?') }}</h3>
                            </div>
                        </div>
                        <p class="section-body">{{ data_get($faqBlock, 'settings.body') ?: __('Your submission is sent directly to the local team and tracked for follow-up.') }}</p>
                    </div>
                @endif
            </aside>
        </section>
    </main>
    @livewireScripts
</body>
</html>
