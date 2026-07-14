<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('MLHUB × FizaHUB Partner API') }}</title>
    <meta name="description" content="{{ __('Partner API technical specification for FizaHUB developers: architecture, operating flows, and the 10 MVP endpoints.') }}">
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
        .hero { padding: 2.6rem 0 1.4rem; }
        .hero-card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: calc(var(--radius) + 4px);
            box-shadow: var(--shadow);
            padding: clamp(1.25rem, 3vw, 2.2rem);
        }
        .eyebrow {
            display: inline-block; margin: 0 0 .55rem; font-size: .78rem; font-weight: 800;
            letter-spacing: .08em; text-transform: uppercase; color: var(--brand-dark);
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
            max-width: 68ch;
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
        .section-intro { color: var(--muted); margin: 0 0 1rem; max-width: 75ch; }
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
        .info {
            margin-top: 1rem; padding: .85rem 1rem; border-radius: 12px;
            background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46;
            font-size: .92rem;
        }
        .chip {
            display: inline-block; margin: .2rem .25rem .2rem 0;
            padding: .25rem .55rem; border-radius: 999px;
            background: #ecfdf5; color: var(--brand-dark); font-size: .8rem; border: 1px solid #a7f3d0;
        }
        .arch {
            display: grid; gap: .75rem; grid-template-columns: 1fr auto 1fr auto 1fr;
            align-items: stretch; margin: 1rem 0;
        }
        .arch-box {
            border: 1px solid var(--line); border-radius: 16px; background: #fff;
            padding: 1rem 1.05rem; min-width: 0;
        }
        .arch-box h3 { margin: 0 0 .55rem; font-size: 1rem; }
        .arch-arrow {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: .35rem; color: var(--muted); font-family: var(--mono); font-size: .72rem;
            text-align: center; min-width: 4.5rem;
        }
        .arch-arrow::before {
            content: ""; width: 42px; height: 2px; background: var(--brand);
            box-shadow: 36px 0 0 var(--brand);
            position: relative;
        }
        .arch-arrow span {
            display: block; max-width: 7rem; line-height: 1.25;
        }
        .seq {
            display: grid; gap: .55rem; margin-top: .85rem;
        }
        .seq-step {
            display: grid; grid-template-columns: 2.1rem 1fr; gap: .7rem; align-items: start;
        }
        .seq-num {
            width: 2.1rem; height: 2.1rem; border-radius: 999px; background: var(--brand);
            color: #fff; font-weight: 800; display: inline-flex; align-items: center; justify-content: center;
            font-size: .85rem;
        }
        .seq-body {
            border: 1px solid var(--line); border-radius: 12px; background: #fff; padding: .75rem .9rem;
        }
        .seq-body strong { display: block; margin-bottom: .2rem; }
        .seq-meta {
            font-family: var(--mono); font-size: .78rem; color: var(--brand-dark); word-break: break-all;
        }
        .flow-card h3 { margin: 0 0 .35rem; font-size: 1.05rem; }
        .flow-card .endpoints-used { margin: .35rem 0 .55rem; }
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
        .quick-links { display: flex; flex-wrap: wrap; gap: .75rem 1.1rem; margin-top: .85rem; font-size: .92rem; }
        @media (max-width: 900px) {
            .grid-2, .grid-4 { grid-template-columns: 1fr; }
            .arch { grid-template-columns: 1fr; }
            .arch-arrow { flex-direction: row; min-height: 1.5rem; }
            .arch-arrow::before {
                width: 2px; height: 28px; box-shadow: none;
            }
        }
    </style>
</head>
<body>
<header class="topbar">
    <div class="wrap topbar-inner">
        <a class="brand" href="{{ route('partner.fizahub.docs') }}">MLHUB × FizaHUB</a>
        <nav class="nav" aria-label="{{ __('Documentation sections') }}">
            <a href="#spec-overview">{{ __('Spec overview') }}</a>
            <a href="#architecture">{{ __('Architecture') }}</a>
            <a href="#flows">{{ __('Operating flows') }}</a>
            <a href="#auth">{{ __('Auth') }}</a>
            <a href="#endpoints">{{ __('Endpoints') }}</a>
            <a href="#postman">{{ __('Postman') }}</a>
            <a href="#data-dictionary">{{ __('Data dictionary') }}</a>
            <a href="#security">{{ __('Security') }}</a>
            <a href="{{ $helpTestUrl }}">{{ __('Postman help test') }}</a>
        </nav>
    </div>
</header>

<main>
    <section class="hero">
        <div class="wrap">
            <div class="hero-card">
                <p class="eyebrow">{{ __('Partner API technical specification') }} · MVP</p>
                <h1>{{ __('MLHUB × FizaHUB Partner API') }}</h1>
                <p class="lead">{{ __('Technical contract for FizaHUB backend engineers: system architecture, four operating flows, authentication headers, response envelope, and the fixed set of 10 MVP endpoints for onboarding, package, dashboard, support, and one-time portal login.') }}</p>
                <div class="cta-row" style="margin-bottom:1rem;">
                    <a class="btn btn-primary" href="{{ $postmanUrl }}">{{ __('Download Postman JSON') }}</a>
                    <a class="btn btn-primary" href="#architecture">{{ __('View architecture diagram') }}</a>
                    <a class="btn btn-ghost" href="#endpoints">{{ __('View 10 MVP endpoints') }}</a>
                    <a class="btn btn-ghost" href="{{ $helpTestUrl }}">{{ __('Step-by-step Postman test guide') }}</a>
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

    <section id="spec-overview">
        <div class="wrap">
            <h2 class="section-title">{{ __('Spec overview') }}</h2>
            <p class="section-intro">{{ __('Quick contract for partner developers. Paths are relative to the Base URL above. Call server-to-server from the FizaHUB backend — never from a public browser with the partner token.') }}</p>

            <div class="grid-2" style="margin-bottom:1rem;">
                <div class="panel">
                    <h3>{{ __('What FizaHUB integrates') }}</h3>
                    <ul class="list-clean">
                        <li>{{ __('Register / map a local business into MLHUB') }}</li>
                        <li>{{ __('Read package summary and growth dashboard') }}</li>
                        <li>{{ __('Bridge support tickets with polling conversation') }}</li>
                        <li>{{ __('Issue a single-use login URL into the MLHUB Portal') }}</li>
                    </ul>
                </div>
                <div class="panel">
                    <h3>{{ __('What this MVP does not expose') }}</h3>
                    <ul class="list-clean">
                        <li>{{ __('Customer CRUD, Chat AI, AI Studio APIs') }}</li>
                        <li>{{ __('Campaign / Coupon / Landing Page creation APIs') }}</li>
                        <li>{{ __('Google Business direct API and revenue / credit APIs') }}</li>
                        <li>{{ __('CCCD / GPKD file upload') }}</li>
                    </ul>
                </div>
            </div>

            <h3 class="section-title" style="font-size:1.15rem;">{{ __('Endpoint overview') }}</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('Method') }}</th>
                            <th>{{ __('Path') }}</th>
                            <th>{{ __('Purpose') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td><span class="method get">GET</span></td>
                            <td><code>/health</code></td>
                            <td>{{ __('Connectivity and auth smoke test') }}</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td><span class="method post">POST</span></td>
                            <td><code>/onboarding-requests</code></td>
                            <td>{{ __('Create or upsert business onboarding') }}</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td><span class="method get">GET</span></td>
                            <td><code>/onboarding-requests/{request_id}</code></td>
                            <td>{{ __('Read onboarding status') }}</td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td><span class="method get">GET</span></td>
                            <td><code>/businesses/{external_business_id}/package</code></td>
                            <td>{{ __('Package summary and whitelisted limits') }}</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td><span class="method get">GET</span></td>
                            <td><code>/businesses/{external_business_id}/dashboard</code></td>
                            <td>{{ __('Limited growth metrics for the mapped business') }}</td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td><span class="method post">POST</span></td>
                            <td><code>/businesses/{external_business_id}/support-tickets</code></td>
                            <td>{{ __('Create a support ticket') }}</td>
                        </tr>
                        <tr>
                            <td>7</td>
                            <td><span class="method get">GET</span></td>
                            <td><code>/businesses/{external_business_id}/support-tickets</code></td>
                            <td>{{ __('List support tickets') }}</td>
                        </tr>
                        <tr>
                            <td>8</td>
                            <td><span class="method get">GET</span></td>
                            <td><code>/support-tickets/{ticket_id}?external_business_id=...</code></td>
                            <td>{{ __('Ticket detail and conversation poll') }}</td>
                        </tr>
                        <tr>
                            <td>9</td>
                            <td><span class="method post">POST</span></td>
                            <td><code>/support-tickets/{ticket_id}/messages?external_business_id=...</code></td>
                            <td>{{ __('Append a support message') }}</td>
                        </tr>
                        <tr>
                            <td>10</td>
                            <td><span class="method post">POST</span></td>
                            <td><code>/businesses/{external_business_id}/one-time-login</code></td>
                            <td>{{ __('Issue a single-use portal login URL') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="info">
                <ul class="list-clean">
                    <li><code>external_business_id</code> {{ __('is the primary technical mapping key between FizaHUB and MLHUB.') }}</li>
                    <li>{{ __('Support ticket detail and message endpoints currently require the query parameter') }} <code>?external_business_id=...</code> {{ __('for tenant isolation; missing value returns 422.') }}</li>
                    <li>{{ __('Plus one signed web consume route (not counted in the 10 API MVP):') }} <code>GET /partners/fizahub/one-time-login/{token}</code></li>
                </ul>
            </div>
            <div class="quick-links">
                <a href="#auth">{{ __('Authentication / Headers') }}</a>
                <a href="#response-format">{{ __('Response format') }}</a>
                <a href="#flows">{{ __('Operating flows') }}</a>
                <a href="#data-dictionary">{{ __('Data dictionary') }}</a>
            </div>
        </div>
    </section>

    <section id="architecture">
        <div class="wrap">
            <h2 class="section-title">{{ __('Technical architecture diagram') }}</h2>
            <p class="section-intro">{{ __('How the FizaHUB backend, the MLHUB partner adapter, and MLHUB core connect over HTTPS with Bearer authentication.') }}</p>

            <div class="arch" aria-label="{{ __('Technical architecture diagram') }}">
                <div class="arch-box">
                    <h3>1. FizaHUB App / Backend</h3>
                    <span class="chip">{{ __('Owner KYC / login') }}</span>
                    <span class="chip">{{ __('Register FizaMKT') }}</span>
                    <span class="chip">{{ __('Dashboard UI') }}</span>
                    <span class="chip">{{ __('Support UI') }}</span>
                    <span class="chip">{{ __('Open Portal CTA') }}</span>
                    <p class="muted" style="margin:.7rem 0 0;font-size:.9rem;">{{ __('Holds FizaHUB identity. Calls MLHUB only from the backend with the partner token.') }}</p>
                </div>
                <div class="arch-arrow" aria-hidden="true"><span>HTTPS<br>Bearer<br>X-Partner</span></div>
                <div class="arch-box">
                    <h3>2. APIPartnerFizaHUB Adapter</h3>
                    <span class="chip">VerifyPartnerToken</span>
                    <span class="chip">X-Request-Id</span>
                    <span class="chip">Idempotency-Key</span>
                    <span class="chip">PartnerIntegration</span>
                    <span class="chip">Audit + Redaction</span>
                    <span class="chip">PartnerApiResponse</span>
                    <p class="muted" style="margin:.7rem 0 0;font-size:.9rem;">{{ __('Validates partner auth, maps external_business_id, redacts secrets in logs, and normalizes JSON envelopes.') }}</p>
                </div>
                <div class="arch-arrow" aria-hidden="true"><span>Internal<br>services</span></div>
                <div class="arch-box">
                    <h3>3. MLHUB Core</h3>
                    <span class="chip">User / Team</span>
                    <span class="chip">lb_businesses</span>
                    <span class="chip">SupportTicket</span>
                    <span class="chip">Dashboard metrics</span>
                    <span class="chip">One-time Login</span>
                    <p class="muted" style="margin:.7rem 0 0;font-size:.9rem;">{{ __('Owns workspace data, support operations, growth aggregation, and portal session issuance.') }}</p>
                </div>
            </div>

            <div class="note">
                <ul class="list-clean">
                    <li><code>external_business_id</code> {{ __('is the main technical key between FizaHUB and MLHUB.') }}</li>
                    <li><code>business_license_number</code> / <code>tax_code</code> {{ __('are used only for duplicate checks and legal verification.') }}</li>
                    <li>{{ __('CCCD/identity files are not stored by the MLHUB Partner API.') }}</li>
                    <li>{{ __('Partner token must stay in FizaHUB server ENV — never in a public frontend.') }}</li>
                </ul>
            </div>
        </div>
    </section>

    <section id="flows">
        <div class="wrap">
            <h2 class="section-title">{{ __('Operating flows') }}</h2>
            <p class="section-intro">{{ __('Four primary sequence flows in the MVP integration. Each step lists the related API contract.') }}</p>

            <div class="grid-2">
                <article class="panel flow-card" id="flow-onboarding">
                    <h3>1. Onboarding</h3>
                    <p class="muted" style="margin:0;">{{ __('Provision a mapped MLHUB workspace for a FizaHUB business.') }}</p>
                    <div class="endpoints-used">
                        <span class="chip">POST /onboarding-requests</span>
                        <span class="chip">GET /onboarding-requests/{request_id}</span>
                    </div>
                    <div class="seq">
                        <div class="seq-step">
                            <span class="seq-num">1</span>
                            <div class="seq-body">
                                <strong>{{ __('FizaHUB verifies the business owner upstream.') }}</strong>
                                <div class="seq-meta">verification.identity_verified</div>
                            </div>
                        </div>
                        <div class="seq-step">
                            <span class="seq-num">2</span>
                            <div class="seq-body">
                                <strong>{{ __('FizaHUB POSTs an onboarding request with Idempotency-Key.') }}</strong>
                                <div class="seq-meta">POST /onboarding-requests</div>
                            </div>
                        </div>
                        <div class="seq-step">
                            <span class="seq-num">3</span>
                            <div class="seq-body">
                                <strong>{{ __('MLHUB runs duplicate checks on email / tax / license.') }}</strong>
                                <div class="seq-meta">detectDuplicates → needs_review | continue</div>
                            </div>
                        </div>
                        <div class="seq-step">
                            <span class="seq-num">4</span>
                            <div class="seq-body">
                                <strong>{{ __('On success: create user, team, business, PartnerIntegration mapping.') }}</strong>
                                <div class="seq-meta">status = completed · current_step = ready</div>
                            </div>
                        </div>
                        <div class="seq-step">
                            <span class="seq-num">5</span>
                            <div class="seq-body">
                                <strong>{{ __('Otherwise return pending_verification or needs_review and open an internal support ticket.') }}</strong>
                                <div class="seq-meta">GET /onboarding-requests/{request_id}</div>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="panel flow-card" id="flow-support">
                    <h3>2. Support ticket bridge</h3>
                    <p class="muted" style="margin:0;">{{ __('Business owners raise tickets in FizaHUB; MLHUB admins reply; FizaHUB polls conversation.') }}</p>
                    <div class="endpoints-used">
                        <span class="chip">POST .../support-tickets</span>
                        <span class="chip">GET .../support-tickets</span>
                        <span class="chip">GET /support-tickets/{ticket_id}</span>
                        <span class="chip">POST .../messages</span>
                    </div>
                    <div class="seq">
                        <div class="seq-step">
                            <span class="seq-num">1</span>
                            <div class="seq-body">
                                <strong>{{ __('Owner submits subject + message from FizaHUB.') }}</strong>
                                <div class="seq-meta">POST /businesses/{external_business_id}/support-tickets</div>
                            </div>
                        </div>
                        <div class="seq-step">
                            <span class="seq-num">2</span>
                            <div class="seq-body">
                                <strong>{{ __('Adapter maps external_business_id and creates support_tickets / support_comments.') }}</strong>
                                <div class="seq-meta">returns ticket_id (id_secure)</div>
                            </div>
                        </div>
                        <div class="seq-step">
                            <span class="seq-num">3</span>
                            <div class="seq-body">
                                <strong>{{ __('MLHUB / FizaMKT admins reply in admin support.') }}</strong>
                                <div class="seq-meta">internal admin UI</div>
                            </div>
                        </div>
                        <div class="seq-step">
                            <span class="seq-num">4</span>
                            <div class="seq-body">
                                <strong>{{ __('FizaHUB polls ticket detail every 15–30 seconds (not realtime).') }}</strong>
                                <div class="seq-meta">GET /support-tickets/{ticket_id}?external_business_id=...</div>
                            </div>
                        </div>
                        <div class="seq-step">
                            <span class="seq-num">5</span>
                            <div class="seq-body">
                                <strong>{{ __('Owner can append messages while the ticket stays open.') }}</strong>
                                <div class="seq-meta">POST /support-tickets/{ticket_id}/messages?external_business_id=...</div>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="panel flow-card" id="flow-dashboard">
                    <h3>3. Growth dashboard</h3>
                    <p class="muted" style="margin:0;">{{ __('Read-only growth overview for a completed mapping.') }}</p>
                    <div class="endpoints-used">
                        <span class="chip">GET .../package</span>
                        <span class="chip">GET .../dashboard</span>
                    </div>
                    <div class="seq">
                        <div class="seq-step">
                            <span class="seq-num">1</span>
                            <div class="seq-body">
                                <strong>{{ __('Confirm onboarding status is completed and mapping exists.') }}</strong>
                                <div class="seq-meta">PartnerIntegration by external_business_id</div>
                            </div>
                        </div>
                        <div class="seq-step">
                            <span class="seq-num">2</span>
                            <div class="seq-body">
                                <strong>{{ __('Optional: read package_code, plan_slug, and whitelisted limits.') }}</strong>
                                <div class="seq-meta">GET /businesses/{external_business_id}/package</div>
                            </div>
                        </div>
                        <div class="seq-step">
                            <span class="seq-num">3</span>
                            <div class="seq-body">
                                <strong>{{ __('Request dashboard with optional from / to (Asia/Ho_Chi_Minh).') }}</strong>
                                <div class="seq-meta">GET /businesses/{external_business_id}/dashboard</div>
                            </div>
                        </div>
                        <div class="seq-step">
                            <span class="seq-num">4</span>
                            <div class="seq-body">
                                <strong>{{ __('MLHUB aggregates QR, leads, review feedback, coupons, bookings.') }}</strong>
                                <div class="seq-meta">metrics · campaigns · trend · insights · suggested_actions</div>
                            </div>
                        </div>
                        <div class="seq-step">
                            <span class="seq-num">5</span>
                            <div class="seq-body">
                                <strong>{{ __('FizaHUB renders the growth screen for the business owner.') }}</strong>
                                <div class="seq-meta">no price / credits / revenue fields</div>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="panel flow-card" id="flow-login">
                    <h3>4. One-time portal login</h3>
                    <p class="muted" style="margin:0;">{{ __('Deep-link the owner into the MLHUB Portal without sharing passwords or admin impersonation.') }}</p>
                    <div class="endpoints-used">
                        <span class="chip">POST .../one-time-login</span>
                        <span class="chip">GET /partners/fizahub/one-time-login/{token}</span>
                    </div>
                    <div class="seq">
                        <div class="seq-step">
                            <span class="seq-num">1</span>
                            <div class="seq-body">
                                <strong>{{ __('FizaHUB requests a one-time login URL for the mapped business.') }}</strong>
                                <div class="seq-meta">POST /businesses/{external_business_id}/one-time-login</div>
                            </div>
                        </div>
                        <div class="seq-step">
                            <span class="seq-num">2</span>
                            <div class="seq-body">
                                <strong>{{ __('MLHUB stores only a SHA-256 token hash with ~5 minute TTL.') }}</strong>
                                <div class="seq-meta">response: url, expires_at</div>
                            </div>
                        </div>
                        <div class="seq-step">
                            <span class="seq-num">3</span>
                            <div class="seq-body">
                                <strong>{{ __('Owner opens the signed URL in a browser and enters the MLHUB Portal.') }}</strong>
                                <div class="seq-meta">GET /partners/fizahub/one-time-login/{token}</div>
                            </div>
                        </div>
                        <div class="seq-step">
                            <span class="seq-num">4</span>
                            <div class="seq-body">
                                <strong>{{ __('Token is marked used_at and cannot be reused.') }}</strong>
                                <div class="seq-meta">single-use · no admin rights</div>
                            </div>
                        </div>
                    </div>
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
                <div style="display:flex;flex-wrap:wrap;gap:.6rem;">
                    <a class="btn btn-primary" href="{{ $postmanUrl }}" download>{{ __('Download Postman JSON') }}</a>
                    <a class="btn btn-ghost" href="{{ $helpTestUrl }}">{{ __('Step-by-step Postman test guide') }}</a>
                </div>
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
        <p style="margin:0;">MLHUB × FizaHUB Partner API · {{ __('Partner API technical specification') }} · <a href="{{ $postmanUrl }}">{{ __('Download Postman JSON') }}</a> · <a href="{{ $helpTestUrl }}">{{ __('Postman help test') }}</a></p>
    </div>
</footer>
</body>
</html>
