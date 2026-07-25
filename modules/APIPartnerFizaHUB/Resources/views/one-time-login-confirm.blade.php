<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('Signing in to MLHUB…') }}</title>
    <link rel="icon" href="{{ asset('img/favicon.svg') }}" type="image/svg+xml">
    <style>
        :root { --ink:#17231e; --muted:#5d6c65; --line:#d9e2dd; --brand:#0f766e; --soft:#eaf7f3; }
        * { box-sizing:border-box; }
        body {
            margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center;
            background:#f4f7f5; color:var(--ink); font:15px/1.65 system-ui,-apple-system,"Segoe UI",sans-serif;
        }
        .card {
            width:min(420px, calc(100% - 32px)); background:#fff; border:1px solid var(--line);
            border-radius:16px; padding:32px 28px; text-align:center;
        }
        .spinner {
            width:36px; height:36px; margin:0 auto 16px; border-radius:50%;
            border:3px solid var(--soft); border-top-color:var(--brand); animation:spin .8s linear infinite;
        }
        @keyframes spin { to { transform:rotate(360deg); } }
        h1 { color:var(--brand); font-size:1.15rem; margin:0 0 6px; }
        p { color:var(--muted); margin:0 0 20px; }
        .btn {
            display:inline-block; text-decoration:none; border:none; cursor:pointer;
            background:var(--brand); color:#fff; border-radius:999px; padding:11px 22px;
            font-weight:700; font-size:14px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="spinner" aria-hidden="true"></div>
        <h1>{{ __('Signing you in to MLHUB…') }}</h1>
        <p>{{ __('Please wait a moment. If the page does not redirect automatically, tap the button below.') }}</p>
        <form id="one-time-login-form" method="POST" action="{{ $actionUrl }}">
            @csrf
            <button type="submit" class="btn">{{ __('Continue signing in') }}</button>
        </form>
    </div>
    <script>
        document.getElementById('one-time-login-form').submit();
    </script>
</body>
</html>
