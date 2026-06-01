@php
    use Modules\AppLandingPages\Support\PageTemplateCatalog;

    $settings = (array) ($card->settings ?: []);
    $template = (string) data_get($settings, 'landing_template', PageTemplateCatalog::defaultForType('loyalty'));
    if (! array_key_exists($template, PageTemplateCatalog::forType('loyalty'))) {
        $template = PageTemplateCatalog::defaultForType('loyalty');
    }

    $design = array_merge(PageTemplateCatalog::designFor($template), (array) data_get($settings, 'design', []));
    $primaryColor = (string) data_get($design, 'primary_color', '#0f766e');
    $backgroundColor = (string) data_get($design, 'background_color', '#f4fbf8');
    $accentColor = (string) data_get($design, 'accent_color', '#ccfbf1');
    $backgroundType = (string) data_get($design, 'background_type', 'gradient');
    $buttonStyle = (string) data_get($design, 'button_style', 'pill');
    $cardStyle = (string) data_get($design, 'card_style', 'soft');
    $fontStyle = (string) data_get($design, 'font_style', 'modern');
    $logoUrl = (string) data_get($design, 'logo_url', '');
    $coverImage = (string) data_get($design, 'cover_image', '');

    $buttonRadius = match ($buttonStyle) {
        'square' => '8px',
        'rounded' => '14px',
        default => '999px',
    };
    $cardRadius = match ($cardStyle) {
        'flat' => '12px',
        'bordered' => '18px',
        default => '24px',
    };
    $fontFamily = match ($fontStyle) {
        'classic' => 'Georgia, "Times New Roman", serif',
        'elegant' => '"Playfair Display", Georgia, serif',
        'friendly' => '"Nunito", ui-sans-serif, system-ui, sans-serif',
        default => 'Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
    };
    $pageBackground = match ($backgroundType) {
        'solid' => $backgroundColor,
        'soft' => "radial-gradient(circle at 18% 18%, {$accentColor}99, transparent 30%), {$backgroundColor}",
        default => "linear-gradient(135deg, {$primaryColor}20, transparent 34%), radial-gradient(circle at 86% 10%, {$accentColor}cc, transparent 28%), {$backgroundColor}",
    };
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $card->name }}</title>
    <style>
        :root{--primary:{{ $primaryColor }};--accent:{{ $accentColor }};--bg:{{ $backgroundColor }};--button-radius:{{ $buttonRadius }};--card-radius:{{ $cardRadius }}}
        *{box-sizing:border-box}
        body{margin:0;font-family:{!! $fontFamily !!};background:{!! $pageBackground !!};color:#0f172a}
        .wrap{min-height:100vh;display:grid;place-items:center;padding:28px}
        .shell{width:min(1040px,100%);display:grid;gap:18px}
        .cover{min-height:220px;border-radius:var(--card-radius);background:linear-gradient(135deg, color-mix(in srgb, var(--primary) 82%, #111827), color-mix(in srgb, var(--accent) 64%, white));overflow:hidden;box-shadow:0 24px 70px rgba(15,23,42,.12)}
        .cover img{display:block;width:100%;height:100%;min-height:220px;object-fit:cover}
        .panel{width:min(640px,100%);margin:auto;border:1px solid color-mix(in srgb, var(--primary) 16%, #d9e2ef);background:rgba(255,255,255,.94);border-radius:var(--card-radius);padding:28px;box-shadow:0 24px 80px rgba(15,23,42,.10)}
        .panel.is-bordered{box-shadow:none;border-width:2px}
        .panel.is-flat{box-shadow:none;background:#fff}
        .brand{display:flex;align-items:center;gap:12px;margin-bottom:16px}
        .brand img{width:44px;height:44px;border-radius:14px;object-fit:cover;border:1px solid #e2e8f0}
        .eyebrow{text-transform:uppercase;letter-spacing:.16em;font-size:12px;color:#607089;font-weight:800}
        h1{margin:10px 0 8px;font-size:clamp(30px,5vw,46px);line-height:1.02;letter-spacing:0;font-weight:850}
        p{line-height:1.65;color:#475569}
        .stamp-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px;margin:18px 0}
        .stamp{display:grid;place-items:center;aspect-ratio:1;border:1px dashed #b8c4d6;border-radius:14px;color:#8a98ad;font-weight:900}
        .stamp.is-filled{border-color:color-mix(in srgb, var(--primary) 55%, white);background:color-mix(in srgb, var(--accent) 55%, white);color:var(--primary)}
        .field{width:100%;border:1px solid #d9e2ef;border-radius:14px;padding:13px 14px;margin-top:10px;font:inherit}
        .field:focus{outline:2px solid color-mix(in srgb, var(--primary) 24%, transparent);border-color:var(--primary)}
        .submit{width:100%;border:0;border-radius:var(--button-radius);background:var(--primary);color:white;padding:14px 16px;font-weight:900;margin-top:12px;cursor:pointer;font:inherit}
        .reward{border:1px solid color-mix(in srgb, var(--primary) 26%, #bbf7d0);background:color-mix(in srgb, var(--accent) 34%, #fff);border-radius:18px;padding:18px;margin:18px 0;color:#14532d}
        .code{overflow-wrap:anywhere;font-size:clamp(28px,7vw,40px);font-weight:950;letter-spacing:.08em;color:color-mix(in srgb, var(--primary) 78%, #064e3b)}
        .actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:14px}
        .action{display:inline-flex;align-items:center;justify-content:center;border:1px solid color-mix(in srgb, var(--primary) 30%, #d1fae5);border-radius:var(--button-radius);background:#fff;color:var(--primary);padding:10px 14px;font-weight:850;text-decoration:none;cursor:pointer}
        .error{border:1px solid #fecaca;background:#fff1f2;color:#991b1b;border-radius:14px;padding:12px;margin:12px 0}
        @media (min-width:900px){.shell.has-cover{grid-template-columns:minmax(0,.9fr) minmax(0,1.1fr);align-items:center}.shell.has-cover .panel{margin:0}}
        @media print{body{background:#fff}.wrap{padding:0}.cover,.submit,form,.actions{display:none}.panel{box-shadow:none;border-color:#d9e2ef}}
    </style>
</head>
<body>
<main class="wrap">
    <div class="shell {{ $coverImage ? 'has-cover' : '' }}">
        @if ($coverImage)
            <aside class="cover"><img src="{{ $coverImage }}" alt=""></aside>
        @endif
        <section class="panel {{ $cardStyle === 'bordered' ? 'is-bordered' : ($cardStyle === 'flat' ? 'is-flat' : '') }}">
            @if ($logoUrl)
                <div class="brand">
                    <img src="{{ $logoUrl }}" alt="">
                    <div class="eyebrow">{{ $card->business?->name ?: __('Local business') }}</div>
                </div>
            @else
                <div class="eyebrow">{{ $card->business?->name ?: __('Local business') }}</div>
            @endif
            <h1>{{ $card->name }}</h1>
            <p>{{ __('Collect :count stamps to unlock: :reward', ['count' => $card->required_stamps, 'reward' => $card->reward_title]) }}</p>

            @if ($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            @if ($result)
                @if (! empty($result['reward']))
                    <div class="reward">
                        <p>{{ __('Congratulations! You earned a reward.') }}</p>
                        <div id="reward-code" class="code">{{ $result['reward']['code'] }}</div>
                        <p id="reward-title">{{ $card->reward_title }} @if($card->reward_value) &middot; {{ $card->reward_value }} @endif</p>
                        <div class="actions">
                            <button class="action" type="button" onclick="navigator.clipboard && navigator.clipboard.writeText(document.getElementById('reward-code').textContent.trim())">{{ __('Copy code') }}</button>
                            <button class="action" type="button" onclick="downloadRewardCode()">{{ __('Download code') }}</button>
                            <button class="action" type="button" onclick="window.print()">{{ __('Print / Save') }}</button>
                        </div>
                    </div>
                @else
                    <p>{{ __('Stamp added for :name.', ['name' => $result['customer']]) }}</p>
                    <div class="stamp-grid">
                        @for ($i = 1; $i <= $card->required_stamps; $i++)
                            <div class="stamp {{ $i <= (int) $result['stamps'] ? 'is-filled' : '' }}">{{ $i }}</div>
                        @endfor
                    </div>
                    <p>{{ __('Progress: :current / :required stamps', ['current' => $result['stamps'], 'required' => $result['required']]) }}</p>
                @endif
            @else
                <form method="post" action="{{ route('loyalty-cards.stamp', ['card' => $card->slug]) }}">
                    @csrf
                    <input class="field" name="name" value="{{ old('name') }}" placeholder="{{ __('Your name') }}" required>
                    <input class="field" name="phone" value="{{ old('phone') }}" placeholder="{{ __('Phone') }}">
                    <input class="field" name="email" value="{{ old('email') }}" placeholder="{{ __('Email') }}">
                    <button class="submit" type="submit">{{ __('Collect stamp') }}</button>
                </form>
            @endif
        </section>
    </div>
</main>
<script>
function downloadRewardCode() {
    const code = document.getElementById('reward-code')?.textContent.trim() || '';
    const reward = document.getElementById('reward-title')?.textContent.trim() || '';
    const card = @json($card->name);
    const business = @json($card->business?->name ?: '');
    const content = [`${card}`, business, '', `Reward code: ${code}`, reward].filter(Boolean).join('\n');
    const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `${code || 'reward-code'}.txt`;
    link.click();
    URL.revokeObjectURL(link.href);
}
</script>
</body>
</html>
