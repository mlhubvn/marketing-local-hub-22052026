<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $campaign->name }}</title>
    <style>
        body{margin:0;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:#f7f8fb;color:#172033}
        .wrap{min-height:100vh;display:grid;place-items:center;padding:24px}
        .panel{width:min(520px,100%);border:1px solid #dfe5ef;background:#fff;border-radius:18px;padding:28px;box-shadow:0 24px 80px rgba(15,23,42,.08)}
        .eyebrow{text-transform:uppercase;letter-spacing:.16em;font-size:12px;color:#62718a;font-weight:700}
        h1{margin:12px 0 8px;font-size:30px;line-height:1.15}
        p{line-height:1.65;color:#516076}
        a.button{display:inline-flex;margin-top:14px;border-radius:12px;background:#2563eb;color:#fff;text-decoration:none;padding:12px 16px;font-weight:700}
    </style>
</head>
<body>
    <main class="wrap">
        <section class="panel">
            <div class="eyebrow">{{ $campaign->business?->name ?: __('Local campaign') }}</div>
            <h1>{{ $campaign->name }}</h1>
            <p>{{ data_get($campaign->settings, 'cta_text') ?: __('Thanks for scanning. Use the action below to continue.') }}</p>
            @if ($campaign->destination_url)
                <a class="button" href="{{ $campaign->destination_url }}">{{ __('Continue') }}</a>
            @endif
        </section>
    </main>
</body>
</html>
