@php
    $content = $landingPage->content ?: [];
    $settings = $landingPage->settings ?: [];
    $design = array_merge(['primary_color'=>'#0f766e','background_color'=>'#f4fbf8','accent_color'=>'#ccfbf1','logo_url'=>'','cover_image'=>'','show_logo'=>true,'show_benefits'=>true], (array) data_get($settings, 'design', []));
    $business = $landingPage->business;
    $primary = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $design['primary_color']) ? $design['primary_color'] : '#0f766e';
    $background = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $design['background_color']) ? $design['background_color'] : '#f4fbf8';
    $accent = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $design['accent_color']) ? $design['accent_color'] : '#ccfbf1';
    $benefits = (array) data_get($content, 'benefits', []);
    $submitRoute = route('landing-pages.submit', ['landingPage' => $landingPage->slug]);
    $variant = $variant ?? 'consultation';
    $label = $label ?? ($landingPage->type === 'custom' ? __('Campaign') : __('Lead'));
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $landingPage->title }}</title>{!! theme_vite('app', ['assets/js/app.js']) !!}
    <style>
        :root{--primary:{{ $primary }};--bg:{{ $background }};--accent:{{ $accent }}}*{box-sizing:border-box}body{margin:0;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:var(--bg);color:#111827}.page{min-height:100vh;padding:26px}.wrap{width:min(1120px,100%);margin:auto;display:grid;gap:22px}.card{border:1px solid rgba(148,163,184,.24);background:#fff;box-shadow:0 28px 80px rgba(15,23,42,.12)}.hero{overflow:hidden}.cover{height:180px;background:linear-gradient(135deg,var(--primary),var(--accent));background-size:cover;background-position:center}.body{padding:28px}.brand{display:flex;align-items:center;gap:14px}.logo{display:grid;width:54px;height:54px;place-items:center;border-radius:18px;background:color-mix(in srgb,var(--primary) 12%,white);color:var(--primary);font-weight:950;overflow:hidden}.logo.has-image{background-size:cover;background-position:center;border:3px solid #fff;box-shadow:0 14px 30px rgba(15,23,42,.14)}.name{font-size:13px;font-weight:850;color:#64748b}.pill{display:inline-flex;border-radius:999px;background:color-mix(in srgb,var(--primary) 10%,white);color:var(--primary);padding:5px 10px;font-size:11px;font-weight:950;text-transform:uppercase;letter-spacing:.08em}h1{margin:26px 0 0;font-size:clamp(36px,5vw,64px);line-height:.98;letter-spacing:-.055em}.lead{font-size:17px;line-height:1.65;color:#475569}.benefits{display:grid;gap:10px;margin:24px 0 0;padding:0;list-style:none}.benefits li{display:flex;gap:10px;align-items:center;color:#334155;font-weight:850}.benefits span{display:grid;width:24px;height:24px;place-items:center;border-radius:999px;background:color-mix(in srgb,var(--primary) 12%,white);color:var(--primary)}.form{padding:28px;border-radius:28px}.form h2{margin:0;font-size:25px;letter-spacing:-.035em}.note{font-size:14px;color:#475569;line-height:1.6}.field{margin-bottom:14px}.field label{display:block;margin-bottom:7px;color:#334155;font-size:13px;font-weight:850}.field input,.field textarea{width:100%;min-height:52px;border:1px solid #dbe1ea;border-radius:16px;padding:14px 15px;font:inherit}.field textarea{min-height:116px}.button{width:100%;min-height:56px;border:0;border-radius:999px;background:linear-gradient(135deg,var(--primary),color-mix(in srgb,var(--primary) 78%,#111827));color:#fff;font-size:16px;font-weight:950;box-shadow:0 18px 38px color-mix(in srgb,var(--primary) 20%,transparent)}.template-consultation{background:linear-gradient(135deg,color-mix(in srgb,var(--primary) 12%,transparent),transparent 34%),var(--bg)}.template-consultation .wrap{grid-template-columns:1.05fr .95fr}.template-consultation .hero,.template-consultation .form{border-radius:30px}.template-quote{background:#f8fafc}.template-quote .wrap{grid-template-columns:320px 1fr}.template-quote .hero{position:sticky;top:24px;border-radius:22px}.template-quote .cover{height:120px}.template-quote .form{border-radius:22px}.template-customer{background:#f0fdf4}.template-customer .wrap{grid-template-columns:.9fr 1.1fr}.template-customer .hero{border:2px dashed color-mix(in srgb,var(--primary) 28%,#bbf7d0);border-radius:28px}.template-customer .form{border-radius:28px}.template-event{background:#111827}.template-event .wrap{grid-template-columns:1fr 1fr}.template-event .hero{border-radius:34px;background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.14)}.template-event h1,.template-event .name{color:#fff}.template-event .lead,.template-event .benefits li{color:#cbd5e1}.template-event .form{border-radius:34px}.template-service{background:#ecfeff}.template-service .wrap{grid-template-columns:1fr 1fr}.template-service .hero,.template-service .form{border-radius:18px}.template-agency{background:#fff}.template-agency .wrap{width:min(880px,100%);display:block}.template-agency .hero{border:0;box-shadow:none;background:transparent}.template-agency .cover,.template-agency .benefits{display:none}.template-agency .form{margin-top:18px;border-radius:18px;box-shadow:none}@media(max-width:860px){.wrap,.template-consultation .wrap,.template-quote .wrap,.template-customer .wrap,.template-event .wrap,.template-service .wrap{grid-template-columns:1fr}.form{order:-1}.template-quote .hero{position:static}.button{position:sticky;bottom:10px;z-index:5}}
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
                            @unless(filled($design['logo_url']))
                                {{ str($business?->name ?: 'LB')->substr(0, 2)->upper() }}
                            @endunless
                        </div>
                    @endif
                    <div>
                        <div class="name">{{ $business?->name ?: __('Local business') }}</div>
                        <span class="pill">{{ $label }}</span>
                    </div>
                </div>
                <h1>{{ data_get($content, 'headline', $landingPage->title) }}</h1>
                <p class="lead">{{ data_get($content, 'subheadline') ?: __('Tell us what you need and our local team will follow up.') }}</p>
                @if($design['show_benefits'] && $benefits)
                    <ul class="benefits">
                        @foreach($benefits as $benefit)
                            <li><span>&#10003;</span>{{ $benefit }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </article>

        <section class="form card">
            @if(session('landing_page_converted'))
                <p class="note" style="background:#ecfdf5;color:#047857;border-radius:16px;padding:14px;font-weight:850;">{{ data_get($content, 'thank_you_message', __('Thank you.')) }}</p>
            @endif
            <h2>{{ __('Send your details') }}</h2>
            <p class="note">{{ __('We will follow up with the next best step.') }}</p>
            <form method="post" action="{{ $submitRoute }}">
                @csrf
                @if($errors->any())
                    <p class="note" style="color:#b91c1c;">{{ $errors->first() }}</p>
                @endif
                <div class="field"><label>{{ __('Name') }}</label><input name="name" required></div>
                <div class="field"><label>{{ __('Email') }}</label><input type="email" name="email"></div>
                <div class="field"><label>{{ __('Phone') }}</label><input name="phone"></div>
                <div class="field"><label>{{ __('Interested service') }}</label><input name="interested_service"></div>
                <div class="field"><label>{{ __('Message') }}</label><textarea name="message"></textarea></div>
                <button class="button" type="submit">{{ data_get($content, 'cta', __('Send request')) }}</button>
            </form>
        </section>
    </section>
</main>
@livewireScripts
</body>
</html>
