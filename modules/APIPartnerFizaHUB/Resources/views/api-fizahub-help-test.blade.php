<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('FizaHUB Partner API - Step-by-step Postman test guide') }}</title>
    <meta name="description" content="{{ __('Copy-and-paste Postman testing guide for non-technical users.') }}">
    @php
        $faviconPath = (string) (function_exists('get_option')
            ? get_option('website_favicon', config('mlhub.site.favicon', 'img/favicon.svg'))
            : config('mlhub.site.favicon', 'img/favicon.svg'));
        $faviconMimeType = str_ends_with(strtolower($faviconPath), '.svg') ? 'image/svg+xml' : 'image/png';
        $siteFavicon = url($faviconPath);
    @endphp
    <link rel="icon" href="{{ $siteFavicon }}" type="{{ $faviconMimeType }}">
    <link rel="apple-touch-icon" href="{{ $siteFavicon }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|instrument-sans:400,500,600,700|plus-jakarta-sans:400,500,600,700,800|manrope:400,500,600,700,800|outfit:400,500,600,700,800" rel="stylesheet" />
    <style>
        :root {
            --bg: #f4f7f5;
            --surface: #ffffff;
            --ink: #14201b;
            --muted: #5b6b63;
            --line: #d7e0db;
            --brand: #0f766e;
            --brand-dark: #0b5f59;
            --code-bg: #0f1c18;
            --code-ink: #d7efe7;
            --warn: #92400e;
            --get: #0369a1;
            --post: #b45309;
            --radius: 14px;
            --shadow: 0 10px 30px rgba(20, 32, 27, .08);
            --font: "Plus Jakarta Sans", Inter, "Instrument Sans", Manrope, Outfit, "Segoe UI", sans-serif;
            --mono: ui-monospace, "Cascadia Code", Consolas, monospace;
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            font-family: var(--font);
            color: var(--ink);
            background:
                radial-gradient(circle at top left, rgba(15, 118, 110, .10), transparent 40%),
                linear-gradient(180deg, #eef5f1 0%, var(--bg) 28%, #f7faf8 100%);
            line-height: 1.55;
        }
        a { color: var(--brand-dark); }
        .wrap { width: min(900px, calc(100% - 2rem)); margin: 0 auto; }
        .topbar {
            position: sticky; top: 0; z-index: 20;
            backdrop-filter: blur(10px);
            background: rgba(244, 247, 245, .88);
            border-bottom: 1px solid rgba(215, 224, 219, .8);
        }
        .topbar-inner {
            display: flex; gap: .75rem; flex-wrap: wrap; align-items: center;
            justify-content: space-between; padding: .85rem 0;
        }
        .brand { font-weight: 800; color: var(--brand-dark); text-decoration: none; }
        .nav { display: flex; flex-wrap: wrap; gap: .55rem; }
        .nav a {
            text-decoration: none; color: var(--muted); font-size: .9rem;
            padding: .35rem .65rem; border-radius: 999px; border: 1px solid transparent;
        }
        .nav a:hover { color: var(--ink); border-color: var(--line); background: #fff; }
        .hero { padding: 2rem 0 1rem; }
        .hero-card, .step, .panel, .remember {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 1.15rem 1.2rem;
        }
        h1 { margin: 0 0 .7rem; font-size: clamp(1.4rem, 4vw, 2.1rem); line-height: 1.2; }
        .lead { margin: 0 0 1rem; color: var(--muted); }
        .btn {
            display: inline-flex; align-items: center; justify-content: center;
            text-decoration: none; border-radius: 999px; padding: .7rem 1.05rem;
            font-weight: 700; font-size: .92rem; border: 1px solid transparent;
        }
        .btn-primary { background: var(--brand); color: #fff; }
        .btn-ghost { background: #fff; color: var(--ink); border-color: var(--line); }
        .cta-row { display: flex; flex-wrap: wrap; gap: .6rem; }
        .note {
            margin: 1rem 0 0; padding: .85rem 1rem; border-radius: 12px;
            background: #fff7ed; border: 1px solid #fed7aa; color: var(--warn); font-size: .92rem;
        }
        .info {
            margin: 1rem 0 0; padding: .85rem 1rem; border-radius: 12px;
            background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; font-size: .92rem;
        }
        .remember { margin: 1rem 0; }
        .remember h2 { margin: 0 0 .55rem; font-size: 1.1rem; }
        .toc {
            margin: 1rem 0 1.5rem; padding: 1rem 1.1rem;
            background: #fff; border: 1px solid var(--line); border-radius: 12px;
        }
        .toc ol { margin: .4rem 0 0; padding-left: 1.2rem; }
        .toc a { text-decoration: none; }
        .step { margin: 0 0 1rem; }
        .step-num {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 2rem; height: 2rem; border-radius: 999px;
            background: var(--brand); color: #fff; font-weight: 800; margin-right: .45rem;
        }
        .step h2 { margin: 0 0 .7rem; font-size: 1.15rem; display: flex; align-items: center; flex-wrap: wrap; gap: .35rem; }
        .step ol, .step ul { margin: .4rem 0 .7rem; padding-left: 1.2rem; }
        .step li { margin: .35rem 0; }
        .method {
            font-family: var(--mono); font-size: .75rem; font-weight: 800;
            padding: .2rem .45rem; border-radius: 8px; color: #fff;
        }
        .method.get { background: var(--get); }
        .method.post { background: var(--post); }
        pre, code { font-family: var(--mono); }
        pre {
            margin: .55rem 0; overflow: auto; background: var(--code-bg); color: var(--code-ink);
            border-radius: 12px; padding: .9rem 1rem; font-size: .8rem; line-height: 1.45;
        }
        .copy-label {
            display: block; margin: .7rem 0 .25rem; font-size: .78rem;
            text-transform: uppercase; letter-spacing: .05em; color: var(--muted); font-weight: 700;
        }
        .check {
            margin-top: .7rem; padding: .7rem .85rem; border-radius: 10px;
            background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; font-size: .92rem;
        }
        .table-wrap { overflow-x: auto; border-radius: 12px; margin: .7rem 0; }
        table {
            width: 100%; border-collapse: collapse; background: #fff;
            border: 1px solid var(--line); font-size: .88rem;
        }
        th, td {
            text-align: left; vertical-align: top; padding: .65rem .7rem;
            border-bottom: 1px solid var(--line);
        }
        th { background: #f1f6f3; font-size: .8rem; }
        tr:last-child td { border-bottom: 0; }
        .footer { padding: 1.5rem 0 2.2rem; color: var(--muted); font-size: .9rem; }
    </style>
</head>
<body>
<header class="topbar">
    <div class="wrap topbar-inner">
        <a class="brand" href="{{ $docsUrl }}">MLHUB × FizaHUB</a>
        <nav class="nav" aria-label="{{ __('Help navigation') }}">
            <a href="{{ $docsUrl }}">{{ __('API docs') }}</a>
            <a href="{{ $postmanUrl }}">{{ __('Download Postman JSON') }}</a>
            <a href="#step-1">{{ __('Start here') }}</a>
            <a href="#troubleshooting">{{ __('Common errors') }}</a>
        </nav>
    </div>
</header>

<main>
    <section class="hero">
        <div class="wrap">
            <div class="hero-card">
                <h1>{{ __('FizaHUB Partner API - Step-by-step Postman test guide') }}</h1>
                <p class="lead">{{ __('For non-technical testers. Go from top to bottom, copy Body/raw from the samples, click Send, then copy returned IDs into Variables.') }}</p>
                <div class="cta-row">
                    <a class="btn btn-primary" href="{{ $postmanUrl }}">{{ __('1. Download Postman JSON first') }}</a>
                    <a class="btn btn-ghost" href="{{ $docsUrl }}">{{ __('Back to API docs') }}</a>
                </div>
                <div class="note">
                    {{ __('Never paste a real partner token into chat, email, or public docs. Ask the MLHUB admin for a test token and keep it private.') }}
                </div>
            </div>

            <div class="remember">
                <h2>{{ __('You only need to remember 5 things') }}</h2>
                <ul>
                    <li><code>base_url</code> — {{ __('the MLHUB domain.') }}</li>
                    <li><code>partner_token</code> — {{ __('the token MLHUB issues only for FizaHUB.') }}</li>
                    <li><code>external_user_id</code> — {{ __('the user ID on the FizaHUB side.') }}</li>
                    <li><code>external_business_id</code> — {{ __('the business/store ID on the FizaHUB side; this is the main key for package/dashboard/support/login.') }}</li>
                    <li><code>from</code> / <code>to</code> — {{ __('only filter the Dashboard date range; they are not the package duration.') }}</li>
                </ul>
            </div>

            <div class="toc">
                <strong>{{ __('Test order (do this from top to bottom)') }}</strong>
                <ol>
                    <li><a href="#step-1">{{ __('Install Postman and import the file') }}</a></li>
                    <li><a href="#step-2">{{ __('Fill in variables') }}</a></li>
                    <li><a href="#step-3">{{ __('Test GET Health') }}</a></li>
                    <li><a href="#step-4">{{ __('Test POST Onboarding') }}</a></li>
                    <li><a href="#step-5">{{ __('Test GET Onboarding Status') }}</a></li>
                    <li><a href="#step-6">{{ __('Test GET Package') }}</a></li>
                    <li><a href="#step-7">{{ __('Test GET Dashboard') }}</a></li>
                    <li><a href="#step-8">{{ __('Test POST Create Support Ticket') }}</a></li>
                    <li><a href="#step-9">{{ __('Test GET List Support Tickets') }}</a></li>
                    <li><a href="#step-10">{{ __('Test GET Support Ticket Detail') }}</a></li>
                    <li><a href="#step-11">{{ __('Test POST Send Support Message') }}</a></li>
                    <li><a href="#step-12">{{ __('Test POST One-time Login') }}</a></li>
                    <li><a href="#troubleshooting">{{ __('Common errors') }}</a></li>
                </ol>
            </div>
        </div>
    </section>

    <section>
        <div class="wrap">
            <article class="step" id="step-1">
                <h2><span class="step-num">1</span> {{ __('Install Postman and import the file') }}</h2>
                <ol>
                    <li>{{ __('Open your browser and go to the Postman website. Download Postman for your computer and install it.') }}</li>
                    <li>{{ __('Open this download link and save the JSON file:') }}</li>
                </ol>
                <a class="btn btn-primary" href="{{ $postmanUrl }}">{{ __('Download Postman JSON') }}</a>
                <ol start="3">
                    <li>{{ __('Open Postman.') }}</li>
                    <li>{{ __('Click Import.') }}</li>
                    <li>{{ __('Choose the downloaded file named MLHUB-FizaHUB-Partner-API.postman_collection.json') }}</li>
                    <li>{{ __('You should see a collection named FizaHUB Partner API (MLHUB MVP).') }}</li>
                </ol>
                <div class="check">{{ __('Done when: you can see 10 requests inside the collection.') }}</div>
            </article>

            <article class="step" id="step-2">
                <h2><span class="step-num">2</span> {{ __('Fill in variables') }}</h2>
                <ol>
                    <li>{{ __('In Postman, open the collection FizaHUB Partner API (MLHUB MVP).') }}</li>
                    <li>{{ __('Open the Variables tab.') }}</li>
                    <li>{{ __('Copy and paste these Current values:') }}</li>
                </ol>
                <span class="copy-label">{{ __('Copy these values') }}</span>
                <pre>base_url = {{ $appUrl }}
partner_token = ({{ __('paste the test token issued by the MLHUB admin') }})
external_user_id = fh-user-demo-001
external_business_id = fh-biz-demo-001
from = {{ $dashboardFrom }}
to = {{ $dashboardTo }}
onboarding_request_id = ({{ __('leave empty at first') }})
ticket_id = ({{ __('leave empty at first') }})</pre>
                <ol start="4">
                    <li>{{ __('Click Save.') }}</li>
                </ol>

                <span class="copy-label">{{ __('Variable meanings') }}</span>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ __('Variable') }}</th>
                                <th>{{ __('Example') }}</th>
                                <th>{{ __('What is it for?') }}</th>
                                <th>{{ __('When should you change it?') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>base_url</code></td>
                                <td><code>{{ $appUrl }}</code></td>
                                <td>{{ __('MLHUB domain.') }}</td>
                                <td>{{ __('Change when testing local/staging/production.') }}</td>
                            </tr>
                            <tr>
                                <td><code>partner_token</code></td>
                                <td>{{ __('token issued by MLHUB') }}</td>
                                <td>{{ __('Authenticates FizaHUB API calls.') }}</td>
                                <td>{{ __('Change when the MLHUB admin issues a new token.') }}</td>
                            </tr>
                            <tr>
                                <td><code>external_user_id</code></td>
                                <td><code>fh-user-demo-001</code></td>
                                <td>{{ __('User ID on the FizaHUB side.') }}</td>
                                <td>{{ __('Each test user / real customer should have its own ID.') }}</td>
                            </tr>
                            <tr>
                                <td><code>external_business_id</code></td>
                                <td><code>fh-biz-demo-001</code></td>
                                <td>{{ __('Primary technical key that maps a FizaHUB business to MLHUB.') }}</td>
                                <td>{{ __('Each business/store should have its own ID.') }}</td>
                            </tr>
                            <tr>
                                <td><code>from</code></td>
                                <td><code>{{ $dashboardFrom }}</code></td>
                                <td>{{ __('Dashboard report start date.') }}</td>
                                <td>{{ __('Change when you want another report range. You may leave it empty so the API uses the last 30 days.') }}</td>
                            </tr>
                            <tr>
                                <td><code>to</code></td>
                                <td><code>{{ $dashboardTo }}</code></td>
                                <td>{{ __('Dashboard report end date.') }}</td>
                                <td>{{ __('Change when you want another report range. You may leave it empty so the API uses today.') }}</td>
                            </tr>
                            <tr>
                                <td><code>onboarding_request_id</code></td>
                                <td>{{ __('leave empty at first') }}</td>
                                <td>{{ __('Read onboarding status.') }}</td>
                                <td>{{ __('After POST Onboarding, copy data.request_id and paste it here.') }}</td>
                            </tr>
                            <tr>
                                <td><code>ticket_id</code></td>
                                <td>{{ __('leave empty at first') }}</td>
                                <td>{{ __('View or send support ticket messages.') }}</td>
                                <td>{{ __('After creating a support ticket, copy data.ticket_id and paste it here.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="note">
                    <strong>{{ __('from/to are not the package duration') }}</strong><br>
                    {{ __('from and to are only used by the Dashboard API to choose the report date range. Example: from=2026-07-01 and to=2026-07-31 means marketing metrics in July. These two fields are not used to renew a 1/3/6/12 month package.') }}
                </div>
                <div class="info">
                    <strong>{{ __('How does package duration work in the MVP?') }}</strong><br>
                    {{ __('In the MVP, FizaHUB sends package_code during onboarding. Example: package_code=base maps to the internal MLHUB plan mlhub-free-da-nang. The Package API returns starts_at/expires_at when MLHUB has duration data. The MVP does not yet have an endpoint to enter 1/3/6/12 months or auto-renew. If FizaHUB needs to sell monthly packages, design a separate package/subscription endpoint in a later phase — do not use from/to.') }}
                </div>
                <div class="check">{{ __('Done when: base_url and partner_token are filled and saved.') }}</div>
            </article>

            <article class="step" id="step-3">
                <h2><span class="step-num">3</span> <span class="method get">GET</span> {{ __('Test GET Health') }}</h2>
                <ol>
                    <li>{{ __('Open the request named GET Health.') }}</li>
                    <li>{{ __('Check the Headers tab. Postman should already fill Authorization, X-Partner, and X-Request-Id.') }}</li>
                    <li>{{ __('Click Send.') }}</li>
                </ol>
                <span class="copy-label">URL</span>
                <pre>@{{base_url}}/api/v1/partners/fizahub/health</pre>
                <span class="copy-label">{{ __('Headers that must exist') }}</span>
                <pre>@verbatim
Authorization: Bearer {{partner_token}}
X-Partner: fizahub
X-Request-Id: {{$guid}}
Accept: application/json
@endverbatim</pre>
                <span class="copy-label">{{ __('Expected') }}</span>
                <pre>HTTP 200</pre>
                <span class="copy-label">{{ __('Sample response') }}</span>
                <pre>{
  "success": true,
  "data": {
    "status": "ok",
    "partner": "fizahub",
    "api_version": "v1"
  },
  "error": null
}</pre>
                <div class="check">{{ __('Done when: success is true and status is ok. If this fails, stop and check partner_token / base_url.') }}</div>
            </article>

            <article class="step" id="step-4">
                <h2><span class="step-num">4</span> <span class="method post">POST</span> {{ __('Test POST Onboarding') }}</h2>
                <p><strong>{{ __('Postman actions') }}</strong></p>
                <ol>
                    <li>{{ __('Open POST Onboarding.') }}</li>
                    <li>{{ __('Open the Body tab.') }}</li>
                    <li>{{ __('Choose raw.') }}</li>
                    <li>{{ __('Choose JSON.') }}</li>
                    <li>{{ __('Copy the full Body raw JSON below and paste it into Body.') }}</li>
                    <li>{{ __('Optional: change owner.email so it does not collide.') }}</li>
                    <li>{{ __('Click Send.') }}</li>
                </ol>
                <span class="copy-label">URL</span>
                <pre>@{{base_url}}/api/v1/partners/fizahub/onboarding-requests</pre>
                <span class="copy-label">Headers</span>
                <pre>@verbatim
Authorization: Bearer {{partner_token}}
X-Partner: fizahub
X-Request-Id: {{$guid}}
Idempotency-Key: {{$guid}}
Accept: application/json
Content-Type: application/json
@endverbatim</pre>
                <span class="copy-label">{{ __('Body raw JSON') }}</span>
                <pre>{
  "external_user_id": "@{{external_user_id}}",
  "external_business_id": "@{{external_business_id}}",
  "package_code": "base",
  "owner": {
    "name": "Nguyen Van A",
    "phone": "0912345678",
    "email": "nguyenvana+demo001@example.com"
  },
  "business": {
    "name": "Fiza Demo Store",
    "industry": "restaurant_food",
    "address": "123 Nguyen Trai, Da Nang",
    "phone": "0912345678",
    "email": "store-demo001@example.com",
    "website": "https://mlhub.vn",
    "tax_code": null,
    "business_license_number": "HKD-DEMO-001"
  },
  "verification": {
    "identity_verified": true,
    "verified_by": "fizahub",
    "verified_at": "{{ $dashboardTo }}T10:00:00+07:00"
  }
}</pre>
                <div class="note">
                    <ul>
                        <li>{{ __('To test again, change external_business_id, owner.email, and business_license_number to avoid duplicates.') }}</li>
                        <li>{{ __('Do not send CCCD, CCCD images, or GPKD files.') }}</li>
                        <li>{{ __('If response status=completed, you can run Package/Dashboard/Login next.') }}</li>
                        <li>{{ __('If response status=needs_review or pending_verification, ask the MLHUB admin to finish review before mapped APIs work.') }}</li>
                    </ul>
                </div>
                <span class="copy-label">{{ __('Expected') }}</span>
                <pre>HTTP 201 (completed) {{ __('or') }} HTTP 202 (pending_verification / needs_review)</pre>
                <span class="copy-label">{{ __('Sample completed response') }}</span>
                <pre>{
  "success": true,
  "data": {
    "request_id": "018f5a64-b40b-7f60-a925-dea047cf6590",
    "external_business_id": "fh-biz-demo-001",
    "package_code": "base",
    "status": "completed",
    "current_step": "ready"
  },
  "error": null
}</pre>
                <div class="check">
                    <strong>{{ __('After the response') }}:</strong>
                    {{ __('Copy data.request_id → paste into the onboarding_request_id variable → Save.') }}
                </div>
            </article>

            <article class="step" id="step-5">
                <h2><span class="step-num">5</span> <span class="method get">GET</span> {{ __('Test GET Onboarding Status') }}</h2>
                <ol>
                    <li>{{ __('Open GET Onboarding Status.') }}</li>
                    <li>{{ __('Make sure onboarding_request_id variable is filled.') }}</li>
                    <li>{{ __('Click Send.') }}</li>
                </ol>
                <span class="copy-label">URL</span>
                <pre>@{{base_url}}/api/v1/partners/fizahub/onboarding-requests/@{{onboarding_request_id}}</pre>
                <span class="copy-label">{{ __('Expected') }}</span>
                <pre>HTTP 200</pre>
                <span class="copy-label">{{ __('Sample response') }}</span>
                <pre>{
  "success": true,
  "data": {
    "request_id": "018f5a64-b40b-7f60-a925-dea047cf6590",
    "status": "completed",
    "current_step": "ready",
    "external_business_id": "fh-biz-demo-001"
  },
  "error": null
}</pre>
                <div class="check">{{ __('Done when: status is returned and matches the previous onboarding result.') }}</div>
            </article>

            <article class="step" id="step-6">
                <h2><span class="step-num">6</span> <span class="method get">GET</span> {{ __('Test GET Package') }}</h2>
                <ol>
                    <li>{{ __('Only run this after onboarding status is completed.') }}</li>
                    <li>{{ __('Open GET Package.') }}</li>
                    <li>{{ __('Click Send.') }}</li>
                </ol>
                <span class="copy-label">URL</span>
                <pre>@{{base_url}}/api/v1/partners/fizahub/businesses/@{{external_business_id}}/package</pre>
                <span class="copy-label">{{ __('Expected') }}</span>
                <pre>HTTP 200 {{ __('when onboarding is completed') }}</pre>
                <span class="copy-label">{{ __('Sample response') }}</span>
                <pre>{
  "success": true,
  "data": {
    "package_code": "base",
    "package_name": "MLHUB Free Da Nang",
    "plan_slug": "mlhub-free-da-nang",
    "status": "active",
    "starts_at": "2026-07-14T10:00:00Z",
    "expires_at": null,
    "is_trial": false,
    "integration_status": "active",
    "limits": {
      "max_businesses": 1,
      "max_campaigns": 3,
      "max_landing_pages": 3,
      "max_qr_codes": 10,
      "max_team_members": 1
    }
  },
  "error": null
}</pre>
                <div class="info">
                    <ul>
                        <li><code>package_code</code> — {{ __('the package code FizaHUB sent (example: package_code=base).') }}</li>
                        <li><code>plan_slug</code> — {{ __('the internal MLHUB plan (example: mlhub-free-da-nang).') }}</li>
                        <li><code>expires_at</code> — {{ __('may be null if the plan has no end date yet.') }}</li>
                        <li>{{ __('This is not Dashboard from/to.') }} {{ __('The MVP does not yet have an endpoint to renew 1/3/6/12 months.') }}</li>
                    </ul>
                </div>
                <div class="check">{{ __('Done when: package summary is returned without price/credits.') }}</div>
            </article>

            <article class="step" id="step-7">
                <h2><span class="step-num">7</span> <span class="method get">GET</span> {{ __('Test GET Dashboard') }}</h2>
                <ol>
                    <li>{{ __('Open GET Dashboard.') }}</li>
                    <li>{{ __('Open the Params tab and check from / to (Dashboard API date range only).') }}</li>
                    <li>{{ __('If you do not know what to enter, leave from/to empty — the API uses the last 30 days.') }}</li>
                    <li>{{ __('Click Send.') }}</li>
                </ol>
                <span class="copy-label">URL</span>
                <pre>@{{base_url}}/api/v1/partners/fizahub/businesses/@{{external_business_id}}/dashboard?from=@{{from}}&to=@{{to}}</pre>
                <span class="copy-label">Params</span>
                <pre>from = {{ __('report start date, format YYYY-MM-DD') }}
to = {{ __('report end date, format YYYY-MM-DD') }}</pre>
                <div class="note">
                    {{ __('from/to are not the package duration') }}.
                    {{ __('from and to only choose the Dashboard API report range. They are not used for 1/3/6/12 month package renewal.') }}
                </div>
                <span class="copy-label">{{ __('Expected') }}</span>
                <pre>HTTP 200</pre>
                <span class="copy-label">{{ __('Sample response') }}</span>
                <pre>{
  "success": true,
  "data": {
    "period": {
      "from": "{{ $dashboardFrom }}",
      "to": "{{ $dashboardTo }}",
      "timezone": "Asia/Ho_Chi_Minh"
    },
    "metrics": {
      "businesses": 1,
      "campaigns": 0,
      "active_campaigns": 0,
      "qr_scans": 0,
      "new_leads": 0,
      "new_reviews": 0,
      "coupon_claims": 0,
      "coupon_used": 0,
      "bookings": 0,
      "feedback": 0,
      "returning_customers": 0,
      "conversion_rate": 0
    },
    "campaigns": [],
    "trend": [],
    "insights": [],
    "suggested_actions": []
  },
  "error": null
}</pre>
                <div class="info">
                    <ul>
                        <li><code>qr_scans</code>: {{ __('QR scan count.') }}</li>
                        <li><code>new_leads</code>: {{ __('new leads.') }}</li>
                        <li><code>new_reviews</code>: {{ __('internal review feedback with rating >= 4, not live Google Reviews yet.') }}</li>
                        <li><code>returning_customers</code>: {{ __('estimate from repeated phone/email identities.') }}</li>
                        <li><code>conversion_rate</code>: {{ __('internal conversion rate.') }}</li>
                    </ul>
                </div>
                <div class="check">{{ __('Done when: metrics object is present even if all numbers are zero.') }}</div>
            </article>

            <article class="step" id="step-8">
                <h2><span class="step-num">8</span> <span class="method post">POST</span> {{ __('Test POST Create Support Ticket') }}</h2>
                <ol>
                    <li>{{ __('Open POST Create Support Ticket.') }}</li>
                    <li>{{ __('Open Body → raw → JSON.') }}</li>
                    <li>{{ __('Copy the full Body raw JSON below and paste it into Body.') }}</li>
                    <li>{{ __('Click Send.') }}</li>
                </ol>
                <span class="copy-label">URL</span>
                <pre>@{{base_url}}/api/v1/partners/fizahub/businesses/@{{external_business_id}}/support-tickets</pre>
                <span class="copy-label">Headers</span>
                <pre>@verbatim
Authorization: Bearer {{partner_token}}
X-Partner: fizahub
X-Request-Id: {{$guid}}
Idempotency-Key: {{$guid}}
Accept: application/json
Content-Type: application/json
@endverbatim</pre>
                <span class="copy-label">{{ __('Body raw JSON') }}</span>
                <pre>{
  "subject": "Need help with FizaMKT Base",
  "message": "Please help me create a check-in QR and Google review request.",
  "category_id": null,
  "type_id": null
}</pre>
                <span class="copy-label">{{ __('Expected') }}</span>
                <pre>HTTP 201</pre>
                <span class="copy-label">{{ __('Sample response') }}</span>
                <pre>{
  "success": true,
  "data": {
    "ticket_id": "secure-ticket-id",
    "subject": "Need help with FizaMKT Base",
    "status": "open"
  },
  "error": null
}</pre>
                <div class="check">
                    <strong>{{ __('After the response') }}:</strong>
                    {{ __('Copy data.ticket_id → paste into the ticket_id variable → Save.') }}
                </div>
            </article>

            <article class="step" id="step-9">
                <h2><span class="step-num">9</span> <span class="method get">GET</span> {{ __('Test GET List Support Tickets') }}</h2>
                <ol>
                    <li>{{ __('Open GET List Support Tickets.') }}</li>
                    <li>{{ __('Optional Params: page=1, per_page=20.') }}</li>
                    <li>{{ __('Click Send.') }}</li>
                </ol>
                <span class="copy-label">URL</span>
                <pre>@{{base_url}}/api/v1/partners/fizahub/businesses/@{{external_business_id}}/support-tickets</pre>
                <span class="copy-label">{{ __('Expected') }}</span>
                <pre>HTTP 200</pre>
                <span class="copy-label">{{ __('Sample response') }}</span>
                <pre>{
  "success": true,
  "data": {
    "items": [
      {
        "ticket_id": "secure-ticket-id",
        "subject": "Need help with FizaMKT Base",
        "status": "open"
      }
    ],
    "pagination": {
      "page": 1,
      "per_page": 20,
      "total": 1
    }
  },
  "error": null
}</pre>
                <div class="check">{{ __('Done when: the new ticket appears in the list.') }}</div>
            </article>

            <article class="step" id="step-10">
                <h2><span class="step-num">10</span> <span class="method get">GET</span> {{ __('Test GET Support Ticket Detail') }}</h2>
                <ol>
                    <li>{{ __('Open GET Support Ticket Detail.') }}</li>
                    <li>{{ __('Open Params and confirm external_business_id is present — this query is required.') }}</li>
                    <li>{{ __('Click Send.') }}</li>
                </ol>
                <span class="copy-label">URL</span>
                <pre>@{{base_url}}/api/v1/partners/fizahub/support-tickets/@{{ticket_id}}?external_business_id=@{{external_business_id}}</pre>
                <div class="note">
                    {{ __('external_business_id is a required query parameter. Missing it returns 422.') }}
                </div>
                <span class="copy-label">{{ __('Expected') }}</span>
                <pre>HTTP 200</pre>
                <span class="copy-label">{{ __('Sample response') }}</span>
                <pre>{
  "success": true,
  "data": {
    "ticket": {
      "ticket_id": "secure-ticket-id",
      "subject": "Need help with FizaMKT Base",
      "status": "open"
    },
    "messages": [
      {
        "message_id": "secure-ticket-id:initial",
        "sender_type": "business",
        "body": "Please help me create a check-in QR and Google review request."
      }
    ],
    "next_poll_after_seconds": 15
  },
  "error": null
}</pre>
                <div class="info">
                    <ul>
                        <li>{{ __('This is a polling API, not realtime.') }}</li>
                        <li>{{ __('The FizaHUB app may call again every 15–30 seconds while the chat screen is open.') }}</li>
                    </ul>
                </div>
                <div class="check">{{ __('Done when: conversation messages are visible.') }}</div>
            </article>

            <article class="step" id="step-11">
                <h2><span class="step-num">11</span> <span class="method post">POST</span> {{ __('Test POST Send Support Message') }}</h2>
                <ol>
                    <li>{{ __('Open POST Send Support Message.') }}</li>
                    <li>{{ __('Keep the external_business_id query as-is — this query is required.') }}</li>
                    <li>{{ __('Open Body → raw → JSON and paste the Body raw JSON below.') }}</li>
                    <li>{{ __('Click Send.') }}</li>
                    <li>{{ __('Open GET Support Ticket Detail again and click Send to see the new message.') }}</li>
                </ol>
                <span class="copy-label">URL</span>
                <pre>@{{base_url}}/api/v1/partners/fizahub/support-tickets/@{{ticket_id}}/messages?external_business_id=@{{external_business_id}}</pre>
                <span class="copy-label">Headers</span>
                <pre>@verbatim
Authorization: Bearer {{partner_token}}
X-Partner: fizahub
X-Request-Id: {{$guid}}
Idempotency-Key: {{$guid}}
Accept: application/json
Content-Type: application/json
@endverbatim</pre>
                <span class="copy-label">{{ __('Body raw JSON') }}</span>
                <pre>{
  "message": "Please prioritize the check-in QR first."
}</pre>
                <span class="copy-label">{{ __('Expected') }}</span>
                <pre>HTTP 201</pre>
                <span class="copy-label">{{ __('Sample response') }}</span>
                <pre>{
  "success": true,
  "data": {
    "message_id": "secure-message-id",
    "sender_type": "business",
    "body": "Please prioritize the check-in QR first."
  },
  "error": null
}</pre>
                <div class="check">{{ __('Done when: the new message appears in ticket detail.') }}</div>
            </article>

            <article class="step" id="step-12">
                <h2><span class="step-num">12</span> <span class="method post">POST</span> {{ __('Test POST One-time Login') }}</h2>
                <ol>
                    <li>{{ __('Open POST One-time Login.') }}</li>
                    <li>{{ __('Leave Body empty. No Body is required.') }}</li>
                    <li>{{ __('Click Send.') }}</li>
                </ol>
                <span class="copy-label">URL</span>
                <pre>@{{base_url}}/api/v1/partners/fizahub/businesses/@{{external_business_id}}/one-time-login</pre>
                <span class="copy-label">Headers</span>
                <pre>@verbatim
Authorization: Bearer {{partner_token}}
X-Partner: fizahub
X-Request-Id: {{$guid}}
Accept: application/json
@endverbatim</pre>
                <span class="copy-label">{{ __('Expected') }}</span>
                <pre>HTTP 201</pre>
                <span class="copy-label">{{ __('Sample response') }}</span>
                <pre>{
  "success": true,
  "data": {
    "url": "https://mlhub.vn/partners/fizahub/one-time-login/...",
    "expires_at": "2026-07-14T10:05:00Z"
  },
  "error": null
}</pre>
                <div class="info">
                    <ul>
                        <li>{{ __('Copy data.url and open it in a browser to enter the MLHUB Portal.') }}</li>
                        <li>{{ __('The link is single-use and expires in about 5 minutes.') }}</li>
                        <li>{{ __('Do not share this link with other people.') }}</li>
                    </ul>
                </div>
                <div class="check">{{ __('Done when: you received a one-time login URL.') }}</div>
            </article>

            <div class="panel" style="margin-top:1.2rem;" id="troubleshooting">
                <h2 style="margin:0 0 .6rem;font-size:1.1rem;">{{ __('Common errors') }}</h2>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ __('HTTP code') }}</th>
                                <th>{{ __('Error') }}</th>
                                <th>{{ __('Common cause') }}</th>
                                <th>{{ __('How to fix') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>401</td>
                                <td><code>invalid_partner_token</code></td>
                                <td>{{ __('partner_token is wrong or not set on the server.') }}</td>
                                <td>{{ __('Check the partner_token variable and the FIZAHUB_PARTNER_TOKEN ENV on the server.') }}</td>
                            </tr>
                            <tr>
                                <td>400</td>
                                <td><code>invalid_partner_header</code></td>
                                <td>{{ __('Missing X-Partner or X-Request-Id.') }}</td>
                                <td>{{ __('Check the request Headers.') }}</td>
                            </tr>
                            <tr>
                                <td>422</td>
                                <td><code>validation_failed</code></td>
                                <td>{{ __('Missing field, bad email/url/date, missing Idempotency-Key, or missing external_business_id on support detail/message.') }}</td>
                                <td>{{ __('Read error.details and fix Body/Params.') }}</td>
                            </tr>
                            <tr>
                                <td>404</td>
                                <td><code>integration_not_found</code></td>
                                <td>{{ __('external_business_id is not onboarded as completed yet.') }}</td>
                                <td>{{ __('Run POST Onboarding first and confirm status completed.') }}</td>
                            </tr>
                            <tr>
                                <td>409</td>
                                <td><code>idempotency_conflict</code></td>
                                <td>{{ __('Reused an old Idempotency-Key with a different Body.') }}</td>
                                <td>{{ __('Create a new key, or Send again with the same Body.') }}</td>
                            </tr>
                            <tr>
                                <td>429</td>
                                <td><code>rate_limit_exceeded</code></td>
                                <td>{{ __('Too many requests per minute.') }}</td>
                                <td>{{ __('Wait one minute and try again.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel" style="margin-top:1.2rem;">
                <h2 style="margin:0 0 .6rem;font-size:1.1rem;">{{ __('Finished checklist') }}</h2>
                <ul>
                    <li>{{ __('Health returned ok') }}</li>
                    <li>{{ __('Onboarding created/updated and request_id saved') }}</li>
                    <li>{{ __('Package and Dashboard returned for a completed mapping') }}</li>
                    <li>{{ __('Support ticket created, listed, detailed, and messaged') }}</li>
                    <li>{{ __('One-time login URL issued') }}</li>
                </ul>
                <div class="cta-row" style="margin-top:1rem;">
                    <a class="btn btn-primary" href="{{ $docsUrl }}">{{ __('Back to API docs') }}</a>
                    <a class="btn btn-ghost" href="{{ $postmanUrl }}">{{ __('Download Postman JSON again') }}</a>
                </div>
            </div>
        </div>
    </section>
</main>

<footer class="footer">
    <div class="wrap">
        <p style="margin:0;">{{ __('FizaHUB Partner API - Step-by-step Postman test guide') }} · <a href="{{ $docsUrl }}">/api-fizahub</a> · <a href="{{ route('partner.fizahub.docs.help-test') }}">/api-fizahub/help-test</a></p>
    </div>
</footer>
</body>
</html>
