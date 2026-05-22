<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $campaign->name }}</title>
    <style>
        body{margin:0;font-family:Inter,ui-sans-serif,system-ui;background:#f7f8fb;color:#172033}
        .wrap{min-height:100vh;display:grid;place-items:center;padding:24px}
        .panel{width:min(540px,100%);border:1px solid #dfe5ef;background:#fff;border-radius:18px;padding:28px;box-shadow:0 24px 80px rgba(15,23,42,.08)}
        .field{width:100%;box-sizing:border-box;border:1px solid #dfe5ef;border-radius:12px;padding:12px;margin-top:10px}
        .submit{border:0;border-radius:12px;background:#2563eb;color:#fff;padding:12px 16px;font-weight:700;margin-top:12px}
        .error{border:1px solid #fecaca;background:#fff1f2;color:#991b1b;border-radius:12px;padding:12px}
    </style>
</head>
<body>
<main class="wrap">
    <section class="panel">
        <p>{{ $campaign->business?->name }}</p>
        <h1>{{ data_get($campaign->settings, 'headline', __('Tell us about your experience')) }}</h1>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        @if (session('feedback_saved'))
            <p>{{ data_get($campaign->settings, 'thank_you_message', __('Thanks. Your feedback was sent.')) }}</p>
        @else
            <form method="post" action="{{ route('feedback-forms.submit', $campaign) }}">
                @csrf
                <select class="field" name="rating" @if(data_get($campaign->settings, 'rating_required')) required @endif>
                    <option value="">{{ __('Rating') }}</option>
                    @for ($i = 1; $i <= 5; $i++)
                        <option value="{{ $i }}">{{ $i }} {{ __('stars') }}</option>
                    @endfor
                </select>
                <input class="field" name="customer_name" placeholder="{{ __('Your name') }}">
                <input class="field" name="customer_phone" placeholder="{{ __('Phone') }}">
                <input class="field" name="customer_email" placeholder="{{ __('Email') }}">
                <textarea class="field" name="message" rows="5" placeholder="{{ __('Your feedback') }}" required></textarea>
                <button class="submit" type="submit">{{ __('Send feedback') }}</button>
            </form>
        @endif
    </section>
</main>
</body>
</html>
