<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('FizaHUB Partner API - Hướng dẫn test Postman từng bước') }}</title>
    <meta name="description" content="{{ __('Hướng dẫn test Postman copy-paste cho người chưa chuyên.') }}">
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
            --patch: #7c3aed;
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
        .toc h3 { margin: 0 0 .3rem; font-size: .95rem; }
        .toc ol { margin: .4rem 0 0; padding-left: 1.2rem; }
        .toc a { text-decoration: none; }
        .step { margin: 0 0 1rem; }
        .step-num {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 2rem; height: 2rem; border-radius: 999px;
            background: var(--brand); color: #fff; font-weight: 800; margin-right: .45rem;
        }
        .step h2 { margin: 0 0 .7rem; font-size: 1.15rem; display: flex; align-items: center; flex-wrap: wrap; gap: .35rem; }
        .step h3 { margin: 1rem 0 .55rem; font-size: 1rem; }
        .step ol, .step ul { margin: .4rem 0 .7rem; padding-left: 1.2rem; }
        .step li { margin: .35rem 0; }
        .category-tag {
            display: inline-block; margin: 0 0 .6rem; font-size: .78rem;
            text-transform: uppercase; letter-spacing: .05em; color: var(--brand-dark); font-weight: 800;
        }
        .method {
            font-family: var(--mono); font-size: .75rem; font-weight: 800;
            padding: .2rem .45rem; border-radius: 8px; color: #fff;
        }
        .method.get { background: var(--get); }
        .method.post { background: var(--post); }
        .method.patch { background: var(--patch); }
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
        <nav class="nav" aria-label="{{ __('Điều hướng trợ giúp') }}">
            <a href="{{ $docsUrl }}">{{ __('Tài liệu API') }}</a>
            <a href="{{ $postmanUrl }}">{{ __('Tải file Postman JSON') }}</a>
            <a href="#step-1">{{ __('Bắt đầu tại đây') }}</a>
            <a href="#troubleshooting">{{ __('Lỗi thường gặp') }}</a>
        </nav>
    </div>
</header>

<main>
    <section class="hero">
        <div class="wrap">
            <div class="hero-card">
                <h1>{{ __('FizaHUB Partner API - Hướng dẫn test Postman từng bước') }}</h1>
                <p class="lead">{{ __('Dành cho người chưa chuyên. Làm từ trên xuống, copy Body/raw, bấm Send, copy ID vào Variables. Thứ tự request dưới đây khớp đúng thứ tự trong file Postman (24 request).') }}</p>
                <div class="cta-row">
                    <a class="btn btn-primary" href="{{ $postmanUrl }}">{{ __('1. Tải file Postman JSON trước') }}</a>
                    <a class="btn btn-ghost" href="{{ $docsUrl }}">{{ __('Quay lại tài liệu API') }}</a>
                </div>
                <div class="note">
                    {{ __('Không dán token partner thật vào chat, email hay tài liệu công khai. Trang này dùng token demo để test; token production do admin MLHUB cấp và giữ bí mật.') }}
                </div>
            </div>

            <div class="remember">
                <h2>{{ __('Chỉ cần nhớ 5 điều') }}</h2>
                <ul>
                    <li><code>base_url</code> — {{ __('tên miền MLHUB.') }}</li>
                    <li><code>partner_token</code> — {{ __('token MLHUB cấp riêng cho FizaHUB.') }}</li>
                    <li><code>external_user_id</code> — {{ __('mã user phía FizaHUB.') }}</li>
                    <li><code>external_business_id</code> — {{ __('mã cửa hàng/doanh nghiệp phía FizaHUB; đây là khóa chính cho gói/dashboard/hỗ trợ/đăng nhập.') }}</li>
                    <li><code>from</code> / <code>to</code> — {{ __('chỉ lọc khoảng ngày báo cáo Dashboard; không phải thời hạn gói.') }}</li>
                </ul>
            </div>

            <div class="toc">
                <strong>{{ __('Thứ tự test (làm từ trên xuống, khớp thứ tự trong Postman)') }}</strong>
                <h3>{{ __('A. Khởi tạo & xác thực') }}</h3>
                <ol>
                    <li><a href="#step-1">{{ __('Cài Postman và import file') }}</a></li>
                    <li><a href="#step-2">{{ __('Điền biến Variables') }}</a></li>
                    <li><a href="#step-3">{{ __('Test GET Health') }}</a></li>
                    <li><a href="#step-4">{{ __('Test POST SSO Verify') }}</a></li>
                    <li><a href="#step-5">{{ __('Test GET Package Catalog') }}</a></li>
                </ol>
                <h3>{{ __('B. Onboarding & hồ sơ doanh nghiệp') }}</h3>
                <ol start="6">
                    <li><a href="#step-6">{{ __('Test POST Onboarding') }}</a></li>
                    <li><a href="#step-7">{{ __('Test GET Onboarding Status') }}</a></li>
                    <li><a href="#step-8">{{ __('Test POST Confirm Onboarding') }}</a></li>
                    <li><a href="#step-9">{{ __('Test POST Cancel Onboarding') }}</a></li>
                    <li><a href="#step-10">{{ __('Test GET Integration Status') }}</a></li>
                    <li><a href="#step-11">{{ __('Test PATCH Update Business Profile') }}</a></li>
                    <li><a href="#step-12">{{ __('Test POST Đăng nhập một lần') }}</a></li>
                </ol>
                <h3>{{ __('C. Gói & dashboard tăng trưởng') }}</h3>
                <ol start="13">
                    <li><a href="#step-13">{{ __('Test GET Package') }}</a></li>
                    <li><a href="#step-14">{{ __('Test GET Dashboard') }}</a></li>
                    <li><a href="#step-15">{{ __('Test GET Insights') }}</a></li>
                    <li><a href="#step-16">{{ __('Test GET Recommendations') }}</a></li>
                    <li><a href="#step-17">{{ __('Test GET Campaigns') }}</a></li>
                    <li><a href="#step-18">{{ __('Test GET Campaign Detail') }}</a></li>
                </ol>
                <h3>{{ __('D. Hỗ trợ & hội thoại') }}</h3>
                <ol start="19">
                    <li><a href="#step-19">{{ __('Test GET Support Summary') }}</a></li>
                    <li><a href="#step-20">{{ __('Test POST Tạo ticket hỗ trợ') }}</a></li>
                    <li><a href="#step-21">{{ __('Test GET Danh sách ticket') }}</a></li>
                    <li><a href="#step-22">{{ __('Test GET Chi tiết ticket') }}</a></li>
                    <li><a href="#step-23">{{ __('Test POST Gửi tin nhắn hỗ trợ') }}</a></li>
                    <li><a href="#step-24">{{ __('Test POST Tải file đính kèm') }}</a></li>
                    <li><a href="#step-25">{{ __('Test PATCH Đóng ticket / POST Mở lại ticket') }}</a></li>
                    <li><a href="#troubleshooting">{{ __('Lỗi thường gặp') }}</a></li>
                </ol>
            </div>
        </div>
    </section>

    <section>
        <div class="wrap">
            <article class="step" id="step-1">
                <h2><span class="step-num">1</span> {{ __('Cài Postman và import file') }}</h2>
                <ol>
                    <li>{{ __('Mở trình duyệt, vào trang Postman, tải bản cài cho máy tính của bạn.') }}</li>
                    <li>{{ __('Cài đặt Postman xong, mở ứng dụng.') }}</li>
                    <li>{{ __('Tải file collection JSON của MLHUB bằng nút bên dưới.') }}</li>
                </ol>
                <div class="cta-row">
                    <a class="btn btn-primary" href="https://www.postman.com/downloads/" target="_blank" rel="noopener noreferrer">{{ __('Tải Postman') }}</a>
                    <a class="btn btn-ghost" href="{{ $postmanUrl }}">{{ __('Tải file Postman JSON') }}</a>
                </div>
                <ol start="4">
                    <li>{{ __('Trong Postman, bấm Import.') }}</li>
                    <li>{{ __('Chọn file vừa tải: MLHUB-FizaHUB-Partner-API.postman_collection.json') }}</li>
                    <li>{{ __('Bạn sẽ thấy collection tên MLHUB × FizaHUB Partner API.') }}</li>
                </ol>
                <div class="check">{{ __('Xong khi: thấy 24 request trong collection, chia theo 4 nhóm A/B/C/D ở mục lục bên trên.') }}</div>
            </article>

            <article class="step" id="step-2">
                <h2><span class="step-num">2</span> {{ __('Điền biến Variables') }}</h2>
                <ol>
                    <li>{{ __('Trong Postman, mở collection MLHUB × FizaHUB Partner API.') }}</li>
                    <li>{{ __('Mở tab Variables.') }}</li>
                    <li>{{ __('Copy và dán các giá trị Current value bên dưới:') }}</li>
                </ol>
                <span class="copy-label">{{ __('Copy các giá trị này') }}</span>
                <pre>base_url = {{ $appUrl }}
partner_token = {{ $demoPartnerToken }}
external_user_id = fh-user-demo-001
external_business_id = fh-biz-demo-001
campaign_id = (để trống ban đầu)
from = {{ $dashboardFrom }}
to = {{ $dashboardTo }}
onboarding_request_id = (để trống ban đầu)
ticket_id = (để trống ban đầu)</pre>
                <ol start="4">
                    <li>{{ __('Bấm Save.') }}</li>
                </ol>

                <span class="copy-label">{{ __('Ý nghĩa từng biến') }}</span>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ __('Biến') }}</th>
                                <th>{{ __('Ví dụ') }}</th>
                                <th>{{ __('Dùng để làm gì?') }}</th>
                                <th>{{ __('Khi nào đổi?') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>base_url</code></td>
                                <td><code>{{ $appUrl }}</code></td>
                                <td>{{ __('Tên miền MLHUB.') }}</td>
                                <td>{{ __('Đổi khi test local/staging/production.') }}</td>
                            </tr>
                            <tr>
                                <td><code>partner_token</code></td>
                                <td><code>{{ $demoPartnerToken }}</code></td>
                                <td>{{ __('Xác thực các lệnh gọi API FizaHUB.') }}</td>
                                <td>{{ __('Đổi khi admin MLHUB cấp token mới.') }}</td>
                            </tr>
                            <tr>
                                <td><code>external_user_id</code></td>
                                <td><code>fh-user-demo-001</code></td>
                                <td>{{ __('Mã user phía FizaHUB.') }}</td>
                                <td>{{ __('Mỗi user test/khách hàng thật nên có ID riêng.') }}</td>
                            </tr>
                            <tr>
                                <td><code>external_business_id</code></td>
                                <td><code>fh-biz-demo-001</code></td>
                                <td>{{ __('Khóa kỹ thuật chính map cửa hàng FizaHUB sang MLHUB.') }}</td>
                                <td>{{ __('Mỗi cửa hàng/doanh nghiệp nên có ID riêng.') }}</td>
                            </tr>
                            <tr>
                                <td><code>campaign_id</code></td>
                                <td>{{ __('để trống ban đầu') }}</td>
                                <td>{{ __('Xem chi tiết một chiến dịch.') }}</td>
                                <td>{{ __('Sau khi gọi GET Campaigns, copy id một chiến dịch và dán vào đây.') }}</td>
                            </tr>
                            <tr>
                                <td><code>from</code></td>
                                <td><code>{{ $dashboardFrom }}</code></td>
                                <td>{{ __('Ngày bắt đầu báo cáo Dashboard.') }}</td>
                                <td>{{ __('Đổi khi muốn khoảng báo cáo khác. Có thể để trống — API dùng 30 ngày gần nhất.') }}</td>
                            </tr>
                            <tr>
                                <td><code>to</code></td>
                                <td><code>{{ $dashboardTo }}</code></td>
                                <td>{{ __('Ngày kết thúc báo cáo Dashboard.') }}</td>
                                <td>{{ __('Đổi khi muốn khoảng báo cáo khác. Có thể để trống — API dùng hôm nay.') }}</td>
                            </tr>
                            <tr>
                                <td><code>onboarding_request_id</code></td>
                                <td>{{ __('để trống ban đầu') }}</td>
                                <td>{{ __('Đọc trạng thái onboarding.') }}</td>
                                <td>{{ __('Sau POST Onboarding, copy data.request_id và dán vào đây.') }}</td>
                            </tr>
                            <tr>
                                <td><code>ticket_id</code></td>
                                <td>{{ __('để trống ban đầu') }}</td>
                                <td>{{ __('Xem hoặc gửi tin nhắn ticket hỗ trợ.') }}</td>
                                <td>{{ __('Sau khi tạo ticket, copy data.ticket_id và dán vào đây.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="note">
                    <strong>{{ __('from/to không phải thời hạn gói') }}</strong><br>
                    {{ __('from và to chỉ dùng cho API Dashboard để chọn khoảng ngày báo cáo. Ví dụ: from=2026-07-01 và to=2026-07-31 nghĩa là số liệu marketing trong tháng 7. Hai trường này không dùng để gia hạn gói 1/3/6/12 tháng.') }}
                </div>
                <div class="info">
                    <strong>{{ __('Thời hạn gói hoạt động thế nào?') }}</strong><br>
                    {{ __('FizaHUB gửi package_code khi onboarding. Ví dụ: package_code=base map sang gói nội bộ MLHUB mlhub-free-da-nang, nhưng MLHUB luôn tạo tài khoản với gói hiệu lực là free trước — requested_package_code được lưu riêng để tư vấn viên xem xét nâng gói. API Package trả starts_at/expires_at khi MLHUB có dữ liệu thời hạn. Chưa có endpoint nhập gói 1/3/6/12 tháng hay tự gia hạn. Nếu FizaHUB cần bán gói theo tháng, thiết kế endpoint package/subscription riêng ở giai đoạn sau — không dùng from/to.') }}
                </div>
                <div class="check">{{ __('Xong khi: base_url và partner_token đã điền và Save.') }}</div>
            </article>

            <p class="category-tag">{{ __('A. Khởi tạo & xác thực') }}</p>

            <article class="step" id="step-3">
                <h2><span class="step-num">3</span> <span class="method get">GET</span> {{ __('Test GET Health') }}</h2>
                <ol>
                    <li>{{ __('Mở request tên GET Health.') }}</li>
                    <li>{{ __('Kiểm tra tab Headers. Postman thường đã điền sẵn Authorization, X-Partner, X-Request-Id.') }}</li>
                    <li>{{ __('Bấm Send.') }}</li>
                </ol>
                <span class="copy-label">URL</span>
                <pre>@{{base_url}}/api/v1/partners/fizahub/health</pre>
                <span class="copy-label">{{ __('Headers bắt buộc') }}</span>
                <pre>@verbatim
Authorization: Bearer {{partner_token}}
X-Partner: fizahub
X-Request-Id: {{$guid}}
Accept: application/json
@endverbatim</pre>
                <span class="copy-label">{{ __('Kết quả mong đợi') }}</span>
                <pre>HTTP 200</pre>
                <span class="copy-label">{{ __('Response mẫu') }}</span>
                <pre>{
  "success": true,
  "data": {
    "status": "ok",
    "partner": "fizahub",
    "api_version": "v1"
  },
  "error": null
}</pre>
                <div class="info">
                    {{ __('HTTP 200 nghĩa là partner_token, base_url và headers đều đúng. Nếu bước này fail, dừng lại và kiểm tra lại partner_token / base_url trước khi test các API khác.') }}
                </div>
                <div class="check">{{ __('Xong khi: success=true và status=ok. Nếu fail, dừng và kiểm tra partner_token / base_url.') }}</div>
            </article>

            <article class="step" id="step-4">
                <h2><span class="step-num">4</span> <span class="method post">POST</span> {{ __('Test POST SSO Verify') }}</h2>
                <ol>
                    <li>{{ __('Mở request POST SSO Verify.') }}</li>
                    <li>{{ __('Body raw JSON có thể để trống {} cho lần test đầu tiên.') }}</li>
                    <li>{{ __('Bấm Send.') }}</li>
                </ol>
                <span class="copy-label">URL</span>
                <pre>@{{base_url}}/api/v1/partners/fizahub/partner/sso/verify</pre>
                <span class="copy-label">{{ __('Body raw JSON') }}</span>
                <pre>{}</pre>
                <span class="copy-label">{{ __('Kết quả mong đợi') }}</span>
                <pre>HTTP 200</pre>
                <div class="check">{{ __('Xong khi: success=true và verified=true.') }}</div>
            </article>

            <article class="step" id="step-5">
                <h2><span class="step-num">5</span> <span class="method get">GET</span> {{ __('Test GET Package Catalog') }}</h2>
                <ol>
                    <li>{{ __('Mở request GET Package Catalog.') }}</li>
                    <li>{{ __('Bấm Send.') }}</li>
                </ol>
                <span class="copy-label">URL</span>
                <pre>@{{base_url}}/api/v1/partners/fizahub/packages</pre>
                <div class="info">{{ __('Đây là danh mục tĩnh chỉ để hiển thị cho FizaHUB; không ảnh hưởng gói hiệu lực của mapping đã có.') }}</div>
                <div class="check">{{ __('Xong khi: thấy danh sách gói (ví dụ free, base) trong data.') }}</div>
            </article>

            <p class="category-tag">{{ __('B. Onboarding & hồ sơ doanh nghiệp') }}</p>

            <article class="step" id="step-6">
                <h2><span class="step-num">6</span> <span class="method post">POST</span> {{ __('Test POST Onboarding') }}</h2>
                <p><strong>{{ __('Thao tác trong Postman') }}</strong></p>
                <ol>
                    <li>{{ __('Mở POST Onboarding.') }}</li>
                    <li>{{ __('Mở tab Body.') }}</li>
                    <li>{{ __('Chọn raw.') }}</li>
                    <li>{{ __('Chọn JSON.') }}</li>
                    <li>{{ __('Copy toàn bộ Body raw JSON bên dưới và dán vào Body.') }}</li>
                    <li>{{ __('Tùy chọn: đổi owner.email để tránh trùng.') }}</li>
                    <li>{{ __('Bấm Send.') }}</li>
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
    "name": "Nguyễn Văn A",
    "phone": "0912345678",
    "email": "nguyenvana+demo001@example.com"
  },
  "business": {
    "name": "Cửa hàng Demo Fiza",
    "industry": "restaurant_food",
    "address": "123 Nguyễn Trãi, Đà Nẵng",
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
                        <li>{{ __('Chỉ gửi các trường API validate. Không gửi note, industry_name, google_maps_url, business_license_verified.') }}</li>
                        <li>{{ __('Test lại thì đổi external_business_id, owner.email và business_license_number để tránh trùng.') }}</li>
                        <li>{{ __('Không gửi CCCD, ảnh CCCD hay file GPKD.') }}</li>
                        <li>{{ __('MLHUB luôn tạo ngay tài khoản Free và trả status=awaiting_consultant.') }}</li>
                        <li>{{ __('package_code gửi lên (ví dụ base) được lưu riêng ở requested_package_code; package_code hiệu lực luôn là free cho tới khi admin xác nhận nâng gói.') }}</li>
                        <li>{{ __('Nếu status=needs_review (trùng email/MST/GPKD), tài khoản vẫn được tạo; nhờ admin MLHUB rà soát ticket.') }}</li>
                        <li>{{ __('One-time login chỉ mở khi status là ready hoặc completed.') }}</li>
                    </ul>
                </div>
                <span class="copy-label">{{ __('Kết quả mong đợi') }}</span>
                <pre>HTTP 201 (awaiting_consultant) {{ __('hoặc') }} HTTP 202 (needs_review)</pre>
                <span class="copy-label">{{ __('Response mẫu khi awaiting_consultant') }}</span>
                <pre>{
  "success": true,
  "data": {
    "request_id": "018f5a64-b40b-7f60-a925-dea047cf6590",
    "external_business_id": "fh-biz-demo-001",
    "requested_package_code": "base",
    "package_code": "free",
    "status": "awaiting_consultant",
    "current_step": "consultant_contact",
    "status_label": "Chờ tư vấn viên liên hệ"
  },
  "error": null
}</pre>

                <h3>{{ __('Bảng trạng thái onboarding') }}</h3>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>{{ __('Nhãn tiếng Việt') }}</th>
                                <th>{{ __('Khi nào xảy ra') }}</th>
                                <th>{{ __('FizaHUB nên làm gì') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>awaiting_consultant</code></td>
                                <td>{{ __('Chờ tư vấn viên liên hệ') }}</td>
                                <td>{{ __('Vừa tạo tài khoản Free, chờ tư vấn viên MLHUB liên hệ') }}</td>
                                <td>{{ __('Chờ MLHUB liên hệ; không tạo lại request') }}</td>
                            </tr>
                            <tr>
                                <td><code>needs_review</code></td>
                                <td>{{ __('Cần kiểm tra') }}</td>
                                <td>{{ __('Trùng email / mã số thuế / GPKD (tài khoản vẫn được tạo)') }}</td>
                                <td>{{ __('Không tạo lại request giống hệt; nhờ MLHUB xử lý ticket') }}</td>
                            </tr>
                            <tr>
                                <td><code>ready</code></td>
                                <td>{{ __('Sẵn sàng sử dụng') }}</td>
                                <td>{{ __('Cấu hình xong, one-time login được phép') }}</td>
                                <td>{{ __('Có thể gọi Package, Dashboard, Support, One-time Login') }}</td>
                            </tr>
                            <tr>
                                <td><code>completed</code></td>
                                <td>{{ __('Hoàn tất') }}</td>
                                <td>{{ __('Đã bàn giao và hoàn tất onboarding') }}</td>
                                <td>{{ __('Sử dụng bình thường') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>current_step</th>
                                <th>{{ __('Nhãn tiếng Việt') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td><code>consultant_contact</code></td><td>{{ __('Chờ tư vấn viên liên hệ') }}</td></tr>
                            <tr><td><code>needs_review</code></td><td>{{ __('Đang rà soát trùng dữ liệu') }}</td></tr>
                            <tr><td><code>ready</code></td><td>{{ __('Sẵn sàng sử dụng') }}</td></tr>
                            <tr><td><code>completed</code></td><td>{{ __('Hoàn tất') }}</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="check">
                    <strong>{{ __('Sau khi có response') }}:</strong>
                    {{ __('Copy data.request_id → dán vào biến onboarding_request_id → Save.') }}
                </div>
            </article>

            <article class="step" id="step-7">
                <h2><span class="step-num">7</span> <span class="method get">GET</span> {{ __('Test GET Onboarding Status') }}</h2>
                <ol>
                    <li>{{ __('Mở GET Onboarding Status.') }}</li>
                    <li>{{ __('Đảm bảo biến onboarding_request_id đã điền.') }}</li>
                    <li>{{ __('Bấm Send.') }}</li>
                </ol>
                <span class="copy-label">URL</span>
                <pre>@{{base_url}}/api/v1/partners/fizahub/onboarding-requests/@{{onboarding_request_id}}</pre>
                <span class="copy-label">{{ __('Kết quả mong đợi') }}</span>
                <pre>HTTP 200</pre>
                <span class="copy-label">{{ __('Response mẫu') }}</span>
                <pre>{
  "success": true,
  "data": {
    "request_id": "018f5a64-b40b-7f60-a925-dea047cf6590",
    "status": "awaiting_consultant",
    "current_step": "consultant_contact",
    "external_business_id": "fh-biz-demo-001"
  },
  "error": null
}</pre>
                <div class="check">{{ __('Xong khi: status trả về và khớp kết quả onboarding trước đó.') }}</div>
            </article>

            <article class="step" id="step-8">
                <h2><span class="step-num">8</span> <span class="method post">POST</span> {{ __('Test POST Confirm Onboarding') }}</h2>
                <ol>
                    <li>{{ __('Mở POST Confirm Onboarding.') }}</li>
                    <li>{{ __('Không cần Body.') }}</li>
                    <li>{{ __('Bấm Send.') }}</li>
                </ol>
                <span class="copy-label">URL</span>
                <pre>@{{base_url}}/api/v1/partners/fizahub/onboarding-requests/@{{onboarding_request_id}}/confirm</pre>
                <div class="note">{{ __('Chỉ hợp lệ từ một số trạng thái theo state machine; nếu sai trạng thái sẽ trả 409 invalid_status_transition.') }}</div>
                <div class="check">{{ __('Xong khi: HTTP 200 và status được cập nhật.') }}</div>
            </article>

            <article class="step" id="step-9">
                <h2><span class="step-num">9</span> <span class="method post">POST</span> {{ __('Test POST Cancel Onboarding') }}</h2>
                <ol>
                    <li>{{ __('Chỉ dùng request này khi bạn muốn hủy hẳn một yêu cầu test — nó sẽ đặt status=cancelled.') }}</li>
                    <li>{{ __('Mở POST Cancel Onboarding.') }}</li>
                    <li>{{ __('Bấm Send.') }}</li>
                </ol>
                <span class="copy-label">URL</span>
                <pre>@{{base_url}}/api/v1/partners/fizahub/onboarding-requests/@{{onboarding_request_id}}/cancel</pre>
                <div class="note">{{ __('Yêu cầu đã completed không thể hủy. Đừng chạy bước này nếu bạn còn muốn tiếp tục các bước 10-24 với cùng onboarding_request_id.') }}</div>
                <div class="check">{{ __('Xong khi: HTTP 200 và status=cancelled.') }}</div>
            </article>

            <article class="step" id="step-10">
                <h2><span class="step-num">10</span> <span class="method get">GET</span> {{ __('Test GET Integration Status') }}</h2>
                <ol>
                    <li>{{ __('Mở GET Integration Status.') }}</li>
                    <li>{{ __('Bấm Send.') }}</li>
                </ol>
                <span class="copy-label">URL</span>
                <pre>@{{base_url}}/api/v1/partners/fizahub/businesses/@{{external_business_id}}/integration-status</pre>
                <div class="check">{{ __('Xong khi: thấy trạng thái onboarding, package_code và cờ cho phép one-time login.') }}</div>
            </article>

            <article class="step" id="step-11">
                <h2><span class="step-num">11</span> <span class="method patch">PATCH</span> {{ __('Test PATCH Update Business Profile') }}</h2>
                <ol>
                    <li>{{ __('Mở PATCH Update Business Profile.') }}</li>
                    <li>{{ __('Mở Body → raw → JSON.') }}</li>
                    <li>{{ __('Bấm Send.') }}</li>
                </ol>
                <span class="copy-label">URL</span>
                <pre>@{{base_url}}/api/v1/partners/fizahub/businesses/@{{external_business_id}}/profile</pre>
                <span class="copy-label">{{ __('Body raw JSON') }}</span>
                <pre>{
  "phone": "0912345679",
  "address": "456 Lê Duẩn, Đà Nẵng"
}</pre>
                <div class="note">{{ __('Chỉ cập nhật hồ sơ cơ bản (tên, địa chỉ, điện thoại, email). Đổi ngành nghề/gói vẫn cần admin MLHUB xử lý.') }}</div>
                <div class="check">{{ __('Xong khi: HTTP 200 và hồ sơ business được cập nhật.') }}</div>
            </article>

            <article class="step" id="step-12">
                <h2><span class="step-num">12</span> <span class="method post">POST</span> {{ __('Test POST Đăng nhập một lần') }}</h2>
                <ol>
                    <li>{{ __('Chỉ chạy được khi onboarding status là ready hoặc completed — nếu chưa sẽ trả 409 onboarding_not_ready.') }}</li>
                    <li>{{ __('Mở POST One-time Login.') }}</li>
                    <li>{{ __('Để trống Body. Không cần Body.') }}</li>
                    <li>{{ __('Bấm Send.') }}</li>
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
                <span class="copy-label">{{ __('Kết quả mong đợi') }}</span>
                <pre>HTTP 201</pre>
                <span class="copy-label">{{ __('Response mẫu') }}</span>
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
                        <li>{{ __('Copy data.url và mở bằng trình duyệt để vào MLHUB Portal.') }}</li>
                        <li>{{ __('Link dùng một lần và hết hạn khoảng 5 phút.') }}</li>
                        <li>{{ __('Không chia sẻ link này cho người khác.') }}</li>
                    </ul>
                </div>
                <div class="check">{{ __('Xong khi: nhận được URL đăng nhập một lần.') }}</div>
            </article>

            <p class="category-tag">{{ __('C. Gói & dashboard tăng trưởng') }}</p>

            <article class="step" id="step-13">
                <h2><span class="step-num">13</span> <span class="method get">GET</span> {{ __('Test GET Package') }}</h2>
                <ol>
                    <li>{{ __('Mở GET Business Package.') }}</li>
                    <li>{{ __('Bấm Send.') }}</li>
                </ol>
                <span class="copy-label">URL</span>
                <pre>@{{base_url}}/api/v1/partners/fizahub/businesses/@{{external_business_id}}/package</pre>
                <span class="copy-label">{{ __('Kết quả mong đợi') }}</span>
                <pre>HTTP 200</pre>
                <span class="copy-label">{{ __('Response mẫu') }}</span>
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
                        <li><code>package_code</code> — {{ __('mã gói FizaHUB gửi (ví dụ: package_code=base).') }}</li>
                        <li><code>plan_slug</code> — {{ __('gói nội bộ MLHUB (ví dụ: mlhub-free-da-nang).') }}</li>
                        <li><code>expires_at</code> — {{ __('có thể null nếu gói chưa có ngày hết hạn.') }}</li>
                        <li>{{ __('Đây không phải from/to của Dashboard.') }} {{ __('Chưa có endpoint gia hạn 1/3/6/12 tháng.') }}</li>
                    </ul>
                </div>
                <div class="check">{{ __('Xong khi: trả về tóm tắt gói, không có giá/credit.') }}</div>
            </article>

            <article class="step" id="step-14">
                <h2><span class="step-num">14</span> <span class="method get">GET</span> {{ __('Test GET Dashboard') }}</h2>
                <ol>
                    <li>{{ __('Mở GET Dashboard.') }}</li>
                    <li>{{ __('Mở tab Params và kiểm tra from / to (chỉ là khoảng ngày báo cáo Dashboard).') }}</li>
                    <li>{{ __('Không chắc điền gì thì để trống from/to — API dùng 30 ngày gần nhất.') }}</li>
                    <li>{{ __('Bấm Send.') }}</li>
                </ol>
                <span class="copy-label">URL</span>
                <pre>@{{base_url}}/api/v1/partners/fizahub/businesses/@{{external_business_id}}/dashboard?from=@{{from}}&to=@{{to}}</pre>
                <span class="copy-label">Params</span>
                <pre>from = {{ __('ngày bắt đầu báo cáo, định dạng YYYY-MM-DD') }}
to = {{ __('ngày kết thúc báo cáo, định dạng YYYY-MM-DD') }}</pre>
                <div class="note">
                    {{ __('from/to không phải thời hạn gói') }}.
                    {{ __('from và to chỉ chọn khoảng báo cáo Dashboard. Không dùng để gia hạn gói 1/3/6/12 tháng.') }}
                </div>
                <span class="copy-label">{{ __('Kết quả mong đợi') }}</span>
                <pre>HTTP 200</pre>
                <span class="copy-label">{{ __('Response mẫu') }}</span>
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
    "suggested_actions": [],
    "cached_at": "{{ $dashboardTo }}T10:00:00+07:00"
  },
  "error": null
}</pre>
                <div class="info">
                    <ul>
                        <li><code>qr_scans</code>: {{ __('số lượt quét QR.') }}</li>
                        <li><code>new_leads</code>: {{ __('lead mới.') }}</li>
                        <li><code>new_reviews</code>: {{ __('phản hồi review nội bộ rating >= 4, chưa phải Google Reviews thật.') }}</li>
                        <li><code>returning_customers</code>: {{ __('ước tính từ SĐT/email lặp lại.') }}</li>
                        <li><code>conversion_rate</code>: {{ __('tỷ lệ chuyển đổi nội bộ.') }}</li>
                        <li>{{ __('Kết quả được cache vài chục phút để giảm tải; cached_at cho biết lần tính gần nhất.') }}</li>
                    </ul>
                </div>
                <div class="check">{{ __('Xong khi: có object metrics dù tất cả số đều bằng 0.') }}</div>
            </article>

            <article class="step" id="step-15">
                <h2><span class="step-num">15</span> <span class="method get">GET</span> {{ __('Test GET Insights') }}</h2>
                <ol>
                    <li>{{ __('Mở GET Insights.') }}</li>
                    <li>{{ __('Bấm Send.') }}</li>
                </ol>
                <span class="copy-label">URL</span>
                <pre>@{{base_url}}/api/v1/partners/fizahub/businesses/@{{external_business_id}}/insights?from=@{{from}}&to=@{{to}}</pre>
                <div class="check">{{ __('Xong khi: trả về danh sách câu insight ngắn (có thể rỗng nếu chưa có dữ liệu).') }}</div>
            </article>

            <article class="step" id="step-16">
                <h2><span class="step-num">16</span> <span class="method get">GET</span> {{ __('Test GET Recommendations') }}</h2>
                <ol>
                    <li>{{ __('Mở GET Recommendations.') }}</li>
                    <li>{{ __('Bấm Send.') }}</li>
                </ol>
                <span class="copy-label">URL</span>
                <pre>@{{base_url}}/api/v1/partners/fizahub/businesses/@{{external_business_id}}/recommendations?from=@{{from}}&to=@{{to}}</pre>
                <div class="check">{{ __('Xong khi: trả về danh sách hành động gợi ý (có thể rỗng).') }}</div>
            </article>

            <article class="step" id="step-17">
                <h2><span class="step-num">17</span> <span class="method get">GET</span> {{ __('Test GET Campaigns') }}</h2>
                <ol>
                    <li>{{ __('Mở GET Campaigns.') }}</li>
                    <li>{{ __('Bấm Send.') }}</li>
                </ol>
                <span class="copy-label">URL</span>
                <pre>@{{base_url}}/api/v1/partners/fizahub/businesses/@{{external_business_id}}/campaigns?from=@{{from}}&to=@{{to}}</pre>
                <div class="note">{{ __('Đây là API chỉ đọc — không dùng để tạo/sửa/xóa chiến dịch.') }}</div>
                <div class="check">
                    <strong>{{ __('Sau khi có response') }}:</strong>
                    {{ __('Nếu danh sách không rỗng, copy id của một chiến dịch → dán vào biến campaign_id → Save.') }}
                </div>
            </article>

            <article class="step" id="step-18">
                <h2><span class="step-num">18</span> <span class="method get">GET</span> {{ __('Test GET Campaign Detail') }}</h2>
                <ol>
                    <li>{{ __('Chỉ chạy được khi campaign_id đã điền (xem bước 17).') }}</li>
                    <li>{{ __('Mở GET Campaign Detail.') }}</li>
                    <li>{{ __('Bấm Send.') }}</li>
                </ol>
                <span class="copy-label">URL</span>
                <pre>@{{base_url}}/api/v1/partners/fizahub/businesses/@{{external_business_id}}/campaigns/@{{campaign_id}}?from=@{{from}}&to=@{{to}}</pre>
                <div class="note">{{ __('Nếu chiến dịch thuộc doanh nghiệp khác, API trả 404 để tránh lộ dữ liệu.') }}</div>
                <div class="check">{{ __('Xong khi: thấy chi tiết chiến dịch tương ứng với campaign_id.') }}</div>
            </article>

            <p class="category-tag">{{ __('D. Hỗ trợ & hội thoại') }}</p>

            <article class="step" id="step-19">
                <h2><span class="step-num">19</span> <span class="method get">GET</span> {{ __('Test GET Support Summary') }}</h2>
                <ol>
                    <li>{{ __('Mở GET Support Summary.') }}</li>
                    <li>{{ __('Bấm Send.') }}</li>
                </ol>
                <span class="copy-label">URL</span>
                <pre>@{{base_url}}/api/v1/partners/fizahub/businesses/@{{external_business_id}}/support-summary</pre>
                <div class="check">{{ __('Xong khi: thấy số liệu đếm ticket open/resolved/closed.') }}</div>
            </article>

            <article class="step" id="step-20">
                <h2><span class="step-num">20</span> <span class="method post">POST</span> {{ __('Test POST Tạo ticket hỗ trợ') }}</h2>
                <ol>
                    <li>{{ __('Mở POST Create Support Ticket.') }}</li>
                    <li>{{ __('Mở Body → raw → JSON.') }}</li>
                    <li>{{ __('Copy toàn bộ Body raw JSON bên dưới và dán vào Body.') }}</li>
                    <li>{{ __('Bấm Send.') }}</li>
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
  "subject": "Yêu cầu hỗ trợ FizaMKT Base",
  "message": "Tôi muốn được hỗ trợ tạo QR check-in và xin review Google.",
  "category_id": null,
  "type_id": null
}</pre>
                <div class="info">
                    {{ __('Request gửi lên dùng trường message. Response trả về dùng trường body (xem bước 22).') }}
                </div>
                <span class="copy-label">{{ __('Kết quả mong đợi') }}</span>
                <pre>HTTP 201</pre>
                <span class="copy-label">{{ __('Response mẫu') }}</span>
                <pre>{
  "success": true,
  "data": {
    "ticket_id": "secure-ticket-id",
    "subject": "Yêu cầu hỗ trợ FizaMKT Base",
    "status": "open"
  },
  "error": null
}</pre>
                <div class="check">
                    <strong>{{ __('Sau khi có response') }}:</strong>
                    {{ __('Copy data.ticket_id → dán vào biến ticket_id → Save.') }}
                </div>
            </article>

            <article class="step" id="step-21">
                <h2><span class="step-num">21</span> <span class="method get">GET</span> {{ __('Test GET Danh sách ticket') }}</h2>
                <ol>
                    <li>{{ __('Mở GET List Support Tickets.') }}</li>
                    <li>{{ __('Params tùy chọn: page=1, per_page=20.') }}</li>
                    <li>{{ __('Bấm Send.') }}</li>
                </ol>
                <span class="copy-label">URL</span>
                <pre>@{{base_url}}/api/v1/partners/fizahub/businesses/@{{external_business_id}}/support-tickets</pre>
                <span class="copy-label">{{ __('Kết quả mong đợi') }}</span>
                <pre>HTTP 200</pre>
                <span class="copy-label">{{ __('Response mẫu') }}</span>
                <pre>{
  "success": true,
  "data": {
    "items": [
      {
        "ticket_id": "secure-ticket-id",
        "subject": "Yêu cầu hỗ trợ FizaMKT Base",
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
                <div class="check">{{ __('Xong khi: ticket mới xuất hiện trong danh sách.') }}</div>
            </article>

            <article class="step" id="step-22">
                <h2><span class="step-num">22</span> <span class="method get">GET</span> {{ __('Test GET Chi tiết ticket') }}</h2>
                <ol>
                    <li>{{ __('Mở GET Support Ticket Detail.') }}</li>
                    <li>{{ __('Mở Params và xác nhận có external_business_id — query này bắt buộc.') }}</li>
                    <li>{{ __('Bấm Send.') }}</li>
                </ol>
                <span class="copy-label">URL</span>
                <pre>@{{base_url}}/api/v1/partners/fizahub/support-tickets/@{{ticket_id}}?external_business_id=@{{external_business_id}}</pre>
                <div class="note">
                    {{ __('external_business_id là query bắt buộc. Thiếu sẽ trả 422.') }}
                </div>
                <span class="copy-label">{{ __('Kết quả mong đợi') }}</span>
                <pre>HTTP 200</pre>
                <span class="copy-label">{{ __('Response mẫu') }}</span>
                <pre>{
  "success": true,
  "data": {
    "ticket": {
      "ticket_id": "secure-ticket-id",
      "subject": "Yêu cầu hỗ trợ FizaMKT Base",
      "status": "open"
    },
    "messages": [
      {
        "message_id": "secure-ticket-id:initial",
        "sender_type": "business",
        "body": "Tôi muốn được hỗ trợ tạo QR check-in và xin review Google."
      }
    ],
    "attachments": [],
    "next_poll_after_seconds": 15
  },
  "error": null
}</pre>
                <div class="info">
                    <ul>
                        <li>{{ __('Đây là API polling, không realtime.') }}</li>
                        <li>{{ __('App FizaHUB có thể gọi lại mỗi 15–30 giây khi màn hình chat đang mở.') }}</li>
                        <li>{{ __('Lưu ý: response dùng trường body, không phải message.') }}</li>
                    </ul>
                </div>
                <div class="check">{{ __('Xong khi: thấy danh sách tin nhắn trong hội thoại.') }}</div>
            </article>

            <article class="step" id="step-23">
                <h2><span class="step-num">23</span> <span class="method post">POST</span> {{ __('Test POST Gửi tin nhắn hỗ trợ') }}</h2>
                <ol>
                    <li>{{ __('Mở POST Send Support Message.') }}</li>
                    <li>{{ __('Giữ nguyên query external_business_id — bắt buộc.') }}</li>
                    <li>{{ __('Mở Body → raw → JSON và dán Body raw JSON bên dưới.') }}</li>
                    <li>{{ __('Bấm Send.') }}</li>
                    <li>{{ __('Mở lại GET Support Ticket Detail và bấm Send để thấy tin nhắn mới.') }}</li>
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
  "message": "Dạ cửa hàng em muốn ưu tiên tạo QR check-in trước."
}</pre>
                <span class="copy-label">{{ __('Kết quả mong đợi') }}</span>
                <pre>HTTP 201</pre>
                <span class="copy-label">{{ __('Response mẫu') }}</span>
                <pre>{
  "success": true,
  "data": {
    "message_id": "secure-message-id",
    "sender_type": "business",
    "body": "Dạ cửa hàng em muốn ưu tiên tạo QR check-in trước."
  },
  "error": null
}</pre>
                <div class="info">
                    {{ __('Request gửi message; response trả về body. Đây là quy ước API partner.') }}
                </div>
                <div class="check">{{ __('Xong khi: tin nhắn mới xuất hiện trong chi tiết ticket.') }}</div>
            </article>

            <article class="step" id="step-24">
                <h2><span class="step-num">24</span> <span class="method post">POST</span> {{ __('Test POST Tải file đính kèm') }}</h2>
                <ol>
                    <li>{{ __('Mở POST Upload Support Attachment.') }}</li>
                    <li>{{ __('Mở tab Body, chọn form-data.') }}</li>
                    <li>{{ __('Thêm field file, đổi Type sang File, chọn một file nhỏ (ảnh/PDF) trên máy.') }}</li>
                    <li>{{ __('Bấm Send.') }}</li>
                </ol>
                <span class="copy-label">URL</span>
                <pre>@{{base_url}}/api/v1/partners/fizahub/support-tickets/@{{ticket_id}}/attachments?external_business_id=@{{external_business_id}}</pre>
                <div class="note">
                    <ul>
                        <li>{{ __('Kích thước và loại file bị giới hạn theo cấu hình; file quá lớn/không hợp lệ trả 422.') }}</li>
                        <li>{{ __('Ticket đã đóng/đã xử lý sẽ từ chối file đính kèm mới (ticket_not_open).') }}</li>
                    </ul>
                </div>
                <span class="copy-label">{{ __('Kết quả mong đợi') }}</span>
                <pre>HTTP 201</pre>
                <div class="check">{{ __('Xong khi: file đính kèm xuất hiện trong attachments của GET Support Ticket Detail.') }}</div>
            </article>

            <article class="step" id="step-25">
                <h2><span class="step-num">25</span> <span class="method patch">PATCH</span> / <span class="method post">POST</span> {{ __('Test PATCH Đóng ticket / POST Mở lại ticket') }}</h2>
                <ol>
                    <li>{{ __('Mở PATCH Close Support Ticket, giữ query external_business_id, bấm Send.') }}</li>
                    <li>{{ __('Kiểm tra GET Support Ticket Detail để thấy status đã đổi.') }}</li>
                    <li>{{ __('Muốn tiếp tục hội thoại thì mở POST Reopen Support Ticket và bấm Send.') }}</li>
                </ol>
                <span class="copy-label">{{ __('URL đóng ticket') }}</span>
                <pre>@{{base_url}}/api/v1/partners/fizahub/support-tickets/@{{ticket_id}}/close?external_business_id=@{{external_business_id}}</pre>
                <span class="copy-label">{{ __('URL mở lại ticket') }}</span>
                <pre>@{{base_url}}/api/v1/partners/fizahub/support-tickets/@{{ticket_id}}/reopen?external_business_id=@{{external_business_id}}</pre>
                <div class="note">{{ __('Ticket đã đóng/đã xử lý sẽ từ chối tin nhắn hoặc file đính kèm mới cho tới khi được mở lại.') }}</div>
                <div class="check">{{ __('Xong khi: status của ticket đổi đúng theo thao tác close/reopen.') }}</div>
            </article>

            <div class="panel" style="margin-top:1.2rem;" id="troubleshooting">
                <h2 style="margin:0 0 .6rem;font-size:1.1rem;">{{ __('Lỗi thường gặp') }}</h2>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ __('Mã HTTP') }}</th>
                                <th>{{ __('Lỗi') }}</th>
                                <th>{{ __('Nguyên nhân thường gặp') }}</th>
                                <th>{{ __('Cách xử lý') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>401</td>
                                <td><code>invalid_partner_token</code></td>
                                <td>{{ __('partner_token sai hoặc chưa cấu hình trên server.') }}</td>
                                <td>{{ __('Kiểm tra biến partner_token và ENV FIZAHUB_PARTNER_TOKEN trên server.') }}</td>
                            </tr>
                            <tr>
                                <td>400</td>
                                <td><code>invalid_partner_header</code></td>
                                <td>{{ __('Thiếu X-Partner hoặc X-Request-Id.') }}</td>
                                <td>{{ __('Kiểm tra tab Headers của request.') }}</td>
                            </tr>
                            <tr>
                                <td>422</td>
                                <td><code>validation_failed</code></td>
                                <td>{{ __('Thiếu field, email/url/ngày sai, thiếu Idempotency-Key, hoặc thiếu external_business_id ở chi tiết/gửi tin ticket.') }}</td>
                                <td>{{ __('Đọc error.details và sửa Body/Params.') }}</td>
                            </tr>
                            <tr>
                                <td>404</td>
                                <td><code>integration_not_found</code> / <code>resource_not_found</code></td>
                                <td>{{ __('external_business_id chưa onboarding completed, hoặc request_id/ticket_id/campaign_id sai.') }}</td>
                                <td>{{ __('Chạy POST Onboarding trước và xác nhận status completed; kiểm tra lại ID.') }}</td>
                            </tr>
                            <tr>
                                <td>409</td>
                                <td><code>idempotency_conflict</code></td>
                                <td>{{ __('Dùng lại Idempotency-Key cũ với Body khác.') }}</td>
                                <td>{{ __('Tạo key mới, hoặc Send lại với cùng Body.') }}</td>
                            </tr>
                            <tr>
                                <td>409</td>
                                <td><code>onboarding_not_ready</code></td>
                                <td>{{ __('Gọi One-time Login khi onboarding chưa ready/completed.') }}</td>
                                <td>{{ __('Chờ tư vấn viên MLHUB hoàn tất cấu hình rồi gọi lại.') }}</td>
                            </tr>
                            <tr>
                                <td>409</td>
                                <td><code>invalid_status_transition</code></td>
                                <td>{{ __('Gọi confirm/cancel khi onboarding đang ở trạng thái không cho phép.') }}</td>
                                <td>{{ __('Kiểm tra status hiện tại bằng GET Onboarding Status trước khi gọi.') }}</td>
                            </tr>
                            <tr>
                                <td>422</td>
                                <td><code>ticket_not_open</code></td>
                                <td>{{ __('Gửi message/attachment vào ticket đã đóng hoặc đã xử lý.') }}</td>
                                <td>{{ __('Mở lại ticket bằng POST Reopen Support Ticket trước khi gửi tiếp.') }}</td>
                            </tr>
                            <tr>
                                <td>429</td>
                                <td><code>rate_limit_exceeded</code></td>
                                <td>{{ __('Gọi API quá nhiều trong một phút.') }}</td>
                                <td>{{ __('Chờ một phút rồi thử lại.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel" style="margin-top:1.2rem;">
                <h2 style="margin:0 0 .6rem;font-size:1.1rem;">{{ __('Checklist hoàn tất') }}</h2>
                <ul>
                    <li>{{ __('Health và SSO Verify trả về thành công') }}</li>
                    <li>{{ __('Onboarding tạo/cập nhật/xác nhận/hủy và đã lưu request_id') }}</li>
                    <li>{{ __('Integration Status và Update Business Profile hoạt động đúng') }}</li>
                    <li>{{ __('Package, Dashboard, Insights, Recommendations, Campaigns trả về cho mapping completed') }}</li>
                    <li>{{ __('Ticket hỗ trợ đã tạo, liệt kê, xem chi tiết, gửi tin nhắn, đính kèm file, đóng và mở lại') }}</li>
                    <li>{{ __('URL đăng nhập một lần đã được cấp') }}</li>
                </ul>
                <div class="cta-row" style="margin-top:1rem;">
                    <a class="btn btn-primary" href="{{ $docsUrl }}">{{ __('Quay lại tài liệu API') }}</a>
                    <a class="btn btn-ghost" href="{{ $postmanUrl }}">{{ __('Tải lại file Postman JSON') }}</a>
                </div>
            </div>
        </div>
    </section>
</main>

<footer class="footer">
    <div class="wrap">
        <p style="margin:0;">{{ __('FizaHUB Partner API - Hướng dẫn test Postman từng bước') }} · <a href="{{ $docsUrl }}">/api-fizahub</a> · <a href="{{ route('partner.fizahub.docs.help-test') }}">/api-fizahub/help-test</a></p>
    </div>
</footer>
</body>
</html>
