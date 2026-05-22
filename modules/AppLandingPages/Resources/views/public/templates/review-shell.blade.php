@php
    $content = $landingPage->content ?: [];
    $settings = $landingPage->settings ?: [];
    $design = array_merge([
        'primary_color' => '#0f766e',
        'background_color' => '#f4fbf8',
        'accent_color' => '#ccfbf1',
        'font_style' => 'modern',
        'logo_url' => '',
        'logo_shape' => 'circle',
        'cover_image' => '',
        'show_logo' => true,
        'show_benefits' => true,
    ], (array) data_get($settings, 'design', []));
    $business = $landingPage->business;
    $primary = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $design['primary_color']) ? $design['primary_color'] : '#0f766e';
    $background = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $design['background_color']) ? $design['background_color'] : '#f4fbf8';
    $accent = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $design['accent_color']) ? $design['accent_color'] : '#ccfbf1';
    $fontFamily = 'Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
    $benefits = (array) data_get($content, 'benefits', []);
    $submitRoute = route('landing-pages.submit', ['landingPage' => $landingPage->slug]);
    $ratingLabels = [1 => __('Poor'), 2 => __('Okay'), 3 => __('Good'), 4 => __('Great'), 5 => __('Excellent')];
    $variant = $variant ?? 'clean';
    $headline = data_get($content, 'headline', $landingPage->title);
    $subheadline = data_get($content, 'subheadline') ?: __('Your rating helps improve local service and helps other customers choose confidently.');
    $cta = data_get($content, 'cta') === 'Continue' ? __('Submit feedback') : data_get($content, 'cta', __('Submit feedback'));
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $landingPage->title }}</title>
    {!! theme_vite('app', ['assets/js/app.js']) !!}
    <style>
        :root{--primary:{{ $primary }};--bg:{{ $background }};--accent:{{ $accent }};--font:{!! $fontFamily !!}}
        *{box-sizing:border-box}body{margin:0;font-family:var(--font);background:var(--bg);color:#111827}.page{min-height:100vh}.logo{display:grid;width:54px;height:54px;place-items:center;overflow:hidden;border-radius:18px;background:color-mix(in srgb,var(--primary) 12%,white);color:var(--primary);font-weight:950}.logo.is-circle{border-radius:999px}.logo.has-image{background-size:cover;background-position:center;border:3px solid rgba(255,255,255,.9);box-shadow:0 14px 30px rgba(15,23,42,.14)}.logo img{display:none}.business{display:flex;align-items:center;gap:14px}.business-name{font-size:13px;font-weight:850;color:#64748b;line-height:1.45}.pill{display:inline-flex;width:max-content;border-radius:999px;background:color-mix(in srgb,var(--primary) 10%,white);color:var(--primary);padding:5px 10px;font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.08em}h1{margin:0;color:#111827;letter-spacing:-.045em;line-height:.98}p{color:#475569;line-height:1.65}.card{border:1px solid rgba(148,163,184,.22);background:#fff;box-shadow:0 28px 80px rgba(15,23,42,.13)}.form{padding:28px;border-radius:28px}.form-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px}.form-head h2{margin:0;font-size:24px;letter-spacing:-.03em}.badge{display:inline-flex;align-items:center;border-radius:999px;background:color-mix(in srgb,var(--primary) 10%,white);color:var(--primary);padding:7px 11px;font-size:12px;font-weight:900}.note{margin:8px 0 18px;font-size:14px}.field{margin-bottom:18px}.field label{display:block;margin-bottom:9px;color:#334155;font-size:13px;font-weight:850}.field input:not([type=hidden]),.field textarea{width:100%;min-height:52px;border:1px solid #dbe1ea;border-radius:16px;background:#fff;padding:14px 15px;font:inherit;color:#111827;outline:0}.field textarea{min-height:122px;resize:vertical}.field input:focus,.field textarea:focus{border-color:var(--primary);box-shadow:0 0 0 4px color-mix(in srgb,var(--primary) 11%,transparent)}.ratings{display:grid;gap:10px}.ratings label{display:flex;min-height:62px;align-items:center;gap:12px;border:1px solid rgba(148,163,184,.28);border-radius:20px;background:#fff;padding:12px 15px;cursor:pointer;box-shadow:0 1px 2px rgba(15,23,42,.035);transition:.18s ease}.ratings label:hover{border-color:color-mix(in srgb,var(--primary) 42%,#cbd5e1);box-shadow:0 14px 30px color-mix(in srgb,var(--primary) 10%,transparent);transform:translateY(-1px)}.ratings label:has(input:checked){border-color:color-mix(in srgb,var(--primary) 62%,#cbd5e1);background:linear-gradient(180deg,color-mix(in srgb,var(--primary) 11%,white),#fff);box-shadow:0 0 0 3px color-mix(in srgb,var(--primary) 11%,transparent),0 16px 34px rgba(15,23,42,.09)}.ratings input{display:none}.stars{min-width:92px;color:#ca8a04}.rating-copy{font-weight:850}.button{display:block;width:min(82%,430px);min-height:56px;margin:4px auto 0;border:0;border-radius:999px;background:linear-gradient(135deg,var(--primary),color-mix(in srgb,var(--primary) 78%,#111827));color:#fff;font-size:16px;font-weight:900;cursor:pointer;box-shadow:0 18px 38px color-mix(in srgb,var(--primary) 20%,transparent)}.benefits{display:grid;gap:10px;margin:24px 0 0;padding:0;list-style:none}.benefits li{display:flex;gap:10px;align-items:center;color:#334155;font-weight:800}.benefits span{display:grid;width:24px;height:24px;place-items:center;border-radius:999px;background:color-mix(in srgb,var(--primary) 12%,white);color:var(--primary)}.cover{background-size:cover;background-position:center}.template-clean{padding:26px;background:linear-gradient(135deg,color-mix(in srgb,var(--primary) 12%,transparent),transparent 34%),radial-gradient(circle at 88% 8%,color-mix(in srgb,var(--accent) 54%,transparent),transparent 30%),var(--bg)}.template-clean .wrap{display:grid;width:min(1120px,100%);margin:auto;grid-template-columns:minmax(0,.96fr) minmax(390px,1.04fr);gap:22px}.template-clean .hero{overflow:hidden;border-radius:28px;background:rgba(255,255,255,.88)}.template-clean .cover{height:138px}.template-clean .hero-body{padding:24px 28px}.template-clean h1{margin-top:24px;font-size:clamp(30px,3.55vw,44px)}.template-google{display:grid;place-items:center;padding:28px;background:#f8fafc}.template-google .wrap{display:grid;width:min(980px,100%);grid-template-columns:.9fr 1.1fr;gap:0;overflow:hidden;border-radius:30px;background:#fff;box-shadow:0 34px 90px rgba(15,23,42,.14)}.template-google .hero{padding:34px;background:linear-gradient(180deg,#fff,color-mix(in srgb,var(--accent) 18%,white))}.template-google .form{border-radius:0;box-shadow:none}.template-google h1{margin-top:34px;font-size:clamp(30px,4vw,48px)}.template-friendly{padding:20px;background:linear-gradient(180deg,color-mix(in srgb,var(--accent) 36%,white),#fff)}.template-friendly .wrap{display:grid;width:min(1040px,100%);margin:auto;gap:18px}.template-friendly .hero{display:grid;grid-template-columns:180px minmax(0,1fr);gap:22px;align-items:center;border-radius:28px;padding:20px;background:#fff}.template-friendly .cover{height:180px;border-radius:22px}.template-friendly h1{font-size:clamp(30px,4vw,46px)}.template-friendly .form{width:min(720px,100%);margin:0 auto}.template-luxury{padding:30px;background:#111827;color:#fff}.template-luxury .wrap{display:grid;width:min(1120px,100%);margin:auto;grid-template-columns:1fr 1fr;gap:24px}.template-luxury .hero{border:1px solid rgba(255,255,255,.12);border-radius:30px;padding:34px;background:linear-gradient(145deg,rgba(255,255,255,.08),rgba(255,255,255,.03))}.template-luxury h1,.template-luxury .business-name{color:#fff}.template-luxury p,.template-luxury .benefits li{color:#cbd5e1}.template-luxury .pill{background:rgba(255,255,255,.1);color:#fde68a}.template-luxury .cover{height:220px;border-radius:24px;margin-bottom:28px}.template-luxury .form{border-color:rgba(255,255,255,.16)}@media(min-width:720px){.ratings{grid-template-columns:repeat(5,minmax(0,1fr))}.ratings label{display:grid;min-height:82px;justify-items:center;gap:5px;padding:12px 8px;text-align:center}.stars{min-width:0;font-size:14px}.rating-copy{font-size:12px}}@media(max-width:860px){.template-clean .wrap,.template-google .wrap,.template-luxury .wrap,.template-friendly .hero{grid-template-columns:1fr}.template-clean .form,.template-google .form,.template-luxury .form,.template-friendly .form{order:-1}.template-friendly .cover{height:130px}.button{width:100%;position:sticky;bottom:10px;z-index:5}}
        .template-restaurant{padding:24px;background:linear-gradient(180deg,#fff7ed,#fff)}.template-restaurant .wrap{display:grid;width:min(1080px,100%);margin:auto;grid-template-columns:.88fr 1.12fr;gap:20px}.template-restaurant .hero{border-radius:34px;overflow:hidden;background:#fff}.template-restaurant .cover{height:240px}.template-restaurant .hero-body{padding:26px}.template-restaurant h1{font-family:Georgia,serif;font-size:clamp(34px,4.2vw,54px)}.template-restaurant .form{border-radius:34px}.template-restaurant .button{background:linear-gradient(135deg,#c2410c,#7c2d12)}
        .template-clinic{padding:26px;background:#f0f9ff}.template-clinic .wrap{display:grid;width:min(1040px,100%);margin:auto;grid-template-columns:1.05fr .95fr;gap:18px}.template-clinic .hero{border-radius:22px;padding:30px;background:#fff;box-shadow:none}.template-clinic .cover{display:none}.template-clinic h1{font-size:clamp(30px,3.5vw,44px);letter-spacing:-.035em}.template-clinic .form{border-radius:22px;box-shadow:0 22px 60px rgba(14,116,144,.12)}.template-clinic .ratings label{border-radius:14px}.template-clinic .button{background:#0369a1}
        .template-star{padding:22px;background:radial-gradient(circle at 50% 0,#fef3c7,transparent 34%),#fff}.template-star .wrap{display:grid;width:min(980px,100%);margin:auto;gap:18px}.template-star .hero{text-align:center;border:0;background:transparent;box-shadow:none}.template-star .cover{display:none}.template-star .business{justify-content:center}.template-star h1{margin-top:24px;font-size:clamp(38px,5vw,62px)}.template-star .form{width:min(760px,100%);margin:auto;border-radius:30px}.template-star .ratings label:before{content:"★";color:#ca8a04;font-size:18px}.template-star .button{background:linear-gradient(135deg,#ca8a04,#854d0e)}
        .template-neighborhood{padding:20px;background:#f0fdf4}.template-neighborhood .wrap{display:grid;width:min(1060px,100%);margin:auto;grid-template-columns:1fr 1fr;gap:18px}.template-neighborhood .hero{border:2px dashed color-mix(in srgb,var(--primary) 28%,#bbf7d0);border-radius:26px;background:#fff;padding:22px}.template-neighborhood .cover{height:160px;border-radius:20px;margin-bottom:20px}.template-neighborhood h1{font-size:clamp(32px,4vw,50px)}.template-neighborhood .form{border-radius:26px}.template-neighborhood .button{background:#047857}
        .template-service{padding:24px;background:#f8fafc}.template-service .wrap{display:grid;width:min(1120px,100%);margin:auto;grid-template-columns:300px minmax(0,1fr);gap:18px}.template-service .hero{position:sticky;top:20px;border-radius:20px;padding:22px;background:#111827;color:#fff}.template-service .cover{height:120px;border-radius:16px;margin-bottom:18px}.template-service h1,.template-service .business-name{color:#fff}.template-service p,.template-service .benefits li{color:#cbd5e1}.template-service .form{border-radius:20px}.template-service .button{background:#334155}
        .template-happy{padding:22px;background:linear-gradient(135deg,#fef9c3,#ecfeff)}.template-happy .wrap{display:grid;width:min(1000px,100%);margin:auto;gap:20px}.template-happy .hero{display:grid;grid-template-columns:1fr 220px;gap:24px;align-items:center;border-radius:32px;padding:24px;background:#fff}.template-happy .cover{order:2;height:220px;border-radius:26px}.template-happy h1{font-size:clamp(34px,4.4vw,56px)}.template-happy .form{width:min(760px,100%);margin:auto;border-radius:32px}.template-happy .button{background:linear-gradient(135deg,#0891b2,#16a34a)}
        .template-minimal{padding:24px;background:#fff}.template-minimal .wrap{display:grid;width:min(880px,100%);margin:auto;gap:18px}.template-minimal .hero{border:0;box-shadow:none;background:transparent;text-align:left}.template-minimal .cover,.template-minimal .benefits,.template-minimal .pill{display:none}.template-minimal h1{font-size:clamp(30px,4vw,44px)}.template-minimal .form{border-radius:18px;box-shadow:none}.template-minimal .ratings label{border-radius:12px;box-shadow:none}.template-minimal .button{width:100%;border-radius:14px;background:#111827}
        .template-warm{padding:22px;background:#fffbeb}.template-warm .wrap{display:grid;width:min(1040px,100%);margin:auto;grid-template-columns:1fr 1fr;gap:20px}.template-warm .hero{border-radius:36px;padding:28px;background:linear-gradient(145deg,#fff,#fef3c7)}.template-warm .cover{height:170px;border-radius:28px;margin-bottom:22px}.template-warm h1{font-family:Georgia,serif;font-size:clamp(34px,4.3vw,54px)}.template-warm .form{border-radius:36px}.template-warm .button{background:linear-gradient(135deg,#b45309,#7c2d12)}
        .template-premium{padding:30px;background:linear-gradient(135deg,#020617,#111827);color:#fff}.template-premium .wrap{display:grid;width:min(1100px,100%);margin:auto;grid-template-columns:.95fr 1.05fr;gap:26px}.template-premium .hero{border:1px solid rgba(253,230,138,.18);border-radius:10px;padding:36px;background:rgba(255,255,255,.04);box-shadow:none}.template-premium .cover{height:190px;margin-bottom:28px}.template-premium h1,.template-premium .business-name{color:#fff}.template-premium p,.template-premium .benefits li{color:#d1d5db}.template-premium .pill{background:rgba(253,230,138,.12);color:#fde68a}.template-premium .form{border-radius:10px}.template-premium .button{border-radius:10px;background:linear-gradient(135deg,#ca8a04,#facc15);color:#111827}
        .template-recovery{padding:24px;background:#f8fafc}.template-recovery .wrap{display:grid;width:min(1040px,100%);margin:auto;grid-template-columns:1fr 1fr;gap:18px}.template-recovery .hero{border-radius:26px;padding:30px;background:#fff}.template-recovery .cover{display:none}.template-recovery h1{font-size:clamp(30px,3.8vw,48px)}.template-recovery .form{border-radius:26px;border-color:#bfdbfe;box-shadow:0 28px 80px rgba(37,99,235,.12)}.template-recovery .badge{background:#eff6ff;color:#1d4ed8}.template-recovery .button{background:#1d4ed8}
        @media(max-width:860px){.template-restaurant .wrap,.template-clinic .wrap,.template-neighborhood .wrap,.template-service .wrap,.template-warm .wrap,.template-premium .wrap,.template-recovery .wrap,.template-happy .hero{grid-template-columns:1fr}.template-restaurant .form,.template-clinic .form,.template-neighborhood .form,.template-service .form,.template-happy .form,.template-warm .form,.template-premium .form,.template-recovery .form{order:-1}.template-service .hero{position:static}.template-happy .cover{height:130px}}
    </style>
</head>
<body>
    <main class="page template-{{ $variant }}">
        <section class="wrap">
            <article class="hero card">
                @if(in_array($variant, ['clean', 'friendly', 'luxury', 'restaurant', 'neighborhood', 'service', 'happy', 'warm', 'premium'], true))
                    <div class="cover" @if(filled($design['cover_image'])) style="background-image:url('{{ $design['cover_image'] }}')" @else style="background:linear-gradient(135deg,var(--primary),var(--accent));" @endif></div>
                @endif
                <div class="hero-body">
                    <div class="business">
                        @if($design['show_logo'])
                            <div class="logo {{ filled($design['logo_url']) ? 'has-image' : '' }} is-{{ $design['logo_shape'] ?? 'circle' }}" @if(filled($design['logo_url'])) style="background-image:url('{{ $design['logo_url'] }}')" @endif>
                                @unless(filled($design['logo_url'])){{ str($business?->name ?: 'LB')->substr(0, 2)->upper() }}@endunless
                            </div>
                        @endif
                        <div>
                            <div class="business-name">{{ $business?->name ?: __('Local business') }}</div>
                            <span class="pill">{{ __('Review') }}</span>
                        </div>
                    </div>
                    <h1>{{ $headline }}</h1>
                    <p>{{ $subheadline }}</p>
                    @if($design['show_benefits'] && $benefits)
                        <ul class="benefits">
                            @foreach($benefits as $benefit)
                                <li><span>&#10003;</span>{{ $benefit }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </article>

            <section class="form card" x-data="{ rating: '' }">
                @if(session('landing_page_converted'))
                    <div class="note" style="background:#ecfdf5;color:#047857;border-radius:16px;padding:14px;font-weight:850;">{{ data_get($content, 'thank_you_message', __('Thank you.')) }}</div>
                @endif
                <div class="form-head">
                    <h2>{{ __('Rate your visit') }}</h2>
                    <span class="badge" x-text="rating === '' ? @js(__('Review flow')) : (Number(rating) >= 4 ? @js(__('Public review')) : @js(__('Internal feedback')))">{{ __('Review flow') }}</span>
                </div>
                <p class="note">{{ __('Your rating helps improve local service and helps other customers choose confidently.') }}</p>
                <form method="post" action="{{ $submitRoute }}">
                    @csrf
                    @if($errors->any())
                        <div class="note" style="background:#fef2f2;color:#b91c1c;border-radius:16px;padding:14px;font-weight:850;">{{ $errors->first() }}</div>
                    @endif
                    <div class="field">
                        <label>{{ __('Choose your rating') }}</label>
                        <div class="ratings">
                            @for($i = 1; $i <= 5; $i++)
                                <label>
                                    <input type="radio" name="rating" value="{{ $i }}" required x-model="rating">
                                    <span class="stars">@for($star = 1; $star <= $i; $star++)&#9733;@endfor</span>
                                    <span class="rating-copy">{{ $ratingLabels[$i] }}</span>
                                </label>
                            @endfor
                        </div>
                    </div>
                    <div class="field"><label>{{ __('What went well or what can we improve?') }}</label><textarea name="feedback" placeholder="{{ __('Your feedback helps improve local service.') }}"></textarea></div>
                    <div class="field"><label>{{ __('Name') }}</label><input name="name"></div>
                    <div class="field"><label>{{ __('Email') }}</label><input type="email" name="email"></div>
                    <button class="button" type="submit">{{ $cta }}</button>
                </form>
            </section>
        </section>
    </main>
    @livewireScripts
</body>
</html>
