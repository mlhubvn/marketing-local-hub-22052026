<?php

use Modules\APIPartnerFizaHUB\Support\PartnerReportingAdminIds;

return [
    'partner_code' => 'fizahub',
    // Default 'fizahub' cho GIAI ĐOẠN THỬ NGHIỆM để đối tác test không cần cấu hình Coolify.
    // ⚠️ Đặt FIZAHUB_PARTNER_TOKEN token mạnh trên Coolify trước khi chạy chính thức.
    'token' => env('FIZAHUB_PARTNER_TOKEN', 'fizahub'),
    'rate_limit_per_minute' => (int) env('FIZAHUB_RATE_LIMIT_PER_MINUTE', 60),
    // 15 phút (không phải 5) để chịu được độ trễ thực tế giữa lúc partner gọi API lấy
    // link và lúc người dùng cuối thực sự bấm (test tay copy/paste, mạng chậm, lệch giờ
    // client). Partner luôn nên đọc `expires_in_seconds` từ response, không hard-code số này.
    'one_time_login_ttl_minutes' => (int) env('FIZAHUB_ONE_TIME_LOGIN_TTL_MINUTES', 15),
    'timezone' => 'Asia/Ho_Chi_Minh',
    'locale' => env('FIZAHUB_PARTNER_LOCALE', 'vi'),
    'default_package' => env('FIZAHUB_DEFAULT_PACKAGE', 'free'),
    'package_map' => [
        'free' => 'mlhub-free-da-nang',
        'base' => 'mlhub-free-da-nang',
        'biz' => 'mlhub-growth-monthly',
        'plus' => 'mlhub-pro-monthly',
    ],
    'package_definitions' => [
        'free' => [
            'description' => 'Gói khởi tạo miễn phí để doanh nghiệp bắt đầu Marketing cùng MLHUB.',
            'features' => ['Hiện diện địa phương', 'QR Check-in cơ bản', 'Theo dõi khách hàng'],
            'recommended_goal_codes' => ['local_presence', 'qr_checkin'],
            'industry_codes' => [],
        ],
        'base' => [
            'description' => 'Gói doanh nghiệp quan tâm với tư vấn và cấu hình tăng trưởng mở rộng.',
            'features' => ['Mã ưu đãi', 'Chăm sóc khách hàng', 'Tư vấn chiến dịch'],
            'recommended_goal_codes' => ['voucher_return', 'customer_retention'],
            'industry_codes' => [],
        ],
        'biz' => [
            'description' => 'Gói doanh nghiệp mở rộng: tự động hóa Marketing, CRM đa kênh và quản lý nhiều cơ sở.',
            'features' => ['Tự động hóa Marketing', 'CRM nâng cao', 'Quản lý nhiều cơ sở'],
            'recommended_goal_codes' => ['voucher_return', 'customer_retention'],
            'industry_codes' => [],
        ],
        'plus' => [
            'description' => 'Gói cao cấp cho doanh nghiệp cần đầy đủ AI Studio, CRM không giới hạn và thương hiệu riêng.',
            'features' => ['AI Studio đầy đủ', 'CRM không giới hạn', 'Loại bỏ thương hiệu MLHUB'],
            'recommended_goal_codes' => ['local_presence', 'qr_checkin', 'voucher_return'],
            'industry_codes' => [],
        ],
    ],
    'marketing_goals' => [
        'local_presence' => [
            'label' => 'Hiện diện',
            'description' => 'Tăng hiện diện địa phương.',
        ],
        'qr_checkin' => [
            'label' => 'QR Check-in',
            'description' => 'Thu lead ngay tại cửa hàng.',
        ],
        'voucher_return' => [
            'label' => 'Mã ưu đãi',
            'description' => 'Khuyến khích khách hàng quay lại.',
        ],
        'customer_retention' => [
            'label' => 'Khách hàng',
            'description' => 'Lưu và chăm sóc khách hàng cũ.',
        ],
    ],
    'provisional_email_domain' => env('FIZAHUB_PROVISIONAL_EMAIL_DOMAIN', 'provisional.mlhub.vn'),
    // URL do FizaHUB cung cấp — để trống thì webhook tự bỏ qua (không lỗi).
    'webhook_base_url' => env('FIZAHUB_WEBHOOK_BASE_URL', ''),
    // Default secret cho giai đoạn thử nghiệm — ⚠️ đổi + chia sẻ lại cho FizaHUB khi chạy chính thức.
    'webhook_secret' => env('FIZAHUB_WEBHOOK_SECRET', '600b3a729836b0461ea51c2067a1911acb83200e0b148404676f037e8556e56a'),
    'dashboard_cache_ttl_minutes' => (int) env('FIZAHUB_DASHBOARD_CACHE_TTL_MINUTES', 45),
    'dashboard_force_refresh_cooldown_seconds' => (int) env('FIZAHUB_DASHBOARD_FORCE_REFRESH_COOLDOWN_SECONDS', 300),
    // Giới hạn chung (ảnh, tài liệu). Video dùng trần riêng cao hơn bên dưới.
    'support_max_attachment_size_mb' => (int) env('FIZAHUB_SUPPORT_MAX_ATTACHMENT_SIZE_MB', 25),
    // Trần riêng cho video vì clip quay tại chỗ thường nặng hơn nhiều so với ảnh/PDF.
    'support_max_video_attachment_size_mb' => (int) env('FIZAHUB_SUPPORT_MAX_VIDEO_ATTACHMENT_SIZE_MB', 100),
    // TTL (ngày) cho image_url ký tạm — app FizaHUB dùng mở ảnh trong chat không cần header partner.
    // Mỗi lần list/upload attachments sẽ cấp URL mới; hết hạn thì gọi lại list để lấy URL mới.
    'support_image_preview_ttl_days' => max(1, (int) env('FIZAHUB_SUPPORT_IMAGE_PREVIEW_TTL_DAYS', 7)),
    // 3 nhóm cho HKD hỗ trợ: ảnh (preview trong chat) + video (quay lỗi) + tài liệu (pdf/office).
    // Không nhận zip / txt / csv / ppt — ít dùng và khó xem trong app.
    'support_allowed_attachment_types' => [
        // Hình ảnh
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        // Video
        'video/mp4',
        'video/quicktime',
        'video/webm',
        // Tài liệu
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ],
    // Đối chiếu song song với phần mở rộng tên file để chặn kiểu đổi tên file nguy hiểm
    // thành đuôi vô hại (vd .php đổi thành .jpg) — cả hai điều kiện đều phải khớp.
    'support_allowed_attachment_extensions' => [
        'jpg', 'jpeg', 'png', 'webp', 'gif',
        'mp4', 'mov', 'webm',
        'pdf', 'doc', 'docx', 'xls', 'xlsx',
    ],
    'support_categories' => [
        ['code' => 'general', 'label' => 'Hỗ trợ chung'],
        ['code' => 'campaign', 'label' => 'Chiến dịch & QR'],
        ['code' => 'account', 'label' => 'Tài khoản & gói dịch vụ'],
        ['code' => 'billing', 'label' => 'Thanh toán'],
        ['code' => 'technical', 'label' => 'Kỹ thuật'],
    ],
    'industry_aliases' => [
        'restaurant_food' => 'restaurant_eatery',
    ],

    // --- FizaHUB Partner Reporting Portal (cổng báo cáo view-only, domain riêng) ---
    // Domain chạy cổng báo cáo. Rỗng = tính năng tắt hẳn (không đăng ký route domain).
    'partner_reporting_domain' => trim((string) env('FIZAHUB_DOMAIN', 'fzh.vmo.com.vn')),
    // Danh sách ID user MLHUB (users.id) được phép xem cổng báo cáo. env() chỉ được gọi ở
    // đây (tương thích `config:cache`); luật parse (khoảng trắng, giá trị rỗng/không hợp lệ,
    // trùng lặp) nằm trong PartnerReportingAdminIds::parse() để có thể unit-test độc lập.
    // Mặc định KHÔNG ai được truy cập nếu chưa cấu hình (chuỗi rỗng → mảng rỗng).
    'partner_reporting_admin_ids' => PartnerReportingAdminIds::parse((string) env('FIZAHUB_ADMIN', '')),
];
