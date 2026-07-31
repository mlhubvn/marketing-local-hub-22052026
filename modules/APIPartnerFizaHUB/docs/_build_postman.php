<?php

/**
 * Generator for FizaHUB-Partner-API.postman_collection.json.
 * Run: php modules/APIPartnerFizaHUB/docs/_build_postman.php
 *
 * Keep this file: docs pages and partner handoff depend on regenerating the
 * collection whenever request params or response examples change.
 */

$PREFIX = 'api/v1/partners/fizahub';
$SAMPLE_REQUEST_ID = '9f8b2c14-7d3e-4a51-9c62-1e0a5b6f7d80';

function h(bool $write): array
{
    $headers = [
        ['key' => 'Authorization', 'value' => 'Bearer {{partner_token}}', 'description' => 'Token đối tác do MLHUB cấp. ĐÃ điền sẵn trong collection này, không cần nhập tay.'],
        ['key' => 'X-Partner', 'value' => 'fizahub', 'description' => 'Mã đối tác cố định. Luôn là "fizahub".'],
        ['key' => 'X-Request-Id', 'value' => '{{$guid}}', 'description' => 'UUID mới mỗi lần gọi (Postman tự sinh). Dùng để truy vết trong log MLHUB.'],
        ['key' => 'Accept', 'value' => 'application/json'],
    ];

    if ($write) {
        $headers[] = ['key' => 'Idempotency-Key', 'value' => '{{$guid}}', 'description' => 'BẮT BUỘC cho mọi request ghi. Cùng key + cùng payload = không tạo trùng; khác payload = HTTP 409 idempotency_conflict.'];
        $headers[] = ['key' => 'Content-Type', 'value' => 'application/json'];
    }

    return $headers;
}

/**
 * Build a Postman v2.1 url object. $query entries: ['key','value','description','disabled'?].
 */
function u(string $path, array $query = []): array
{
    global $PREFIX;

    $full = '{{base_url}}/'.$PREFIX.($path === '' ? '' : '/'.$path);
    $enabled = [];
    foreach ($query as $q) {
        if (empty($q['disabled'])) {
            $enabled[] = $q['key'].'='.$q['value'];
        }
    }
    $raw = $full.($enabled !== [] ? '?'.implode('&', $enabled) : '');

    $segments = array_merge(explode('/', $PREFIX), $path === '' ? [] : explode('/', $path));

    $url = [
        'raw' => $raw,
        'host' => ['{{base_url}}'],
        'path' => $segments,
    ];

    if ($query !== []) {
        $url['query'] = $query;
    }

    return $url;
}

function body(array|object $data): array
{
    return [
        'mode' => 'raw',
        'raw' => json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'options' => ['raw' => ['language' => 'json']],
    ];
}

function ok(array $data): array
{
    global $SAMPLE_REQUEST_ID;

    return ['success' => true, 'data' => $data, 'meta' => ['request_id' => $SAMPLE_REQUEST_ID], 'error' => null];
}

function err(string $code, string $message, array $details = []): array
{
    global $SAMPLE_REQUEST_ID;

    return [
        'success' => false,
        'data' => null,
        'meta' => ['request_id' => $SAMPLE_REQUEST_ID],
        'error' => ['code' => $code, 'message' => $message, 'details' => (object) $details],
    ];
}

/** Slim originalRequest for a saved example (no header descriptions). */
function slim(array $request): array
{
    $r = ['method' => $request['method']];
    $r['header'] = array_map(fn (array $x): array => ['key' => $x['key'], 'value' => $x['value']], $request['header']);
    if (isset($request['body'])) {
        $r['body'] = $request['body'];
    }
    $r['url'] = $request['url'];

    return $r;
}

/** Build a saved response example. */
function ex(array $request, string $name, int $code, string $statusText, array $bodyData): array
{
    return [
        'name' => $name,
        'originalRequest' => slim($request),
        'status' => $statusText,
        'code' => $code,
        '_postman_previewlanguage' => 'json',
        'header' => [['key' => 'Content-Type', 'value' => 'application/json; charset=utf-8']],
        'cookie' => [],
        'body' => json_encode($bodyData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ];
}

function testEvent(array $exec): array
{
    return ['listen' => 'test', 'script' => ['type' => 'text/javascript', 'exec' => $exec]];
}

function preEvent(array $exec): array
{
    return ['listen' => 'prerequest', 'script' => ['type' => 'text/javascript', 'exec' => $exec]];
}

// ---------------------------------------------------------------------------
// Reusable example payload fragments (accurate to the serializers).
// ---------------------------------------------------------------------------

$onboardingData201 = [
    'request_id' => 'req_9f8b2c14',
    'external_business_id' => 'fiza-store-001',
    'external_user_id' => 'fiza-owner-001',
    'username' => 'vankhoa',
    'account_created' => true,
    'business_created' => true,
    'integration_created' => true,
    'already_registered' => false,
    'status' => 'awaiting_consultant',
    'current_step' => 'awaiting_consultant',
    'status_label' => 'Chờ tư vấn viên liên hệ',
    'timeline' => [
        ['code' => 'account_created', 'label' => 'Đã tạo tài khoản và cơ sở', 'description' => 'Tài khoản MLHUB và cơ sở đã được khởi tạo.', 'status' => 'completed', 'completed_at' => '2026-07-21T01:00:00+00:00'],
        ['code' => 'awaiting_consultant', 'label' => 'Chờ tư vấn viên liên hệ', 'description' => 'MLHUB đang tiếp nhận và kiểm tra yêu cầu.', 'status' => 'current', 'completed_at' => null],
        ['code' => 'in_consultation', 'label' => 'Đang tư vấn nhu cầu', 'description' => 'Tư vấn viên đang làm rõ mục tiêu tăng trưởng.', 'status' => 'pending', 'completed_at' => null],
        ['code' => 'configuring', 'label' => 'Đang cấu hình Marketing', 'description' => 'MLHUB đang cấu hình các công cụ Marketing.', 'status' => 'pending', 'completed_at' => null],
        ['code' => 'ready', 'label' => 'Sẵn sàng sử dụng', 'description' => 'Doanh nghiệp có thể truy cập CRM MLHUB.', 'status' => 'pending', 'completed_at' => null],
    ],
    'requested_package_code' => 'base',
    'approved_package_code' => null,
    'package_code' => 'free',
    'duplicate_check' => [],
    'support_ticket_id' => 'tkt_onboarding_abc123',
    'mlhub_user_id' => 1024,
    'mlhub_workspace_id' => 512,
    'mlhub_business_id' => 256,
    'partner_confirmed_at' => null,
    'assigned_consultant_id' => null,
    'last_synced_at' => null,
];

$marketingStatusActive = [
    'external_business_id' => 'fiza-store-001',
    'external_user_id' => 'fiza-owner-001',
    'activation_status' => 'active',
    'onboarding_status' => 'ready',
    'is_ready' => true,
    'effective_package_code' => 'free',
    'requested_package_code' => 'base',
    'onboarding_request_id' => 'req_9f8b2c14',
    'support_ticket_id' => 'tkt_onboarding_abc123',
    'mlhub_user_id' => 1024,
    'mlhub_workspace_id' => 512,
    'mlhub_business_id' => 256,
    'capabilities' => ['dashboard' => true, 'support' => true, 'crm' => true],
    'links' => [
        'onboarding' => '/api/v1/partners/fizahub/onboarding-requests/req_9f8b2c14',
        'dashboard' => '/api/v1/partners/fizahub/businesses/fiza-store-001/dashboard',
        'support_tickets' => '/api/v1/partners/fizahub/businesses/fiza-store-001/support-tickets',
        'onboarding_ticket' => '/api/v1/partners/fizahub/businesses/fiza-store-001/support-tickets/tkt_onboarding_abc123',
        'crm_login_links' => '/api/v1/partners/fizahub/businesses/fiza-store-001/crm-login-links',
    ],
];

$ticketData = [
    'ticket_id' => 'tkt_7de1f9a4c0',
    'ticket_type' => 'support',
    'source' => 'fizahub',
    'preset_code' => 'qr_scan_not_recorded',
    'campaign_id' => null,
    'subject' => 'QR check-in không ghi nhận lượt quét',
    'status' => 'open',
    'last_message' => 'QR đã đặt tại quầy nhưng dashboard chưa cập nhật lượt quét.',
    'created_at' => '2026-07-21T01:05:00+00:00',
    'updated_at' => '2026-07-21T01:05:00+00:00',
    'last_message_at' => '2026-07-21T01:05:00+00:00',
    'unread_by_business' => false,
];

$attachmentData = [
    'attachment_id' => 'att_4f8c1a2b9d',
    'original_name' => 'may-quet-qr-loi.jpg',
    'mime_type' => 'image/jpeg',
    'extension' => 'jpg',
    'size_bytes' => 482133,
    'sender_type' => 'business',
    'created_at' => '2026-07-21T01:08:00+00:00',
    'download_url' => '{{base_url}}/api/v1/partners/fizahub/businesses/fiza-store-001/support-tickets/tkt_7de1f9a4c0/attachments/att_4f8c1a2b9d',
    'image_url' => '{{base_url}}/partners/fizahub/support-attachments/att_4f8c1a2b9d/preview.jpg?expires=1780000000&signature=example',
];

// ---------------------------------------------------------------------------
// 1. SYSTEM
// ---------------------------------------------------------------------------

$reqHealth = ['method' => 'GET', 'header' => h(false), 'url' => u('health')];
$health = [
    'name' => '01 · Health (kiểm tra API sẵn sàng)',
    'request' => $reqHealth + ['description' =>
        "**Màn hình:** hệ thống (chạy đầu tiên để chắc chắn API sống).\n\n".
        "Kiểm tra API, schema DB, plan mặc định và bảng hỗ trợ.\n\n".
        "**Không cần body.**\n\n".
        "**Trạng thái trả về:**\n".
        "- `200` + `data.status = \"ok\"`: mọi thứ sẵn sàng.\n".
        "- `503` + `data.status = \"degraded\"`: có dependency thiếu (xem `data.checks`). MLHUB cần chạy migration/seed."],
    'response' => [
        ex($reqHealth, '200 · OK (sẵn sàng)', 200, 'OK', ok([
            'status' => 'ok', 'partner' => 'fizahub', 'api_version' => 'v1',
            'server_time' => '2026-07-21T01:00:00+00:00',
            'checks' => [
                'database' => ['status' => 'ok', 'message' => 'Database connection is reachable.'],
                'partner_schema' => ['status' => 'ok', 'message' => 'All FizaHUB partner tables and columns are present.'],
                'default_plan' => ['status' => 'ok', 'message' => "Default plan 'free' is active."],
                'support_tables' => ['status' => 'ok', 'message' => 'AdminSupport ticket tables are present.'],
            ],
        ])),
        ex($reqHealth, '503 · Degraded (chưa deploy đủ)', 503, 'Service Unavailable', ok([
            'status' => 'degraded', 'partner' => 'fizahub', 'api_version' => 'v1',
            'server_time' => '2026-07-21T01:00:00+00:00',
            'checks' => [
                'database' => ['status' => 'ok', 'message' => 'Database connection is reachable.'],
                'partner_schema' => ['status' => 'degraded', 'message' => 'Missing FizaHUB schema: partner_support_presets. Run pending module migrations.'],
                'default_plan' => ['status' => 'ok', 'message' => "Default plan 'free' is active."],
                'support_tables' => ['status' => 'ok', 'message' => 'AdminSupport ticket tables are present.'],
            ],
        ])),
    ],
];

$reqSso = ['method' => 'POST', 'header' => h(false), 'body' => body(new stdClass), 'url' => u('partner/sso/verify')];
$sso = [
    'name' => '02 · SSO Verify (xác minh token)',
    'request' => $reqSso + ['description' =>
        "**Màn hình:** hệ thống. Xác minh token đối tác còn hợp lệ. Chỉ đọc, KHÔNG cần Idempotency-Key.\n\n".
        "**Body:** gửi `{}` (rỗng). Body khác cũng bị bỏ qua.\n\n".
        "**HTTP:**\n".
        "- `200`: token hợp lệ → `authenticated=true`.\n".
        "- `401 invalid_partner_token`: token sai/thiếu.\n".
        "- `400 invalid_partner_header`: thiếu/sai `X-Partner` hoặc `X-Request-Id` không phải UUID."],
    'response' => [
        ex($reqSso, '200 · OK', 200, 'OK', ok(['partner' => 'fizahub', 'authenticated' => true, 'server_time' => '2026-07-21T08:00:00+07:00'])),
        ex($reqSso, '401 · Token sai', 401, 'Unauthorized', err('invalid_partner_token', 'Partner token không hợp lệ.')),
        ex($reqSso, '400 · Header đối tác sai', 400, 'Bad Request', err('invalid_partner_header', 'Header X-Partner hoặc X-Request-Id không hợp lệ.')),
    ],
];

// ---------------------------------------------------------------------------
// 2. ONBOARDING
// ---------------------------------------------------------------------------

$reqCatalog = ['method' => 'GET', 'header' => h(false), 'url' => u('marketing-catalog', [
    ['key' => 'industry', 'value' => 'restaurant_food', 'description' => 'TÙY CHỌN. Mã ngành để gợi ý gói theo ngành. Ví dụ: restaurant_food.'],
])];
$catalog = [
    'name' => '03 · Marketing Catalog (mục tiêu + ngành + gói)',
    'request' => $reqCatalog + ['description' =>
        "**Màn hình 02 — Chọn giải pháp Marketing.**\n\n".
        "Lấy danh sách mục tiêu Marketing, cây ngành nghề và các gói dịch vụ.\n\n".
        "**Query params:**\n".
        "- `industry` (tùy chọn): mã ngành, để MLHUB gợi ý gói theo ngành.\n\n".
        "**Giá trị dùng ở các bước sau:**\n".
        "- `marketing_goals[].code`: `local_presence`, `qr_checkin`, `voucher_return`, `customer_retention` (chọn tối đa 3).\n".
        "- `packages[].package_code`: `free`, `base`, `biz`, `plus`."],
    'response' => [
        ex($reqCatalog, '200 · OK', 200, 'OK', ok([
            'max_goal_selection' => 3,
            'default_package_code' => 'free',
            'marketing_goals' => [
                ['code' => 'local_presence', 'label' => 'Hiện diện', 'description' => 'Tăng hiện diện địa phương.'],
                ['code' => 'qr_checkin', 'label' => 'QR Check-in', 'description' => 'Thu lead ngay tại cửa hàng.'],
                ['code' => 'voucher_return', 'label' => 'Mã ưu đãi', 'description' => 'Khuyến khích khách hàng quay lại.'],
                ['code' => 'customer_retention', 'label' => 'Khách hàng', 'description' => 'Lưu và chăm sóc khách hàng cũ.'],
            ],
            'industries' => [['code' => 'food_beverage', 'label' => 'Ẩm thực & Đồ uống', 'children' => [['code' => 'restaurant_eatery', 'label' => 'Nhà hàng / Quán ăn']]]],
            'packages' => [
                ['package_code' => 'free', 'description' => 'Gói khởi tạo miễn phí để doanh nghiệp bắt đầu Marketing cùng MLHUB.', 'features' => ['Hiện diện địa phương', 'QR Check-in cơ bản', 'Theo dõi khách hàng'], 'recommended_goal_codes' => ['local_presence', 'qr_checkin'], 'industry_codes' => ['restaurant_eatery']],
                ['package_code' => 'base', 'description' => 'Gói doanh nghiệp quan tâm với tư vấn và cấu hình tăng trưởng mở rộng.', 'features' => ['Mã ưu đãi', 'Chăm sóc khách hàng', 'Tư vấn chiến dịch'], 'recommended_goal_codes' => ['voucher_return', 'customer_retention'], 'industry_codes' => ['restaurant_eatery']],
                ['package_code' => 'biz', 'description' => 'Gói doanh nghiệp mở rộng: tự động hóa Marketing, CRM đa kênh và quản lý nhiều cơ sở.', 'features' => ['Tự động hóa Marketing', 'CRM nâng cao', 'Quản lý nhiều cơ sở'], 'recommended_goal_codes' => ['voucher_return', 'customer_retention'], 'industry_codes' => ['restaurant_eatery']],
                ['package_code' => 'plus', 'description' => 'Gói cao cấp cho doanh nghiệp cần đầy đủ AI Studio, CRM không giới hạn và thương hiệu riêng.', 'features' => ['AI Studio đầy đủ', 'CRM không giới hạn', 'Loại bỏ thương hiệu MLHUB'], 'recommended_goal_codes' => ['local_presence', 'qr_checkin', 'voucher_return'], 'industry_codes' => ['restaurant_eatery']],
            ],
        ])),
    ],
];

$onboardingBody = [
    'external_business_id' => '{{external_business_id}}',
    'external_user_id' => '{{external_user_id}}',
    'marketing_goal_codes' => ['qr_checkin', 'customer_retention'],
    'package_code' => 'base',
    'owner' => ['name' => 'Đoàn Văn Khoa', 'phone' => '0901234888', 'email' => 'fizahub.owner+{{external_business_id}}@example.com'],
    'business' => [
        'name' => 'Fiza Store',
        'industry' => 'restaurant_food',
        'phone' => '0901234888',
        'email' => 'contact@fizastore.vn',
        'website' => 'https://fizastore.vn',
        'address' => '888 Lê Duẩn, Đà Nẵng',
    ],
];
$reqCreateOnboarding = ['method' => 'POST', 'header' => h(true), 'body' => body($onboardingBody), 'url' => u('onboarding-requests')];
$createOnboarding = [
    'name' => '04 · Create Onboarding (tạo tài khoản + provision Free)',
    'request' => $reqCreateOnboarding + ['description' =>
        "**Màn hình 03 — Thông tin đăng ký.** Tạo tài khoản MLHUB + cơ sở + gói Free trong 1 lần (atomic).\n\n".
        "**Body — các trường:**\n".
        "- `external_business_id` (bắt buộc): ID business phía FizaHUB. Collection tự sinh.\n".
        "- `external_user_id` (tùy chọn): ID user phía FizaHUB.\n".
        "- `marketing_goal_codes` (bắt buộc, 1–3): trong `local_presence`, `qr_checkin`, `voucher_return`, `customer_retention`.\n".
        "- `package_code` HOẶC `requested_package_code` (bắt buộc 1 trong 2): `free` | `base` | `biz` | `plus`.\n".
        "- `owner.name` (bắt buộc), `owner.email` (bắt buộc), `owner.phone` (tùy chọn).\n".
        "- `business.name/industry/phone/email/address` (bắt buộc), `business.website` (tùy chọn).\n".
        "- KHÔNG gửi giấy tờ tùy thân/CCCD/giấy phép kinh doanh dạng file — sẽ bị 422.\n\n".
        "**Trạng thái (HTTP):**\n".
        "- `201` tạo mới → `awaiting_consultant`.\n".
        "- `202` tạo mới nhưng cần rà soát → `needs_review`.\n".
        "- `200` đã đăng ký trước đó → `already_registered=true`, trả request/ticket cũ.\n".
        "- `409 email_already_registered`: email đã thuộc tài khoản MLHUB khác.\n".
        "- `409 onboarding_email_mismatch`: business đã map bằng email khác.\n".
        "- `422 validation_failed`: payload sai.\n".
        "- `503`: schema/plan chưa sẵn sàng."],
    'event' => [testEvent([
        "pm.test('Onboarding accepted (200/201/202)', () => pm.expect([200, 201, 202]).to.include(pm.response.code));",
        "const onboardingData = (pm.response.json() || {}).data || {};",
        "if (onboardingData.request_id) {",
        "  pm.collectionVariables.set('onboarding_request_id', onboardingData.request_id);",
        "  pm.collectionVariables.set('onboarding_ticket_id', onboardingData.support_ticket_id || '');",
        "  pm.collectionVariables.set('onboarding_ready', ['ready', 'completed'].includes(onboardingData.status) ? '1' : '0');",
        "}",
    ])],
    'response' => [
        ex($reqCreateOnboarding, '201 · Tạo mới (awaiting_consultant)', 201, 'Created', ok($onboardingData201)),
        ex($reqCreateOnboarding, '202 · Cần rà soát (needs_review)', 202, 'Accepted', ok(array_merge($onboardingData201, [
            'status' => 'needs_review', 'current_step' => 'awaiting_consultant', 'status_label' => 'Cần rà soát thủ công',
            'duplicate_check' => [['type' => 'tax_code', 'id' => 77]],
        ]))),
        ex($reqCreateOnboarding, '200 · Đã đăng ký trước đó', 200, 'OK', ok(array_merge($onboardingData201, [
            'account_created' => false, 'business_created' => false, 'integration_created' => false, 'already_registered' => true,
        ]))),
        ex($reqCreateOnboarding, '409 · Email đã tồn tại', 409, 'Conflict', err('email_already_registered', 'Địa chỉ email này đã được đăng ký trên MLHUB.', ['next_action' => 'use_existing_account_or_contact_support'])),
        ex($reqCreateOnboarding, '409 · Email không khớp mapping', 409, 'Conflict', err('onboarding_email_mismatch', 'Doanh nghiệp đã liên kết với một email khác trên MLHUB.', ['next_action' => 'contact_support'])),
        ex($reqCreateOnboarding, '422 · Sai dữ liệu', 422, 'Unprocessable Entity', err('validation_failed', 'Dữ liệu không hợp lệ.', ['marketing_goal_codes' => ['The marketing goal codes field is required.']])),
        ex($reqCreateOnboarding, '503 · Plan mặc định chưa sẵn sàng', 503, 'Service Unavailable', err('default_plan_not_found', 'Gói Free mặc định chưa được cấu hình trên MLHUB.')),
    ],
];

$reqOnboardingDetail = ['method' => 'GET', 'header' => h(false), 'url' => u('onboarding-requests/{{onboarding_request_id}}')];
$onboardingDetail = [
    'name' => '05 · Onboarding Detail (theo dõi timeline)',
    'request' => $reqOnboardingDetail + ['description' =>
        "**Màn hình 05 — Theo dõi Onboarding.** Trạng thái + timeline của một request onboarding.\n\n".
        "**Path param:** `onboarding_request_id` (collection tự lấy từ bước 04).\n\n".
        "**Trạng thái onboarding:** `awaiting_consultant`, `needs_review`, `in_consultation`, `configuring`, `ready`, `completed`, `cancelled`.\n".
        "**Timeline step status:** `completed`, `current`, `pending`, `blocked`.\n\n".
        "**HTTP:**\n- `200`: trả đúng payload onboarding.\n- `404 onboarding_request_not_found`: sai/không tồn tại `request_id`."],
    'event' => [
        preEvent(["if (!pm.collectionVariables.get('onboarding_request_id')) pm.execution.skipRequest();"]),
        testEvent([
            "const d = (pm.response.json() || {}).data || {};",
            "pm.collectionVariables.set('onboarding_ready', ['ready', 'completed'].includes(d.status) ? '1' : '0');",
        ]),
    ],
    'response' => [
        ex($reqOnboardingDetail, '200 · awaiting_consultant', 200, 'OK', ok($onboardingData201)),
        ex($reqOnboardingDetail, '200 · ready (cho phép CRM login)', 200, 'OK', ok(array_merge($onboardingData201, [
            'status' => 'ready', 'current_step' => 'ready', 'status_label' => 'Sẵn sàng sử dụng',
            'timeline' => [
                ['code' => 'account_created', 'label' => 'Đã tạo tài khoản và cơ sở', 'description' => 'Tài khoản MLHUB và cơ sở đã được khởi tạo.', 'status' => 'completed', 'completed_at' => '2026-07-21T01:00:00+00:00'],
                ['code' => 'awaiting_consultant', 'label' => 'Chờ tư vấn viên liên hệ', 'description' => 'MLHUB đang tiếp nhận và kiểm tra yêu cầu.', 'status' => 'completed', 'completed_at' => '2026-07-21T02:00:00+00:00'],
                ['code' => 'in_consultation', 'label' => 'Đang tư vấn nhu cầu', 'description' => 'Tư vấn viên đang làm rõ mục tiêu tăng trưởng.', 'status' => 'completed', 'completed_at' => '2026-07-21T03:00:00+00:00'],
                ['code' => 'configuring', 'label' => 'Đang cấu hình Marketing', 'description' => 'MLHUB đang cấu hình các công cụ Marketing.', 'status' => 'completed', 'completed_at' => '2026-07-21T04:00:00+00:00'],
                ['code' => 'ready', 'label' => 'Sẵn sàng sử dụng', 'description' => 'Doanh nghiệp có thể truy cập CRM MLHUB.', 'status' => 'current', 'completed_at' => null],
            ],
        ]))),
        ex($reqOnboardingDetail, '404 · Không tìm thấy request', 404, 'Not Found', err('onboarding_request_not_found', 'Không tìm thấy yêu cầu onboarding.', ['next_action' => 'create_onboarding_request'])),
    ],
];

$reqStatus = ['method' => 'GET', 'header' => h(false), 'url' => u('businesses/{{external_business_id}}/marketing-status')];
$status = [
    'name' => '06 · Marketing Status (kích hoạt + capability)',
    'request' => $reqStatus + ['description' =>
        "**Màn hình 01/04 — Trang chủ (chưa/đã kích hoạt).** Trạng thái kích hoạt, capability và link điều hướng.\n\n".
        "**Path param:** `external_business_id` (collection tự sinh).\n\n".
        "**activation_status:** `onboarding` | `active` | `suspended` (business chưa map → `404`, không trả `inactive`).\n".
        "**onboarding_status:** `awaiting_consultant` | `needs_review` | `in_consultation` | `configuring` | `ready` | `completed` | `cancelled`.\n".
        "`is_ready=true` khi onboarding `ready`/`completed` → được phép tạo CRM login link.\n\n".
        "**HTTP:**\n- `200`: có mapping.\n- `404 integration_not_found`: chưa gọi Create Onboarding hoặc sai ID."],
    'event' => [testEvent([
        "const d = (pm.response.json() || {}).data || {};",
        "pm.collectionVariables.set('onboarding_ready', d.is_ready ? '1' : '0');",
    ])],
    'response' => [
        ex($reqStatus, '200 · Đã kích hoạt (active + ready)', 200, 'OK', ok($marketingStatusActive)),
        ex($reqStatus, '200 · Đang onboarding', 200, 'OK', ok(array_merge($marketingStatusActive, [
            'activation_status' => 'onboarding', 'onboarding_status' => 'awaiting_consultant', 'is_ready' => false,
            'capabilities' => ['dashboard' => false, 'support' => true, 'crm' => false],
        ]))),
        ex($reqStatus, '200 · Tạm khóa (suspended)', 200, 'OK', ok(array_merge($marketingStatusActive, [
            'activation_status' => 'suspended', 'onboarding_status' => 'ready', 'is_ready' => true,
            'capabilities' => ['dashboard' => false, 'support' => true, 'crm' => false],
        ]))),
        ex($reqStatus, '404 · Chưa liên kết', 404, 'Not Found', err('integration_not_found', 'Chưa tìm thấy liên kết doanh nghiệp với MLHUB.', ['next_action' => 'create_onboarding_request'])),
    ],
];

$profileBody = [
    'owner' => ['name' => 'Đoàn Văn Khoa'],
    'business' => ['name' => 'Fiza Store', 'industry' => 'restaurant_food', 'phone' => '0901234888', 'email' => 'contact@fizastore.vn', 'website' => 'https://fizastore.vn', 'address' => '888 Lê Duẩn, Đà Nẵng'],
];
$reqProfile = ['method' => 'PATCH', 'header' => h(true), 'body' => body($profileBody), 'url' => u('businesses/{{external_business_id}}/profile')];
$profile = [
    'name' => '07 · Update Business Profile',
    'request' => $reqProfile + ['description' =>
        "**Cập nhật hồ sơ owner + business.** Gửi ít nhất 1 trường trong `owner` hoặc `business`.\n\n".
        "**Body — được phép:**\n".
        "- `owner.name` (string ≤255)\n".
        "- `business.name` (≤255), `industry` (≤80), `phone` (≤40), `email` (email ≤255), `website` (URL ≤2048), `address` (≤1000)\n\n".
        "**Body — CẤM gửi (422):** `owner.email`, `external_business_id`, `external_user_id`, `mlhub_user_id`, `mlhub_business_id`, `mlhub_workspace_id`.\n\n".
        "**HTTP:**\n- `200`: trả marketing-status mới.\n- `404 integration_not_found`.\n- `422 validation_failed`."],
    'response' => [
        ex($reqProfile, '200 · OK', 200, 'OK', ok($marketingStatusActive)),
        ex($reqProfile, '404 · Chưa liên kết', 404, 'Not Found', err('integration_not_found', 'Chưa tìm thấy liên kết doanh nghiệp với MLHUB.', ['next_action' => 'create_onboarding_request'])),
        ex($reqProfile, '422 · Gửi trường cấm', 422, 'Unprocessable Entity', err('validation_failed', 'Dữ liệu không hợp lệ.', ['owner.email' => ['The owner.email field is prohibited.']])),
    ],
];

$prefBody = ['marketing_goal_codes' => ['qr_checkin', 'customer_retention'], 'requested_package_code' => 'base'];
$reqPref = ['method' => 'PATCH', 'header' => h(true), 'body' => body($prefBody), 'url' => u('businesses/{{external_business_id}}/marketing-preferences')];
$pref = [
    'name' => '08 · Update Marketing Preferences',
    'request' => $reqPref + ['description' =>
        "**Màn hình 02/10 — lưu mục tiêu + gói quan tâm.** KHÔNG tự kích hoạt gói (chỉ ghi nhận nguyện vọng).\n\n".
        "**Body (đều bắt buộc):**\n".
        "- `marketing_goal_codes` (array, 1–3, distinct): `local_presence` | `qr_checkin` | `voucher_return` | `customer_retention`.\n".
        "- `requested_package_code`: `free` | `base` | `biz` | `plus`.\n\n".
        "**HTTP:**\n- `200`: lưu thành công (`effective_package_code` vẫn là gói đang chạy).\n- `404 integration_not_found`.\n- `422 validation_failed`."],
    'response' => [
        ex($reqPref, '200 · OK', 200, 'OK', ok([
            'external_business_id' => 'fiza-store-001',
            'marketing_goal_codes' => ['qr_checkin', 'customer_retention'],
            'requested_package_code' => 'base',
            'effective_package_code' => 'free',
        ])),
        ex($reqPref, '422 · Sai mã mục tiêu', 422, 'Unprocessable Entity', err('validation_failed', 'Dữ liệu không hợp lệ.', ['marketing_goal_codes.0' => ['The selected marketing_goal_codes.0 is invalid.']])),
        ex($reqPref, '404 · Chưa liên kết', 404, 'Not Found', err('integration_not_found', 'Chưa tìm thấy liên kết doanh nghiệp với MLHUB.', ['next_action' => 'create_onboarding_request'])),
    ],
];

// ---------------------------------------------------------------------------
// 3. GROWTH
// ---------------------------------------------------------------------------

$dashQuery = [
    ['key' => 'range', 'value' => '30d', 'description' => 'today | 7d | 30d | 90d | custom. Mặc định 30d.'],
    ['key' => 'from', 'value' => '{{from}}', 'description' => 'CHỈ khi range=custom. Định dạng YYYY-MM-DD.', 'disabled' => true],
    ['key' => 'to', 'value' => '{{to}}', 'description' => 'CHỈ khi range=custom. YYYY-MM-DD, from <= to, tối đa 366 ngày.', 'disabled' => true],
    ['key' => 'force_refresh', 'value' => 'false', 'description' => 'true để bỏ cache (giới hạn 1 lần / 5 phút).', 'disabled' => true],
];
$reqDashboard = ['method' => 'GET', 'header' => h(false), 'url' => u('businesses/{{external_business_id}}/dashboard', $dashQuery)];
$dashboardData = [
    'new_customers' => 12, 'qr_scans' => 45, 'returning_customers' => 3, 'positive_feedback' => 5,
    'vouchers_redeemed' => 2, 'rating' => 4.6, 'active_campaigns' => 1,
    'period' => ['from' => '2026-06-22', 'to' => '2026-07-21', 'timezone' => 'Asia/Ho_Chi_Minh', 'range' => '30d'],
    'metrics' => ['businesses' => 1, 'campaigns' => 2, 'active_campaigns' => 1, 'qr_scans' => 45, 'new_customers' => 12, 'new_leads' => 12, 'positive_feedback' => 5, 'new_reviews' => 5, 'vouchers_redeemed' => 2, 'coupon_claims' => 4, 'coupon_used' => 2, 'bookings' => 1, 'feedback' => 1, 'returning_customers' => 3, 'conversion_rate' => 46.67, 'rating' => 4.6],
    'trend' => [['date' => '2026-07-20', 'scans' => 3, 'leads' => 1, 'reviews' => 0, 'coupons' => 0, 'bookings' => 0, 'feedback' => 0]],
    'insights' => [['code' => 'period_scans', 'message' => 'QR scans in this period: 45']],
    'suggested_actions' => [['code' => 'follow_up_leads', 'message' => 'Follow up on new leads captured in this period.']],
    'generated_at' => '2026-07-21T01:00:00+00:00', 'last_synced_at' => '2026-07-21T01:00:00+00:00',
    'data_freshness' => 'live', 'next_refresh_at' => '2026-07-21T01:45:00+00:00', 'timezone' => 'Asia/Ho_Chi_Minh',
];
$dashboard = [
    'name' => '09 · Dashboard (KPI + trend)',
    'request' => $reqDashboard + ['description' =>
        "**Màn hình 06/07 — Trang chủ đã kích hoạt / Tổng quan tăng trưởng.**\n\n".
        "**Query params (đầy đủ):**\n".
        "- `range` (tùy chọn): `today` | `7d` | `30d` | `90d` | `custom`. Mặc định `30d`.\n".
        "- `from`, `to` (bắt buộc khi `range=custom`): `YYYY-MM-DD`, `from <= to`, tối đa **366** ngày.\n".
        "- `force_refresh` (tùy chọn): `true`/`false` — bỏ cache (giới hạn 1 lần / 5 phút).\n\n".
        "Trong collection: `from`/`to`/`force_refresh` đang **tắt (disabled)** — bật trong tab Params khi cần.\n".
        "Doanh nghiệp chưa có dữ liệu vẫn trả `200` (số 0), không lỗi.\n\n".
        "**HTTP:** `200` | `404 integration_not_found` | `422 validation_failed` (sai range/ngày)."],
    'response' => [
        ex($reqDashboard, '200 · Có dữ liệu', 200, 'OK', ok($dashboardData)),
        ex($reqDashboard, '200 · Zero-data (chưa phát sinh)', 200, 'OK', ok(array_merge($dashboardData, [
            'new_customers' => 0, 'qr_scans' => 0, 'returning_customers' => 0, 'positive_feedback' => 0,
            'vouchers_redeemed' => 0, 'rating' => null, 'active_campaigns' => 0,
            'metrics' => array_merge($dashboardData['metrics'], ['qr_scans' => 0, 'new_customers' => 0, 'new_leads' => 0, 'active_campaigns' => 0, 'conversion_rate' => 0, 'rating' => null]),
            'trend' => [], 'insights' => [], 'suggested_actions' => [],
        ]))),
        ex($reqDashboard, '422 · Sai khoảng ngày', 422, 'Unprocessable Entity', err('validation_failed', 'Dữ liệu không hợp lệ.', ['to' => ['The to must be a date after or equal to from.']])),
        ex($reqDashboard, '404 · Chưa liên kết', 404, 'Not Found', err('integration_not_found', 'Chưa tìm thấy liên kết doanh nghiệp với MLHUB.', ['next_action' => 'create_onboarding_request'])),
    ],
];

$reqInsights = ['method' => 'GET', 'header' => h(false), 'url' => u('businesses/{{external_business_id}}/growth-insights', [
    ['key' => 'range', 'value' => '30d', 'description' => 'today | 7d | 30d | 90d | custom. Mặc định 30d.'],
    ['key' => 'from', 'value' => '{{from}}', 'description' => 'CHỈ khi range=custom. YYYY-MM-DD.', 'disabled' => true],
    ['key' => 'to', 'value' => '{{to}}', 'description' => 'CHỈ khi range=custom. YYYY-MM-DD.', 'disabled' => true],
])];
$insights = [
    'name' => '10 · Growth Insights (score + đề xuất)',
    'request' => $reqInsights + ['description' =>
        "**Màn hình 08 — Phân tích tăng trưởng.** Growth score, nguồn khách, highlights và các đề xuất (recommendations).\n\n".
        "**Query params:** `range`, `from`, `to` (giống Dashboard; không dùng `force_refresh`).\n".
        "Mỗi recommendation có `cta.preset_code` (thường `growth_recommendation`) để tạo ticket hỗ trợ ở bước 16.\n\n".
        "**HTTP:** `200` | `404 integration_not_found` | `422 validation_failed`."],
    'response' => [
        ex($reqInsights, '200 · OK', 200, 'OK', ok([
            'growth_score' => 62, 'growth_score_change' => 0,
            'customer_sources' => [['code' => 'qr_checkin', 'label' => 'QR Check-in', 'value' => 45], ['code' => 'lead_form', 'label' => 'Khách hàng mới', 'value' => 12]],
            'highlights' => [['code' => 'period_scans', 'message' => 'QR scans in this period: 45']],
            'recommendations' => [['code' => 'follow_up_leads', 'label' => 'Đề xuất tăng trưởng', 'description' => 'Follow up on new leads captured in this period.', 'priority' => 'medium', 'preset_code' => 'growth_recommendation', 'related_campaign_id' => null, 'cta_label' => 'Thiết lập ngay', 'cta' => ['action_type' => 'create_support_ticket', 'preset_code' => 'growth_recommendation', 'campaign_id' => null, 'label' => 'Thiết lập ngay']]],
            'data_period' => ['from' => '2026-06-22', 'to' => '2026-07-21', 'timezone' => 'Asia/Ho_Chi_Minh', 'range' => '30d'],
            'data_freshness' => 'live',
        ])),
        ex($reqInsights, '404 · Chưa liên kết', 404, 'Not Found', err('integration_not_found', 'Chưa tìm thấy liên kết doanh nghiệp với MLHUB.', ['next_action' => 'create_onboarding_request'])),
    ],
];

$campaignsQuery = [
    ['key' => 'per_page', 'value' => '20', 'description' => '1–100. Mặc định 20.'],
    ['key' => 'status', 'value' => 'active', 'description' => 'draft | pending_approval | active | paused | completed | cancelled.', 'disabled' => true],
    ['key' => 'search', 'value' => '', 'description' => 'Tìm theo tên chiến dịch (≤255).', 'disabled' => true],
    ['key' => 'cursor', 'value' => '', 'description' => 'Con trỏ trang tiếp theo (lấy từ pagination.next_cursor).', 'disabled' => true],
    ['key' => 'range', 'value' => '30d', 'description' => 'today | 7d | 30d | 90d | custom cho khoảng metric.', 'disabled' => true],
    ['key' => 'from', 'value' => '{{from}}', 'description' => 'CHỈ khi range=custom. YYYY-MM-DD.', 'disabled' => true],
    ['key' => 'to', 'value' => '{{to}}', 'description' => 'CHỈ khi range=custom. YYYY-MM-DD.', 'disabled' => true],
];
$reqCampaignList = ['method' => 'GET', 'header' => h(false), 'url' => u('businesses/{{external_business_id}}/campaigns', $campaignsQuery)];
$campaignList = [
    'name' => '11 · Campaign List (danh sách chiến dịch)',
    'request' => $reqCampaignList + ['description' =>
        "**Màn hình 09 — Danh sách chiến dịch.** Có filter, tìm kiếm và cursor pagination.\n\n".
        "**Query params (đầy đủ):**\n".
        "- `per_page` (1–100, mặc định 20)\n".
        "- `status`: `draft` | `pending_approval` | `active` | `paused` | `completed` | `cancelled`\n".
        "- `search` (≤255), `cursor`, `range`/`from`/`to`\n\n".
        "Zero-data vẫn `200`. Collection tự lưu `campaign_id` item đầu + cờ `campaign_approval_ready` nếu status=`pending_approval`.\n\n".
        "**HTTP:** `200` | `404 integration_not_found` | `422 validation_failed` (cursor/ngày sai)."],
    'event' => [testEvent([
        "const items = ((pm.response.json() || {}).data || {}).items || [];",
        "if (items.length) {",
        "  pm.collectionVariables.set('campaign_id', items[0].campaign_id);",
        "  pm.collectionVariables.set('campaign_approval_ready', items[0].status === 'pending_approval' ? '1' : '0');",
        "}",
    ])],
    'response' => [
        ex($reqCampaignList, '200 · Có chiến dịch', 200, 'OK', ok([
            'period' => ['from' => '2026-06-22', 'to' => '2026-07-21', 'timezone' => 'Asia/Ho_Chi_Minh'],
            'items' => [[
                'campaign_id' => 88, 'name' => 'QR Check-in quầy thu ngân', 'status' => 'active', 'campaign_type' => 'qr',
                'started_at' => '2026-07-01T00:00:00+00:00', 'ends_at' => null, 'qr_scans' => 45, 'scans' => 45,
                'leads' => 12, 'conversions' => 20, 'positive_feedback' => 5, 'rating' => 4.6, 'voucher_issued' => 4, 'voucher_used' => 2,
            ]],
            'summary' => ['total' => 2, 'by_status' => ['active' => 1, 'draft' => 1]],
            'pagination' => ['next_cursor' => null, 'has_more' => false, 'per_page' => 20],
        ])),
        ex($reqCampaignList, '200 · Zero-data (chưa có chiến dịch)', 200, 'OK', ok([
            'period' => ['from' => '2026-06-22', 'to' => '2026-07-21', 'timezone' => 'Asia/Ho_Chi_Minh'],
            'items' => [],
            'summary' => ['total' => 0, 'by_status' => (object) []],
            'pagination' => ['next_cursor' => null, 'has_more' => false, 'per_page' => 20],
        ])),
        ex($reqCampaignList, '404 · Chưa liên kết', 404, 'Not Found', err('integration_not_found', 'Chưa tìm thấy liên kết doanh nghiệp với MLHUB.', ['next_action' => 'create_onboarding_request'])),
    ],
];

$reqCampaignDetail = ['method' => 'GET', 'header' => h(false), 'url' => u('businesses/{{external_business_id}}/campaigns/{{campaign_id}}', [
    ['key' => 'range', 'value' => '30d', 'description' => 'today | 7d | 30d | 90d | custom. Mặc định 30d.', 'disabled' => true],
    ['key' => 'from', 'value' => '{{from}}', 'description' => 'CHỈ khi range=custom. YYYY-MM-DD.', 'disabled' => true],
    ['key' => 'to', 'value' => '{{to}}', 'description' => 'CHỈ khi range=custom. YYYY-MM-DD.', 'disabled' => true],
])];
$campaignDetail = [
    'name' => '12 · Campaign Detail',
    'request' => $reqCampaignDetail + ['description' =>
        "**Màn hình 11 — Chi tiết chiến dịch.** Metrics, trend và đề xuất.\n\n".
        "**Path param:** `campaign_id` (tự lấy từ bước 11).\n".
        "**Query (tùy chọn):** `range`, `from`, `to` — giống Dashboard.\n\n".
        "**HTTP:**\n- `200`: chi tiết + recommendations.\n- `404 campaign_not_found` / `integration_not_found`.\n- `422 validation_failed`."],
    'event' => [preEvent(["if (!pm.collectionVariables.get('campaign_id')) pm.execution.skipRequest();"])],
    'response' => [
        ex($reqCampaignDetail, '200 · OK', 200, 'OK', ok([
            'campaign_id' => 88, 'name' => 'QR Check-in quầy thu ngân', 'status' => 'active', 'objective' => 'qr_checkin',
            'period' => ['from' => '2026-06-22', 'to' => '2026-07-21', 'timezone' => 'Asia/Ho_Chi_Minh'],
            'metrics' => ['scans' => 45, 'leads' => 12, 'reviews' => 5, 'coupons' => 4, 'bookings' => 1, 'feedback' => 1, 'conversions' => 20, 'conversion_rate' => 44.44],
            'qr_scans' => 45, 'qr_scan_change' => 0, 'valid_leads' => 12, 'conversion_rate' => 44.44, 'last_synced_at' => '2026-07-21T01:00:00+00:00',
            'recommendations' => [['code' => 'campaign_review', 'label' => 'Yêu cầu điều chỉnh', 'description' => 'Gửi yêu cầu để MLHUB rà soát và điều chỉnh chiến dịch.', 'preset_code' => 'campaign_request', 'campaign_id' => '88']],
            'campaign' => ['campaign_id' => 88, 'name' => 'QR Check-in quầy thu ngân', 'status' => 'active', 'published_at' => '2026-07-01T00:00:00+00:00', 'created_at' => '2026-06-30T00:00:00+00:00'],
            'trend' => [['date' => '2026-07-20', 'scans' => 3, 'leads' => 1, 'reviews' => 0, 'coupons' => 0, 'bookings' => 0, 'feedback' => 0]],
        ])),
        ex($reqCampaignDetail, '404 · Không tìm thấy chiến dịch', 404, 'Not Found', err('campaign_not_found', 'Không tìm thấy chiến dịch.', ['next_action' => 'list_campaigns_first'])),
    ],
];

$approvalBody = ['decision' => 'approved', 'note' => 'FizaHUB đã duyệt chiến dịch.'];
$reqApproval = ['method' => 'POST', 'header' => h(true), 'body' => body($approvalBody), 'url' => u('businesses/{{external_business_id}}/campaigns/{{campaign_id}}/approval')];
$reqApprovalChanges = ['method' => 'POST', 'header' => h(true), 'body' => body(['decision' => 'changes_requested', 'note' => 'Vui lòng chỉnh lại mô tả ưu đãi.']), 'url' => u('businesses/{{external_business_id}}/campaigns/{{campaign_id}}/approval')];
$approval = [
    'name' => '13 · Campaign Approval (duyệt / yêu cầu chỉnh sửa)',
    'request' => $reqApproval + ['description' =>
        "**Màn hình 11 — Duyệt chiến dịch.** Ghi 1 quyết định có audit.\n\n".
        "**Body:**\n- `decision` (bắt buộc): `approved` | `changes_requested`.\n- `note` (tùy chọn, ≤2000 ký tự).\n\n".
        "`approved` → chiến dịch chuyển `active`.\n".
        "`changes_requested` → giữ `pending_approval` + tạo ticket `campaign_request` (có `support_ticket_id`).\n\n".
        "**HTTP:**\n- `200`: ghi nhận thành công.\n- `404 campaign_not_found` / `integration_not_found`.\n- `409 campaign_invalid_state`: không còn ở `pending_approval`.\n- `422 validation_failed`.\n\n".
        "*Collection chỉ chạy khi `campaign_approval_ready=1`; nếu không sẽ tự bỏ qua.*"],
    'event' => [preEvent(["if (!pm.collectionVariables.get('campaign_id') || pm.collectionVariables.get('campaign_approval_ready') !== '1') pm.execution.skipRequest();"])],
    'response' => [
        ex($reqApproval, '200 · Đã duyệt (approved)', 200, 'OK', ok(['campaign_id' => 88, 'decision' => 'approved', 'status' => 'active', 'support_ticket_id' => null, 'decided_at' => '2026-07-21T01:10:00+00:00'])),
        ex($reqApprovalChanges, '200 · Yêu cầu chỉnh sửa (changes_requested)', 200, 'OK', ok(['campaign_id' => 88, 'decision' => 'changes_requested', 'status' => 'pending_approval', 'support_ticket_id' => 'tkt_campaign_req_01', 'decided_at' => '2026-07-21T01:12:00+00:00'])),
        ex($reqApproval, '409 · Sai trạng thái', 409, 'Conflict', err('campaign_invalid_state', 'Chiến dịch không còn ở trạng thái chờ duyệt.', ['current_status' => 'active'])),
        ex($reqApproval, '404 · Không tìm thấy chiến dịch', 404, 'Not Found', err('campaign_not_found', 'Không tìm thấy chiến dịch.', ['next_action' => 'list_campaigns_first'])),
    ],
];

$reqPackage = ['method' => 'GET', 'header' => h(false), 'url' => u('businesses/{{external_business_id}}/package')];
$package = [
    'name' => '14 · Business Package (gói hiệu lực)',
    'request' => $reqPackage + ['description' =>
        "**Màn hình 10 — Quản lý gói.** Gói effective/requested/approved, limits và thời hạn.\n\n".
        "**Path param:** `external_business_id`. Không cần body.\n\n".
        "**`status` gói:** `none` | `expired` | `active` | `inactive`.\n".
        "**limits** (whitelist): `max_businesses`, `max_campaigns`, `max_landing_pages`, `max_qr_codes`, `max_team_members`.\n\n".
        "**HTTP:** `200` | `404 integration_not_found` | `409 integration_broken`."],
    'response' => [
        ex($reqPackage, '200 · OK', 200, 'OK', ok([
            'effective_package' => 'free', 'requested_package' => 'base', 'approved_package' => null,
            'package_name' => 'Free', 'plan_slug' => 'mlhub-free-da-nang', 'status' => 'active',
            'starts_at' => '2026-07-21T01:00:00+00:00', 'expires_at' => null, 'is_trial' => false, 'mapping_status' => 'active',
            'limits' => ['max_businesses' => 1, 'max_campaigns' => 3, 'max_landing_pages' => 1, 'max_qr_codes' => 5, 'max_team_members' => 2],
        ])),
        ex($reqPackage, '404 · Chưa liên kết', 404, 'Not Found', err('integration_not_found', 'Chưa tìm thấy liên kết doanh nghiệp với MLHUB.', ['next_action' => 'create_onboarding_request'])),
        ex($reqPackage, '409 · Mapping hỏng', 409, 'Conflict', err('integration_broken', 'Liên kết doanh nghiệp bị hỏng, cần hỗ trợ MLHUB.', ['next_action' => 'contact_support'])),
    ],
];

// ---------------------------------------------------------------------------
// 4. SUPPORT
// ---------------------------------------------------------------------------

$reqPresets = ['method' => 'GET', 'header' => h(false), 'url' => u('businesses/{{external_business_id}}/support-presets')];
$presets = [
    'name' => '15 · Support Presets (mẫu yêu cầu)',
    'request' => $reqPresets + ['description' =>
        "**Màn hình 12/13 — Trung tâm hỗ trợ.** Danh sách preset SOP công khai.\n\n".
        "**Preset công khai:**\n".
        "- `qr_scan_not_recorded`\n".
        "- `growth_recommendation`\n".
        "- `campaign_request` (**bắt buộc** `campaign_id` khi tạo ticket)\n".
        "- `package_upgrade`\n\n".
        "`response_channels`: `in_app` | `phone`.\n".
        "Có thể đính kèm ảnh / video / tài liệu (PDF/Office) qua nhóm request **Attachments** (bước 22–24).\n\n".
        "**HTTP:** `200` | `404 integration_not_found`."],
    'response' => [
        ex($reqPresets, '200 · OK', 200, 'OK', ok([
            'items' => [
                ['preset_code' => 'qr_scan_not_recorded', 'subject' => 'QR check-in không ghi nhận lượt quét', 'subject_locked' => true, 'description' => 'Doanh nghiệp phản ánh mã QR check-in không phát sinh lượt quét nào.', 'ticket_type' => 'support', 'required_context' => ['related_resource'], 'sla_hours' => 24, 'response_channels' => ['in_app', 'phone'], 'requires_campaign' => false],
                ['preset_code' => 'growth_recommendation', 'subject' => 'Yêu cầu hỗ trợ đề xuất tăng trưởng', 'subject_locked' => true, 'description' => 'Doanh nghiệp muốn MLHUB hỗ trợ triển khai đề xuất tăng trưởng.', 'ticket_type' => 'support', 'required_context' => [], 'sla_hours' => 24, 'response_channels' => ['in_app', 'phone'], 'requires_campaign' => false],
                ['preset_code' => 'campaign_request', 'subject' => 'Yêu cầu điều chỉnh chiến dịch', 'subject_locked' => true, 'description' => 'Yêu cầu MLHUB rà soát một chiến dịch cụ thể.', 'ticket_type' => 'campaign_request', 'required_context' => ['campaign_id'], 'sla_hours' => 24, 'response_channels' => ['in_app', 'phone'], 'requires_campaign' => true],
                ['preset_code' => 'package_upgrade', 'subject' => 'Yêu cầu nâng cấp gói Marketing', 'subject_locked' => true, 'description' => 'Doanh nghiệp muốn tư vấn nâng cấp gói.', 'ticket_type' => 'support', 'required_context' => [], 'sla_hours' => 24, 'response_channels' => ['in_app', 'phone'], 'requires_campaign' => false],
            ],
        ])),
        ex($reqPresets, '404 · Chưa liên kết', 404, 'Not Found', err('integration_not_found', 'Chưa tìm thấy liên kết doanh nghiệp với MLHUB.', ['next_action' => 'create_onboarding_request'])),
    ],
];

$ticketBody = [
    'preset_code' => 'qr_scan_not_recorded',
    'subject' => 'QR Check-in không ghi nhận lượt quét',
    'message' => 'QR đã đặt tại quầy nhưng dashboard chưa cập nhật lượt quét.',
    'response_channel' => 'in_app',
    'metadata' => ['screen' => 'growth_marketing', 'source' => 'fizahub'],
];
$ticketBodyCampaign = [
    'preset_code' => 'campaign_request',
    'campaign_id' => '{{campaign_id}}',
    'message' => 'Vui lòng điều chỉnh nội dung chiến dịch QR Check-in.',
    'response_channel' => 'phone',
];
$reqCreateTicket = ['method' => 'POST', 'header' => h(true), 'body' => body($ticketBody), 'url' => u('businesses/{{external_business_id}}/support-tickets')];
$reqCreateTicketCampaign = ['method' => 'POST', 'header' => h(true), 'body' => body($ticketBodyCampaign), 'url' => u('businesses/{{external_business_id}}/support-tickets')];
$createTicket = [
    'name' => '16 · Create Support Ticket',
    'request' => $reqCreateTicket + ['description' =>
        "**Màn hình 13 — Tạo yêu cầu hỗ trợ.**\n\n".
        "**Body — đầy đủ trường:**\n".
        "- `preset_code` (tùy chọn, ≤80): mã preset ở bước 15. Có preset thì `subject` lấy từ preset.\n".
        "- `subject` (bắt buộc nếu KHÔNG có `preset_code`, 1–255).\n".
        "- `message` (bắt buộc, 1–5000).\n".
        "- `response_channel` (tùy chọn): `in_app` | `phone` (mặc định `in_app`).\n".
        "- `campaign_id` (bắt buộc nếu `preset_code=campaign_request`, ≤128).\n".
        "- `metadata` (tùy chọn, object).\n\n".
        "**HTTP:**\n".
        "- `201`: tạo thành công (collection lưu `ticket_id`).\n".
        "- `422 support_ticket_invalid` / `support_preset_not_found` / `support_context_required` / `validation_failed`.\n".
        "- `404 integration_not_found`.\n".
        "- `409 integration_mapping_invalid`."],
    'event' => [testEvent([
        "pm.test('Ticket created (201)', () => pm.response.to.have.status(201));",
        "const t = (pm.response.json() || {}).data || {};",
        "if (t.ticket_id) pm.collectionVariables.set('ticket_id', t.ticket_id);",
    ])],
    'response' => [
        ex($reqCreateTicket, '201 · Tạo từ preset qr_scan_not_recorded', 201, 'Created', ok(array_merge($ticketData, ['next_poll_after_seconds' => 15]))),
        ex($reqCreateTicketCampaign, '201 · Tạo từ preset campaign_request', 201, 'Created', ok(array_merge($ticketData, [
            'ticket_id' => 'tkt_campaign_req_01', 'ticket_type' => 'campaign_request', 'preset_code' => 'campaign_request',
            'campaign_id' => '88', 'subject' => 'Yêu cầu điều chỉnh chiến dịch', 'next_poll_after_seconds' => 15,
        ]))),
        ex($reqCreateTicket, '422 · Thiếu tiêu đề/nội dung', 422, 'Unprocessable Entity', err('support_ticket_invalid', 'Vui lòng cung cấp tiêu đề và nội dung, hoặc chọn một mẫu yêu cầu.')),
        ex($reqCreateTicketCampaign, '422 · Thiếu campaign_id', 422, 'Unprocessable Entity', err('support_context_required', 'Preset này yêu cầu thêm ngữ cảnh chiến dịch.', ['field' => 'campaign_id'])),
        ex($reqCreateTicket, '422 · Preset không tồn tại', 422, 'Unprocessable Entity', err('support_preset_not_found', 'Không tìm thấy mẫu yêu cầu hỗ trợ.')),
    ],
];

$listTicketsQuery = [
    ['key' => 'per_page', 'value' => '20', 'description' => '1–100. Mặc định 20.'],
    ['key' => 'q', 'value' => '', 'description' => 'Tìm theo mã ticket / tiêu đề / nội dung (≤255).', 'disabled' => true],
    ['key' => 'status', 'value' => 'open', 'description' => 'open | resolved | closed.', 'disabled' => true],
    ['key' => 'cursor', 'value' => '', 'description' => 'Con trỏ trang tiếp theo (pagination.next_cursor).', 'disabled' => true],
];
$reqListTickets = ['method' => 'GET', 'header' => h(false), 'url' => u('businesses/{{external_business_id}}/support-tickets', $listTicketsQuery)];
$listTickets = [
    'name' => '17 · List Support Tickets',
    'request' => $reqListTickets + ['description' =>
        "**Màn hình 12 — Trung tâm hỗ trợ.** Summary + danh sách ticket có cursor.\n\n".
        "**Query params:**\n".
        "- `per_page` (1–100, mặc định 20)\n".
        "- `q` (≤255): tìm theo mã / tiêu đề / nội dung\n".
        "- `status`: `open` | `resolved` | `closed`\n".
        "- `cursor`: trang tiếp theo\n\n".
        "Ticket onboarding nội bộ (`ticket_type=onboarding`) vẫn xuất hiện trong list.\n\n".
        "**HTTP:** `200` | `404 integration_not_found` | `422 validation_failed`."],
    'event' => [testEvent([
        "pm.test('Items is an array', () => pm.expect(((pm.response.json() || {}).data || {}).items || []).to.be.an('array'));",
        "if (!pm.collectionVariables.get('ticket_id')) {",
        "  const items = ((pm.response.json() || {}).data || {}).items || [];",
        "  const userTicket = items.find(item => item.ticket_type !== 'onboarding' && item.preset_code === 'qr_scan_not_recorded');",
        "  if (userTicket) pm.collectionVariables.set('ticket_id', userTicket.ticket_id);",
        "}",
    ])],
    'response' => [
        ex($reqListTickets, '200 · OK', 200, 'OK', ok([
            'summary' => ['open' => 2, 'resolved' => 0, 'closed' => 1, 'unread_by_business' => 1],
            'items' => [$ticketData, array_merge($ticketData, ['ticket_id' => 'tkt_onboarding_abc123', 'ticket_type' => 'onboarding', 'preset_code' => 'fizahub_onboarding', 'subject' => 'FizaHUB onboarding awaiting consultant: fiza-store-001'])],
            'pagination' => ['next_cursor' => null, 'has_more' => false, 'per_page' => 20],
        ])),
        ex($reqListTickets, '404 · Chưa liên kết', 404, 'Not Found', err('integration_not_found', 'Chưa tìm thấy liên kết doanh nghiệp với MLHUB.', ['next_action' => 'create_onboarding_request'])),
    ],
];

$detailQuery = [
    ['key' => 'messages_since', 'value' => '', 'description' => 'TÙY CHỌN. ISO8601 — chỉ lấy message mới hơn thời điểm này (polling). Alias: since.', 'disabled' => true],
];
$reqTicketDetail = ['method' => 'GET', 'header' => h(false), 'url' => u('businesses/{{external_business_id}}/support-tickets/{{ticket_id}}', $detailQuery)];
$ticketDetail = [
    'name' => '18 · Support Ticket Detail (hội thoại)',
    'request' => $reqTicketDetail + ['description' =>
        "**Màn hình 14 — Chi tiết và hội thoại.** `messages[]` dạng text và `next_poll_after_seconds` (thường 15).\n\n".
        "**Path param:** `ticket_id` (tự lấy từ bước 16/17).\n".
        "**Query:** `messages_since` hoặc `since` (ISO8601) để polling tin mới — giá trị sai sẽ bị bỏ qua (không 422).\n\n".
        "`sender_type`: `business` | `admin`. Tệp đính kèm nằm ở request riêng **List Attachments** (bước 22), không trả trong `messages[]`.\n\n".
        "**HTTP:** `200` | `404 ticket_not_found` / `integration_not_found`."],
    'event' => [preEvent(["if (!pm.collectionVariables.get('ticket_id')) pm.execution.skipRequest();"])],
    'response' => [
        ex($reqTicketDetail, '200 · OK', 200, 'OK', ok(array_merge($ticketData, [
            'messages' => [
                ['message_id' => 'tkt_7de1f9a4c0:initial', 'sender_type' => 'business', 'body' => 'QR đã đặt tại quầy nhưng dashboard chưa cập nhật lượt quét.', 'created_at' => '2026-07-21T01:05:00+00:00'],
                ['message_id' => 'cmt_1a2b3c', 'sender_type' => 'admin', 'body' => 'MLHUB đang kiểm tra, sẽ phản hồi trong 24h.', 'created_at' => '2026-07-21T01:20:00+00:00'],
            ],
            'next_poll_after_seconds' => 15, 'last_message_at' => '2026-07-21T01:20:00+00:00',
        ]))),
        ex($reqTicketDetail, '404 · Không tìm thấy ticket', 404, 'Not Found', err('ticket_not_found', 'Không tìm thấy phiếu hỗ trợ.', ['next_action' => 'create_support_ticket'])),
    ],
];

$msgBody = ['message' => 'FizaHUB bổ sung thông tin cho yêu cầu hỗ trợ.'];
$reqSendMsg = ['method' => 'POST', 'header' => h(true), 'body' => body($msgBody), 'url' => u('businesses/{{external_business_id}}/support-tickets/{{ticket_id}}/messages')];
$sendMsg = [
    'name' => '19 · Send Support Message',
    'request' => $reqSendMsg + ['description' =>
        "**Màn hình 14 — Gửi tin nhắn text vào ticket.**\n\n".
        "**Body:** `message` (bắt buộc, 1–5000 ký tự). Idempotency-Key chống gửi trùng.\n".
        "Chỉ gửi được khi ticket đang `open`.\n\n".
        "**HTTP:**\n- `201`: gửi thành công.\n- `404 ticket_not_found` / `integration_not_found`.\n- `409 ticket_not_open`: ticket đã đóng/đã xử lý.\n- `422 validation_failed`."],
    'event' => [preEvent(["if (!pm.collectionVariables.get('ticket_id')) pm.execution.skipRequest();"])],
    'response' => [
        ex($reqSendMsg, '201 · Đã gửi', 201, 'Created', ok(['message_id' => 'cmt_9z8y7x', 'sender_type' => 'business', 'body' => 'FizaHUB bổ sung thông tin cho yêu cầu hỗ trợ.', 'created_at' => '2026-07-21T01:25:00+00:00'])),
        ex($reqSendMsg, '409 · Ticket không còn mở', 409, 'Conflict', err('ticket_not_open', 'Chỉ có thể gửi tin nhắn khi phiếu hỗ trợ đang mở.')),
        ex($reqSendMsg, '404 · Không tìm thấy ticket', 404, 'Not Found', err('ticket_not_found', 'Không tìm thấy phiếu hỗ trợ.', ['next_action' => 'create_support_ticket'])),
    ],
];

$closeBody = ['reason' => 'Đã xử lý xong, đóng phiếu.'];
$reqClose = ['method' => 'POST', 'header' => h(true), 'body' => body($closeBody), 'url' => u('businesses/{{external_business_id}}/support-tickets/{{ticket_id}}/close')];
$close = [
    'name' => '20 · Close Support Ticket',
    'request' => $reqClose + ['description' =>
        "**Màn hình 14 — Đóng ticket.**\n\n".
        "**Body:** `reason` (tùy chọn) — nếu có sẽ thêm 1 tin nhắn ghi chú lý do đóng. Có thể gửi `{}`.\n\n".
        "**HTTP:**\n- `200`: đã đóng (`status=closed`). Gọi lại khi đã đóng vẫn `200` (idempotent).\n- `404 ticket_not_found` / `integration_not_found`."],
    'event' => [preEvent(["if (!pm.collectionVariables.get('ticket_id')) pm.execution.skipRequest();"])],
    'response' => [
        ex($reqClose, '200 · Đã đóng', 200, 'OK', ok(array_merge($ticketData, ['status' => 'closed']))),
        ex($reqClose, '200 · Đã đóng sẵn (idempotent)', 200, 'OK', ok(array_merge($ticketData, ['status' => 'closed']))),
        ex($reqClose, '404 · Không tìm thấy ticket', 404, 'Not Found', err('ticket_not_found', 'Không tìm thấy phiếu hỗ trợ.', ['next_action' => 'create_support_ticket'])),
    ],
];

$reqReopen = ['method' => 'POST', 'header' => h(true), 'body' => body(new stdClass), 'url' => u('businesses/{{external_business_id}}/support-tickets/{{ticket_id}}/reopen')];
$reopen = [
    'name' => '21 · Reopen Support Ticket',
    'request' => $reqReopen + ['description' =>
        "**Màn hình 14 — Mở lại ticket đã đóng/đã xử lý.**\n\n".
        "**Body:** gửi `{}`.\n\n".
        "**HTTP:**\n- `200`: đã mở lại (`status=open`). Gọi lại khi đang mở vẫn `200` (idempotent).\n- `404 ticket_not_found` / `integration_not_found`."],
    'event' => [preEvent(["if (!pm.collectionVariables.get('ticket_id')) pm.execution.skipRequest();"])],
    'response' => [
        ex($reqReopen, '200 · Đã mở lại', 200, 'OK', ok(array_merge($ticketData, ['status' => 'open']))),
        ex($reqReopen, '200 · Đang mở sẵn (idempotent)', 200, 'OK', ok(array_merge($ticketData, ['status' => 'open']))),
        ex($reqReopen, '404 · Không tìm thấy ticket', 404, 'Not Found', err('ticket_not_found', 'Không tìm thấy phiếu hỗ trợ.', ['next_action' => 'create_support_ticket'])),
    ],
];

$reqListAttachments = ['method' => 'GET', 'header' => h(false), 'url' => u('businesses/{{external_business_id}}/support-tickets/{{ticket_id}}/attachments')];
$listAttachments = [
    'name' => '22 · List Support Attachments (đính kèm)',
    'request' => $reqListAttachments + ['description' =>
        "**Màn hình 14 — Tệp đính kèm của ticket.** Danh sách tệp cả hai chiều (business tải lên và admin MLHUB đính kèm khi trả lời).\n\n".
        "**Path param:** `ticket_id` (tự lấy từ bước 16/17).\n\n".
        "`sender_type`: `business` | `admin`. Dùng `download_url` (hoặc bước 24) để tải tệp (cần Bearer + X-Partner).\n\n".
        "Mọi item có `extension` (đuôi file, không có dấu chấm — ví dụ `jpg`, `png`, `pdf`).\n\n".
        "Nếu `mime_type` là ảnh (`image/*`) thì có thêm `image_url`: URL ký tạm, **đuôi thật** (`preview.jpg`…), **không cần** header partner — app đưa thẳng vào `<img>` / ImageView trên màn tư vấn. Hết hạn thì gọi lại List để lấy URL mới. Tệp không phải ảnh → `image_url = null`.\n\n".
        "**HTTP:** `200` | `404 ticket_not_found` / `integration_not_found`."],
    'event' => [preEvent(["if (!pm.collectionVariables.get('ticket_id')) pm.execution.skipRequest();"])],
    'response' => [
        ex($reqListAttachments, '200 · OK', 200, 'OK', ok(['items' => [$attachmentData]])),
        ex($reqListAttachments, '404 · Không tìm thấy ticket', 404, 'Not Found', err('ticket_not_found', 'Không tìm thấy phiếu hỗ trợ.', ['next_action' => 'create_support_ticket'])),
    ],
];

$reqUploadAttachment = [
    'method' => 'POST',
    'header' => array_values(array_filter(h(true), fn (array $x): bool => $x['key'] !== 'Content-Type')),
    'body' => [
        'mode' => 'formdata',
        'formdata' => [
            ['key' => 'file', 'type' => 'file', 'src' => [], 'description' => 'Ảnh (jpg/png/webp/gif), video (mp4/mov/webm), hoặc tài liệu (pdf/doc/docx/xls/xlsx). Không nhận zip/txt/csv/ppt.'],
        ],
    ],
    'url' => u('businesses/{{external_business_id}}/support-tickets/{{ticket_id}}/attachments'),
];
$uploadAttachment = [
    'name' => '23 · Upload Support Attachment (tải lên đính kèm)',
    'request' => $reqUploadAttachment + ['description' =>
        "**Màn hình 14 — Gửi tệp đính kèm vào ticket.** `multipart/form-data`, field `file` (đúng 1 tệp / request).\n\n".
        "**Loại được hỗ trợ (3 nhóm):** ảnh `jpg/jpeg/png/webp/gif`; video `mp4/mov/webm`; tài liệu `pdf/doc/docx/xls/xlsx`. **Không** nhận zip / txt / csv / ppt / avi.\n".
        "**Dung lượng:** mặc định tối đa 25MB (ảnh/tài liệu) hoặc 100MB (video) — MLHUB có thể chỉnh qua cấu hình server.\n".
        "Server tự dò MIME thật theo nội dung file (không tin `Content-Type` client gửi) và đối chiếu song song với đuôi file.\n".
        "Chỉ gửi được khi ticket đang `open`.\n\n".
        "**HTTP:**\n- `201`: tải lên thành công, trả về `attachment_id` + `extension` + `download_url` (+ `image_url` ký tạm nếu là ảnh).\n".
        "- `422 attachment_type_not_allowed`: sai định dạng/đuôi file.\n".
        "- `422 attachment_too_large`: vượt dung lượng cho phép.\n".
        "- `409 ticket_not_open`: ticket đã đóng/đã xử lý.\n".
        "- `404 ticket_not_found` / `integration_not_found`.\n".
        "- `503 support_attachments_unavailable`: tính năng chưa sẵn sàng trên môi trường này."],
    'event' => [
        preEvent(["if (!pm.collectionVariables.get('ticket_id')) pm.execution.skipRequest();"]),
        testEvent([
            "const a = (pm.response.json() || {}).data || {};",
            "if (a.attachment_id) pm.collectionVariables.set('attachment_id', a.attachment_id);",
        ]),
    ],
    'response' => [
        ex($reqUploadAttachment, '201 · Đã tải lên', 201, 'Created', ok($attachmentData)),
        ex($reqUploadAttachment, '422 · Sai định dạng', 422, 'Unprocessable Entity', err('attachment_type_not_allowed', 'Định dạng tệp đính kèm không được hỗ trợ.', ['file' => ['application/x-msdownload']])),
        ex($reqUploadAttachment, '422 · Vượt dung lượng', 422, 'Unprocessable Entity', err('attachment_too_large', 'Tệp đính kèm vượt quá dung lượng cho phép (25 MB).', ['max_size_mb' => 25])),
        ex($reqUploadAttachment, '409 · Ticket không còn mở', 409, 'Conflict', err('ticket_not_open', 'This support ticket is closed or resolved.')),
        ex($reqUploadAttachment, '404 · Không tìm thấy ticket', 404, 'Not Found', err('ticket_not_found', 'Không tìm thấy phiếu hỗ trợ.', ['next_action' => 'create_support_ticket'])),
    ],
];

$reqDownloadAttachment = ['method' => 'GET', 'header' => h(false), 'url' => u('businesses/{{external_business_id}}/support-tickets/{{ticket_id}}/attachments/{{attachment_id}}')];
$downloadAttachment = [
    'name' => '24 · Download Support Attachment (tải xuống đính kèm)',
    'request' => $reqDownloadAttachment + ['description' =>
        "**Màn hình 14 — Tải nội dung tệp đính kèm.** Trả về file nhị phân (cần Bearer + X-Partner).\n\n".
        "- Xem ảnh trong chat: dùng `image_url` từ bước 22/23 (URL ký tạm có đuôi `.jpg`…, **không** cần header).\n".
        "- Tải tệp / không phải ảnh: dùng endpoint này (`download_url`).\n\n".
        "**Path param:** `attachment_id` (lấy từ bước 22/23). Luôn xác thực partner token + tenant scope theo ticket.\n\n".
        "**HTTP:** `200` (file) | `404 attachment_not_found` (sai id hoặc file vật lý không còn) | `404 ticket_not_found` / `integration_not_found`."],
    'event' => [preEvent(["if (!pm.collectionVariables.get('ticket_id') || !pm.collectionVariables.get('attachment_id')) pm.execution.skipRequest();"])],
    'response' => [
        ex($reqDownloadAttachment, '404 · Không tìm thấy tệp đính kèm', 404, 'Not Found', err('attachment_not_found', 'Không tìm thấy tệp đính kèm này.', ['next_action' => 'list_attachments'])),
    ],
];

// ---------------------------------------------------------------------------
// 5. CRM
// ---------------------------------------------------------------------------

$reqCrm = ['method' => 'POST', 'header' => h(true), 'body' => body(new stdClass), 'url' => u('businesses/{{external_business_id}}/crm-login-links')];
$crm = [
    'name' => '25 · Create CRM Login Link (đăng nhập 1 lần)',
    'request' => $reqCrm + ['description' =>
        "**Màn hình 15 — Truy cập CRM MLHUB.** Tạo link đăng nhập dùng một lần, có hạn (mặc định 5 phút).\n\n".
        "**Điều kiện:** onboarding mới nhất phải `ready` hoặc `completed` (`is_ready=true` ở bước 06).\n".
        "**Body:** gửi `{}`. Cùng Idempotency-Key trả lại cùng link khi còn hiệu lực / chưa dùng.\n\n".
        "**HTTP:**\n".
        "- `201`: trả `url`, `expires_at`, `expires_in_seconds`.\n".
        "- `409 onboarding_not_ready`: chưa sẵn sàng.\n".
        "- `409 crm_login_link_not_reusable`: link đã dùng/hết hạn → cần Idempotency-Key mới (`next_action=new_idempotency_key`).\n".
        "- `404 integration_not_found`.\n\n".
        "*Collection tự bỏ qua nếu `onboarding_ready` chưa = 1.*"],
    'event' => [preEvent(["if (pm.collectionVariables.get('onboarding_ready') !== '1') pm.execution.skipRequest();"])],
    'response' => [
        ex($reqCrm, '201 · Tạo link', 201, 'Created', ok(['url' => 'https://mlhub.vn/partners/fizahub/login/<signed-token>?signature=<sig>', 'expires_at' => '2026-07-21T01:05:00+00:00', 'expires_in_seconds' => 300])),
        ex($reqCrm, '409 · Chưa sẵn sàng', 409, 'Conflict', err('onboarding_not_ready', 'Tài khoản đang chờ tư vấn viên MLHUB hoàn tất cấu hình.', ['status' => 'awaiting_consultant', 'status_label' => 'Chờ tư vấn viên liên hệ'])),
        ex($reqCrm, '409 · Link cũ không tái sử dụng', 409, 'Conflict', err('crm_login_link_not_reusable', 'Link đăng nhập trước đó đã hết hạn hoặc đã được sử dụng.', ['next_action' => 'new_idempotency_key'])),
        ex($reqCrm, '404 · Chưa liên kết', 404, 'Not Found', err('integration_not_found', 'Chưa tìm thấy liên kết doanh nghiệp với MLHUB.', ['next_action' => 'create_onboarding_request'])),
    ],
];

// ---------------------------------------------------------------------------
// Collection assembly
// ---------------------------------------------------------------------------

$collection = [
    'info' => [
        'name' => 'MLHUB × FizaHUB Partner API',
        'description' =>
            "# MLHUB × FizaHUB Partner API (v1)\n\n".
            "Breaking cutover: **25 request** cho **15 màn hình Marketing** đã được Product duyệt. Support hỗ trợ tin nhắn text và đính kèm **3 nhóm**: ảnh / video / tài liệu (PDF/Office). Không nhận zip.\n\n".
            "**Cập nhật:** List/Upload trả `extension` + `image_url` ký tạm (ảnh) — URL có đuôi `.jpg`…, mở trong chat không cần header. Allow-list: ảnh, video (`mp4/mov/webm`), tài liệu (`pdf/doc/docx/xls/xlsx`).\n\n".
            "## Dùng ngay trong 3 bước (không cần biết Postman sâu)\n".
            "1. **Import** file này vào Postman: nút **Import** (góc trên trái) → chọn file → Import.\n".
            "2. Mở collection → tab **Variables**: `base_url` và `partner_token` **ĐÃ điền sẵn**. Không cần gõ tay.\n".
            "3. Đưa chuột vào tên collection → bấm **Run** → **Run MLHUB × FizaHUB Partner API**. Hoặc mở từng request 01→25 rồi bấm **Send**.\n\n".
            "Hướng dẫn click từng nút (tiếng Việt, cho người mới): https://mlhub.vn/api-fizahub/help-test\n\n".
            "## Vì sao chạy được ngay?\n".
            "- `partner_token`, `base_url` cấu hình sẵn cho giai đoạn thử nghiệm.\n".
            "- Collection **tự sinh** `external_business_id`, `external_user_id`, `from`, `to`.\n".
            "- Mỗi request tự gắn `X-Request-Id` (UUID); request ghi tự gắn `Idempotency-Key` (UUID).\n".
            "- Các ID phụ thuộc (`onboarding_request_id`, `campaign_id`, `ticket_id`) được **tự lưu**; bước phụ thuộc tự **skip** nếu chưa có ID.\n\n".
            "## Chạy theo folder\n".
            "1. **System (2):** Health, SSO Verify.\n".
            "2. **Onboarding (6):** Catalog → Create → Detail → Marketing Status → Profile → Preferences.\n".
            "3. **Growth (6):** Dashboard → Insights → Campaign List → Campaign Detail → Approval → Package.\n".
            "4. **Support (10):** Presets → Create → List → Detail → Message → Close → Reopen → List Attachments → Upload Attachment → Download Attachment.\n".
            "5. **CRM (1):** tạo link khi onboarding ready/completed.\n\n".
            "## Xem đầy đủ tham số & trạng thái\n".
            "- Tab **Params** / **Body** của mỗi request: liệt kê đủ trường (một số param tắt sẵn — bật checkbox khi cần).\n".
            "- Mục **Examples** (góc phải trên): mẫu response thành công + lỗi (401/404/409/422/503…).\n".
            "- Phần **Description** của mỗi request: màn hình UI, input, enum, HTTP status.\n\n".
            "## Response envelope\n".
            "Thành công: `{ \"success\": true, \"data\": {...}, \"meta\": { \"request_id\": \"uuid\" }, \"error\": null }`\n\n".
            "Lỗi: `{ \"success\": false, \"data\": null, \"meta\": { \"request_id\": \"uuid\" }, \"error\": { \"code\": \"...\", \"message\": \"...\", \"details\": {} } }`\n\n".
            "## Header bắt buộc (đã gắn sẵn)\n".
            "| Header | Giá trị |\n|---|---|\n| Authorization | Bearer {{partner_token}} |\n| X-Partner | fizahub |\n| X-Request-Id | UUID mới mỗi lần |\n| Idempotency-Key | UUID (mọi POST/PATCH trừ SSO Verify) |\n| Accept | application/json |\n\n".
            "> ⚠️ Token trong file này là token **thử nghiệm**. Khi chạy chính thức, MLHUB sẽ đổi token và gửi lại — tải file mới từ https://mlhub.vn/api-fizahub/postman\n\n".
            "## Ngoài phạm vi collection này: Cổng báo cáo cho lãnh đạo FizaHUB\n".
            "25 request ở trên là API cho **đội kỹ thuật** tích hợp app. Nếu chỉ cần **xem báo cáo** onboarding/HKD (không sửa gì), MLHUB có một **website riêng** cho lãnh đạo FizaHUB, không liên quan Postman/token ở đây: https://fzh.vmo.com.vn — đăng nhập bằng tài khoản MLHUB có sẵn, được MLHUB cấp quyền theo danh sách email do FizaHUB cung cấp. Xem chi tiết tại https://mlhub.vn/api-fizahub/help-test (Bước 9).",
        'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
    ],
    'event' => [
        [
            'listen' => 'prerequest',
            'script' => [
                'type' => 'text/javascript',
                'exec' => [
                    "const cv = pm.collectionVariables;",
                    "if (!cv.get('external_business_id')) cv.set('external_business_id', 'fiza-' + pm.variables.replaceIn('{{\$guid}}'));",
                    "if (!cv.get('external_user_id')) cv.set('external_user_id', 'owner-' + pm.variables.replaceIn('{{\$guid}}'));",
                    "if (!cv.get('from')) { const d = new Date(); d.setUTCDate(d.getUTCDate() - 29); cv.set('from', d.toISOString().slice(0, 10)); }",
                    "if (!cv.get('to')) cv.set('to', new Date().toISOString().slice(0, 10));",
                ],
            ],
        ],
    ],
    'variable' => [
        ['key' => 'base_url', 'value' => 'https://mlhub.vn', 'type' => 'string', 'description' => 'Gốc URL MLHUB. Đã điền sẵn https://mlhub.vn — không cần sửa khi test production.'],
        ['key' => 'partner_token', 'value' => 'fizahub_6199e9a82d0e961002965d406c970efc0038d2a4e658c1cf', 'type' => 'string', 'description' => 'Token đối tác (thử nghiệm). Đã điền sẵn — không cần nhập tay. Khi chạy chính thức sẽ đổi.'],
        ['key' => 'external_user_id', 'value' => '', 'type' => 'string', 'description' => 'Tự sinh khi chạy (owner- + UUID).'],
        ['key' => 'external_business_id', 'value' => '', 'type' => 'string', 'description' => 'Tự sinh khi chạy (fiza- + UUID).'],
        ['key' => 'onboarding_request_id', 'value' => '', 'type' => 'string', 'description' => 'Tự lưu từ bước 04 Create Onboarding.'],
        ['key' => 'onboarding_ticket_id', 'value' => '', 'type' => 'string', 'description' => 'Tự lưu từ bước 04 (ticket tiếp nhận onboarding).'],
        ['key' => 'onboarding_ready', 'value' => '0', 'type' => 'string', 'description' => '1 khi onboarding ready/completed — dùng để chạy bước 22 CRM.'],
        ['key' => 'ticket_id', 'value' => '', 'type' => 'string', 'description' => 'Tự lưu từ bước 16/17.'],
        ['key' => 'attachment_id', 'value' => '', 'type' => 'string', 'description' => 'Tự lưu từ bước 23 Upload Attachment.'],
        ['key' => 'campaign_id', 'value' => '', 'type' => 'string', 'description' => 'Tự lưu từ bước 11 Campaign List.'],
        ['key' => 'campaign_approval_ready', 'value' => '0', 'type' => 'string', 'description' => '1 khi chiến dịch đầu tiên đang pending_approval — dùng cho bước 13.'],
        ['key' => 'from', 'value' => '', 'type' => 'string', 'description' => 'Tự sinh YYYY-MM-DD (30 ngày trước).'],
        ['key' => 'to', 'value' => '', 'type' => 'string', 'description' => 'Tự sinh YYYY-MM-DD (hôm nay).'],
    ],
    'item' => [
        ['name' => 'System', 'description' => '2 request hệ thống: kiểm tra API sống và xác minh token.', 'item' => [$health, $sso]],
        ['name' => 'Onboarding', 'description' => '6 request cho luồng đăng ký và theo dõi onboarding (màn hình 01–05).', 'item' => [$catalog, $createOnboarding, $onboardingDetail, $status, $profile, $pref]],
        ['name' => 'Growth', 'description' => '6 request cho dashboard, phân tích tăng trưởng, chiến dịch và gói (màn hình 06–11).', 'item' => [$dashboard, $insights, $campaignList, $campaignDetail, $approval, $package]],
        ['name' => 'Support', 'description' => '10 request cho trung tâm hỗ trợ: tin nhắn text + đính kèm 3 nhóm ảnh/video/tài liệu (màn hình 12–14). Ảnh có `image_url` ký tạm để preview trong chat.', 'item' => [$presets, $createTicket, $listTickets, $ticketDetail, $sendMsg, $close, $reopen, $listAttachments, $uploadAttachment, $downloadAttachment]],
        ['name' => 'CRM', 'description' => '1 request tạo link đăng nhập CRM một lần (màn hình 15).', 'item' => [$crm]],
    ],
];

$json = json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
$target = __DIR__.'/FizaHUB-Partner-API.postman_collection.json';
file_put_contents($target, $json."\n");

// Sanity summary.
$check = json_decode($json, true);
$folders = $check['item'];
$counts = array_map(fn ($f) => count($f['item']), $folders);
echo 'Folders: '.implode(',', array_map(fn ($f) => $f['name'], $folders)).PHP_EOL;
echo 'Counts: '.implode(',', $counts).' (total '.array_sum($counts).')'.PHP_EOL;
echo 'Bytes: '.strlen($json).PHP_EOL;
