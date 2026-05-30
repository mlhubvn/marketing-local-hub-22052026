<?php

/**
 * Dữ liệu demo MLHUB cho thị trường Việt Nam (hộ kinh doanh, spa, F&B, nha khoa mẫu).
 * Dùng bởi LocalBoostDemoSeeder khi cài mới hoặc seed thủ công.
 */
return [
    'user' => [
        'email' => 'demo@mlhub.vn',
        'name' => 'MLHUB Demo',
        'username' => 'mlhubdemo',
        'password' => '123456',
        'locale' => 'vi',
    ],

    'weekly_hours' => [
        'mon' => ['is_closed' => false, 'open_time' => '08:30', 'close_time' => '21:00'],
        'tue' => ['is_closed' => false, 'open_time' => '08:30', 'close_time' => '21:00'],
        'wed' => ['is_closed' => false, 'open_time' => '08:30', 'close_time' => '21:00'],
        'thu' => ['is_closed' => false, 'open_time' => '08:30', 'close_time' => '21:00'],
        'fri' => ['is_closed' => false, 'open_time' => '08:30', 'close_time' => '22:00'],
        'sat' => ['is_closed' => false, 'open_time' => '09:00', 'close_time' => '22:00'],
        'sun' => ['is_closed' => true, 'open_time' => '09:00', 'close_time' => '18:00'],
    ],

    'businesses' => [
        'spa' => [
            'slug' => 'sen-vang-spa',
            'name' => 'Sen Vàng Spa',
            'type' => 'spa',
            'phone' => '028 7300 1001',
            'email' => 'lienhe@senvangspa.vn',
            'website' => 'https://senvangspa.vn',
            'address' => '45 Nguyễn Huệ, Quận 1, TP. Hồ Chí Minh',
            'google_maps_url' => 'https://maps.google.com/?q=45+Nguyen+Hue+District+1+Ho+Chi+Minh+City',
        ],
        'restaurant' => [
            'slug' => 'com-nha-bistro',
            'name' => 'Cơm Nhà Bistro',
            'type' => 'restaurant',
            'phone' => '024 7300 2002',
            'email' => 'datban@comnhabistro.vn',
            'website' => 'https://comnhabistro.vn',
            'address' => '12 Lý Thường Kiệt, Hoàn Kiếm, Hà Nội',
            'google_maps_url' => 'https://maps.google.com/?q=12+Ly+Thuong+Kiet+Hanoi',
        ],
        'bistro' => [
            'slug' => 'com-nha-bistro',
            'name' => 'Cơm Nhà Bistro',
            'type' => 'restaurant',
            'phone' => '024 7300 2002',
            'email' => 'datban@comnhabistro.vn',
            'website' => 'https://comnhabistro.vn',
            'address' => '12 Lý Thường Kiệt, Hoàn Kiếm, Hà Nội',
            'google_maps_url' => 'https://maps.google.com/?q=12+Ly+Thuong+Kiet+Hanoi',
        ],
        'clinic' => [
            'slug' => 'nha-khoa-an-nhien',
            'name' => 'Nha Khoa An Nhiên',
            'type' => 'clinic',
            'phone' => '0236 7300 3003',
            'email' => 'chamsoc@nhakhoaannhien.vn',
            'website' => 'https://nhakhoaannhien.vn',
            'address' => '88 Bạch Đằng, Hải Châu, Đà Nẵng',
            'google_maps_url' => 'https://maps.google.com/?q=88+Bach+Dang+Da+Nang',
        ],
    ],

    'campaigns' => [
        [
            'slug' => 'sen-vang-danh-gia-google',
            'business' => 'spa',
            'name' => 'Thu thập đánh giá Google',
            'type' => 'review',
            'settings' => [
                'google_review_url' => 'https://www.google.com/maps/search/Sen+Vàng+Spa+đánh+giá',
                'facebook_review_url' => 'https://www.facebook.com/',
                'positive_threshold' => 4,
                'preferred_destination' => 'google',
                'thank_you_message' => 'Cảm ơn bạn đã ghé Sen Vàng Spa.',
                'negative_feedback_message' => 'Hãy cho chúng tôi biết điều cần cải thiện trước lần ghé tiếp theo.',
            ],
        ],
        [
            'slug' => 'an-nhien-tu-van-mien-phi',
            'business' => 'clinic',
            'name' => 'Form đăng ký tư vấn miễn phí',
            'type' => 'lead',
            'settings' => [
                'headline' => 'Đặt lịch tư vấn nha khoa miễn phí',
            ],
        ],
        [
            'slug' => 'com-nha-uudai-cuoi-tuan',
            'business' => 'restaurant',
            'name' => 'Giảm 20% bữa tối cuối tuần',
            'type' => 'coupon',
            'settings' => [
                'discount_type' => 'percentage',
                'discount_value' => '20',
                'coupon_code' => 'CUOITUAN20',
                'usage_limit' => 200,
                'expiry_date' => null,
                'terms' => 'Áp dụng khi ăn tại chỗ, bữa tối thứ Sáu–Chủ nhật. Không áp dụng ngày lễ.',
            ],
        ],
        [
            'slug' => 'sen-vang-dat-lich-massage',
            'business' => 'spa',
            'name' => 'Đặt lịch massage thư giãn',
            'type' => 'booking',
            'settings' => [
                'headline' => 'Đặt lịch massage 60 phút',
            ],
        ],
        [
            'slug' => 'com-nha-phan-hoi-am-thuc',
            'business' => 'restaurant',
            'name' => 'Khảo sát trải nghiệm ẩm thực',
            'type' => 'feedback',
            'settings' => [
                'headline' => 'Chia sẻ trải nghiệm bữa ăn của bạn',
                'thank_you_message' => 'Cảm ơn bạn. Ý kiến giúp đội ngũ Cơm Nhà phục vụ tốt hơn.',
                'rating_required' => false,
                'contact_required' => false,
            ],
        ],
    ],

    'booking_service' => [
        'business' => 'spa',
        'name' => 'Massage thư giãn 60 phút',
        'duration_minutes' => 60,
        'price' => 450000,
        'description' => 'Liệu trình massage thư giãn cho khách mới và khách quen.',
        'available_days' => ['mon', 'tue', 'wed', 'thu', 'fri', 'sat'],
        'time_slots' => ['09:00', '10:00', '14:00', '15:00', '16:00'],
    ],

    'customers' => [
        ['name' => 'Nguyễn Thị Mai', 'phone' => '0901 234 567', 'email' => 'mainguyen@demo.mlhub.vn', 'business' => 'spa'],
        ['name' => 'Trần Văn Đức', 'phone' => '0912 345 678', 'email' => 'ductran@demo.mlhub.vn', 'business' => 'restaurant'],
        ['name' => 'Lê Thị Hương', 'phone' => '0933 456 789', 'email' => 'huongle@demo.mlhub.vn', 'business' => 'clinic'],
        ['name' => 'Phạm Minh Khôi', 'phone' => '0944 567 890', 'email' => 'khoipham@demo.mlhub.vn', 'business' => 'spa'],
        ['name' => 'Hoàng Lan Anh', 'phone' => '0965 678 901', 'email' => 'lananh@demo.mlhub.vn', 'business' => 'restaurant'],
    ],

    'scan_cities' => ['TP. Hồ Chí Minh', 'Hà Nội', 'Đà Nẵng'],
    'scan_country' => 'VN',

    /*
     * Chỉ số demo cho dashboard tăng trưởng (lượt quét QR = visits; conversions theo loại chiến dịch).
     * Tổng visits ~15k+, conversions ~1.2k+, tỷ lệ chuyển đổi ~8–10% (không vượt 100%).
     */
    'campaign_metrics' => [
        'sen-vang-danh-gia-google' => ['visits' => 4200, 'conversions' => 360],
        'an-nhien-tu-van-mien-phi' => ['visits' => 2800, 'conversions' => 245],
        'com-nha-uudai-cuoi-tuan' => ['visits' => 4800, 'conversions' => 420],
        'sen-vang-dat-lich-massage' => ['visits' => 3100, 'conversions' => 275],
        'com-nha-phan-hoi-am-thuc' => ['visits' => 2400, 'conversions' => 195],
    ],

    'messages' => [
        'review_positive' => 'Dịch vụ tốt, nhân viên thân thiện.',
        'review_negative' => 'Thời gian chờ hơi lâu so với dự kiến.',
        'lead' => 'Tôi muốn đặt lịch tư vấn.',
        'booking_note' => 'Yêu cầu đặt lịch buổi chiều.',
        'feedback_positive' => 'Món ăn ngon, không gian ấm cúng.',
        'feedback_negative' => 'Bàn chưa sẵn sàng đúng giờ đặt.',
    ],
];
