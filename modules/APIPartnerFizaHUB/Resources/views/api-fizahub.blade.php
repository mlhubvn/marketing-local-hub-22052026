<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MLHUB × FizaHUB Partner API</title>
    <meta name="description" content="FizaHUB Partner API: 25 endpoint cho 15 màn hình Marketing.">
    <link rel="icon" href="{{ asset('img/favicon.svg') }}" type="image/svg+xml">
    <style>
        :root { color-scheme: light; --ink:#17231e; --muted:#5d6c65; --line:#d9e2dd; --brand:#0f766e; }
        * { box-sizing:border-box; }
        body { margin:0; color:var(--ink); background:#f5f8f6; font:15px/1.65 system-ui,-apple-system,"Segoe UI",sans-serif; }
        main { width:min(1120px,calc(100% - 32px)); margin:28px auto 64px; background:#fff; border:1px solid var(--line); border-radius:18px; padding:clamp(20px,4vw,48px); box-shadow:0 16px 45px rgba(23,35,30,.08); }
        h1,h2,h3 { line-height:1.25; } h1 { color:var(--brand); font-size:clamp(28px,5vw,46px); }
        h2 { margin-top:36px; border-bottom:1px solid var(--line); padding-bottom:8px; }
        p,li { max-width:88ch; } a { color:var(--brand); }
        table { width:100%; border-collapse:collapse; display:block; overflow:auto; }
        th,td { border:1px solid var(--line); padding:8px 10px; text-align:left; white-space:nowrap; }
        code { background:#edf5f1; padding:2px 5px; border-radius:5px; }
        pre { overflow:auto; padding:16px; background:#10201a; color:#d9f2e7; border-radius:12px; }
        pre code { background:none; padding:0; border-radius:0; color:inherit; white-space:pre; }
        .actions { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:26px; }
        .actions a { text-decoration:none; border:1px solid var(--brand); border-radius:999px; padding:8px 14px; font-weight:700; }
        .quickstart { margin:22px 0 30px; padding:18px 20px; border:1px solid #99d5c5; border-radius:14px; background:#eefaf6; }
        .quickstart h2 { margin-top:0; }
        .warning { color:#8a4b08; font-weight:650; }
        .portal-callout { margin:0 0 30px; padding:18px 20px; border:1px solid #a9c6f0; border-radius:14px; background:#eef4fd; }
        .portal-callout h2 { margin-top:0; }
        .new-tag { display:inline-block; background:#1d4ed8; color:#fff; border-radius:6px; padding:1px 8px; font-size:12px; font-weight:700; margin-right:8px; vertical-align:middle; }
    </style>
</head>
<body>
<main>
    <div class="actions">
        <a href="{{ route('partner.fizahub.docs.postman') }}">⬇ Tải Postman JSON (25 request, đã cấu hình sẵn)</a>
        <a href="{{ route('partner.fizahub.docs.help-test') }}">📘 Hướng dẫn chạy từng bước (cho người mới)</a>
    </div>
    <section class="quickstart">
        <h2>Quick start — Tải về là chạy được ngay</h2>
        <p><strong>Không cần cấu hình gì.</strong> File Postman tải từ trang này đã điền sẵn <code>base_url</code> và <code>partner_token</code>. Mỗi request có đủ Params/Body và tab <strong>Examples</strong> (thành công + lỗi). Chỉ cần:</p>
        <ol>
            <li>Bấm nút <strong>Tải Postman JSON</strong> ở trên → file <code>MLHUB-FizaHUB-Partner-API.postman_collection.json</code>.</li>
            <li>Mở Postman → <strong>Import</strong> → chọn file vừa tải.</li>
            <li>Đưa chuột vào collection → bấm <strong>Run</strong> → chạy lần lượt 01 → 25 (hoặc mở từng request bấm <strong>Send</strong>).</li>
        </ol>
        <p>Collection tự sinh <code>external_business_id</code>, <code>external_user_id</code>, <code>from</code>, <code>to</code>; tự gắn <code>X-Request-Id</code> và <code>Idempotency-Key</code>; tự lưu <code>onboarding_request_id</code> / <code>campaign_id</code> / <code>ticket_id</code>. Endpoint gốc:</p>
        <pre><code>base_url={{ $appUrl }}
api_prefix={{ $baseUrl }}
health={{ $healthUrl }}</code></pre>
        <p><strong>Chưa từng dùng Postman?</strong> Làm theo <a href="{{ $helpTestUrl }}">hướng dẫn click từng nút</a> (cài Postman → Import → Variables → Run → tra lỗi).</p>
        <p class="warning">⚠️ Token trong file là token thử nghiệm. Khi chạy chính thức, MLHUB sẽ đổi token và gửi lại — vui lòng tải/import bản mới.</p>
    </section>
    <section class="portal-callout">
        <h2><span class="new-tag">CẬP NHẬT</span>Đính kèm hỗ trợ — 3 nhóm + <code>image_url</code></h2>
        <p><strong>Cho phép:</strong> ảnh (<code>jpg/png/webp/gif</code>) · video (<code>mp4/mov/webm</code>) · tài liệu (<code>pdf/doc/docx/xls/xlsx</code>).</p>
        <p><strong>Không nhận:</strong> zip, txt, csv, ppt/pptx, avi.</p>
        <ul>
            <li><code>extension</code> — đuôi file (<code>jpg</code>, <code>pdf</code>…)</li>
            <li><code>image_url</code> — chỉ ảnh: URL ký tạm có đuôi thật, <strong>không cần</strong> header — gắn ImageView. TTL 7 ngày.</li>
            <li><code>download_url</code> — tải tệp (cần Bearer); dùng cho video/tài liệu, <strong>không</strong> dùng để hiện ảnh.</li>
        </ul>
        <p><strong>Dev FizaHUB:</strong> <a href="{{ route('partner.fizahub.docs.postman') }}">tải JSON mới</a> → Import đè → Upload ảnh (23) mở <code>image_url</code> trên trình duyệt phải thấy ảnh.</p>
    </section>
    @if($reportingPortalUrl)
        <section class="portal-callout">
            <h2><span class="new-tag">MỚI</span>Cổng báo cáo dành cho lãnh đạo FizaHUB</h2>
            <p>Ngoài 25 request API ở trên (dành cho đội kỹ thuật FizaHUB tích hợp app), MLHUB có thêm một <strong>website riêng, chỉ để xem báo cáo</strong> — dành cho lãnh đạo FizaHUB, <strong>không cần biết Postman hay lập trình</strong>:</p>
            <pre><code>{{ $reportingPortalUrl }}</code></pre>
            <p>Đăng nhập bằng <strong>tài khoản MLHUB có sẵn</strong> (không cần tạo tài khoản mới). Sau khi đăng nhập, xem được: tổng số yêu cầu onboarding và tình trạng xử lý, số HKD đã có tài khoản, biểu đồ tăng trưởng theo ngày/tháng, danh sách toàn bộ HKD thuộc FizaHUB kèm bộ lọc/tìm kiếm, và trang chi tiết từng HKD (gói dịch vụ, thời hạn, chỉ số hoạt động, vé hỗ trợ) — hoàn toàn <strong>chỉ xem, không sửa được</strong> bất kỳ dữ liệu nào.</p>
            <p><strong>Cách cấp quyền:</strong> gửi cho MLHUB danh sách <em>tên và email tài khoản MLHUB</em> của những người bên FizaHUB cần xem báo cáo (mỗi người phải đã có sẵn tài khoản MLHUB) — MLHUB sẽ cấp quyền theo đúng danh sách đó. Người chưa được cấp quyền đăng nhập vào sẽ thấy thông báo từ chối truy cập rõ ràng, không thấy được dữ liệu.</p>
        </section>
    @endif
    {!! Illuminate\Support\Str::markdown((string) file_get_contents(base_path('modules/APIPartnerFizaHUB/README.md'))) !!}
</main>
</body>
</html>
