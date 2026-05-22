@php
    $content = $landingPage->content ?: [];
    $settings = $landingPage->settings ?: [];
    $design = array_merge(['primary_color'=>'#0f766e','background_color'=>'#f4fbf8','accent_color'=>'#ccfbf1','logo_url'=>'','cover_image'=>'','show_logo'=>true,'show_benefits'=>true], (array) data_get($settings, 'design', []));
    $business = $landingPage->business;
    $primary = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $design['primary_color']) ? $design['primary_color'] : '#0f766e';
    $background = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $design['background_color']) ? $design['background_color'] : '#f4fbf8';
    $accent = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $design['accent_color']) ? $design['accent_color'] : '#ccfbf1';
    $benefits = (array) data_get($content, 'benefits', []);
    $bookingServices = collect($bookingServices ?? []);
    $slots = (array) data_get($settings, 'available_slots', ['09:00','10:00','14:00','15:00']);
    $initialBookingServiceId = (string) old('service_id', $bookingServices->count() === 1 ? $bookingServices->first()?->id : '');
    $submitRoute = route('landing-pages.submit', ['landingPage' => $landingPage->slug]);
    $variant = $variant ?? 'spa';
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $landingPage->title }}</title>{!! theme_vite('app', ['assets/js/app.js']) !!}
<style>
:root{--primary:{{ $primary }};--bg:{{ $background }};--accent:{{ $accent }}}*{box-sizing:border-box}body{margin:0;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:var(--bg);color:#111827}.page{min-height:100vh;padding:26px}.wrap{width:min(1120px,100%);margin:auto;display:grid;gap:22px}.card{border:1px solid rgba(148,163,184,.24);background:#fff;box-shadow:0 28px 80px rgba(15,23,42,.12)}.hero{overflow:hidden}.cover{height:190px;background:linear-gradient(135deg,var(--primary),var(--accent));background-size:cover;background-position:center}.body{padding:28px}.brand{display:flex;align-items:center;gap:14px}.logo{display:grid;width:54px;height:54px;place-items:center;border-radius:18px;background:color-mix(in srgb,var(--primary) 12%,white);color:var(--primary);font-weight:950;overflow:hidden}.logo.has-image{background-size:cover;background-position:center;border:3px solid #fff;box-shadow:0 14px 30px rgba(15,23,42,.14)}.name{font-size:13px;font-weight:850;color:#64748b}.pill{display:inline-flex;border-radius:999px;background:color-mix(in srgb,var(--primary) 10%,white);color:var(--primary);padding:5px 10px;font-size:11px;font-weight:950;text-transform:uppercase;letter-spacing:.08em}h1{margin:26px 0 0;font-size:clamp(36px,5vw,62px);line-height:.98;letter-spacing:-.055em}.lead{font-size:17px;line-height:1.65;color:#475569}.benefits{display:grid;gap:10px;margin:24px 0 0;padding:0;list-style:none}.benefits li{display:flex;gap:10px;align-items:center;color:#334155;font-weight:850}.benefits span{display:grid;width:24px;height:24px;place-items:center;border-radius:999px;background:color-mix(in srgb,var(--primary) 12%,white);color:var(--primary)}.form{padding:28px;border-radius:28px}.form h2{margin:0;font-size:25px;letter-spacing:-.035em}.note{font-size:14px;color:#475569;line-height:1.6}.field{margin-bottom:14px}.field label{display:block;margin-bottom:7px;color:#334155;font-size:13px;font-weight:850}.field input,.field select{width:100%;min-height:52px;border:1px solid #dbe1ea;border-radius:16px;padding:14px 15px;font:inherit}.slot-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.slot-option input{position:absolute;opacity:0}.slot-option span{display:flex;min-height:48px;align-items:center;justify-content:center;border:1px solid #dbe1ea;border-radius:14px;background:#fff;font-weight:850}.slot-option input:checked+span{border-color:var(--primary);background:color-mix(in srgb,var(--primary) 12%,white);color:var(--primary)}.button{width:100%;min-height:56px;border:0;border-radius:999px;background:linear-gradient(135deg,var(--primary),color-mix(in srgb,var(--primary) 78%,#111827));color:#fff;font-size:16px;font-weight:950;box-shadow:0 18px 38px color-mix(in srgb,var(--primary) 20%,transparent)}.template-spa{background:linear-gradient(135deg,color-mix(in srgb,var(--accent) 38%,white),#fff)}.template-spa .wrap{grid-template-columns:1fr 1fr}.template-spa .hero,.template-spa .form{border-radius:34px}.template-salon{background:#fdf2f8}.template-salon .wrap{grid-template-columns:.9fr 1.1fr}.template-salon .hero,.template-salon .form{border-radius:28px}.template-clinic{background:#f0f9ff}.template-clinic .wrap{grid-template-columns:1.08fr .92fr}.template-clinic .hero{border-radius:18px}.template-clinic .form{border-radius:18px;box-shadow:0 20px 60px rgba(8,145,178,.12)}.template-gym{background:#111827}.template-gym .wrap{grid-template-columns:320px 1fr}.template-gym .hero{position:sticky;top:24px;border-radius:24px;background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.14)}.template-gym h1,.template-gym .name{color:#fff}.template-gym .lead,.template-gym .benefits li{color:#cbd5e1}.template-restaurant{background:#fff7ed}.template-restaurant .wrap{grid-template-columns:1fr 1fr}.template-restaurant h1{font-family:Georgia,serif}.template-restaurant .hero,.template-restaurant .form{border-radius:18px}.template-minimal{background:#fff}.template-minimal .wrap{width:min(820px,100%);display:block}.template-minimal .hero{border:0;box-shadow:none;background:transparent}.template-minimal .cover,.template-minimal .benefits{display:none}.template-minimal .form{margin-top:18px;border-radius:18px;box-shadow:none}@media(max-width:860px){.wrap,.template-spa .wrap,.template-salon .wrap,.template-clinic .wrap,.template-gym .wrap,.template-restaurant .wrap{grid-template-columns:1fr}.form{order:-1}.template-gym .hero{position:static}.button{position:sticky;bottom:10px;z-index:5}}
</style></head>
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
                        <span class="pill">{{ __('Booking') }}</span>
                    </div>
                </div>
                <h1>{{ data_get($content, 'headline', $landingPage->title) }}</h1>
                <p class="lead">{{ data_get($content, 'subheadline') ?: __('Choose a service, pick a time, and send your booking request.') }}</p>
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
            <h2>{{ __('Request booking') }}</h2>
            <p class="note">{{ __('Choose a date and time. The team will confirm availability.') }}</p>
            <form method="post" action="{{ $submitRoute }}">
                @csrf
                @if($errors->any())
                    <p class="note" style="color:#b91c1c;">{{ $errors->first() }}</p>
                @endif
                @if($bookingServices->isNotEmpty())
                    <div class="field">
                        <label>{{ __('Service') }}</label>
                        <select name="service_id" required>
                            <option value="">{{ __('Choose a service') }}</option>
                            @foreach($bookingServices as $service)
                                <option value="{{ $service->id }}" @selected((string)$service->id === $initialBookingServiceId)>{{ $service->name }} - {{ $service->duration_minutes }} {{ __('min') }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <input type="hidden" name="service" value="{{ data_get($settings, 'service', $landingPage->title) }}">
                @endif
                <div class="field"><label>{{ __('Date') }}</label><input type="date" name="date" value="{{ old('date') }}" required></div>
                <div class="field">
                    <label>{{ __('Available time') }}</label>
                    <div class="slot-grid">
                        @foreach($slots ?: ['09:00','10:00','14:00','15:00'] as $slot)
                            <label class="slot-option"><input type="radio" name="time" value="{{ $slot }}" required @checked($loop->first)><span>{{ $slot }}</span></label>
                        @endforeach
                    </div>
                </div>
                <div class="field"><label>{{ __('Name *') }}</label><input name="name" required></div>
                <div class="field"><label>{{ __('Phone *') }}</label><input name="phone" required minlength="6" maxlength="30"></div>
                <div class="field"><label>{{ __('Email') }}</label><input type="email" name="email"></div>
                <button class="button" type="submit">{{ data_get($content, 'cta', __('Request booking')) }}</button>
            </form>
        </section>
    </section>
</main>
@livewireScripts
</body>
</html>
