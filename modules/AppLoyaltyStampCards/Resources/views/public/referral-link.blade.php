<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $link->campaign->name }}</title>
    <style>
        *{box-sizing:border-box}body{margin:0;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:linear-gradient(135deg,#2563eb18,transparent 34%),#f8fafc;color:#0f172a}.wrap{min-height:100vh;display:grid;place-items:center;padding:28px}.panel{width:min(620px,100%);border:1px solid #dbe4f0;background:#fff;border-radius:24px;padding:28px;box-shadow:0 24px 80px rgba(15,23,42,.1)}.eyebrow{text-transform:uppercase;letter-spacing:.16em;font-size:12px;color:#607089;font-weight:800}h1{margin:12px 0 8px;font-size:clamp(30px,5vw,44px);line-height:1.04}.field{width:100%;border:1px solid #d9e2ef;border-radius:14px;padding:13px 14px;margin-top:10px;font:inherit}.button{width:100%;border:0;border-radius:999px;background:#2563eb;color:#fff;padding:14px 16px;font-weight:900;margin-top:12px;cursor:pointer}.success{border:1px solid #bfdbfe;background:#eff6ff;color:#1d4ed8;border-radius:18px;padding:18px;margin:18px 0}.reward{font-size:28px;font-weight:950;letter-spacing:.08em;color:#0f766e}.error{border:1px solid #fecaca;background:#fff1f2;color:#991b1b;border-radius:14px;padding:12px;margin:12px 0}p{line-height:1.65;color:#475569}
    </style>
</head>
<body>
<main class="wrap">
    <section class="panel">
        <div class="eyebrow">{{ $link->campaign->business?->name ?: __('Local business') }}</div>
        <h1>{{ __(':name invited you', ['name' => $link->customer?->name ?: __('A friend')]) }}</h1>
        <p>{{ __('Submit your details for :campaign. Your friend can earn: :reward', ['campaign' => $link->campaign->name, 'reward' => $link->campaign->reward_title]) }}</p>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        @if ($result)
            <div class="success">
                <p>{{ __('Thanks, :name. Your referral was recorded.', ['name' => $result['friend']]) }}</p>
                @if (! empty($result['reward']))
                    <p>{{ __('Your friend earned a reward.') }}</p>
                    <div class="reward">{{ $result['reward']['code'] }}</div>
                @else
                    <p>{{ __('Your friend has :current / :required referrals.', ['current' => $result['converted_count'], 'required' => $result['required']]) }}</p>
                @endif
            </div>
        @else
            <form method="post" action="{{ route('referral-links.convert', ['link' => $link->code]) }}">
                @csrf
                <input class="field" name="name" value="{{ old('name') }}" placeholder="{{ __('Your name') }}" required>
                <input class="field" name="phone" value="{{ old('phone') }}" placeholder="{{ __('Phone') }}">
                <input class="field" name="email" value="{{ old('email') }}" placeholder="{{ __('Email') }}">
                <textarea class="field" name="message" placeholder="{{ __('Message') }}">{{ old('message') }}</textarea>
                <button class="button" type="submit">{{ __('Submit referral') }}</button>
            </form>
        @endif
    </section>
</main>
</body>
</html>
