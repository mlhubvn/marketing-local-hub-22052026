@php
    $threshold = (int) data_get($campaign->settings, 'positive_threshold', 4);
    $preferredDestination = (string) data_get($campaign->settings, 'preferred_destination', 'google');
    $destinationName = $preferredDestination === 'facebook' ? 'Facebook' : 'Google';
@endphp

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $campaign->name }}</title>
    <style>
        :root{color-scheme:light;--ink:#102033;--muted:#66758a;--line:#dfe7ef;--soft:#f5f8fb;--brand:#0f8277;--brand-soft:#e4f5f2;--warn:#c77800;--warn-soft:#fff4df}
        *{box-sizing:border-box}body{margin:0;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:radial-gradient(circle at top left,#dff3ef,transparent 34%),#f5f7fb;color:var(--ink)}
        .wrap{min-height:100vh;display:grid;place-items:center;padding:22px}.panel{width:min(620px,100%);overflow:hidden;border:1px solid var(--line);background:rgba(255,255,255,.94);border-radius:28px;box-shadow:0 34px 90px rgba(16,32,51,.12)}
        .hero{padding:30px 28px 22px;background:linear-gradient(135deg,#e3f5f1,transparent 62%)}.badge{display:inline-flex;align-items:center;gap:8px;border:1px solid #cfe4df;background:#fff;border-radius:999px;padding:7px 11px;font-size:11px;font-weight:800;letter-spacing:.16em;text-transform:uppercase;color:#53675f}
        h1{margin:18px 0 10px;font-size:34px;line-height:1.04;letter-spacing:-.05em}.sub{margin:0;color:var(--muted);line-height:1.7}.body{padding:24px 28px 28px}.stars{display:grid;grid-template-columns:repeat(5,1fr);gap:10px;margin:18px 0}
        .star{border:1px solid var(--line);background:#fff;border-radius:18px;padding:16px 0;cursor:pointer;color:#8a6b23;font-size:18px;font-weight:900;box-shadow:0 10px 28px rgba(16,32,51,.05);transition:.18s}.star:hover{transform:translateY(-2px);border-color:#f0c76c;background:#fff9ea}
        .route{display:grid;gap:10px;margin-top:12px}.route div{border-radius:16px;padding:12px 14px;font-size:13px;line-height:1.5}.good{background:var(--brand-soft);color:#0f746b}.bad{background:var(--warn-soft);color:var(--warn)}
        .feedback{display:none;margin-top:18px;border-top:1px solid var(--line);padding-top:18px}.feedback.is-open{display:block}.field{width:100%;border:1px solid var(--line);border-radius:14px;padding:13px 14px;margin-top:10px;font:inherit;outline:none}.field:focus{border-color:var(--brand);box-shadow:0 0 0 4px rgba(15,130,119,.1)}
        .submit{width:100%;border:0;border-radius:15px;background:var(--brand);color:white;padding:14px 16px;font-weight:800;margin-top:12px;cursor:pointer}.thanks{padding:26px;border-radius:20px;background:var(--brand-soft);color:#0f746b;line-height:1.7}
        @media(max-width:520px){.hero,.body{padding-left:20px;padding-right:20px}h1{font-size:29px}.stars{gap:7px}.star{border-radius:14px;padding:14px 0}}
    </style>
</head>
<body>
    <main class="wrap">
        <section class="panel">
            <div class="hero">
                <span class="badge">{{ $campaign->business?->name ?: __('Local business') }}</span>
                <h1>{{ __('How was your experience?') }}</h1>
                <p class="sub">{{ __('Your feedback helps us recognize great service and fix issues quickly.') }}</p>
            </div>

            <div class="body">
                @if (session('review_feedback_saved'))
                    <div class="thanks">{{ data_get($campaign->settings, 'thank_you_message') }}</div>
                @else
                    <form id="review-form" method="post" action="{{ route('review-booster.feedback', $campaign) }}">
                        @csrf
                        <input id="rating-input" type="hidden" name="rating" value="">

                        <div class="stars" aria-label="{{ __('Choose rating') }}">
                            @for ($rating = 1; $rating <= 5; $rating++)
                                <button class="star" type="button" data-rating="{{ $rating }}">{{ $rating }} &#9733;</button>
                            @endfor
                        </div>

                        <div class="route">
                            <div class="good">{{ __(':stars+ stars opens :destination review.', ['stars' => $threshold, 'destination' => $destinationName]) }}</div>
                            <div class="bad">{{ __('Lower scores open a private feedback form so the team can make it right.') }}</div>
                        </div>

                        <div id="feedback-fields" class="feedback">
                            <p class="sub">{{ data_get($campaign->settings, 'negative_feedback_message') }}</p>
                            <input class="field" name="customer_name" placeholder="{{ __('Your name') }}">
                            <input class="field" name="customer_phone" placeholder="{{ __('Phone') }}">
                            <input class="field" name="customer_email" placeholder="{{ __('Email') }}">
                            <textarea class="field" name="message" rows="4" placeholder="{{ __('Tell us what happened') }}"></textarea>
                            <button class="submit" type="submit">{{ __('Send private feedback') }}</button>
                        </div>
                    </form>
                @endif
            </div>
        </section>
    </main>

    <script>
        const threshold = @json($threshold);
        const form = document.getElementById('review-form');
        const ratingInput = document.getElementById('rating-input');
        const feedbackFields = document.getElementById('feedback-fields');

        document.querySelectorAll('[data-rating]').forEach((button) => {
            button.addEventListener('click', () => {
                const rating = Number(button.dataset.rating);
                ratingInput.value = rating;

                if (rating >= threshold) {
                    form.submit();
                    return;
                }

                feedbackFields.classList.add('is-open');
                feedbackFields.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            });
        });
    </script>
</body>
</html>
