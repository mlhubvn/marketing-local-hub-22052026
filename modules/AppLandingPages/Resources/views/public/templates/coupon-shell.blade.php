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
        'show_terms' => true,
    ], (array) data_get($settings, 'design', []));
    $business = $landingPage->business;
    $primary = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $design['primary_color']) ? $design['primary_color'] : '#0f766e';
    $background = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $design['background_color']) ? $design['background_color'] : '#f4fbf8';
    $accent = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $design['accent_color']) ? $design['accent_color'] : '#ccfbf1';
    $fontFamily = 'Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
    $benefits = (array) data_get($content, 'benefits', []);
    $discount = trim((string) data_get($settings, 'discount', __('20% off')));
    $code = trim((string) data_get($settings, 'coupon_title', __('LOCAL20')));
    $expiry = trim((string) data_get($settings, 'expiry', ''));
    $terms = trim((string) data_get($settings, 'terms', ''));
    $submitRoute = route('landing-pages.submit', ['landingPage' => $landingPage->slug]);
    $variant = $variant ?? 'classic';
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
        *{box-sizing:border-box}
        body{margin:0;font-family:var(--font);background:var(--bg);color:#111827}
        .page{min-height:100vh;padding:24px;background:radial-gradient(circle at 15% 0%,color-mix(in srgb,var(--primary) 14%,transparent),transparent 30%),linear-gradient(135deg,var(--bg),color-mix(in srgb,var(--accent) 18%,#fff))}
        .wrap{width:min(1180px,100%);margin:0 auto;display:grid;grid-template-columns:minmax(0,1.02fr) minmax(360px,.78fr);gap:24px;align-items:start}
        .card{border:1px solid rgba(148,163,184,.22);background:rgba(255,255,255,.94);box-shadow:0 22px 70px rgba(15,23,42,.11)}
        .hero{overflow:hidden;border-radius:28px}
        .cover{height:150px;background:linear-gradient(135deg,var(--primary),var(--accent));background-size:cover;background-position:center}
        .body{padding:26px}
        .brand{display:flex;align-items:center;gap:14px}
        .logo{display:grid;width:52px;height:52px;place-items:center;overflow:hidden;border-radius:18px;background:color-mix(in srgb,var(--primary) 12%,white);color:var(--primary);font-weight:950}
        .logo.has-image{background-size:cover;background-position:center;border:3px solid #fff;box-shadow:0 14px 30px rgba(15,23,42,.16)}
        .name{font-size:13px;font-weight:850;color:#64748b}
        .pill{display:inline-flex;margin-top:4px;border-radius:999px;background:color-mix(in srgb,var(--primary) 10%,white);color:var(--primary);padding:5px 10px;font-size:10px;font-weight:950;text-transform:uppercase;letter-spacing:.08em}
        h1{max-width:650px;margin:24px 0 0;font-size:clamp(34px,4.2vw,58px);line-height:1;letter-spacing:-.055em}
        .lead{max-width:620px;margin:12px 0 0;font-size:16px;line-height:1.65;color:#475569}
        .benefits{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin:24px 0 0;padding:0;list-style:none}
        .benefits li{display:flex;min-height:44px;align-items:center;gap:9px;border:1px solid rgba(148,163,184,.18);border-radius:14px;background:#fff;padding:10px 12px;color:#334155;font-size:14px;font-weight:850}
        .benefits span{display:grid;width:22px;height:22px;flex:0 0 auto;place-items:center;border-radius:999px;background:color-mix(in srgb,var(--primary) 12%,white);color:var(--primary)}
        .claim{position:sticky;top:22px;border-radius:28px;padding:26px}
        .claim-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px}
        .claim h2{margin:0;font-size:24px;letter-spacing:-.035em}
        .claim-copy{margin:6px 0 0;color:#64748b;font-size:14px;line-height:1.5}
        .offer{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:20px 0}
        .offer div{border:1px solid color-mix(in srgb,var(--primary) 24%,#dbe1ea);border-radius:18px;padding:16px;background:linear-gradient(145deg,color-mix(in srgb,var(--accent) 28%,white),#fff)}
        .offer span{display:block;color:#64748b;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.06em}
        .offer strong{display:block;margin-top:5px;font-size:22px;letter-spacing:-.025em}
        .terms{border-top:1px solid #e5e7eb;padding-top:14px;color:#475569;font-size:13px;line-height:1.55}
        .field{margin-bottom:13px}
        .field label{display:block;margin-bottom:7px;color:#334155;font-size:13px;font-weight:850}
        .field input,.field textarea{width:100%;min-height:50px;border:1px solid #dbe1ea;border-radius:14px;background:#fff;padding:13px 14px;font:inherit;outline:none;transition:border-color .16s,box-shadow .16s}
        .field input:focus,.field textarea:focus{border-color:color-mix(in srgb,var(--primary) 56%,#dbe1ea);box-shadow:0 0 0 4px color-mix(in srgb,var(--primary) 12%,transparent)}
        .field textarea{min-height:98px;resize:vertical}
        .button{width:100%;min-height:56px;border:0;border-radius:16px;background:linear-gradient(135deg,var(--primary),color-mix(in srgb,var(--primary) 78%,#111827));color:#fff;font-size:16px;font-weight:950;box-shadow:0 16px 34px color-mix(in srgb,var(--primary) 22%,transparent)}
        .template-weekend{--bg:#111827;background:#111827;color:#fff}
        .template-weekend .page{background:radial-gradient(circle at 20% 0%,color-mix(in srgb,var(--primary) 38%,transparent),transparent 30%),#111827}
        .template-weekend .hero{background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.12)}
        .template-weekend .body,.template-weekend .name,.template-weekend .lead{color:#e5e7eb}
        .template-comeback .hero{border:2px dashed color-mix(in srgb,var(--primary) 30%,#bbf7d0)}
        .template-birthday .page{background:radial-gradient(circle at 20% 0%,#fbcfe8,transparent 30%),linear-gradient(135deg,#fff7ed,#fff1f2)}
        .template-birthday .cover{height:130px;border-radius:0}
        .template-restaurant .hero,.template-restaurant .claim,.template-retail .hero,.template-retail .claim{border-radius:18px}
        .template-restaurant .button{background:linear-gradient(135deg,#c2410c,#7c2d12)}
        .template-retail .wrap{grid-template-columns:minmax(330px,.75fr) minmax(0,1fr)}
        .template-retail .claim{position:relative;top:auto}
        @media(max-width:960px){.page{padding:14px}.wrap,.template-retail .wrap{grid-template-columns:1fr}.claim{position:relative;top:auto;order:-1}.benefits{grid-template-columns:1fr}.offer{grid-template-columns:1fr}.button{position:sticky;bottom:10px;z-index:5}.cover{height:126px}h1{font-size:38px}}
    </style>
</head>
<body>
    <main class="page template-{{ $variant }}">
        <section class="wrap">
            <article class="hero card">
                <div class="cover" @if(filled($design['cover_image'])) style="background-image:url('{{ $design['cover_image'] }}')" @endif></div>
                <div class="body">
                    <div class="brand">
                        @if($design['show_logo'])
                            <div class="logo {{ filled($design['logo_url']) ? 'has-image' : '' }}" @if(filled($design['logo_url'])) style="background-image:url('{{ $design['logo_url'] }}')" @endif>
                                @unless(filled($design['logo_url'])){{ str($business?->name ?: 'LB')->substr(0, 2)->upper() }}@endunless
                            </div>
                        @endif
                        <div><div class="name">{{ $business?->name ?: __('Local business') }}</div><span class="pill">{{ __('Coupon') }}</span></div>
                    </div>
                    <h1>{{ data_get($content, 'headline', $landingPage->title) }}</h1>
                    <p class="lead">{{ data_get($content, 'subheadline') ?: __('Claim this limited-time offer and show your code in-store.') }}</p>
                    @if($design['show_benefits'] && $benefits)
                        <ul class="benefits">
                            @foreach($benefits as $benefit)
                                <li><span>&#10003;</span>{{ $benefit }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </article>
            <section class="claim card">
                @if(session('landing_page_converted'))
                    <div class="terms" style="border:0;background:#ecfdf5;color:#047857;border-radius:16px;padding:14px;margin-bottom:16px;font-weight:850;">{{ data_get($content, 'thank_you_message', __('Thank you.')) }}</div>
                @endif
                <div class="claim-head">
                    <div>
                        <h2>{{ __('Claim your offer') }}</h2>
                        <p class="claim-copy">{{ __('Enter your details and show this offer when you visit.') }}</p>
                    </div>
                    <span class="pill">{{ __('Offer') }}</span>
                </div>
                <div class="offer">
                    <div><span>{{ __('Discount') }}</span><strong>{{ $discount }}</strong>{{ $code }}</div>
                    @if($expiry !== '')
                        <div><span>{{ __('Expires') }}</span><strong>{{ __('Valid until') }}</strong>{{ $expiry }}</div>
                    @endif
                </div>
                @if($terms !== '' && $design['show_terms'])<p class="terms">{{ $terms }}</p>@endif
                <form method="post" action="{{ $submitRoute }}">
                    @csrf
                    @if($errors->any())<p class="terms" style="color:#b91c1c;">{{ $errors->first() }}</p>@endif
                    <div class="field"><label>{{ __('Name') }}</label><input name="name" required></div>
                    <div class="field"><label>{{ __('Email') }}</label><input type="email" name="email"></div>
                    <div class="field"><label>{{ __('Phone') }}</label><input name="phone"></div>
                    <div class="field"><label>{{ __('Interested service') }}</label><input name="interested_service"></div>
                    <div class="field"><label>{{ __('Message') }}</label><textarea name="message"></textarea></div>
                    <button class="button" type="submit">{{ data_get($content, 'cta', __('Claim coupon')) }}</button>
                </form>
            </section>
        </section>
    </main>
    @livewireScripts
</body>
</html>
