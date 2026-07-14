<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('FizaHUB Postman step-by-step test guide') }}</title>
    <meta name="description" content="{{ __('Copy-and-paste Postman testing guide for non-technical users.') }}">
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
            --font: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
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
        .wrap { width: min(860px, calc(100% - 2rem)); margin: 0 auto; }
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
        .hero-card, .step, .panel {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 1.15rem 1.2rem;
        }
        h1 { margin: 0 0 .7rem; font-size: clamp(1.5rem, 4vw, 2.2rem); line-height: 1.2; }
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
            border-radius: 12px; padding: .9rem 1rem; font-size: .82rem; line-height: 1.45;
        }
        .copy-label {
            display: block; margin: .55rem 0 .25rem; font-size: .78rem;
            text-transform: uppercase; letter-spacing: .05em; color: var(--muted); font-weight: 700;
        }
        .check {
            margin-top: .7rem; padding: .7rem .85rem; border-radius: 10px;
            background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; font-size: .92rem;
        }
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
        </nav>
    </div>
</header>

<main>
    <section class="hero">
        <div class="wrap">
            <div class="hero-card">
                <h1>{{ __('FizaHUB Postman step-by-step test guide') }}</h1>
                <p class="lead">{{ __('This page is for non-technical testers. Follow every step from top to bottom. Copy and paste where shown. Do not skip steps.') }}</p>
                <div class="cta-row">
                    <a class="btn btn-primary" href="{{ $postmanUrl }}">{{ __('1. Download Postman JSON first') }}</a>
                    <a class="btn btn-ghost" href="{{ $docsUrl }}">{{ __('Back to API docs') }}</a>
                </div>
                <div class="note">
                    {{ __('Never paste a real partner token into chat, email, or public docs. Ask the MLHUB admin for a test token and keep it private.') }}
                </div>
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
partner_token = (paste the token given by MLHUB admin)
external_user_id = fh-user-demo-001
external_business_id = fh-biz-demo-001
from = 2026-06-14
to = 2026-07-13
onboarding_request_id = (leave empty for now)
ticket_id = (leave empty for now)</pre>
                <ol start="4">
                    <li>{{ __('Click Save.') }}</li>
                </ol>
                <div class="note">
                    {{ __('Important: partner_token must be the real test token from MLHUB. Do not invent it. Do not publish it.') }}
                </div>
                <div class="check">{{ __('Done when: base_url and partner_token are filled and saved.') }}</div>
            </article>

            <article class="step" id="step-3">
                <h2><span class="step-num">3</span> <span class="method get">GET</span> {{ __('Test GET Health') }}</h2>
                <ol>
                    <li>{{ __('Open the request named GET Health.') }}</li>
                    <li>{{ __('Click Send.') }}</li>
                    <li>{{ __('Look at the status code on the right. It should be 200.') }}</li>
                </ol>
                <span class="copy-label">{{ __('You should see JSON like this') }}</span>
                <pre>{
  "success": true,
  "data": {
    "status": "ok",
    "partner": "fizahub",
    "api_version": "v1"
  }
}</pre>
                <div class="check">{{ __('Done when: success is true and status is ok. If this fails, stop and check partner_token / base_url.') }}</div>
            </article>

            <article class="step" id="step-4">
                <h2><span class="step-num">4</span> <span class="method post">POST</span> {{ __('Test POST Onboarding') }}</h2>
                <ol>
                    <li>{{ __('Open the request named POST Onboarding.') }}</li>
                    <li>{{ __('Do not change headers. Postman already fills Authorization, X-Partner, X-Request-Id, and Idempotency-Key.') }}</li>
                    <li>{{ __('Optional: change email in the Body to a unique test email so it does not collide.') }}</li>
                    <li>{{ __('Click Send.') }}</li>
                    <li>{{ __('Expected status: 201 (completed) or 202 (pending_verification / needs_review).') }}</li>
                    <li>{{ __('Copy data.request_id from the response.') }}</li>
                    <li>{{ __('Paste it into collection variable onboarding_request_id, then Save.') }}</li>
                    <li>{{ __('If status is completed, keep external_business_id as the same value used in the request body.') }}</li>
                </ol>
                <div class="note">
                    {{ __('If you get needs_review or pending_verification, Package/Dashboard/Login may return 404 until mapping is completed. Continue Health/Onboarding Status first, then ask MLHUB to complete review.') }}
                </div>
                <div class="check">{{ __('Done when: you saved onboarding_request_id.') }}</div>
            </article>

            <article class="step" id="step-5">
                <h2><span class="step-num">5</span> <span class="method get">GET</span> {{ __('Test GET Onboarding Status') }}</h2>
                <ol>
                    <li>{{ __('Open GET Onboarding Status.') }}</li>
                    <li>{{ __('Make sure onboarding_request_id variable is filled.') }}</li>
                    <li>{{ __('Click Send.') }}</li>
                    <li>{{ __('Expected status: 200.') }}</li>
                    <li>{{ __('Check data.status: pending_verification, needs_review, or completed.') }}</li>
                </ol>
                <div class="check">{{ __('Done when: status is returned and matches the previous onboarding result.') }}</div>
            </article>

            <article class="step" id="step-6">
                <h2><span class="step-num">6</span> <span class="method get">GET</span> {{ __('Test GET Package') }}</h2>
                <ol>
                    <li>{{ __('Only run this after onboarding status is completed.') }}</li>
                    <li>{{ __('Open GET Package.') }}</li>
                    <li>{{ __('Click Send.') }}</li>
                    <li>{{ __('Expected status: 200.') }}</li>
                    <li>{{ __('You should see package_code, plan_slug, and limits. You should NOT see price or credits.') }}</li>
                </ol>
                <div class="check">{{ __('Done when: package summary is returned without price/credits.') }}</div>
            </article>

            <article class="step" id="step-7">
                <h2><span class="step-num">7</span> <span class="method get">GET</span> {{ __('Test GET Dashboard') }}</h2>
                <ol>
                    <li>{{ __('Open GET Dashboard.') }}</li>
                    <li>{{ __('from and to are already filled by variables. You may leave them as-is.') }}</li>
                    <li>{{ __('Click Send.') }}</li>
                    <li>{{ __('Expected status: 200.') }}</li>
                    <li>{{ __('You should see data.metrics with qr_scans, new_leads, new_reviews, returning_customers, and conversion_rate.') }}</li>
                </ol>
                <div class="check">{{ __('Done when: metrics object is present even if all numbers are zero.') }}</div>
            </article>

            <article class="step" id="step-8">
                <h2><span class="step-num">8</span> <span class="method post">POST</span> {{ __('Test POST Create Support Ticket') }}</h2>
                <ol>
                    <li>{{ __('Open POST Create Support Ticket.') }}</li>
                    <li>{{ __('Body already has subject and message. You can edit the text if you want.') }}</li>
                    <li>{{ __('Click Send.') }}</li>
                    <li>{{ __('Expected status: 201.') }}</li>
                    <li>{{ __('Copy data.ticket_id from the response.') }}</li>
                    <li>{{ __('Paste it into collection variable ticket_id, then Save.') }}</li>
                </ol>
                <div class="check">{{ __('Done when: ticket_id is saved.') }}</div>
            </article>

            <article class="step" id="step-9">
                <h2><span class="step-num">9</span> <span class="method get">GET</span> {{ __('Test GET List Support Tickets') }}</h2>
                <ol>
                    <li>{{ __('Open GET List Support Tickets.') }}</li>
                    <li>{{ __('Click Send.') }}</li>
                    <li>{{ __('Expected status: 200.') }}</li>
                    <li>{{ __('You should see data.items and your new ticket in the list.') }}</li>
                </ol>
                <div class="check">{{ __('Done when: the new ticket appears in the list.') }}</div>
            </article>

            <article class="step" id="step-10">
                <h2><span class="step-num">10</span> <span class="method get">GET</span> {{ __('Test GET Support Ticket Detail') }}</h2>
                <ol>
                    <li>{{ __('Open GET Support Ticket Detail.') }}</li>
                    <li>{{ __('Confirm ticket_id and external_business_id variables are filled.') }}</li>
                    <li>{{ __('This request already includes ?external_business_id=... Do not remove it.') }}</li>
                    <li>{{ __('Click Send.') }}</li>
                    <li>{{ __('Expected status: 200.') }}</li>
                    <li>{{ __('You should see messages and next_poll_after_seconds.') }}</li>
                </ol>
                <div class="check">{{ __('Done when: conversation messages are visible.') }}</div>
            </article>

            <article class="step" id="step-11">
                <h2><span class="step-num">11</span> <span class="method post">POST</span> {{ __('Test POST Send Support Message') }}</h2>
                <ol>
                    <li>{{ __('Open POST Send Support Message.') }}</li>
                    <li>{{ __('Body already has a message. You can edit it.') }}</li>
                    <li>{{ __('Keep the external_business_id query as-is.') }}</li>
                    <li>{{ __('Click Send.') }}</li>
                    <li>{{ __('Expected status: 201.') }}</li>
                    <li>{{ __('Open GET Support Ticket Detail again and click Send to see the new message.') }}</li>
                </ol>
                <div class="check">{{ __('Done when: the new message appears in ticket detail.') }}</div>
            </article>

            <article class="step" id="step-12">
                <h2><span class="step-num">12</span> <span class="method post">POST</span> {{ __('Test POST One-time Login') }}</h2>
                <ol>
                    <li>{{ __('Open POST One-time Login.') }}</li>
                    <li>{{ __('Click Send.') }}</li>
                    <li>{{ __('Expected status: 201.') }}</li>
                    <li>{{ __('Response should include data.url and data.expires_at.') }}</li>
                    <li>{{ __('Optional: open data.url in a private browser window to confirm portal login works once.') }}</li>
                    <li>{{ __('Do not reuse the same URL. It is single-use and expires quickly (about 5 minutes).') }}</li>
                </ol>
                <div class="check">{{ __('Done when: you received a one-time login URL.') }}</div>
            </article>

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
        <p style="margin:0;">{{ __('FizaHUB Postman step-by-step test guide') }} · <a href="{{ $docsUrl }}">/api-fizahub</a> · <a href="{{ route('partner.fizahub.docs.help-test') }}">/api-fizahub/help-test</a></p>
    </div>
</footer>
</body>
</html>
