<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FizaHUB Partner API — Hướng dẫn chạy Postman từng bước</title>
    <meta name="description" content="Hướng dẫn từng bước cho dev FizaHUB: tải Postman, import collection 25 request, chạy thử API MLHUB.">
    <link rel="icon" href="{{ asset('img/favicon.svg') }}" type="image/svg+xml">
    <style>
        :root { --ink:#17231e; --muted:#5d6c65; --line:#d9e2dd; --brand:#0f766e; --soft:#eaf7f3; }
        * { box-sizing:border-box; }
        body { margin:0; background:#f4f7f5; color:var(--ink); font:15px/1.65 system-ui,-apple-system,"Segoe UI",sans-serif; }
        main { width:min(960px,calc(100% - 32px)); margin:30px auto 70px; }
        header.hero { background:#fff; border:1px solid var(--line); border-radius:16px; padding:24px; }
        header.hero h1 { color:var(--brand); margin:0 0 6px; font-size:clamp(24px,4vw,34px); }
        article { background:#fff; border:1px solid var(--line); border-radius:16px; padding:22px 24px; margin:16px 0; }
        h2 { color:var(--brand); margin-top:0; font-size:1.25rem; }
        h3 { margin:18px 0 8px; font-size:1.05rem; }
        code { background:#eaf4ef; padding:2px 6px; border-radius:5px; font-size:13px; }
        pre { overflow:auto; padding:14px 16px; background:#10201a; color:#d9f2e7; border-radius:12px; }
        pre code { background:none; padding:0; color:inherit; }
        .note { padding:12px 15px; border-left:4px solid var(--brand); background:var(--soft); border-radius:0 10px 10px 0; margin:14px 0; }
        .warn { padding:12px 15px; border-left:4px solid #b45309; background:#fff5e6; border-radius:0 10px 10px 0; margin:14px 0; color:#7a3d05; }
        .ok { padding:12px 15px; border-left:4px solid #15803d; background:#ecfdf3; border-radius:0 10px 10px 0; margin:14px 0; }
        a { color:var(--brand); }
        ol.steps > li { margin:10px 0; }
        table { width:100%; border-collapse:collapse; margin:12px 0; font-size:14px; }
        th,td { border:1px solid var(--line); padding:8px 10px; text-align:left; vertical-align:top; }
        th { background:#eefaf6; }
        .btn { display:inline-block; text-decoration:none; border:1px solid var(--brand); color:var(--brand); border-radius:999px; padding:9px 16px; font-weight:700; margin:4px 8px 4px 0; }
        .btn-primary { background:var(--brand); color:#fff; }
        .tag { display:inline-block; background:#0f766e; color:#fff; border-radius:6px; padding:1px 7px; font-size:12px; font-weight:700; }
        .kbd { background:#fff; border:1px solid #9db8ad; border-bottom-width:2px; border-radius:6px; padding:1px 6px; font-size:13px; white-space:nowrap; }
        .toc a { display:block; margin:4px 0; text-decoration:none; }
        .toc a:hover { text-decoration:underline; }
        .ui-box { border:1px dashed #9db8ad; background:#fbfefd; border-radius:10px; padding:12px 14px; margin:12px 0; font-family:ui-monospace,Consolas,monospace; font-size:13px; white-space:pre-wrap; }
        .muted { color:var(--muted); }
        .check { list-style:none; padding-left:0; }
        .check li::before { content:"✓ "; color:var(--brand); font-weight:700; }
    </style>
</head>
<body>
<main>
    <header class="hero">
        <h1>Chạy thử API FizaHUB × MLHUB — hướng dẫn từng bước</h1>
        <p>Viết cho người <strong>chưa từng dùng Postman</strong>. Làm đúng thứ tự bên dưới là gọi được đủ <strong>25 request</strong> bám <strong>15 màn hình Marketing</strong>. Hỗ trợ (Support) có cả tin nhắn text và <strong>đính kèm tệp</strong> (ảnh/video/zip/văn bản).</p>
        <p>
            <a class="btn btn-primary" href="{{ route('partner.fizahub.docs.postman') }}">⬇ 1. Tải file Postman (đã cấu hình sẵn)</a>
            <a class="btn" href="{{ route('partner.fizahub.docs') }}">Xem tài liệu contract</a>
        </p>
        <div class="ok"><strong>Không cần cấu hình token.</strong> File tải về đã có sẵn địa chỉ máy chủ <code>{{ $appUrl }}</code> và token thử nghiệm. Bạn gần như chỉ cần bấm <em>Import</em> rồi <em>Run</em>.</div>
        <nav class="toc note" aria-label="Mục lục">
            <strong>Mục lục nhanh</strong>
            <a href="#buoc-0">Bước 0 · Cài Postman</a>
            <a href="#buoc-1">Bước 1 · Tải &amp; Import file</a>
            <a href="#buoc-2">Bước 2 · Kiểm tra biến (Variables)</a>
            <a href="#buoc-3">Bước 3 · Chạy cả collection (khuyên dùng)</a>
            <a href="#buoc-4">Bước 4 · Chạy từng request + xem Examples</a>
            <a href="#buoc-5">Bước 5 · 25 request nghĩa là gì?</a>
            <a href="#buoc-6">Bước 6 · Tham số đầu vào thường dùng</a>
            <a href="#buoc-7">Bước 7 · Kết quả mong đợi &amp; lỗi thường gặp</a>
            <a href="#buoc-8">Bước 8 · Checklist gửi cho MLHUB khi kẹt</a>
        </nav>
    </header>

    <article id="buoc-0">
        <h2>Bước 0 · Cài Postman (một lần duy nhất)</h2>
        <ol class="steps">
            <li>Mở trình duyệt → vào <a href="https://www.postman.com/downloads/" target="_blank" rel="noreferrer">postman.com/downloads</a>.</li>
            <li>Tải bản phù hợp máy bạn (Windows / macOS), cài đặt như phần mềm bình thường.</li>
            <li>Mở Postman. Nếu hỏi đăng nhập: bấm <em>Skip for now</em> / <em>Continue without an account</em> — <strong>không bắt buộc</strong> tạo tài khoản.</li>
        </ol>
        <div class="ui-box">Màn hình chào Postman
┌────────────────────────────────────────┐
│  Welcome to Postman                    │
│  [ Create Account ]  [ Skip for now ] ← chọn cái này
└────────────────────────────────────────┘</div>
    </article>

    <article id="buoc-1">
        <h2>Bước 1 · Tải và Import collection</h2>
        <ol class="steps">
            <li>Trên trang này, bấm nút xanh <strong>⬇ 1. Tải file Postman</strong> (đủ 25 request). File tải về tên:
                <br><code>MLHUB-FizaHUB-Partner-API.postman_collection.json</code></li>
            <li>Trong Postman, góc trên bên trái, bấm nút <span class="kbd">Import</span>.</li>
            <li>Kéo–thả file vừa tải vào cửa sổ, hoặc bấm <em>Upload Files</em> / <em>Files</em> rồi chọn file.</li>
            <li>Bấm <strong>Import</strong>.</li>
            <li>Cột trái xuất hiện collection <strong>“MLHUB × FizaHUB Partner API”</strong> với 5 thư mục:
                <span class="tag">System</span>
                <span class="tag">Onboarding</span>
                <span class="tag">Growth</span>
                <span class="tag">Support</span>
                <span class="tag">CRM</span>
            </li>
        </ol>
        <div class="ui-box">Postman — Import
┌─ Collections ─────────────────────────┐
│ [Import]  ← bấm đây                   │
│                                       │
│  Sau khi import:                      │
│  ▸ MLHUB × FizaHUB Partner API        │
│     ▸ System (2)                      │
│     ▸ Onboarding (6)                  │
│     ▸ Growth (6)                      │
│     ▸ Support (10)                    │
│     ▸ CRM (1)                         │
└───────────────────────────────────────┘</div>
        <div class="warn">Nếu trước đó bạn đã import bản cũ: xóa collection cũ (chuột phải → Delete) rồi Import lại file mới nhất từ trang này.</div>
    </article>

    <article id="buoc-2">
        <h2>Bước 2 · Kiểm tra biến (đã điền sẵn — gần như không cần sửa)</h2>
        <ol class="steps">
            <li>Bấm vào tên collection <strong>MLHUB × FizaHUB Partner API</strong> (không phải từng request).</li>
            <li>Chọn tab <span class="kbd">Variables</span>.</li>
            <li>Xác nhận hai dòng sau đã có giá trị (không để trống):</li>
        </ol>
        <pre><code>base_url      = {{ $appUrl }}
partner_token = (đã điền sẵn token thử nghiệm — không cần gõ tay)</code></pre>
        <p>Các biến còn lại để trống hoặc <code>0</code> là <strong>bình thường</strong> — collection tự điền khi chạy:</p>
        <table>
            <thead><tr><th>Biến</th><th>Ai điền?</th><th>Dùng cho</th></tr></thead>
            <tbody>
                <tr><td><code>external_business_id</code></td><td>Tự sinh</td><td>Mọi API theo doanh nghiệp</td></tr>
                <tr><td><code>external_user_id</code></td><td>Tự sinh</td><td>Onboarding</td></tr>
                <tr><td><code>from</code> / <code>to</code></td><td>Tự tính 30 ngày</td><td>Dashboard / Insights / Campaigns</td></tr>
                <tr><td><code>onboarding_request_id</code></td><td>Sau bước 04</td><td>Onboarding Detail</td></tr>
                <tr><td><code>ticket_id</code></td><td>Sau bước 16/17</td><td>Support Detail → Reopen</td></tr>
                <tr><td><code>campaign_id</code></td><td>Sau bước 11</td><td>Campaign Detail / Approval</td></tr>
                <tr><td><code>onboarding_ready</code></td><td>Sau bước 05/06</td><td>Bật bước 25 CRM khi = <code>1</code></td></tr>
                <tr><td><code>attachment_id</code></td><td>Sau bước 23</td><td>Download Attachment (bước 24)</td></tr>
                <tr><td><code>campaign_approval_ready</code></td><td>Sau bước 11</td><td>Bật bước 13 khi = <code>1</code></td></tr>
            </tbody>
        </table>
        <div class="note">Bạn <strong>không cần</strong> copy token từ email hay Coolify. Khi MLHUB đổi token chính thức, chỉ cần tải lại file mới và Import đè.</div>
    </article>

    <article id="buoc-3">
        <h2>Bước 3 · Chạy toàn bộ bằng Collection Runner (khuyên dùng)</h2>
        <ol class="steps">
            <li>Đưa chuột vào tên collection → bấm nút <span class="kbd">Run</span>
                <br><span class="muted">(hoặc menu ba chấm <em>…</em> → <em>Run collection</em>).</span></li>
            <li>Giữ nguyên thứ tự request từ trên xuống (01 → 25).</li>
            <li>Nếu thấy tùy chọn <em>Keep variable values</em> — hãy <strong>bật</strong>.</li>
            <li>Bấm nút lớn <strong>Run MLHUB × FizaHUB Partner API</strong>.</li>
            <li>Đợi Postman chạy xong. Các bước kiểm tra (Tests) hiện màu xanh là ổn.</li>
        </ol>
        <div class="ui-box">Collection Runner
┌─ Run collection ──────────────────────┐
│  MLHUB × FizaHUB Partner API          │
│  ☑ Keep variable values               │
│                                       │
│  [ Run MLHUB × FizaHUB Partner API ] ←│
└───────────────────────────────────────┘</div>
        <p>Thứ tự nghiệp vụ đã sắp sẵn:</p>
        <table>
            <thead><tr><th>Thư mục</th><th>Số</th><th>Việc làm</th></tr></thead>
            <tbody>
                <tr><td><span class="tag">System</span></td><td>2</td><td>Kiểm tra API sống + xác minh token.</td></tr>
                <tr><td><span class="tag">Onboarding</span></td><td>6</td><td>Catalog → tạo tài khoản → theo dõi → cập nhật hồ sơ/mục tiêu.</td></tr>
                <tr><td><span class="tag">Growth</span></td><td>6</td><td>Dashboard, phân tích, chiến dịch, gói.</td></tr>
                <tr><td><span class="tag">Support</span></td><td>10</td><td>Preset → tạo ticket → hội thoại text → đóng/mở lại → liệt kê/tải lên/tải xuống đính kèm.</td></tr>
                <tr><td><span class="tag">CRM</span></td><td>1</td><td>Tạo link đăng nhập 1 lần (chỉ khi onboarding sẵn sàng).</td></tr>
            </tbody>
        </table>
        <div class="note"><strong>Skip là bình thường:</strong> bước 12/13/18–24 có thể tự bỏ qua nếu chưa có <code>campaign_id</code>, <code>ticket_id</code>, hoặc onboarding chưa <code>ready</code>. Không phải lỗi máy chủ.</div>
    </article>

    <article id="buoc-4">
        <h2>Bước 4 · Hoặc chạy từng request để xem chi tiết</h2>
        <ol class="steps">
            <li>Mở thư mục <em>System</em> → chọn <em>01 · Health</em>.</li>
            <li>Bấm nút xanh <span class="kbd">Send</span>.</li>
            <li>Xem khung <em>Response</em> phía dưới: mã HTTP (góc trên) và JSON.</li>
            <li>Mở tab <span class="kbd">Body</span> (phía trên Send) để xem dữ liệu gửi đi (với POST/PATCH).</li>
            <li>Mở tab <span class="kbd">Params</span> để xem query — một số dòng đang tắt (checkbox trống); bật khi muốn thử <code>custom</code> range, filter, v.v.</li>
            <li>Góc phải trên request, mở mục <strong>Examples</strong>: xem sẵn mẫu <code>200</code>, <code>201</code>, <code>404</code>, <code>409</code>, <code>422</code>… trước khi gọi thật.</li>
        </ol>
        <div class="ui-box">Một request trong Postman
┌─ 01 · Health ──────────── [Examples ▾] ┐
│  GET  @{{base_url}}/api/v1/.../health  │
│  [Params] [Authorization] [Headers]    │
│  [Body] [Scripts]                      │
│                         [ Save ][ Send ]│
├─ Response ─────────────────────────────┤
│  Status: 200 OK                        │
│  { "success": true, "data": {...} }    │
└────────────────────────────────────────┘</div>
        <p class="muted">Phần Description (bên dưới tên request hoặc tab Docs) giải thích: màn hình UI nào, tham số, enum, mã lỗi.</p>
    </article>

    <article id="buoc-5">
        <h2>Bước 5 · 25 request nghĩa là gì? (map 15 màn hình)</h2>
        <table>
            <thead><tr><th>#</th><th>Request trong Postman</th><th>Màn hình / việc</th><th>Kết quả mong đợi</th></tr></thead>
            <tbody>
                <tr><td>01</td><td>Health</td><td>Hệ thống</td><td><code>200</code> + <code>status=ok</code> (hoặc <code>503</code> degraded)</td></tr>
                <tr><td>02</td><td>SSO Verify</td><td>Hệ thống</td><td><code>200</code> authenticated</td></tr>
                <tr><td>03</td><td>Marketing Catalog</td><td>Màn 02 — Chọn giải pháp</td><td>Danh sách goals / ngành / gói</td></tr>
                <tr><td>04</td><td>Create Onboarding</td><td>Màn 03 — Đăng ký</td><td><code>200</code>/<code>201</code>/<code>202</code> + lưu request_id</td></tr>
                <tr><td>05</td><td>Onboarding Detail</td><td>Màn 05 — Theo dõi</td><td>Timeline + status</td></tr>
                <tr><td>06</td><td>Marketing Status</td><td>Màn 01/04 — Trang chủ</td><td>activation + capabilities</td></tr>
                <tr><td>07</td><td>Update Profile</td><td>Cập nhật hồ sơ</td><td><code>200</code></td></tr>
                <tr><td>08</td><td>Update Preferences</td><td>Màn 02/10 — Mục tiêu/gói</td><td><code>200</code> (không tự kích hoạt gói)</td></tr>
                <tr><td>09</td><td>Dashboard</td><td>Màn 06/07</td><td><code>200</code> (kể cả số 0)</td></tr>
                <tr><td>10</td><td>Growth Insights</td><td>Màn 08</td><td>score + recommendations</td></tr>
                <tr><td>11</td><td>Campaign List</td><td>Màn 09</td><td>list + lưu campaign_id</td></tr>
                <tr><td>12</td><td>Campaign Detail</td><td>Màn 11</td><td>chi tiết (skip nếu chưa có ID)</td></tr>
                <tr><td>13</td><td>Campaign Approval</td><td>Màn 11 — Duyệt</td><td>chỉ chạy khi pending_approval</td></tr>
                <tr><td>14</td><td>Business Package</td><td>Màn 10</td><td>gói effective/requested</td></tr>
                <tr><td>15</td><td>Support Presets</td><td>Màn 12/13</td><td>4 preset công khai</td></tr>
                <tr><td>16</td><td>Create Support Ticket</td><td>Màn 13</td><td><code>201</code> + lưu ticket_id</td></tr>
                <tr><td>17</td><td>List Support Tickets</td><td>Màn 12</td><td>summary + items</td></tr>
                <tr><td>18</td><td>Ticket Detail</td><td>Màn 14</td><td>messages[] text</td></tr>
                <tr><td>19</td><td>Send Message</td><td>Màn 14</td><td><code>201</code></td></tr>
                <tr><td>20</td><td>Close Ticket</td><td>Màn 14</td><td><code>200</code> closed</td></tr>
                <tr><td>21</td><td>Reopen Ticket</td><td>Màn 14</td><td><code>200</code> open</td></tr>
                <tr><td>22</td><td>List Attachments</td><td>Màn 14</td><td>danh sách tệp đính kèm (business + admin)</td></tr>
                <tr><td>23</td><td>Upload Attachment</td><td>Màn 14</td><td><code>201</code> + lưu attachment_id</td></tr>
                <tr><td>24</td><td>Download Attachment</td><td>Màn 14</td><td><code>200</code> trả file nhị phân</td></tr>
                <tr><td>25</td><td>CRM Login Link</td><td>Màn 15</td><td><code>201</code> khi ready (không thì skip)</td></tr>
            </tbody>
        </table>
    </article>

    <article id="buoc-6">
        <h2>Bước 6 · Tham số đầu vào thường dùng (đã có sẵn trong file)</h2>
        <h3>Header (collection tự gắn — bạn không cần thêm)</h3>
        <table>
            <thead><tr><th>Header</th><th>Giá trị</th><th>Khi nào</th></tr></thead>
            <tbody>
                <tr><td><code>Authorization</code></td><td><code>Bearer @{{partner_token}}</code></td><td>Mọi request</td></tr>
                <tr><td><code>X-Partner</code></td><td><code>fizahub</code></td><td>Mọi request</td></tr>
                <tr><td><code>X-Request-Id</code></td><td>UUID mới</td><td>Mọi request</td></tr>
                <tr><td><code>Idempotency-Key</code></td><td>UUID mới</td><td>POST/PATCH (trừ SSO Verify)</td></tr>
                <tr><td><code>Accept</code></td><td><code>application/json</code></td><td>Mọi request</td></tr>
            </tbody>
        </table>

        <h3>Body Create Onboarding (bước 04) — các trường</h3>
        <table>
            <thead><tr><th>Trường</th><th>Bắt buộc?</th><th>Ghi chú</th></tr></thead>
            <tbody>
                <tr><td><code>external_business_id</code></td><td>Có</td><td>Tự sinh trong collection</td></tr>
                <tr><td><code>external_user_id</code></td><td>Không</td><td>Tự sinh</td></tr>
                <tr><td><code>marketing_goal_codes</code></td><td>Có (1–3)</td><td><code>local_presence</code>, <code>qr_checkin</code>, <code>voucher_return</code>, <code>customer_retention</code></td></tr>
                <tr><td><code>package_code</code> hoặc <code>requested_package_code</code></td><td>Có 1 trong các gói</td><td><code>free</code> | <code>base</code> | <code>biz</code> | <code>plus</code></td></tr>
                <tr><td><code>owner.name</code>, <code>owner.email</code></td><td>Có</td><td><code>owner.phone</code> tùy chọn</td></tr>
                <tr><td><code>business.name/industry/phone/email/address</code></td><td>Có</td><td><code>website</code> tùy chọn</td></tr>
            </tbody>
        </table>
        <div class="warn">Không gửi giấy tờ/CCCD/file giấy phép trong payload JSON của Onboarding — sẽ bị <code>422</code>. Tệp đính kèm (ảnh/video/zip/văn bản) chỉ gửi qua request <em>23 · Upload Attachment</em> ở nhóm Support (<code>multipart/form-data</code>, field <code>file</code>).</div>

        <h3>Upload Attachment (bước 23) — field <code>file</code></h3>
        <table>
            <thead><tr><th>Loại</th><th>Đuôi/MIME cho phép</th><th>Dung lượng tối đa</th></tr></thead>
            <tbody>
                <tr><td>Hình ảnh</td><td><code>jpg</code>, <code>jpeg</code>, <code>png</code>, <code>webp</code>, <code>gif</code></td><td>25MB (mặc định)</td></tr>
                <tr><td>Video</td><td><code>mp4</code>, <code>mov</code>, <code>webm</code>, <code>avi</code></td><td>100MB (mặc định, riêng cho video)</td></tr>
                <tr><td>Nén</td><td><code>zip</code></td><td>25MB (mặc định)</td></tr>
                <tr><td>Văn bản</td><td><code>pdf</code>, <code>txt</code>, <code>csv</code>, <code>doc(x)</code>, <code>xls(x)</code>, <code>ppt(x)</code></td><td>25MB (mặc định)</td></tr>
            </tbody>
        </table>
        <div class="note">Đúng <strong>1 tệp / request</strong>. Cần gửi nhiều tệp thì gọi lại request 23 nhiều lần. Server tự dò định dạng thật của tệp (không tin theo phần mở rộng hay Content-Type client khai) — đổi tên tệp nguy hiểm thành đuôi ảnh/văn bản sẽ vẫn bị từ chối.</div>

        <h3>Query Dashboard / Insights / Campaigns</h3>
        <table>
            <thead><tr><th>Param</th><th>Giá trị</th><th>Mặc định</th></tr></thead>
            <tbody>
                <tr><td><code>range</code></td><td><code>today</code> | <code>7d</code> | <code>30d</code> | <code>90d</code> | <code>custom</code></td><td><code>30d</code></td></tr>
                <tr><td><code>from</code>, <code>to</code></td><td><code>YYYY-MM-DD</code>, from ≤ to, ≤366 ngày</td><td>Chỉ khi <code>custom</code></td></tr>
                <tr><td><code>force_refresh</code></td><td><code>true</code>/<code>false</code></td><td>Chỉ Dashboard</td></tr>
                <tr><td><code>status</code> (campaigns)</td><td><code>draft</code>…<code>cancelled</code></td><td>Tất cả</td></tr>
                <tr><td><code>per_page</code></td><td>1–100</td><td>20</td></tr>
                <tr><td><code>cursor</code></td><td>từ <code>pagination.next_cursor</code></td><td>—</td></tr>
            </tbody>
        </table>

        <h3>Body Support Ticket (bước 16)</h3>
        <ul>
            <li><code>preset_code</code> (tùy chọn): <code>qr_scan_not_recorded</code>, <code>growth_recommendation</code>, <code>campaign_request</code>, <code>package_upgrade</code></li>
            <li><code>message</code> (bắt buộc, 1–5000 ký tự)</li>
            <li><code>subject</code> bắt buộc nếu không dùng preset</li>
            <li><code>campaign_id</code> bắt buộc nếu preset = <code>campaign_request</code></li>
            <li><code>response_channel</code>: <code>in_app</code> | <code>phone</code></li>
        </ul>

        <h3>Enum trạng thái hay gặp</h3>
        <ul>
            <li><strong>Onboarding:</strong> <code>awaiting_consultant</code>, <code>needs_review</code>, <code>in_consultation</code>, <code>configuring</code>, <code>ready</code>, <code>completed</code>, <code>cancelled</code></li>
            <li><strong>Activation:</strong> <code>onboarding</code>, <code>active</code>, <code>suspended</code></li>
            <li><strong>Ticket:</strong> <code>open</code>, <code>resolved</code>, <code>closed</code></li>
            <li><strong>Campaign:</strong> <code>draft</code>, <code>pending_approval</code>, <code>active</code>, <code>paused</code>, <code>completed</code>, <code>cancelled</code></li>
        </ul>
    </article>

    <article id="buoc-7">
        <h2>Bước 7 · Kết quả mong đợi &amp; lỗi thường gặp</h2>
        <h3>Khi chạy thành công</h3>
        <ul class="check">
            <li><strong>Health</strong>: <code>200</code>, <code>data.status = "ok"</code></li>
            <li><strong>Create Onboarding</strong>: <code>200</code>/<code>201</code>/<code>202</code> và có <code>request_id</code></li>
            <li><strong>Dashboard / Campaigns / Tickets</strong>: luôn <code>200</code> dù chưa có dữ liệu (số 0 / mảng rỗng)</li>
            <li><strong>Create Support Ticket</strong>: <code>201</code>, rồi Detail → Message (<code>201</code>) → Close → Reopen dùng chung <code>ticket_id</code></li>
            <li>Gọi lại Onboarding cùng business/email đã map → <code>200</code>, <code>already_registered=true</code> (không tạo trùng)</li>
            <li>Mọi response có <code>meta.request_id</code> — gửi kèm khi hỏi MLHUB</li>
        </ul>

        <h3>Bảng lỗi tra nhanh</h3>
        <table>
            <thead><tr><th>HTTP / code</th><th>Ý nghĩa</th><th>Bạn làm gì?</th></tr></thead>
            <tbody>
                <tr><td><code>401 invalid_partner_token</code></td><td>Token sai/thiếu</td><td>Tải lại file Postman mới nhất từ trang này và Import lại</td></tr>
                <tr><td><code>400 invalid_partner_header</code></td><td>Sai <code>X-Partner</code> hoặc <code>X-Request-Id</code></td><td>Dùng lại request trong collection (đã đúng sẵn)</td></tr>
                <tr><td><code>422 validation_failed</code></td><td>Dữ liệu sai</td><td>Xem <code>error.details</code> — trường nào sai</td></tr>
                <tr><td><code>409 idempotency_conflict</code></td><td>Cùng key nhưng body khác</td><td>Gửi lại (Postman tự sinh Idempotency-Key mới)</td></tr>
                <tr><td><code>409 onboarding_not_ready</code></td><td>CRM khi chưa ready</td><td>Chờ MLHUB cấu hình xong; request 25 sẽ skip tự động</td></tr>
                <tr><td><code>409 campaign_invalid_state</code></td><td>Duyệt khi không còn pending</td><td>Bình thường nếu chiến dịch đã active</td></tr>
                <tr><td><code>409 ticket_not_open</code></td><td>Gửi tin/đính kèm vào ticket đã đóng</td><td>Reopen ticket (bước 21) rồi thử lại</td></tr>
                <tr><td><code>422 attachment_type_not_allowed</code></td><td>Sai định dạng/đuôi tệp đính kèm</td><td>Xem bảng loại tệp cho phép ở Bước 6</td></tr>
                <tr><td><code>422 attachment_too_large</code></td><td>Tệp vượt dung lượng cho phép</td><td>Xem <code>error.details.max_size_mb</code></td></tr>
                <tr><td><code>404 attachment_not_found</code></td><td>Sai <code>attachment_id</code> hoặc file đã bị xóa</td><td>Gọi lại bước 22 List Attachments để lấy id đúng</td></tr>
                <tr><td><code>404 integration_not_found</code></td><td>Chưa onboarding / sai business id</td><td>Chạy lại từ bước 04 Create Onboarding</td></tr>
                <tr><td><code>503</code> / degraded</td><td>Máy chủ chưa sẵn sàng</td><td>Chạy lại Health; vẫn lỗi thì gửi <code>meta.request_id</code> cho MLHUB</td></tr>
                <tr><td><code>429 rate_limit_exceeded</code></td><td>Gọi quá nhanh</td><td>Đợi ~1 phút rồi chạy lại</td></tr>
            </tbody>
        </table>
        <div class="ok"><strong>Support đã hỗ trợ đính kèm.</strong> Ngoài tin nhắn text trong <code>messages[]</code>, dùng nhóm request 22–24 để liệt kê/tải lên/tải xuống ảnh, video, zip và văn bản đính kèm ticket.</div>
    </article>

    <article id="buoc-8">
        <h2>Bước 8 · Checklist gửi cho MLHUB khi cần hỗ trợ</h2>
        <ol class="steps">
            <li>Copy <code>meta.request_id</code> trong response lỗi.</li>
            <li>Ghi rõ request số mấy (ví dụ <em>04 · Create Onboarding</em>) và giờ gọi (giờ Việt Nam).</li>
            <li>Đính kèm screenshot khung Response (che bớt token nếu gửi kênh công khai).</li>
            <li>Cho biết đã Import file từ <code>{{ url('/api-fizahub/postman') }}</code> ngày nào.</li>
        </ol>
        <p>
            <a class="btn btn-primary" href="{{ route('partner.fizahub.docs.postman') }}">⬇ Tải lại Postman JSON</a>
            <a class="btn" href="{{ route('partner.fizahub.docs') }}">Quay lại tài liệu API</a>
        </p>
        <p class="muted">Endpoint gốc: <code>{{ $appUrl }}/api/v1/partners/fizahub</code> · Khoảng ngày mẫu Dashboard: <code>{{ $dashboardFrom }}</code> → <code>{{ $dashboardTo }}</code></p>
    </article>
</main>
</body>
</html>
