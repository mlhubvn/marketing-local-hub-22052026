<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FizaHUB Partner API — Hướng dẫn chạy Postman</title>
    <link rel="icon" href="{{ asset('img/favicon.svg') }}" type="image/svg+xml">
    <style>
        body { margin:0; background:#f4f7f5; color:#17231e; font:15px/1.65 system-ui,-apple-system,"Segoe UI",sans-serif; }
        main { width:min(920px,calc(100% - 32px)); margin:30px auto 70px; }
        article { background:#fff; border:1px solid #d9e2dd; border-radius:16px; padding:24px; margin:16px 0; }
        h1,h2 { line-height:1.25; } h1 { color:#0f766e; } code { background:#eaf4ef; padding:2px 5px; border-radius:5px; }
        .note { padding:12px 15px; border-left:4px solid #0f766e; background:#eaf7f3; }
        a { color:#0f766e; }
    </style>
</head>
<body>
<main>
    <h1>FizaHUB Partner API — chạy tuần tự 22 request</h1>
    <p>Collection bám đúng 15 màn hình UI và tự truyền ID giữa các bước. Support là text-only.</p>
    <p><a href="{{ route('partner.fizahub.docs.postman') }}">Tải Postman collection</a> · <a href="{{ route('partner.fizahub.docs') }}">Xem tài liệu contract</a></p>

    <article>
        <h2>1. Nhập partner_token một lần</h2>
        <p>File tải từ trang này đã có sẵn URL production. Sau khi import, dán token được MLHUB cấp vào collection variable <code>partner_token</code>:</p>
        <pre><code>base_url={{ $appUrl }}
partner_token=&lt;dán token FizaHUB được cấp&gt;</code></pre>
        <p>Collection tự sinh <code>external_business_id</code>, <code>external_user_id</code>, <code>from</code> và <code>to</code>.</p>
        <p>Mỗi request dùng <code>X-Request-Id</code> là UUID mới. Mỗi write request dùng <code>Idempotency-Key</code> mới.</p>
        <div class="note">Token không nằm trong HTML hoặc JSON tải công khai. Chỉ lưu token trong Postman collection/environment riêng của đội FizaHUB.</div>
    </article>

    <article>
        <h2>2. Chạy theo thứ tự folder</h2>
        <ol>
            <li><strong>System (2):</strong> Health, SSO Verify.</li>
            <li><strong>Onboarding (6):</strong> Catalog, Create, Detail, Marketing Status, Profile, Preferences.</li>
            <li><strong>Growth (6):</strong> Dashboard, Insights, Campaign List, Detail, Approval, Package.</li>
            <li><strong>Support (7):</strong> Presets, Create, List, Detail, Message, Close, Reopen.</li>
            <li><strong>CRM (1):</strong> tạo link khi onboarding đã ready/completed.</li>
        </ol>
    </article>

    <article>
        <h2>3. ID được lưu tự động</h2>
        <ul>
            <li><code>onboarding_request_id</code> và <code>onboarding_ticket_id</code> lấy từ Create Onboarding.</li>
            <li><code>campaign_id</code> lấy từ campaign đầu tiên nếu có.</li>
            <li><code>ticket_id</code> lấy đúng ticket vừa tạo; List không chọn nhầm ticket onboarding.</li>
        </ul>
        <div class="note">Request phụ thuộc sẽ gọi <code>pm.execution.skipRequest()</code> nếu ID chưa có. CRM cũng được skip khi onboarding chưa sẵn sàng.</div>
    </article>

    <article>
        <h2>4. Kết quả mong đợi</h2>
        <ul>
            <li>Onboarding chấp nhận HTTP 200/201/202 và luôn trả request ID.</li>
            <li>Dashboard/campaign/support list zero-data vẫn HTTP 200.</li>
            <li>Create Ticket HTTP 201, sau đó Detail → Message → Close → Reopen đều dùng cùng <code>ticket_id</code>.</li>
            <li>Gửi lại Onboarding bằng key mới trả HTTP 200, <code>already_registered=true</code>, ticket onboarding giữ nguyên.</li>
            <li>Mọi response có <code>meta.request_id</code> để truy vết.</li>
        </ul>
    </article>
</main>
</body>
</html>
