@php
    $content = $landingPage->content ?: [];
    $settings = $landingPage->settings ?: [];
    $design = array_merge([
        'primary_color' => '#4f46e5',
        'background_color' => '#eef2ff',
        'accent_color' => '#c7d2fe',
        'logo_url' => '',
        'cover_image' => '',
        'show_logo' => true,
        'show_benefits' => true,
    ], (array) data_get($settings, 'design', []));
    $business = $landingPage->business;
    $primary = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $design['primary_color']) ? $design['primary_color'] : '#4f46e5';
    $background = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $design['background_color']) ? $design['background_color'] : '#eef2ff';
    $accent = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $design['accent_color']) ? $design['accent_color'] : '#c7d2fe';
    $benefits = (array) data_get($content, 'benefits', []);
    $submitRoute = route('landing-pages.submit', ['landingPage' => $landingPage->slug]);
    $variant = $variant ?? 'private';
    $headline = data_get($content, 'headline', $landingPage->title);
    $subheadline = data_get($content, 'subheadline') ?: __('Tell us what went well or what we can improve. Your response stays with the local team.');
    $cta = data_get($content, 'cta') ?: __('Send feedback');
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $landingPage->title }}</title>
    {!! theme_vite('app', ['assets/js/app.js']) !!}
    <style>
        :root{--primary:{{ $primary }};--bg:{{ $background }};--accent:{{ $accent }}}*{box-sizing:border-box}body{margin:0;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:var(--bg);color:#111827}.page{min-height:100vh;padding:26px}.wrap{width:min(1120px,100%);margin:0 auto;display:grid;gap:22px}.card{border:1px solid rgba(148,163,184,.24);background:#fff;box-shadow:0 28px 80px rgba(15,23,42,.12)}.hero{overflow:hidden}.cover{height:180px;background:linear-gradient(135deg,var(--primary),var(--accent));background-size:cover;background-position:center}.body{padding:28px}.brand{display:flex;align-items:center;gap:14px}.logo{display:grid;width:54px;height:54px;place-items:center;overflow:hidden;border-radius:18px;background:color-mix(in srgb,var(--primary) 12%,white);color:var(--primary);font-weight:950}.logo.has-image{background-size:cover;background-position:center;border:3px solid #fff;box-shadow:0 14px 30px rgba(15,23,42,.14)}.name{font-size:13px;font-weight:850;color:#64748b}.pill{display:inline-flex;border-radius:999px;background:color-mix(in srgb,var(--primary) 10%,white);color:var(--primary);padding:5px 10px;font-size:11px;font-weight:950;text-transform:uppercase;letter-spacing:.08em}h1{margin:26px 0 0;font-size:clamp(34px,4.4vw,58px);line-height:1;letter-spacing:-.05em}.lead{font-size:16px;line-height:1.65;color:#475569}.benefits{display:grid;gap:10px;margin:24px 0 0;padding:0;list-style:none}.benefits li{display:flex;gap:10px;align-items:center;color:#334155;font-weight:850}.benefits span{display:grid;width:24px;height:24px;place-items:center;border-radius:999px;background:color-mix(in srgb,var(--primary) 12%,white);color:var(--primary)}.form{padding:28px;border-radius:28px}.form h2{margin:0;font-size:25px;letter-spacing:-.035em}.note{font-size:14px;color:#475569;line-height:1.6}.field{margin-bottom:16px}.field label{display:block;margin-bottom:8px;color:#334155;font-size:13px;font-weight:850}.field input,.field textarea,.field select{width:100%;min-height:52px;border:1px solid #dbe1ea;border-radius:16px;padding:14px 15px;font:inherit;background:#fff}.field textarea{min-height:128px}.button{width:100%;min-height:56px;border:0;border-radius:999px;background:linear-gradient(135deg,var(--primary),color-mix(in srgb,var(--primary) 78%,#111827));color:#fff;font-size:16px;font-weight:950;box-shadow:0 18px 38px color-mix(in srgb,var(--primary) 20%,transparent)}.rating-select{appearance:none}.template-private{background:linear-gradient(135deg,color-mix(in srgb,var(--primary) 12%,transparent),transparent 36%),var(--bg)}.template-private .wrap{grid-template-columns:.9fr 1.1fr}.template-private .hero,.template-private .form{border-radius:30px}.template-recovery{background:#f8fafc}.template-recovery .wrap{grid-template-columns:1fr 1fr}.template-recovery .hero{border-radius:26px;padding:30px;background:#fff;box-shadow:none}.template-recovery .cover{display:none}.template-recovery .form{border-radius:26px;border-color:#bfdbfe;box-shadow:0 28px 80px rgba(37,99,235,.12)}.template-recovery .button{background:#1d4ed8}.template-post{background:#111827}.template-post .wrap{grid-template-columns:320px 1fr}.template-post .hero{position:sticky;top:24px;border-radius:24px;background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.14)}.template-post h1,.template-post .name{color:#fff}.template-post .lead,.template-post .benefits li{color:#cbd5e1}.template-post .form{border-radius:24px}.template-quality{background:#ecfeff}.template-quality .wrap{grid-template-columns:1.08fr .92fr}.template-quality .hero{border-radius:18px}.template-quality .form{border-radius:18px;box-shadow:0 20px 60px rgba(8,145,178,.12)}.template-quality .button{background:#0891b2}.template-quick{background:#fff}.template-quick .wrap{width:min(760px,100%);display:block}.template-quick .hero{text-align:center;border:0;background:transparent;box-shadow:none}.template-quick .cover,.template-quick .benefits{display:none}.template-quick .brand{justify-content:center}.template-quick .form{margin-top:16px;border-radius:20px;box-shadow:none}.template-satisfaction{background:linear-gradient(135deg,#fef9c3,#dcfce7)}.template-satisfaction .wrap{display:grid;gap:18px}.template-satisfaction .hero{display:grid;grid-template-columns:1fr 220px;gap:22px;align-items:center;border-radius:32px;padding:24px;background:#fff}.template-satisfaction .cover{order:2;height:220px;border-radius:26px}.template-satisfaction .form{width:min(760px,100%);margin:auto;border-radius:32px}.template-minute{background:#f8fafc}.template-minute .wrap{grid-template-columns:.75fr 1.25fr}.template-minute .hero{border-radius:22px;padding:26px;background:#fff}.template-minute .cover{display:none}.template-minute .form{border-radius:22px}.template-experience{background:#fdf2f8}.template-experience .wrap{grid-template-columns:1fr 1fr}.template-experience .hero,.template-experience .form{border-radius:34px}.template-experience .button{background:#be185d}.template-manager{background:#f5f3ff}.template-manager .wrap{grid-template-columns:300px minmax(0,1fr)}.template-manager .hero{border-radius:22px;padding:24px;background:#fff}.template-manager .cover{height:120px;border-radius:16px;margin-bottom:18px}.template-manager .form{border-radius:22px}.template-anonymous{background:#020617;color:#fff}.template-anonymous .wrap{width:min(900px,100%);display:block}.template-anonymous .hero{text-align:center;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.04);border-radius:30px;box-shadow:none}.template-anonymous h1,.template-anonymous .name{color:#fff}.template-anonymous .lead{color:#cbd5e1}.template-anonymous .cover{display:none}.template-anonymous .form{margin-top:18px;border-radius:30px}.template-nps{background:#eff6ff}.template-nps .wrap{grid-template-columns:1fr 1fr}.template-nps .hero{border-radius:28px;background:#fff}.template-nps .form{border-radius:28px}.template-nps .button{background:#2563eb}@media(max-width:860px){.wrap,.template-private .wrap,.template-recovery .wrap,.template-post .wrap,.template-quality .wrap,.template-minute .wrap,.template-experience .wrap,.template-manager .wrap,.template-nps .wrap,.template-satisfaction .hero{grid-template-columns:1fr}.form{order:-1}.template-post .hero{position:static}.template-satisfaction .cover{height:130px}.button{position:sticky;bottom:10px;z-index:5}}
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
                        <div><div class="name">{{ $business?->name ?: __('Local business') }}</div><span class="pill">{{ __('Feedback') }}</span></div>
                    </div>
                    <h1>{{ $headline }}</h1>
                    <p class="lead">{{ $subheadline }}</p>
                    @if($design['show_benefits'] && $benefits)
                        <ul class="benefits">@foreach($benefits as $benefit)<li><span>&#10003;</span>{{ $benefit }}</li>@endforeach</ul>
                    @endif
                </div>
            </article>
            <section class="form card">
                @if(session('landing_page_converted'))<p class="note" style="background:#ecfdf5;color:#047857;border-radius:16px;padding:14px;font-weight:850;">{{ data_get($content, 'thank_you_message', __('Thank you.')) }}</p>@endif
                <h2>{{ __('Share private feedback') }}</h2>
                <p class="note">{{ __('Tell us what went well or what we can improve. Your response stays with the local team.') }}</p>
                <form method="post" action="{{ $submitRoute }}">
                    @csrf
                    @if($errors->any())<p class="note" style="color:#b91c1c;">{{ $errors->first() }}</p>@endif
                    <div class="field"><label>{{ __('Rating') }}</label><select class="rating-select" name="rating"><option value="">{{ __('Choose rating') }}</option>@for($i=1;$i<=5;$i++)<option value="{{ $i }}">{{ $i }}</option>@endfor</select></div>
                    <div class="field"><label>{{ __('Topic') }}</label><input name="topic"></div>
                    <div class="field"><label>{{ __('Feedback') }}</label><textarea name="feedback" required></textarea></div>
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
