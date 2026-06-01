<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $campaign->name }}</title>
    <style>
        *{box-sizing:border-box}body{margin:0;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:linear-gradient(135deg,#0f766e18,transparent 34%),#f6faf9;color:#0f172a}.wrap{min-height:100vh;display:grid;place-items:center;padding:28px}.panel{width:min(620px,100%);border:1px solid #d9e6e4;background:#fff;border-radius:24px;padding:28px;box-shadow:0 24px 80px rgba(15,23,42,.1)}.eyebrow{text-transform:uppercase;letter-spacing:.16em;font-size:12px;color:#607089;font-weight:800}h1{margin:12px 0 8px;font-size:clamp(30px,5vw,44px);line-height:1.04}.field{width:100%;border:1px solid #d9e2ef;border-radius:14px;padding:13px 14px;margin-top:10px;font:inherit}.button{width:100%;border:0;border-radius:999px;background:#0f766e;color:#fff;padding:14px 16px;font-weight:900;margin-top:12px;cursor:pointer}.success{border:1px solid #99f6e4;background:#f0fdfa;color:#115e59;border-radius:18px;padding:18px;margin:18px 0}.link{overflow-wrap:anywhere;font-weight:850}.actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}.action{border:1px solid #99f6e4;border-radius:999px;background:#fff;color:#0f766e;padding:10px 14px;font-weight:850;cursor:pointer}.error{border:1px solid #fecaca;background:#fff1f2;color:#991b1b;border-radius:14px;padding:12px;margin:12px 0}p{line-height:1.65;color:#475569}
    </style>
</head>
<body>
<main class="wrap">
    <section class="panel">
        <div class="eyebrow">{{ $campaign->business?->name ?: __('Local business') }}</div>
        <h1>{{ $campaign->name }}</h1>
        <p>{{ __('Invite friends and earn: :reward', ['reward' => $campaign->reward_title]) }}</p>
        <p>{{ __('Required referrals: :count', ['count' => $campaign->required_referrals]) }}</p>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        @if ($link)
            <div class="success">
                <p>{{ __('Your referral link is ready.') }}</p>
                <p id="referral-link" class="link">{{ $link['url'] }}</p>
                <div class="actions">
                    <button class="action" type="button" onclick="navigator.clipboard && navigator.clipboard.writeText(document.getElementById('referral-link').textContent.trim())">{{ __('Copy link') }}</button>
                    <button class="action" type="button" onclick="window.print()">{{ __('Print / Save') }}</button>
                </div>
            </div>
        @endif

        <form method="post" action="{{ route('referral-campaigns.link', ['campaign' => $campaign->slug]) }}">
            @csrf
            <input class="field" name="name" value="{{ old('name') }}" placeholder="{{ __('Your name') }}" required>
            <input class="field" name="phone" value="{{ old('phone') }}" placeholder="{{ __('Phone') }}">
            <input class="field" name="email" value="{{ old('email') }}" placeholder="{{ __('Email') }}">
            <button class="button" type="submit">{{ __('Get my invite link') }}</button>
        </form>
    </section>
</main>
</body>
</html>
