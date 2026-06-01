<?php

/**
 * Dữ liệu mở rộng cho Admin Faker — module upstream (CRM, email, loyalty).
 * Được nạp bởi MLHUBAdminFakerConfig::extensions().
 */
return [
    'loyalty_cards' => [
        [
            'business_key' => 'mi_quang_1a',
            'slug' => 'admin-faker-tich-diem-mi-quang',
            'name' => 'Thẻ tích điểm Mì Quảng 1A',
            'required_stamps' => 8,
            'reward_title' => 'Tô mì quảng size lớn',
            'reward_value' => 'Miễn phí',
        ],
        [
            'business_key' => 'banh_trang_tran',
            'slug' => 'admin-faker-tich-diem-banh-trang',
            'name' => 'Thẻ khách quen Bánh Tráng Trần',
            'required_stamps' => 10,
            'reward_title' => 'Combo cuốn + nước chấm',
            'reward_value' => 'Giảm 50%',
        ],
        [
            'business_key' => 'vanda_spa',
            'slug' => 'admin-faker-tich-diem-vanda',
            'name' => 'Spa Vanda — 6 lần là tặng',
            'required_stamps' => 6,
            'reward_title' => 'Massage body 45 phút',
            'reward_value' => 'Tặng',
        ],
        [
            'business_key' => 'highlands_dn',
            'slug' => 'admin-faker-tich-diem-highlands',
            'name' => 'Highlands Đà Nẵng — cà phê thưởng',
            'required_stamps' => 5,
            'reward_title' => 'Ly Phin chủ đạo',
            'reward_value' => 'Miễn phí',
        ],
    ],
    'referral_campaigns' => [
        [
            'business_key' => 'mi_quang_1a',
            'slug' => 'admin-faker-gioi-thieu-mi-quang',
            'name' => 'Giới thiệu bạn ăn Mì Quảng',
            'reward_title' => 'Mã giảm 20% cho cả hai',
            'required_referrals' => 1,
            'target_action' => 'lead',
        ],
        [
            'business_key' => 'east_west_dental',
            'slug' => 'admin-faker-gioi-thieu-nha-khoa',
            'name' => 'Giới thiệu khách nha khoa',
            'reward_title' => 'Khám tổng quát miễn phí',
            'required_referrals' => 1,
            'target_action' => 'booking',
        ],
    ],
    'email_automations' => [
        ['name' => '[DEMO] Xác nhận đặt lịch', 'trigger_event' => 'booking.confirmed', 'template' => 'Booking Confirmed', 'business_key' => null],
        ['name' => '[DEMO] Nhắc lịch hẹn', 'trigger_event' => 'booking.completed', 'template' => 'Booking Reminder', 'business_key' => 'vanda_spa'],
        ['name' => '[DEMO] Cảm ơn nhận coupon', 'trigger_event' => 'coupon.claimed', 'template' => 'Coupon Claimed', 'business_key' => null],
        ['name' => '[DEMO] Lead mới — chào khách', 'trigger_event' => 'lead.submitted', 'template' => 'Lead Received', 'business_key' => null],
        ['name' => '[DEMO] Feedback thấp — xin lỗi', 'trigger_event' => 'review.low_score', 'template' => 'Low-score Recovery', 'business_key' => null],
        ['name' => '[DEMO] Khách mới CRM', 'trigger_event' => 'customer.created', 'template' => 'Lead Follow-up', 'business_key' => null],
    ],
    'crm_segments' => [
        ['name' => 'Khách VIP Đà Nẵng', 'color' => '#f59e0b', 'filters' => ['rules' => [['field' => 'tag', 'operator' => 'has', 'value' => 'vip']]]],
        ['name' => 'Cần follow-up tuần này', 'color' => '#dc2626', 'filters' => ['rules' => [['field' => 'tag', 'operator' => 'has', 'value' => 'needs-follow-up']]]],
        ['name' => 'Khách quay lại', 'color' => '#16a34a', 'filters' => ['rules' => [['field' => 'tag', 'operator' => 'has', 'value' => 'returning-customer']]]],
        ['name' => 'Đã nhận ưu đãi', 'color' => '#7c3aed', 'filters' => ['rules' => [['field' => 'tag', 'operator' => 'has', 'value' => 'coupon-claimed']]]],
    ],
    'crm_automations' => [
        ['name' => '[DEMO] Task khi lead mới', 'trigger_event' => 'lead.created', 'action_type' => 'create_task', 'action_title' => 'Gọi xác nhận lead trong 24h'],
        ['name' => '[DEMO] Gắn tag sau booking', 'trigger_event' => 'booking.completed', 'action_type' => 'add_tag', 'action_value' => 'loyal-customer'],
        ['name' => '[DEMO] Nhắc follow-up coupon', 'trigger_event' => 'coupon.claimed', 'action_type' => 'create_task', 'action_title' => 'Nhắc khách dùng mã trước hạn'],
        ['name' => '[DEMO] VIP khi điểm cao', 'trigger_event' => 'score.updated', 'action_type' => 'add_tag', 'action_value' => 'vip'],
    ],
];
