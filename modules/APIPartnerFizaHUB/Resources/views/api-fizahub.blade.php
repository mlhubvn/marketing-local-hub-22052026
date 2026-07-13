<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('MLHUB × FizaHUB Partner API') }}</title>
    <meta name="description" content="{{ __('Technical MVP documentation for connecting the FizaHUB App with MLHUB/FizaMKT.') }}">
    <style>
        :root {
            --bg: #f4f7f5;
            --surface: #ffffff;
            --ink: #14201b;
            --muted: #5b6b63;
            --line: #d7e0db;
            --brand: #0f766e;
            --brand-dark: #0b5f59;
            --accent: #c45c26;
            --code-bg: #0f1c18;
            --code-ink: #d7efe7;
            --ok: #166534;
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
        .wrap { width: min(1120px, calc(100% - 2rem)); margin: 0 auto; }
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
        .brand { font-weight: 800; letter-spacing: .02em; color: var(--brand-dark); text-decoration: none; }
        .nav { display: flex; flex-wrap: wrap; gap: .55rem; }
        .nav a {
            text-decoration: none; color: var(--muted); font-size: .9rem;
            padding: .35rem .65rem; border-radius: 999px; border: 1px solid transparent;
        }
        .nav a:hover { color: var(--ink); border-color: var(--line); background: #fff; }
        .hero {
            padding: 2.6rem 0 1.4rem;
        }
        .hero-card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: calc(var(--radius) + 4px);
            box-shadow: var(--shadow);
            padding: clamp(1.25rem, 3vw, 2.2rem);
        }
        .hero h1 {
            margin: 0 0 .75rem;
            font-size: clamp(1.7rem, 4vw, 2.6rem);
            line-height: 1.15;
            letter-spacing: -.02em;
        }
        .hero .lead {
            margin: 0 0 1.25rem;
            color: var(--muted);
            max-width: 62ch;
            font-size: 1.05rem;
        }
        .cta-row, .badge-row { display: flex; flex-wrap: wrap; gap: .6rem; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center;
            gap: .4rem; text-decoration: none; border-radius: 999px;
            padding: .7rem 1.05rem; font-weight: 700; font-size: .92rem;
            border: 1px solid transparent;
        }
        .btn-primary { background: var(--brand); color: #fff; }
        .btn-primary:hover { background: var(--brand-dark); }
        .btn-ghost { background: #fff; color: var(--ink); border-color: var(--line); }
        .btn-ghost:hover { border-color: var(--brand); color: var(--brand-dark); }
        .badge {
            display: inline-flex; align-items: center; gap: .35rem;
            border: 1px solid var(--line); background: #f8fbf9;
            color: var(--muted); border-radius: 999px;
            padding: .35rem .7rem; font-size: .82rem;
        }
        .badge strong { color: var(--ink); font-weight: 700; }
        section { padding: 1.6rem 0; }
        .section-title {
            margin: 0 0 .85rem;
            font-size: clamp(1.25rem, 2.4vw, 1.7rem);
            letter-spacing: -.01em;
        }
        .section-intro { color: var(--muted); margin: 0 0 1rem; max-width: 70ch; }
        .panel {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 1.1rem 1.15rem;
            box-shadow: 0 4px 16px rgba(20, 32, 27, .04);
        }
        .grid-2 { display: grid; gap: 1rem; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .grid-4 { display: grid; gap: 1rem; grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .list-clean { margin: 0; padding-left: 1.1rem; }
        .list-clean li { margin: .35rem 0; }
        .muted { color: var(--muted); }
        .note {
            margin-top: 1rem; padding: .85rem 1rem; border-radius: 12px;
            background: #fff7ed; border: 1px solid #fed7aa; color: var(--warn);
            font-size: .92rem;
        }
        .flow {
            display: grid; gap: .85rem; justify-items: center; margin: 1rem 0;
        }
        .flow-box {
            width: min(100%, 720px);
            border-radius: 16px; padding: 1rem 1.1rem;
            border: 1px solid var(--line); background: #fff;
        }
        .flow-box h3 { margin: 0 0 .55rem; font-size: 1rem; }
        .flow-arrow {
            width: 2px; height: 28px; background: var(--brand);
            position: relative;
        }
        .flow-arrow::after {
            content: ""; position: absolute; left: -5px; bottom: -2px;
            border-left: 6px solid transparent; border-right: 6px solid transparent;
            border-top: 8px solid var(--brand);
        }
        .chip {
            display: inline-block; margin: .2rem .25rem .2rem 0;
            padding: .25rem .55rem; border-radius: 999px;
            background: #ecfdf5; color: var(--brand-dark); font-size: .8rem; border: 1px solid #a7f3d0;
        }
        .card-flow h3 { margin: 0 0 .5rem; font-size: 1.02rem; }
        .card-flow ol { margin: 0; padding-left: 1.15rem; }
        .card-flow li { margin: .3rem 0; }
        pre, code { font-family: var(--mono); }
        pre {
            margin: 0; overflow: auto; background: var(--code-bg); color: var(--code-ink);
            border-radius: 12px; padding: 1rem; font-size: .82rem; line-height: 1.45;
        }
        .code-label {
            display: inline-block; margin-bottom: .45rem; font-size: .78rem;
            text-transform: uppercase; letter-spacing: .06em; color: var(--muted); font-weight: 700;
        }
        .endpoint {
            border: 1px solid var(--line); border-radius: var(--radius);
            background: #fff; margin: 0 0 1rem; overflow: hidden;
        }
        .endpoint-head {
            display: flex; flex-wrap: wrap; gap: .55rem; align-items: center;
            padding: .85rem 1rem; border-bottom: 1px solid var(--line); background: #f8fbf9;
        }
        .endpoint-body { padding: 1rem; }
        .method {
            font-family: var(--mono); font-size: .78rem; font-weight: 800;
            padding: .25rem .5rem; border-radius: 8px; color: #fff;
        }
        .method.get { background: var(--get); }
        .method.post { background: var(--post); }
        .path { font-family: var(--mono); font-size: .9rem; word-break: break-all; }
        table {
            width: 100%; border-collapse: collapse; background: #fff;
            border: 1px solid var(--line); border-radius: 12px; overflow: hidden;
            font-size: .9rem;
        }
        th, td {
            text-align: left; vertical-align: top; padding: .7rem .75rem;
            border-bottom: 1px solid var(--line);
        }
        th { background: #f1f6f3; font-size: .82rem; letter-spacing: .02em; }
        tr:last-child td { border-bottom: 0; }
        .table-wrap { overflow-x: auto; border-radius: 12px; }
        .sensitive-high { color: #991b1b; font-weight: 700; }
        .sensitive-med { color: #9a3412; font-weight: 600; }
        .footer {
            padding: 2rem 0 2.5rem; color: var(--muted); font-size: .9rem;
            border-top: 1px solid var(--line); margin-top: 1rem;
        }
        .postman-hero {
            display: flex; flex-wrap: wrap; gap: 1rem; align-items: center;
            justify-content: space-between;
        }
        @media (max-width: 900px) {
            .grid-2, .grid-4 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<header class="topbar">
    <div class="wrap topbar-inner">
        <a class="brand" href="{{ route('partner.fizahub.docs') }}">MLHUB × FizaHUB</a>
        <nav class="nav" aria-label="{{ __('Documentation sections') }}">
            <a href="#mvp-scope">{{ __('MVP scope') }}</a>
            <a href="#architecture">{{ __('Architecture') }}</a>
            <a href="#endpoints">{{ __('Endpoints') }}</a>
            <a href="#postman">{{ __('Postman') }}</a>
            <a href="#function-reference">{{ __('Function reference') }}</a>
            <a href="#data-dictionary">{{ __('Data dictionary') }}</a>
            <a href="#security">{{ __('Security') }}</a>
        </nav>
    </div>
</header>

<main>
    <section class="hero">
        <div class="wrap">
            <div class="hero-card">
                <h1>{{ __('MLHUB × FizaHUB Partner API') }}</h1>
                <p class="lead">{{ __('Technical MVP documentation for connecting the FizaHUB App with MLHUB/FizaMKT: business onboarding, support operations, growth dashboard, and one-time login into the MLHUB Portal.') }}</p>
                <div class="cta-row" style="margin-bottom:1rem;">
                    <a class="btn btn-primary" href="{{ $postmanUrl }}">{{ __('Download Postman JSON') }}</a>
                    <a class="btn btn-ghost" href="#health-check">{{ __('View Health Check') }}</a>
                    <a class="btn btn-ghost" href="#endpoints">{{ __('View 10 MVP endpoints') }}</a>
                    <a class="btn btn-ghost" href="#data-dictionary">{{ __('View data field table') }}</a>
                </div>
                <div class="badge-row">
                    <span class="badge"><strong>{{ __('API Version') }}:</strong> v1</span>
                    <span class="badge"><strong>{{ __('Partner') }}:</strong> fizahub</span>
                    <span class="badge"><strong>{{ __('Base URL') }}:</strong> {{ $baseUrl }}</span>
                    <span class="badge"><strong>{{ __('Auth') }}:</strong> Bearer Token</span>
                    <span class="badge"><strong>{{ __('Status') }}:</strong> {{ __('MVP Integration Ready') }}</span>
                </div>
            </div>
        </div>
    </section>

    <section id="mvp-scope">
        <div class="wrap">
            <h2 class="section-title">{{ __('MVP scope') }}</h2>
            <div class="grid-2">
                <div class="panel">
                    <h3>{{ __('What FizaHUB does') }}</h3>
                    <p class="muted">{{ __('FizaHUB does not control all of MLHUB through the API. FizaHUB is the entry door for local businesses:') }}</p>
                    <ul class="list-clean">
                        <li>{{ __('Register to use FizaMKT/MLHUB') }}</li>
                        <li>{{ __('View the current package') }}</li>
                        <li>{{ __('View growth dashboard results') }}</li>
                        <li>{{ __('Send support requests') }}</li>
                        <li>{{ __('Exchange messages with the MLHUB/FizaMKT team via tickets') }}</li>
                        <li>{{ __('Open the MLHUB Portal with one-time login when deeper actions are needed') }}</li>
                    </ul>
                </div>
                <div class="panel">
                    <h3>{{ __('What MLHUB/FizaMKT handles') }}</h3>
                    <ul class="list-clean">
                        <li>{{ __('Create/update business profiles') }}</li>
                        <li>{{ __('Create workspace/team') }}</li>
                        <li>{{ __('Operate support tickets') }}</li>
                        <li>{{ __('Aggregate QR/Lead/Review/Coupon/Booking data') }}</li>
                        <li>{{ __('Provide the dashboard') }}</li>
                        <li>{{ __('Allow portal access through a single-use link') }}</li>
                    </ul>
                    <div class="note">
                        {{ __('MVP exclusions: Customer CRUD API, Chat AI API, AI Studio API, Campaign/Coupon/Landing Page creation API, Google Business direct API, Revenue/Cost/Credit API, and CCCD/GPKD file upload.') }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="architecture">
        <div class="wrap">
            <h2 class="section-title">{{ __('Technical architecture diagram') }}</h2>
            <p class="section-intro">{{ __('Sơ đồ kỹ thuật') }} — {{ __('How FizaHUB, the partner adapter, and MLHUB core connect over HTTPS with Bearer authentication.') }}</p>
            <div class="flow" aria-label="{{ __('Technical architecture diagram') }}">
                <div class="flow-box">
                    <h3>[FizaHUB App]</h3>
                    <span class="chip">{{ __('Business owner login with phone/KYC') }}</span>
                    <span class="chip">{{ __('Register FizaMKT') }}</span>
                    <span class="chip">{{ __('View dashboard') }}</span>
                    <span class="chip">{{ __('Send support') }}</span>
                </div>
                <div class="flow-arrow" aria-hidden="true"></div>
                <div class="muted" style="font-family:var(--mono);font-size:.85rem;">HTTPS + Bearer Token</div>
                <div class="flow-arrow" aria-hidden="true"></div>
                <div class="flow-box">
                    <h3>[APIPartnerFizaHUB Adapter]</h3>
                    <span class="chip">VerifyPartnerToken</span>
                    <span class="chip">X-Request-Id</span>
                    <span class="chip">Idempotency-Key</span>
                    <span class="chip">Partner Mapping</span>
                    <span class="chip">Audit Log + Redaction</span>
                    <span class="chip">API Response Formatter</span>
                </div>
                <div class="flow-arrow" aria-hidden="true"></div>
                <div class="muted" style="font-family:var(--mono);font-size:.85rem;">Internal Services</div>
                <div class="flow-arrow" aria-hidden="true"></div>
                <div class="flow-box">
                    <h3>[MLHUB Core]</h3>
                    <span class="chip">User</span>
                    <span class="chip">Team/Workspace</span>
                    <span class="chip">LocalBusiness / lb_businesses</span>
                    <span class="chip">SupportTicket / SupportComment</span>
                    <span class="chip">QR / Lead / ReviewFeedback / Coupon / Booking</span>
                    <span class="chip">One-time Login</span>
                    <span class="chip">Dashboard Service</span>
                </div>
            </div>
            <div class="note">
                <ul class="list-clean">
                    <li><code>external_business_id</code> {{ __('is the main technical key between FizaHUB and MLHUB.') }}</li>
                    <li><code>business_license_number</code> / <code>tax_code</code> {{ __('are used only for duplicate checks and legal verification.') }}</li>
                    <li>{{ __('CCCD/identity files are not stored by the MLHUB Partner API.') }}</li>
                </ul>
            </div>
        </div>
    </section>

    <section id="operating-model">
        <div class="wrap">
            <h2 class="section-title">{{ __('Mô hình hoạt động') }}</h2>
            <p class="section-intro">{{ __('Four primary operating flows in the MVP integration.') }}</p>
            <div class="grid-2">
                <article class="panel card-flow">
                    <h3>1. {{ __('Khởi tạo tài khoản') }} / Onboarding</h3>
                    <ol>
                        <li>{{ __('FizaHUB verifies the business owner.') }}</li>
                        <li>{{ __('FizaHUB sends an onboarding request.') }}</li>
                        <li>{{ __('MLHUB checks duplicate email/tax code/business license.') }}</li>
                        <li>{{ __('If valid: create user, team/workspace, business, and partner mapping.') }}</li>
                        <li>{{ __('If verification is missing or data collides: create a support ticket for the MLHUB/FizaMKT team.') }}</li>
                    </ol>
                </article>
                <article class="panel card-flow">
                    <h3>2. {{ __('Hỗ trợ vận hành') }} / Support Ticket</h3>
                    <ol>
                        <li>{{ __('The business owner sends a request from the FizaHUB app.') }}</li>
                        <li>{{ __('The API creates a support ticket in MLHUB admin/support.') }}</li>
                        <li>{{ __('MLHUB/FizaMKT admins reply in admin/support.') }}</li>
                        <li>{{ __('FizaHUB retrieves the conversation through API polling.') }}</li>
                    </ol>
                </article>
                <article class="panel card-flow">
                    <h3>3. {{ __('Dashboard tăng trưởng') }} / Growth Dashboard</h3>
                    <ol>
                        <li>{{ __('FizaHUB calls dashboard by external_business_id and date range.') }}</li>
                        <li>{{ __('MLHUB aggregates QR, lead, review feedback, coupon, and booking data.') }}</li>
                        <li>{{ __('The API returns metrics, campaign list, trend, insights, and suggested_actions.') }}</li>
                        <li>{{ __('FizaHUB renders the result screen for the business owner.') }}</li>
                    </ol>
                </article>
                <article class="panel card-flow">
                    <h3>4. One-time Login</h3>
                    <ol>
                        <li>{{ __('FizaHUB calls the API to create a one-time login URL.') }}</li>
                        <li>{{ __('MLHUB creates a token hash, 5-minute TTL, single-use.') }}</li>
                        <li>{{ __('The business owner opens the MLHUB Portal.') }}</li>
                        <li>{{ __('After use the token is marked used_at and cannot be reused.') }}</li>
                    </ol>
                </article>
            </div>
        </div>
    </section>

    <section id="auth">
        <div class="wrap">
            <h2 class="section-title">{{ __('Authentication / Headers') }}</h2>
            <div class="panel">
                <div class="code-label">{{ __('Base URL') }}</div>
                <pre>{{ $baseUrl }}</pre>
                <div class="code-label" style="margin-top:1rem;">{{ __('Required headers') }}</div>
                <pre>@verbatim
Authorization: Bearer {{partner_token}}
X-Partner: fizahub
X-Request-Id: {{$guid}}
Accept: application/json
Content-Type: application/json
@endverbatim</pre>
                <p style="margin:1rem 0 .4rem;"><strong>{{ __('Idempotency-Key required for:') }}</strong></p>
                <ul class="list-clean">
                    <li><code>POST /onboarding-requests</code></li>
                    <li><code>POST /businesses/{external_business_id}/support-tickets</code></li>
                    <li><code>POST /support-tickets/{ticket_id}/messages</code></li>
                </ul>
                <div class="note">
                    {{ __('X-Request-Id must be a UUID. Idempotency-Key prevents duplicate users/tickets/messages. Never publish a real partner_token in public documentation.') }}
                </div>
            </div>
        </div>
    </section>

    <section id="response-format">
        <div class="wrap">
            <h2 class="section-title">{{ __('Response format') }}</h2>
            <div class="grid-2">
                <div>
                    <div class="code-label">{{ __('Success response') }}</div>
                    <pre>{
  "success": true,
  "data": {},
  "meta": {
    "request_id": "018f5a64-b40b-7f60-a925-dea047cf6590"
  },
  "error": null
}</pre>
                </div>
                <div>
                    <div class="code-label">{{ __('Error response') }}</div>
                    <pre>{
  "success": false,
  "data": null,
  "meta": {
    "request_id": "018f5a64-b40b-7f60-a925-dea047cf6590"
  },
  "error": {
    "code": "validation_failed",
    "message": "The given data was invalid.",
    "details": {}
  }
}</pre>
                </div>
            </div>
            <div class="panel" style="margin-top:1rem;">
                <strong>{{ __('Error codes') }}:</strong>
                <div style="margin-top:.6rem;">
                    @foreach ([
                        'invalid_partner_header',
                        'invalid_partner_token',
                        'integration_not_found',
                        'resource_not_found',
                        'idempotency_conflict',
                        'idempotency_in_progress',
                        'validation_failed',
                        'rate_limit_exceeded',
                        'ticket_not_open',
                        'partner_api_error',
                    ] as $code)
                        <span class="chip"><code>{{ $code }}</code></span>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section id="endpoints">
        <div class="wrap">
            <h2 class="section-title">{{ __('10 MVP endpoints') }}</h2>
            <p class="section-intro">{{ __('All paths below are relative to the partner API base prefix.') }}</p>

            <article class="endpoint" id="health-check">
                <div class="endpoint-head">
                    <span class="method get">GET</span>
                    <span class="path">/health</span>
                    <strong>{{ __('Health Check') }}</strong>
                </div>
                <div class="endpoint-body">
                    <p>{{ __('Verify API connectivity, auth headers, partner identity, and version.') }}</p>
                    <div class="code-label">{{ __('Response data') }}</div>
                    <pre>{
  "status": "ok",
  "partner": "fizahub",
  "api_version": "v1",
  "server_time": "..."
}</pre>
                </div>
            </article>

            <article class="endpoint">
                <div class="endpoint-head">
                    <span class="method post">POST</span>
                    <span class="path">/onboarding-requests</span>
                    <strong>{{ __('Khởi tạo tài khoản') }} / Onboarding</strong>
                </div>
                <div class="endpoint-body">
                    <p>{{ __('FizaHUB submits a registration request for MLHUB/FizaMKT for a local business.') }}</p>
                    <p class="muted">{{ __('Requires Idempotency-Key. Status values: pending_verification, needs_review, completed.') }}</p>
                    <div class="code-label">{{ __('Sample body') }}</div>
                    <pre>{
  "external_user_id": "fizahub_user_123",
  "external_business_id": "fizahub_business_456",
  "package_code": "base",
  "owner": {
    "name": "Nguyen Van A",
    "phone": "0912345678",
    "email": "owner@example.com"
  },
  "business": {
    "name": "Fiza Store",
    "industry": "restaurant_food",
    "address": "123 Nguyen Trai, Da Nang",
    "phone": "0912345678",
    "email": "store@example.com",
    "tax_code": null,
    "business_license_number": "HKD-123456"
  },
  "verification": {
    "identity_verified": true,
    "verified_by": "fizahub",
    "verified_at": "2026-07-13T10:00:00+07:00"
  }
}</pre>
                </div>
            </article>

            <article class="endpoint">
                <div class="endpoint-head">
                    <span class="method get">GET</span>
                    <span class="path">/onboarding-requests/{request_id}</span>
                    <strong>{{ __('Onboarding status') }}</strong>
                </div>
                <div class="endpoint-body">
                    <p>{{ __('FizaHUB checks which step the registration request has reached.') }}</p>
                </div>
            </article>

            <article class="endpoint">
                <div class="endpoint-head">
                    <span class="method get">GET</span>
                    <span class="path">/businesses/{external_business_id}/package</span>
                    <strong>{{ __('Package') }}</strong>
                </div>
                <div class="endpoint-body">
                    <p>{{ __('View the current MLHUB/FizaMKT package for the business.') }}</p>
                    <p><strong>{{ __('Returns') }}:</strong> package_code, package_name, plan_slug, status, starts_at, expires_at, is_trial, limits whitelist.</p>
                    <p class="muted">{{ __('Does not return price, payment, subscription, credits, or full permissions JSON.') }}</p>
                </div>
            </article>

            <article class="endpoint">
                <div class="endpoint-head">
                    <span class="method get">GET</span>
                    <span class="path">/businesses/{external_business_id}/dashboard?from=YYYY-MM-DD&amp;to=YYYY-MM-DD</span>
                    <strong>{{ __('Dashboard tăng trưởng') }}</strong>
                </div>
                <div class="endpoint-body">
                    <p>{{ __('Returns overview data so the FizaHUB app can display marketing results.') }}</p>
                    <p><strong>{{ __('Metrics') }}:</strong> businesses, campaigns, active_campaigns, qr_scans, new_leads, new_reviews, coupon_claims, coupon_used, bookings, feedback, returning_customers, conversion_rate.</p>
                    <ul class="list-clean muted">
                        <li>{{ __('new_reviews definition (MVP): internal review feedback with rating >= 4, not live Google Reviews.') }}</li>
                        <li>{{ __('returning_customers estimated: phone/email identities appearing in at least 2 events in the period.') }}</li>
                        <li>{{ __('Default date range: last 30 days. Timezone: Asia/Ho_Chi_Minh.') }}</li>
                    </ul>
                </div>
            </article>

            <article class="endpoint">
                <div class="endpoint-head">
                    <span class="method post">POST</span>
                    <span class="path">/businesses/{external_business_id}/support-tickets</span>
                    <strong>{{ __('Create support ticket') }}</strong>
                </div>
                <div class="endpoint-body">
                    <p>{{ __('Business owners send support requests from the FizaHUB app.') }}</p>
                    <div class="code-label">{{ __('Sample body') }}</div>
                    <pre>{
  "subject": "Need help with FizaMKT Base",
  "message": "Please help me create a check-in QR and review flow."
}</pre>
                    <p class="muted">{{ __('Optional fields in the current API: category_id, type_id. priority is not accepted by the MVP validator.') }}</p>
                </div>
            </article>

            <article class="endpoint">
                <div class="endpoint-head">
                    <span class="method get">GET</span>
                    <span class="path">/businesses/{external_business_id}/support-tickets</span>
                    <strong>{{ __('List support tickets') }}</strong>
                </div>
                <div class="endpoint-body">
                    <p>{{ __('Returns the support request list for the mapped business.') }}</p>
                </div>
            </article>

            <article class="endpoint">
                <div class="endpoint-head">
                    <span class="method get">GET</span>
                    <span class="path">/support-tickets/{ticket_id}?external_business_id={external_business_id}</span>
                    <strong>{{ __('Ticket detail + conversation') }}</strong>
                </div>
                <div class="endpoint-body">
                    <p>{{ __('Returns ticket detail and messages.') }}</p>
                    <ul class="list-clean muted">
                        <li>{{ __('external_business_id is currently a required query parameter for tenant isolation.') }}</li>
                        <li>{{ __('If the ticket does not belong to the business, the API returns 404.') }}</li>
                        <li>{{ __('MVP uses polling (about 15–30 seconds), not realtime.') }}</li>
                    </ul>
                </div>
            </article>

            <article class="endpoint">
                <div class="endpoint-head">
                    <span class="method post">POST</span>
                    <span class="path">/support-tickets/{ticket_id}/messages?external_business_id={external_business_id}</span>
                    <strong>{{ __('Send support message') }}</strong>
                </div>
                <div class="endpoint-body">
                    <div class="code-label">{{ __('Sample body') }}</div>
                    <pre>{
  "message": "Please prioritize the check-in QR first."
}</pre>
                    <ul class="list-clean muted">
                        <li>{{ __('HTML is stripped. Max 5000 characters.') }}</li>
                        <li>{{ __('Closed/resolved tickets reject new messages.') }}</li>
                        <li>{{ __('Idempotency-Key is required.') }}</li>
                    </ul>
                </div>
            </article>

            <article class="endpoint">
                <div class="endpoint-head">
                    <span class="method post">POST</span>
                    <span class="path">/businesses/{external_business_id}/one-time-login</span>
                    <strong>One-time Login</strong>
                </div>
                <div class="endpoint-body">
                    <p>{{ __('Creates a single-use login link into the MLHUB Portal.') }}</p>
                    <div class="code-label">{{ __('Actual response fields') }}</div>
                    <pre>{
  "url": "https://mlhub.vn/partners/fizahub/one-time-login/...",
  "expires_at": "..."
}</pre>
                    <ul class="list-clean muted">
                        <li>{{ __('Behavior: single-use token, default TTL 5 minutes, SHA-256 hash only in storage.') }}</li>
                        <li>{{ __('Does not grant admin rights. Login URL is redacted in audit logs.') }}</li>
                    </ul>
                </div>
            </article>
        </div>
    </section>

    <section id="postman">
        <div class="wrap">
            <h2 class="section-title">{{ __('Download Postman') }}</h2>
            <div class="panel postman-hero">
                <div>
                    <p style="margin:0 0 .55rem;">{{ __('Tải xuống Postman Collection JSON') }}</p>
                    <p class="muted" style="margin:0;">{{ __('Import into Postman, set variables, then test Health Check before onboarding and other flows.') }}</p>
                </div>
                <a class="btn btn-primary" href="{{ $postmanUrl }}" download>{{ __('Download Postman JSON') }}</a>
            </div>
            <ol class="list-clean" style="margin-top:1rem;">
                <li>{{ __('Open Postman.') }}</li>
                <li>{{ __('Import the JSON file.') }}</li>
                <li>{{ __('Set variables: base_url = https://mlhub.vn, partner_token = token issued by MLHUB, external_business_id / external_user_id from FizaHUB.') }}</li>
                <li>{{ __('Test Health Check first.') }}</li>
                <li>{{ __('Then test Onboarding, Package, Dashboard, Support, and One-time Login.') }}</li>
            </ol>
        </div>
    </section>

    <section id="app-screens">
        <div class="wrap">
            <h2 class="section-title">{{ __('Suggested FizaHUB app screens') }}</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('Screen') }}</th>
                            <th>{{ __('API used') }}</th>
                            <th>{{ __('Purpose') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ __('Register FizaMKT') }}</td>
                            <td>POST onboarding · GET onboarding status</td>
                            <td>{{ __('Create and track registration') }}</td>
                        </tr>
                        <tr>
                            <td>{{ __('Package') }}</td>
                            <td>GET package</td>
                            <td>{{ __('Show current plan limits') }}</td>
                        </tr>
                        <tr>
                            <td>{{ __('Dashboard tăng trưởng') }}</td>
                            <td>GET dashboard</td>
                            <td>{{ __('Show growth results') }}</td>
                        </tr>
                        <tr>
                            <td>{{ __('Support requests') }}</td>
                            <td>POST/GET support tickets</td>
                            <td>{{ __('Create and list support tickets') }}</td>
                        </tr>
                        <tr>
                            <td>{{ __('Request detail') }}</td>
                            <td>GET ticket detail · POST message</td>
                            <td>{{ __('Conversation polling') }}</td>
                        </tr>
                        <tr>
                            <td>{{ __('Open MLHUB') }}</td>
                            <td>POST one-time login</td>
                            <td>{{ __('Portal deep-link') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section id="function-reference">
        <div class="wrap">
            <h2 class="section-title">{{ __('Bảng tra cứu tên hàm') }}</h2>
            <p class="section-intro">{{ __('Function / service / controller reference based on the current APIPartnerFizaHUB module.') }}</p>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('Group') }}</th>
                            <th>{{ __('File / Class') }}</th>
                            <th>{{ __('Method') }}</th>
                            <th>{{ __('Description') }}</th>
                            <th>{{ __('Related endpoint') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Routing</td><td>Routes/api.php</td><td>route definitions</td><td>{{ __('Defines the 10 partner API endpoints.') }}</td><td>all API</td></tr>
                        <tr><td>Routing</td><td>Routes/web.php</td><td>partner.fizahub.login.consume</td><td>{{ __('Signed web route to consume one-time login.') }}</td><td>/partners/fizahub/one-time-login/{token}</td></tr>
                        <tr><td>Docs</td><td>Routes/web.php</td><td>partner.fizahub.docs</td><td>{{ __('Public documentation page.') }}</td><td>/api-fizahub</td></tr>
                        <tr><td>Auth</td><td>VerifyPartnerToken</td><td>handle()</td><td>{{ __('Validates Bearer token and X-Partner.') }}</td><td>all API</td></tr>
                        <tr><td>Lifecycle</td><td>HandlePartnerRequest</td><td>handle()</td><td>{{ __('Audit log, idempotency, exception formatting.') }}</td><td>all API</td></tr>
                        <tr><td>Response</td><td>PartnerApiResponse</td><td>success(), error()</td><td>{{ __('Normalizes JSON success/error responses.') }}</td><td>all API</td></tr>
                        <tr><td>Redaction</td><td>PartnerPayloadRedactor</td><td>redact()</td><td>{{ __('Redacts token, password, CCCD, identity files, login URL in logs.') }}</td><td>all API</td></tr>
                        <tr><td>Onboarding</td><td>OnboardingController</td><td>store(), show()</td><td>{{ __('Accepts onboarding requests and returns status.') }}</td><td>onboarding-requests</td></tr>
                        <tr><td>Onboarding</td><td>OnboardingService</td><td>upsert(), find()</td><td>{{ __('Duplicate checks; creates user/team/business/integration/support ticket.') }}</td><td>onboarding</td></tr>
                        <tr><td>Mapping</td><td>PartnerMappingService</td><td>detectDuplicates(), resolvePlan(), normalizeExternalId()</td><td>{{ __('Normalizes IDs and resolves package/industry/duplicates.') }}</td><td>onboarding</td></tr>
                        <tr><td>Mapping</td><td>SupportTicketBridge</td><td>findIntegrationOrFail()</td><td>{{ __('Resolves external_business_id to a mapped PartnerIntegration.') }}</td><td>package/dashboard/support/login</td></tr>
                        <tr><td>Support</td><td>SupportTicketController</td><td>store(), index(), show()</td><td>{{ __('Create/list/detail support tickets.') }}</td><td>support-tickets</td></tr>
                        <tr><td>Support</td><td>SupportMessageController</td><td>store()</td><td>{{ __('Sends a FizaHUB message into a ticket.') }}</td><td>messages</td></tr>
                        <tr><td>Support</td><td>SupportTicketBridge</td><td>create(), list(), detail(), addMessage()</td><td>{{ __('Bridge to existing support_tickets/support_comments.') }}</td><td>support APIs</td></tr>
                        <tr><td>Login</td><td>OneTimeLoginController</td><td>store()</td><td>{{ __('Creates a one-time login URL.') }}</td><td>one-time-login</td></tr>
                        <tr><td>Login</td><td>ConsumeOneTimeLoginController</td><td>__invoke()</td><td>{{ __('Consumes signed token, logs in user, marks used_at.') }}</td><td>web consume</td></tr>
                        <tr><td>Login</td><td>OneTimeLoginService</td><td>issue(), consume()</td><td>{{ __('Generates token, hash, TTL, single-use behavior.') }}</td><td>one-time-login</td></tr>
                        <tr><td>Package</td><td>PackageController</td><td>show()</td><td>{{ __('Returns package summary.') }}</td><td>package</td></tr>
                        <tr><td>Package</td><td>PackageService</td><td>forBusiness()</td><td>{{ __('Resolves plan and whitelisted limits.') }}</td><td>package</td></tr>
                        <tr><td>Dashboard</td><td>DashboardController</td><td>show()</td><td>{{ __('Returns growth dashboard overview.') }}</td><td>dashboard</td></tr>
                        <tr><td>Dashboard</td><td>DashboardService</td><td>summarize()</td><td>{{ __('Aggregates metrics, campaigns, trend, insights, suggested_actions.') }}</td><td>dashboard</td></tr>
                        <tr><td>Models</td><td>PartnerIntegration</td><td>—</td><td>{{ __('Stores FizaHUB business ↔ MLHUB user/team/business mapping.') }}</td><td>package/dashboard/support/login</td></tr>
                        <tr><td>Models</td><td>PartnerOnboardingRequest</td><td>—</td><td>{{ __('Stores onboarding request, status, duplicate_check, payload.') }}</td><td>onboarding</td></tr>
                        <tr><td>Models</td><td>PartnerApiLog</td><td>—</td><td>{{ __('Stores request/response audit logs and idempotency.') }}</td><td>all</td></tr>
                        <tr><td>Models</td><td>PartnerOneTimeLogin</td><td>—</td><td>{{ __('Stores token_hash, expires_at, used_at.') }}</td><td>one-time login</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section id="data-dictionary">
        <div class="wrap">
            <h2 class="section-title">{{ __('Bảng tra cứu trường dữ liệu') }}</h2>
            <p class="section-intro">{{ __('Data dictionary for the partner contract. Fields marked as not persisted are ignored by the current MVP onboarding service even if clients send them.') }}</p>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Field</th>
                            <th>Type</th>
                            <th>{{ __('Required') }}</th>
                            <th>{{ __('Source') }}</th>
                            <th>{{ __('Used for') }}</th>
                            <th>{{ __('Stored in') }}</th>
                            <th>{{ __('Sensitive?') }}</th>
                            <th>{{ __('Notes') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>external_user_id</td><td>string</td><td>{{ __('yes') }}</td><td>FizaHUB</td><td>{{ __('FizaHUB user ID') }}</td><td>partner_integrations, partner_onboarding_requests</td><td class="sensitive-med">low</td><td>{{ __('Not used as a direct MLHUB login.') }}</td></tr>
                        <tr><td>external_business_id</td><td>string</td><td>{{ __('yes') }}</td><td>FizaHUB</td><td>{{ __('Primary technical business key') }}</td><td>partner_integrations, partner_onboarding_requests</td><td class="sensitive-med">low/medium</td><td>{{ __('Used by package/dashboard/support/login.') }}</td></tr>
                        <tr><td>package_code</td><td>string</td><td>{{ __('yes') }}</td><td>FizaHUB</td><td>{{ __('Partner-facing package') }}</td><td>partner_integrations, partner_onboarding_requests</td><td>low</td><td>base → mlhub-free-da-nang</td></tr>
                        <tr><td>owner.name / phone / email</td><td>string</td><td>{{ __('yes') }}</td><td>FizaHUB</td><td>{{ __('Create MLHUB user / duplicate email check') }}</td><td>users, onboarding payload</td><td class="sensitive-med">medium</td><td>{{ __('Duplicate unmapped email becomes needs_review.') }}</td></tr>
                        <tr><td>business.name / industry / address</td><td>string</td><td>{{ __('name/industry yes; address optional') }}</td><td>FizaHUB</td><td>{{ __('Business profile + taxonomy') }}</td><td>lb_businesses, payload</td><td class="sensitive-med">medium</td><td>restaurant_food → restaurant_eatery</td></tr>
                        <tr><td>business.tax_code / business_license_number</td><td>string|null</td><td>{{ __('no') }}</td><td>FizaHUB</td><td>{{ __('Legal duplicate checks') }}</td><td>integration metadata / duplicate_check</td><td class="sensitive-high">medium/high</td><td>{{ __('No GPKD/CCCD file upload.') }}</td></tr>
                        <tr><td>verification.identity_verified</td><td>boolean</td><td>{{ __('yes') }}</td><td>FizaHUB</td><td>{{ __('Upstream verification flag') }}</td><td>verification_status</td><td class="sensitive-med">medium</td><td>{{ __('If true, email_verified_at may be set from verified_at or now().') }}</td></tr>
                        <tr><td>verification.verified_at / verified_by</td><td>datetime|string</td><td>{{ __('no') }}</td><td>FizaHUB</td><td>{{ __('Verification metadata') }}</td><td>verification_status</td><td>low</td><td>{{ __('Upstream FizaHUB verification, not MLHUB email-link verification.') }}</td></tr>
                        <tr><td>X-Request-Id / Idempotency-Key</td><td>UUID / string(&lt;=128)</td><td>{{ __('yes for traced/idempotent POSTs') }}</td><td>client</td><td>{{ __('Tracing and replay safety') }}</td><td>partner_api_logs</td><td>low</td><td>{{ __('Same key + same body replays the stored response.') }}</td></tr>
                        <tr><td>ticket_id</td><td>string</td><td>route</td><td>MLHUB</td><td>{{ __('Public secure ticket ID') }}</td><td>support_tickets.id_secure</td><td class="sensitive-med">medium</td><td>{{ __('Numeric IDs are not exposed.') }}</td></tr>
                        <tr><td>message</td><td>string</td><td>{{ __('yes') }}</td><td>FizaHUB</td><td>{{ __('Support message body') }}</td><td>support_comments</td><td class="sensitive-med">medium</td><td>{{ __('Strip HTML, max 5000 chars.') }}</td></tr>
                        <tr><td>from / to</td><td>date</td><td>{{ __('no') }}</td><td>FizaHUB</td><td>{{ __('Dashboard date range') }}</td><td>{{ __('not stored') }}</td><td>low</td><td>{{ __('Default 30 days, max 366 days.') }}</td></tr>
                        <tr><td>login url / token_hash</td><td>string</td><td>response / internal</td><td>MLHUB</td><td>{{ __('One-time portal login') }}</td><td>hash only in partner_one_time_logins</td><td class="sensitive-high">high</td><td>{{ __('Plain URL must not appear in audit logs.') }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section id="security">
        <div class="wrap">
            <h2 class="section-title">{{ __('Security notes') }}</h2>
            <div class="panel">
                <ul class="list-clean">
                    <li>{{ __('Store the partner token in ENV only.') }}</li>
                    <li>{{ __('Do not use demo tokens in production.') }}</li>
                    <li>{{ __('Do not put the partner token in a public frontend.') }}</li>
                    <li>{{ __('Call the API server-to-server or through the FizaHUB backend.') }}</li>
                    <li>{{ __('Use Idempotency-Key on critical POSTs to avoid duplicates.') }}</li>
                    <li>{{ __('Audit logs must redact token/password/CCCD/identity files/login URL.') }}</li>
                    <li>{{ __('Do not store raw CCCD.') }}</li>
                    <li>{{ __('Do not expose numeric internal IDs.') }}</li>
                    <li>{{ __('Wrong business mapping returns 404 to avoid information leaks.') }}</li>
                </ul>
            </div>
        </div>
    </section>
</main>

<footer class="footer">
    <div class="wrap">
        <p style="margin:0;">MLHUB × FizaHUB Partner API · {{ __('Public technical documentation') }} · <a href="{{ $postmanUrl }}">{{ __('Download Postman JSON') }}</a></p>
    </div>
</footer>
</body>
</html>
