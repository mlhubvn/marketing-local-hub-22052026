<?php

namespace Modules\CustomMLHUB\Support\DemoData;

use Illuminate\Support\Str;

class DemoContentCatalog
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function demoUsers(): array
    {
        return [
            'mlhubfree' => [
                'email' => 'mlhubfree@mlhub.vn',
                'name' => 'Nguyễn Thị Phúc Lâm',
                'domain_slug' => 'phuclam-hkd',
                'plan' => 'mlhub-free-da-nang',
                'businesses' => 1,
                'campaigns' => 5,
                'landing_pages' => 4,
                'customers' => 68,
                'scans' => 184,
                'industries' => ['food_beverage'],
            ],
            'mlhubstarter' => [
                'email' => 'mlhubstarter@mlhub.vn',
                'name' => 'Trần Minh Tuấn',
                'domain_slug' => 'trasua-an-thuong',
                'plan' => 'mlhub-starter-monthly',
                'businesses' => 1,
                'campaigns' => 17,
                'landing_pages' => 9,
                'customers' => 237,
                'scans' => 1013,
                'industries' => ['food_beverage', 'beauty_personal_care'],
            ],
            'mlhubgrowth' => [
                'email' => 'mlhubgrowth@mlhub.vn',
                'name' => 'Lê Hoàng Yến',
                'domain_slug' => 'moc-spa-group',
                'plan' => 'mlhub-growth-monthly',
                'businesses' => 3,
                'campaigns' => 64,
                'landing_pages' => 41,
                'customers' => 816,
                'scans' => 3467,
                'industries' => ['beauty_personal_care', 'food_beverage', 'tourism_hospitality_experience'],
            ],
            'mlhubpro' => [
                'email' => 'mlhubpro@mlhub.vn',
                'name' => 'Phạm Quốc Anh',
                'domain_slug' => 'pqa-local-group',
                'plan' => 'mlhub-pro-monthly',
                'businesses' => 9,
                'campaigns' => 257,
                'landing_pages' => 169,
                'customers' => 2183,
                'scans' => 8729,
                'industries' => [
                    'food_beverage',
                    'beauty_personal_care',
                    'tourism_hospitality_experience',
                    'retail_goods',
                    'technical_repair_maintenance',
                    'health_dental_fitness',
                    'education_training_coaching',
                    'professional_b2b_services',
                    'real_estate_rental_property',
                ],
            ],
            'mlhubpartner' => [
                'email' => 'mlhubpartner@mlhub.vn',
                'name' => 'Nguyễn Thị Mai Linh',
                'domain_slug' => 'mai-linh-agency',
                'plan' => 'mlhub-partner-monthly',
                'businesses' => 100,
                'campaigns' => 617,
                'landing_pages' => 431,
                'customers' => 4761,
                'scans' => 11847,
                'industries' => [
                    'food_beverage',
                    'beauty_personal_care',
                    'tourism_hospitality_experience',
                    'retail_goods',
                    'technical_repair_maintenance',
                    'health_dental_fitness',
                    'education_training_coaching',
                    'professional_b2b_services',
                    'real_estate_rental_property',
                    'wholesale_distribution',
                    'home_construction_interior',
                    'transport_delivery_logistics',
                    'digital_creator_online_business',
                    'small_manufacturing_processing_ocop',
                    'agriculture_fisheries_local_supply',
                    'culture_entertainment_sports_community',
                    'organization_association_public_community',
                    'other_needs_classification',
                ],
            ],
        ];
    }

    /**
     * Pool cơ sở demo = nhóm "featured" (tên đẹp, mã category hợp lệ, ngành phổ biến lên đầu)
     * + phần tự sinh phủ MỌI danh mục còn lại từ chính BusinessTypeCatalog (không thể sai mã,
     * và mỗi danh mục có ít nhất 1 cơ sở để trang Danh mục không bị trống).
     *
     * @return list<array{group: string, category: string, type: string, name: string}>
     */
    public static function businessPool(): array
    {
        $featured = self::featuredBusinessPool();

        $seenCategories = [];
        foreach ($featured as $business) {
            $seenCategories[$business['category']] = true;
        }

        $catalog = 'Modules\\AppBusinessProfiles\\Support\\BusinessTypeCatalog';

        if (! class_exists($catalog)) {
            return $featured;
        }

        $districts = self::branchDistricts();

        // Gom danh mục còn lại theo từng nhóm để có thể xen kẽ (round-robin) giữa các nhóm.
        $byGroup = [];
        foreach ($catalog::taxonomyTree() as $group) {
            $groupCode = (string) $group['code'];

            foreach ((array) ($group['categories'] ?? []) as $category) {
                $categoryCode = (string) ($category['code'] ?? '');

                if ($categoryCode === '' || isset($seenCategories[$categoryCode])) {
                    continue;
                }

                $seenCategories[$categoryCode] = true;
                $byGroup[$groupCode][] = [
                    'group' => $groupCode,
                    'category' => $categoryCode,
                    'type' => (string) ($category['legacy_type'] ?? 'Other'),
                    'label' => (string) ($category['label'] ?? $categoryCode),
                ];
            }
        }

        // Xen kẽ: mỗi vòng lấy 1 danh mục từ mỗi nhóm → khi cắt theo số lượng cơ sở,
        // các cơ sở vẫn trải đều khắp 18 ngành thay vì dồn vào vài ngành đầu.
        $generated = [];
        $index = 0;
        $exhausted = false;

        while (! $exhausted) {
            $exhausted = true;

            foreach ($byGroup as $groupCode => $categories) {
                $next = array_shift($byGroup[$groupCode]);

                if ($next === null) {
                    continue;
                }

                $exhausted = false;
                $district = $districts[$index % count($districts)];
                $index++;

                $generated[] = [
                    'group' => $next['group'],
                    'category' => $next['category'],
                    'type' => $next['type'],
                    'name' => self::categoryBusinessName($next['label']).' '.$district,
                ];
            }
        }

        return array_merge($featured, $generated);
    }

    /**
     * Cơ sở "featured" có kịch bản/tên riêng cho các tài khoản demo nhỏ (free/starter/growth/pro).
     * Mã category đã được kiểm tra hợp lệ với BusinessTypeCatalog 2026.06.
     *
     * @return list<array{group: string, category: string, type: string, name: string}>
     */
    private static function featuredBusinessPool(): array
    {
        return [
            ['group' => 'food_beverage', 'category' => 'restaurant_eatery', 'type' => 'Restaurant', 'name' => 'Hộ Kinh Doanh Phúc Lâm'],
            ['group' => 'food_beverage', 'category' => 'cafe_milk_tea', 'type' => 'Coffee shop', 'name' => 'Hộ Kinh Doanh Trà Sữa An Thượng'],
            ['group' => 'food_beverage', 'category' => 'restaurant_eatery', 'type' => 'Restaurant', 'name' => 'Hộ Kinh Doanh Bánh Mì Sông Hàn'],
            ['group' => 'beauty_personal_care', 'category' => 'spa_massage', 'type' => 'Spa', 'name' => 'Mộc Spa Đà Nẵng'],
            ['group' => 'beauty_personal_care', 'category' => 'spa_massage', 'type' => 'Spa', 'name' => 'An Nhiên Gội Đầu Dưỡng Sinh'],
            ['group' => 'beauty_personal_care', 'category' => 'nail_lash_brow', 'type' => 'Nail studio', 'name' => 'Nail House Hải Châu'],
            ['group' => 'beauty_personal_care', 'category' => 'hair_salon', 'type' => 'Salon', 'name' => 'Salon Tóc Sông Hàn'],
            ['group' => 'food_beverage', 'category' => 'cafe_milk_tea', 'type' => 'Coffee shop', 'name' => 'Cà Phê Sông Hàn'],
            ['group' => 'food_beverage', 'category' => 'seafood_local_restaurant', 'type' => 'Restaurant', 'name' => 'Hải Sản Mỹ Khê'],
            ['group' => 'food_beverage', 'category' => 'restaurant_eatery', 'type' => 'Restaurant', 'name' => 'Bún Chả Cá Hải Châu'],
            ['group' => 'tourism_hospitality_experience', 'category' => 'homestay_villa_apartment', 'type' => 'Hotel', 'name' => 'Homestay An Thượng'],
            ['group' => 'tourism_hospitality_experience', 'category' => 'serviced_apartment', 'type' => 'Hotel', 'name' => 'Villa Mỹ Khê'],
            ['group' => 'tourism_hospitality_experience', 'category' => 'local_experience_tour', 'type' => 'Other', 'name' => 'Tour Trải Nghiệm Hội An'],
            ['group' => 'retail_goods', 'category' => 'souvenir_ocop_gifts', 'type' => 'Local store', 'name' => 'Đặc Sản Quảng Đà'],
            ['group' => 'retail_goods', 'category' => 'souvenir_ocop_gifts', 'type' => 'Local store', 'name' => 'Quà Tặng OCOP Đà Nẵng'],
            ['group' => 'technical_repair_maintenance', 'category' => 'auto_motorbike_repair', 'type' => 'Auto repair', 'name' => 'Garage Sơn Trà'],
            ['group' => 'technical_repair_maintenance', 'category' => 'appliance_ac_repair', 'type' => 'Other', 'name' => 'Điện Lạnh Thanh Khê'],
            ['group' => 'technical_repair_maintenance', 'category' => 'laundry_dry_cleaning', 'type' => 'Other', 'name' => 'Giặt Ủi Thanh Khê'],
            ['group' => 'health_dental_fitness', 'category' => 'dental_clinic', 'type' => 'Dentist', 'name' => 'Nha Khoa Hải Châu'],
            ['group' => 'health_dental_fitness', 'category' => 'gym_fitness_center', 'type' => 'Gym', 'name' => 'Gym Sơn Trà'],
            ['group' => 'education_training_coaching', 'category' => 'language_center', 'type' => 'Education center', 'name' => 'Trung Tâm Tiếng Anh Đà Nẵng'],
            ['group' => 'professional_b2b_services', 'category' => 'accounting_tax_service', 'type' => 'Professional service', 'name' => 'Kế Toán Quảng Đà'],
            ['group' => 'real_estate_rental_property', 'category' => 'boarding_house_room_rental', 'type' => 'Real estate office', 'name' => 'Nhà Trọ Liên Chiểu'],
            ['group' => 'home_construction_interior', 'category' => 'furniture_store_workshop', 'type' => 'Other', 'name' => 'Nội Thất Cẩm Lệ'],
            ['group' => 'transport_delivery_logistics', 'category' => 'motorbike_delivery', 'type' => 'Other', 'name' => 'Vận Chuyển Đà Nẵng'],
            ['group' => 'digital_creator_online_business', 'category' => 'content_creator_kol', 'type' => 'Other', 'name' => 'Creator Studio An Thượng'],
            ['group' => 'small_manufacturing_processing_ocop', 'category' => 'ocop_local_producer', 'type' => 'Other', 'name' => 'Hợp Tác Xã Nông Sản Hòa Vang'],
            ['group' => 'wholesale_distribution', 'category' => 'b2b_trade_supply', 'type' => 'Other', 'name' => 'Kho Sỉ Quảng Nam'],
            ['group' => 'agriculture_fisheries_local_supply', 'category' => 'farm_produce_supplier', 'type' => 'Other', 'name' => 'Vườn Rau Hòa Vang'],
            ['group' => 'culture_entertainment_sports_community', 'category' => 'recreation_attraction', 'type' => 'Other', 'name' => 'Sân Khấu Cộng Đồng Sơn Trà'],
            ['group' => 'organization_association_public_community', 'category' => 'community_organization', 'type' => 'Other', 'name' => 'Hội Quán Khởi Nghiệp Địa Phương'],
            ['group' => 'other_needs_classification', 'category' => 'multi_industry_business', 'type' => 'Other', 'name' => 'Dịch Vụ Tổng Hợp Hải Châu'],
        ];
    }

    /**
     * Rút tên cơ sở gọn từ nhãn danh mục (bỏ phần sau "/" hoặc "(").
     */
    private static function categoryBusinessName(string $label): string
    {
        $parts = preg_split('~\s*[/(]~u', $label, 2);
        $base = trim((string) ($parts[0] ?? $label));

        return $base !== '' ? $base : $label;
    }

    /**
     * @return list<string>
     */
    public static function addresses(): array
    {
        return [
            '23 Bạch Đằng, Hải Châu',
            '41 Trần Phú, Hải Châu',
            '87 Nguyễn Văn Linh, Hải Châu',
            '58 Lê Duẩn, Hải Châu',
            '112 Võ Nguyên Giáp, Sơn Trà',
            '19 Hồ Nghinh, Sơn Trà',
            '71 Phạm Văn Đồng, Sơn Trà',
            '95 Ngô Quyền, Sơn Trà',
            '36 An Thượng, Ngũ Hành Sơn',
            '63 Châu Thị Vĩnh Tế, Ngũ Hành Sơn',
            '144 Điện Biên Phủ, Thanh Khê',
            '29 Hà Huy Tập, Thanh Khê',
            '202 Nguyễn Tất Thành, Thanh Khê',
            '78 Cách Mạng Tháng 8, Cẩm Lệ',
            '15 Ông Ích Đường, Cẩm Lệ',
            '256 Tôn Đức Thắng, Liên Chiểu',
            '91 Nguyễn Lương Bằng, Liên Chiểu',
        ];
    }

    /**
     * @return list<string>
     */
    public static function customerNames(): array
    {
        return array_values(array_unique(array_map(
            static fn (int $index): string => static::customerDisplayName($index, 1),
            range(0, 59),
        )));
    }

    /**
     * @return list<string>
     */
    public static function branchDistricts(): array
    {
        return [
            'Hải Châu', 'Sơn Trà', 'Thanh Khê', 'Ngũ Hành Sơn', 'Liên Chiểu', 'Cẩm Lệ', 'Hòa Vang',
        ];
    }

    /**
     * Tên khách hiển thị — kết hợp họ/tên Việt Nam + khách quốc tế, không dùng hậu tố số.
     */
    public static function customerDisplayName(int $index, int $userId): string
    {
        $vietnameseFirst = [
            'Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Huỳnh', 'Võ', 'Đặng', 'Bùi', 'Đỗ',
            'Hồ', 'Ngô', 'Dương', 'Lý', 'Đinh', 'Cao', 'Vũ', 'Tạ', 'Lâm', 'Châu',
        ];
        $vietnameseGiven = [
            'Minh Anh', 'Quốc Bảo', 'Thanh Hằng', 'Gia Huy', 'Ngọc Linh', 'Tuấn Kiệt', 'Phương Vy',
            'Minh Châu', 'Anh Thư', 'Bảo Trân', 'Hoài Nam', 'Thiên Ân', 'Khánh Linh', 'Đức Phát',
            'Hà My', 'Quang Vinh', 'Ngọc Hân', 'Minh Quân', 'Gia Hân', 'Thành Đạt', 'Kim Ngân',
            'Hữu Lộc', 'Bảo Châu', 'Thúy Vy', 'Hoàng Long', 'Diễm My', 'Công Danh', 'Mỹ Duyên',
            'Quốc Khánh', 'Bích Trâm', 'Xuân Mai', 'Hồng Nhung', 'Văn Tài', 'Thu Hà',
        ];
        $foreignFirst = [
            'James', 'Sarah', 'David', 'Emma', 'Michael', 'Sophie', 'Daniel', 'Olivia',
            'Robert', 'Anna', 'Thomas', 'Lisa', 'Chen', 'Yuki', 'Marcus', 'Isabella',
        ];
        $foreignLast = [
            'Morrison', 'Chen', 'Park', 'Wilson', 'Thompson', 'Nguyen', 'Miller', 'Brown',
            'Johnson', 'Schmidt', 'Tanaka', 'Lee', 'Williams', 'Garcia', 'Kim', 'Anderson',
        ];

        $seed = ($userId * 7919 + $index * 104729) % 10000;

        if ($seed % 7 === 0) {
            $first = $foreignFirst[($index + $userId) % count($foreignFirst)];
            $last = $foreignLast[($index * 3 + $userId) % count($foreignLast)];

            return $first.' '.$last;
        }

        $first = $vietnameseFirst[($index + $userId * 3) % count($vietnameseFirst)];
        $given = $vietnameseGiven[($index * 5 + $userId) % count($vietnameseGiven)];

        return $first.' '.$given;
    }

    public static function customerEmail(int $index, int $userId, string $name): string
    {
        $local = Str::slug($name, '.');
        $domains = ['gmail.com', 'yahoo.com', 'outlook.com', 'icloud.com', 'hotmail.com'];
        $domain = $domains[($index + $userId) % count($domains)];
        $suffix = ($index * 17 + $userId * 13) % 997;

        return ($local !== '' ? $local : 'khach.hang').'.'.$suffix.'@'.$domain;
    }

    public static function businessContactEmail(string $username, int $businessIndex, string $businessName): string
    {
        $slug = Str::slug($businessName);

        if ($slug === '') {
            $slug = $username.'-cs-'.($businessIndex + 1);
        }

        return 'lienhe@'.$slug.'.vn';
    }

    public static function customerScore(int $index, int $userId): int
    {
        $seed = ($userId * 7919 + $index * 104729) % 10000;
        $bucket = $seed % 100;

        if ($bucket < 14) {
            return 12 + ($seed % 24);
        }

        if ($bucket < 38) {
            return 36 + ($seed % 20);
        }

        if ($bucket < 67) {
            return 56 + ($seed % 17);
        }

        if ($bucket < 87) {
            return 73 + ($seed % 16);
        }

        return 89 + ($seed % 10);
    }

    public static function customerLifetimeValue(int $index, int $userId, int $score): int
    {
        $base = 43000 + (($userId * 1337 + $index * 977) % 890000);
        $value = (int) round($base * (1 + ($score / 100)));

        return $value + (($index * 173 + $userId * 419) % 97000);
    }

    public static function reviewRating(int $index, int $userId): int
    {
        $weights = [5, 5, 5, 5, 4, 4, 4, 4, 4, 3, 3, 3, 2, 1];

        return $weights[($userId * 31 + $index * 17) % count($weights)];
    }

    public static function realisticUserAgent(int $index): string
    {
        $agents = [
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Linux; Android 14; SM-S918B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Mobile Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_3) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.3 Safari/605.1.15',
            'Mozilla/5.0 (iPad; CPU OS 17_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.3 Mobile/15E148 Safari/604.1',
        ];

        return $agents[$index % count($agents)];
    }

    /**
     * @return list<string>
     */
    public static function blogTitles(): array
    {
        return DemoArticleLibrary::titles();
    }

    /**
     * @return list<string>
     */
    public static function faqQuestions(): array
    {
        return array_map(
            static fn (array $item): string => $item['q'],
            DemoArticleLibrary::faqs()
        );
    }

    /**
     * Danh mục yêu cầu hỗ trợ (admin dùng chung).
     *
     * @return list<string>
     */
    public static function supportCategories(): array
    {
        return [
            'Tài khoản & đăng nhập',
            'Thanh toán & gói dịch vụ',
            'Mã QR & chiến dịch',
            'CRM & khách hàng',
            'Google Business & đánh giá',
            'Tự động hóa & tích hợp',
            'Lỗi kỹ thuật',
            'Góp ý tính năng',
        ];
    }

    /**
     * @return list<string>
     */
    public static function supportTypes(): array
    {
        return ['Câu hỏi', 'Sự cố', 'Yêu cầu tính năng', 'Khiếu nại', 'Hướng dẫn sử dụng'];
    }

    /**
     * @return list<string>
     */
    public static function supportLabels(): array
    {
        return ['Khẩn cấp', 'Đang chờ khách', 'Đã chuyển kỹ thuật', 'Chờ thanh toán', 'Đã giải quyết'];
    }

    /**
     * Tiêu đề + nội dung ticket hỗ trợ demo.
     *
     * @return list<array{title: string, content: string}>
     */
    public static function supportTickets(): array
    {
        return [
            ['title' => 'Không tạo thêm được chiến dịch QR', 'content' => 'Mình đang dùng gói hiện tại và muốn tạo thêm chiến dịch QR cho cơ sở mới nhưng hệ thống báo đã đạt giới hạn. Nhờ team kiểm tra giúp mình ạ.'],
            ['title' => 'Nâng gói từ Starter lên Growth', 'content' => 'Cửa hàng mình đang mở thêm chi nhánh, mình muốn nâng lên gói Growth để quản lý nhiều cơ sở. Cho mình hỏi quy trình và chi phí chênh lệch.'],
            ['title' => 'Khách quét QR nhưng không thấy lượt scan', 'content' => 'Mình test quét mã QR review tại quán nhưng số liệu lượt quét chưa thấy cập nhật trên dashboard. Nhờ kiểm tra giúp mình.'],
            ['title' => 'Muốn kết nối Google Business Profile', 'content' => 'Mình muốn đồng bộ đánh giá Google về MLHUB để quản lý tập trung. Hiện chưa rõ cách kết nối tài khoản Google của cửa hàng.'],
            ['title' => 'Xuất danh sách khách hàng ra Excel', 'content' => 'Mình cần xuất toàn bộ danh sách khách hàng đã thu được qua QR để gửi cho kế toán. Có hỗ trợ xuất file không ạ?'],
            ['title' => 'Email tự động chưa gửi cho khách', 'content' => 'Mình đã bật automation gửi email cảm ơn sau khi khách để lại thông tin nhưng chưa thấy khách nhận được. Nhờ team hỗ trợ kiểm tra cấu hình.'],
            ['title' => 'Đổi tên miền riêng cho landing page', 'content' => 'Mình muốn dùng tên miền riêng của cửa hàng cho các trang landing thay vì đường dẫn mặc định. Cần làm những bước gì?'],
            ['title' => 'Góp ý thêm mẫu coupon theo mùa', 'content' => 'Đề xuất MLHUB bổ sung thêm các mẫu coupon theo dịp lễ Tết, mùa du lịch Đà Nẵng để hộ kinh doanh dùng nhanh hơn.'],
        ];
    }

    /**
     * Mẫu thông báo trong ứng dụng.
     *
     * @return list<array{type: string, title: string, message: string}>
     */
    public static function notificationSamples(): array
    {
        return [
            ['type' => 'success', 'title' => 'Chiến dịch QR đã được kích hoạt', 'message' => 'Chiến dịch xin đánh giá của bạn đã sẵn sàng nhận lượt quét từ khách hàng.'],
            ['type' => 'info', 'title' => 'Khách hàng mới từ form thu lead', 'message' => 'Có khách hàng mới vừa để lại thông tin qua landing page của bạn.'],
            ['type' => 'warning', 'title' => 'Đánh giá 2 sao cần phản hồi', 'message' => 'Một đánh giá thấp vừa được ghi nhận. Hãy phản hồi sớm để giữ uy tín cơ sở.'],
            ['type' => 'success', 'title' => 'Coupon vừa được sử dụng', 'message' => 'Một khách hàng vừa dùng mã ưu đãi tại cơ sở của bạn.'],
            ['type' => 'info', 'title' => 'Báo cáo tuần đã sẵn sàng', 'message' => 'Báo cáo tăng trưởng 7 ngày của bạn đã được cập nhật trên dashboard.'],
            ['type' => 'info', 'title' => 'Khách hàng đạt mốc tích điểm', 'message' => 'Một khách quen vừa hoàn thành thẻ tích điểm và đủ điều kiện nhận quà.'],
            ['type' => 'news', 'title' => 'MLHUB cập nhật tính năng mới', 'message' => 'Bộ tăng trưởng vừa bổ sung mẫu chiến dịch mới phù hợp cho hộ kinh doanh Đà Nẵng.'],
            ['type' => 'warning', 'title' => 'Gói dịch vụ sắp đến hạn', 'message' => 'Gói của bạn sẽ gia hạn trong vài ngày tới. Kiểm tra thông tin thanh toán để không gián đoạn.'],
        ];
    }

    /**
     * Thông báo phát toàn hệ thống (admin broadcast).
     *
     * @return list<array{type: string, title: string, message: string}>
     */
    public static function broadcastNotifications(): array
    {
        return [
            ['type' => 'news', 'title' => 'Chào mừng đến với MLHUB', 'message' => 'Cảm ơn bạn đã đồng hành cùng MLHUB. Khám phá bộ tăng trưởng để bắt đầu thu hút và giữ chân khách hàng.'],
            ['type' => 'news', 'title' => 'Hướng dẫn tạo chiến dịch QR đầu tiên', 'message' => 'Chỉ với vài phút, bạn có thể tạo mã QR xin đánh giá và bắt đầu thu thập dữ liệu khách hàng thật.'],
            ['type' => 'update', 'title' => 'Cập nhật CRM & phân khúc khách hàng', 'message' => 'MLHUB vừa nâng cấp bộ lọc phân khúc khách hàng giúp bạn chăm sóc đúng nhóm, đúng thời điểm.'],
        ];
    }

    /**
     * Mẫu coupon hệ thống (admin).
     *
     * @return list<array{name: string, code: string, type: int, discount: float, plans: array<int, string>}>
     */
    public static function coupons(): array
    {
        return [
            ['name' => 'Ưu đãi chào mừng hộ kinh doanh', 'code' => 'MLHUB-WELCOME', 'type' => 1, 'discount' => 20.0, 'plans' => []],
            ['name' => 'Khuyến mãi mùa du lịch Đà Nẵng', 'code' => 'DANANG-SUMMER', 'type' => 1, 'discount' => 15.0, 'plans' => []],
            ['name' => 'Giảm giá đối tác triển khai', 'code' => 'PARTNER-2NAM', 'type' => 1, 'discount' => 30.0, 'plans' => []],
            ['name' => 'Ưu đãi Tết Nguyên Đán', 'code' => 'MLHUB-TET', 'type' => 1, 'discount' => 25.0, 'plans' => []],
        ];
    }

    /**
     * Gói nạp tín dụng AI (admin).
     *
     * @return list<array{name: string, slug: string, credits: int, price: float, featured: bool}>
     */
    public static function creditPacks(): array
    {
        return [
            ['name' => 'Gói tín dụng Khởi động', 'slug' => 'credit-starter', 'credits' => 500, 'price' => 99000.0, 'featured' => false],
            ['name' => 'Gói tín dụng Tăng trưởng', 'slug' => 'credit-growth', 'credits' => 2000, 'price' => 299000.0, 'featured' => true],
            ['name' => 'Gói tín dụng Chuyên nghiệp', 'slug' => 'credit-pro', 'credits' => 5000, 'price' => 599000.0, 'featured' => false],
            ['name' => 'Gói tín dụng Đối tác', 'slug' => 'credit-partner', 'credits' => 15000, 'price' => 1490000.0, 'featured' => false],
        ];
    }

    /**
     * Vai trò quản trị viên hệ thống (admin RBAC).
     *
     * @return list<array{name: string, slug: string, description: string, permissions: array<int, string>}>
     */
    public static function adminRoles(): array
    {
        return [
            ['name' => 'Quản trị tối cao', 'slug' => 'super-admin', 'description' => 'Toàn quyền quản trị hệ thống MLHUB.', 'permissions' => ['*']],
            ['name' => 'Quản lý nội dung', 'slug' => 'content-manager', 'description' => 'Quản lý blog, FAQ và nội dung marketing.', 'permissions' => ['blogs.manage', 'faqs.manage', 'pages.manage']],
            ['name' => 'Chăm sóc khách hàng', 'slug' => 'support-agent', 'description' => 'Xử lý ticket hỗ trợ và tương tác người dùng.', 'permissions' => ['support.manage', 'users.view']],
            ['name' => 'Kế toán & thanh toán', 'slug' => 'billing-staff', 'description' => 'Theo dõi thanh toán, hoá đơn và gói dịch vụ.', 'permissions' => ['payments.view', 'plans.view']],
        ];
    }

    /**
     * Tên mẫu marketing template (theo ngành/dịp).
     *
     * @return list<array{name: string, type: string, category: string, goal: string, description: string}>
     */
    public static function marketingTemplates(): array
    {
        return [
            ['name' => 'Landing ưu đãi quán cà phê', 'type' => 'landing_page', 'category' => 'food_beverage', 'goal' => 'coupon', 'description' => 'Mẫu trang ưu đãi cho quán cà phê, kèm form thu thông tin khách quay lại.'],
            ['name' => 'Trang xin đánh giá nhà hàng hải sản', 'type' => 'landing_page', 'category' => 'food_beverage', 'goal' => 'review', 'description' => 'Hướng khách hài lòng để lại đánh giá thật cho nhà hàng.'],
            ['name' => 'Đặt lịch spa & gội đầu dưỡng sinh', 'type' => 'landing_page', 'category' => 'beauty_personal_care', 'goal' => 'booking', 'description' => 'Mẫu trang đặt lịch dịch vụ spa, hiển thị khung giờ và gói dịch vụ.'],
            ['name' => 'Thu khách tiềm năng cho homestay', 'type' => 'landing_page', 'category' => 'tourism_hospitality_experience', 'goal' => 'lead', 'description' => 'Form thu thông tin khách quan tâm phòng nghỉ và tour trải nghiệm.'],
            ['name' => 'Chương trình tích điểm khách quen', 'type' => 'landing_page', 'category' => 'general', 'goal' => 'loyalty', 'description' => 'Giới thiệu thẻ tích điểm và phần quà cho khách quay lại.'],
            ['name' => 'Ưu đãi khai trương cơ sở mới', 'type' => 'landing_page', 'category' => 'general', 'goal' => 'coupon', 'description' => 'Mẫu trang khai trương kèm mã giảm giá có hạn.'],
            ['name' => 'Giới thiệu bạn bè nhận quà', 'type' => 'landing_page', 'category' => 'general', 'goal' => 'referral', 'description' => 'Mẫu chương trình giới thiệu khách mới đổi ưu đãi.'],
            ['name' => 'Đặc sản OCOP Đà Nẵng', 'type' => 'landing_page', 'category' => 'retail_goods', 'goal' => 'lead', 'description' => 'Trang giới thiệu sản phẩm đặc sản và thu đơn đặt hàng.'],
        ];
    }

    public static function slug(string $value, string $prefix = ''): string
    {
        $slug = Str::slug($value);

        if ($slug === '') {
            $slug = substr(md5($value), 0, 12);
        }

        return trim($prefix.'-'.$slug, '-');
    }
}
