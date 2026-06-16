<?php

namespace Modules\AppBusinessProfiles\Support;

class BusinessTypeCatalog
{
    /**
     * Canonical type keys — these are the values stored in lb_businesses.type.
     */
    private const TYPES = [
        'Restaurant',
        'Coffee shop',
        'Bakery',
        'Bar / Pub',
        'Food truck',
        'Salon',
        'Barbershop',
        'Spa',
        'Nail studio',
        'Clinic',
        'Dentist',
        'Chiropractor',
        'Optical store',
        'Pharmacy',
        'Gym',
        'Yoga studio',
        'Fitness coach',
        'Local store',
        'Boutique',
        'Auto repair',
        'Car wash',
        'Real estate office',
        'Hotel',
        'Event venue',
        'Education center',
        'Pet grooming',
        'Agency client',
        'Professional service',
        'Other',
    ];

    /**
     * Group definitions: group_code => label translation key.
     */
    private static function groupDefs(): array
    {
        return [
            'food_beverage' => [
                'label' => __('Food & Beverage'),
                'icon'  => 'fa-utensils',
            ],
            'beauty_personal_care' => [
                'label' => __('Beauty & Personal Care'),
                'icon'  => 'fa-scissors',
            ],
            'retail_store' => [
                'label' => __('Retail & Local Stores'),
                'icon'  => 'fa-store',
            ],
            'local_repair_service' => [
                'label' => __('Local Services & Repair'),
                'icon'  => 'fa-wrench',
            ],
            'tourism_hospitality' => [
                'label' => __('Tourism, Hospitality & Events'),
                'icon'  => 'fa-hotel',
            ],
            'education_training' => [
                'label' => __('Education & Training'),
                'icon'  => 'fa-graduation-cap',
            ],
            'health_dental_fitness' => [
                'label' => __('Health, Dental & Fitness'),
                'icon'  => 'fa-heart-pulse',
            ],
            'b2b_professional_service' => [
                'label' => __('B2B, Real Estate & Professional Services'),
                'icon'  => 'fa-briefcase',
            ],
            'other' => [
                'label' => __('Other / Not Sure'),
                'icon'  => 'fa-circle-question',
            ],
        ];
    }

    /**
     * Full definition per type: label, group, icon.
     */
    private static function typeDefs(): array
    {
        return [
            'Restaurant' => [
                'label' => __('Restaurant / Eatery'),
                'group' => 'food_beverage',
                'icon'  => 'fa-utensils',
            ],
            'Coffee shop' => [
                'label' => __('Café / Milk Tea'),
                'group' => 'food_beverage',
                'icon'  => 'fa-mug-hot',
            ],
            'Bakery' => [
                'label' => __('Bakery / Pastry Shop'),
                'group' => 'food_beverage',
                'icon'  => 'fa-bread-slice',
            ],
            'Bar / Pub' => [
                'label' => __('Bar / Pub'),
                'group' => 'food_beverage',
                'icon'  => 'fa-beer-mug-empty',
            ],
            'Food truck' => [
                'label' => __('Food Truck / Street Food Stall'),
                'group' => 'food_beverage',
                'icon'  => 'fa-truck-fast',
            ],
            'Salon' => [
                'label' => __('Hair Salon'),
                'group' => 'beauty_personal_care',
                'icon'  => 'fa-scissors',
            ],
            'Barbershop' => [
                'label' => __('Barbershop'),
                'group' => 'beauty_personal_care',
                'icon'  => 'fa-user-hair-buns',
            ],
            'Spa' => [
                'label' => __('Spa / Massage / Beauty'),
                'group' => 'beauty_personal_care',
                'icon'  => 'fa-spa',
            ],
            'Nail studio' => [
                'label' => __('Nail Studio / Lash & Brow Bar'),
                'group' => 'beauty_personal_care',
                'icon'  => 'fa-hand-sparkles',
            ],
            'Clinic' => [
                'label' => __('Medical Clinic'),
                'group' => 'health_dental_fitness',
                'icon'  => 'fa-stethoscope',
            ],
            'Dentist' => [
                'label' => __('Dental Clinic'),
                'group' => 'health_dental_fitness',
                'icon'  => 'fa-tooth',
            ],
            'Chiropractor' => [
                'label' => __('Therapy & Rehabilitation'),
                'group' => 'health_dental_fitness',
                'icon'  => 'fa-person-walking',
            ],
            'Optical store' => [
                'label' => __('Optical Store'),
                'group' => 'health_dental_fitness',
                'icon'  => 'fa-glasses',
            ],
            'Pharmacy' => [
                'label' => __('Pharmacy'),
                'group' => 'health_dental_fitness',
                'icon'  => 'fa-pills',
            ],
            'Gym' => [
                'label' => __('Gym / Fitness Center'),
                'group' => 'health_dental_fitness',
                'icon'  => 'fa-dumbbell',
            ],
            'Yoga studio' => [
                'label' => __('Yoga / Pilates Studio'),
                'group' => 'health_dental_fitness',
                'icon'  => 'fa-person-yoga',
            ],
            'Fitness coach' => [
                'label' => __('Personal Fitness Trainer'),
                'group' => 'health_dental_fitness',
                'icon'  => 'fa-person-running',
            ],
            'Local store' => [
                'label' => __('Local Store / Grocery / Specialty'),
                'group' => 'retail_store',
                'icon'  => 'fa-store',
            ],
            'Boutique' => [
                'label' => __('Fashion Boutique & Accessories'),
                'group' => 'retail_store',
                'icon'  => 'fa-shirt',
            ],
            'Auto repair' => [
                'label' => __('Auto Repair & Motorbike Service'),
                'group' => 'local_repair_service',
                'icon'  => 'fa-car-wrench',
            ],
            'Car wash' => [
                'label' => __('Car Wash & Detailing'),
                'group' => 'local_repair_service',
                'icon'  => 'fa-car-burst',
            ],
            'Pet grooming' => [
                'label' => __('Pet Grooming & Care'),
                'group' => 'local_repair_service',
                'icon'  => 'fa-paw',
            ],
            'Hotel' => [
                'label' => __('Hotel / Accommodation / Homestay'),
                'group' => 'tourism_hospitality',
                'icon'  => 'fa-hotel',
            ],
            'Event venue' => [
                'label' => __('Event Venue / Wedding / Workshop'),
                'group' => 'tourism_hospitality',
                'icon'  => 'fa-champagne-glasses',
            ],
            'Education center' => [
                'label' => __('Education Center / Tutoring / Training'),
                'group' => 'education_training',
                'icon'  => 'fa-graduation-cap',
            ],
            'Real estate office' => [
                'label' => __('Real Estate / Property Rental / Brokerage'),
                'group' => 'b2b_professional_service',
                'icon'  => 'fa-building',
            ],
            'Agency client' => [
                'label' => __('Marketing Agency / Agency Client'),
                'group' => 'b2b_professional_service',
                'icon'  => 'fa-bullhorn',
            ],
            'Professional service' => [
                'label' => __('Accounting / Legal / Consulting / Professional Service'),
                'group' => 'b2b_professional_service',
                'icon'  => 'fa-scale-balanced',
            ],
            'Other' => [
                'label' => __('Other / Not Sure'),
                'group' => 'other',
                'icon'  => 'fa-circle-question',
            ],
        ];
    }

    /**
     * Returns the flat key-value list of canonical type => display label.
     * Replaces the old indexed array from ManagesBusinessForm.
     *
     * @return array<string, string>
     */
    public static function typeOptions(): array
    {
        $defs = self::typeDefs();
        $result = [];

        foreach (self::TYPES as $type) {
            $result[$type] = $defs[$type]['label'] ?? $type;
        }

        return $result;
    }

    /**
     * Returns grouped options for the enhanced UI picker.
     *
     * @return array<string, array{label: string, icon: string, options: array<string, array{label: string, icon: string}>}>
     */
    public static function groupedOptions(): array
    {
        $groups = self::groupDefs();
        $defs   = self::typeDefs();
        $result = [];

        foreach (self::TYPES as $type) {
            $def   = $defs[$type] ?? ['group' => 'other', 'label' => $type, 'icon' => 'fa-circle-question'];
            $group = $def['group'];

            if (! isset($result[$group])) {
                $result[$group] = [
                    'group'   => $group,
                    'label'   => $groups[$group]['label'] ?? $group,
                    'icon'    => $groups[$group]['icon'] ?? 'fa-circle-question',
                    'options' => [],
                ];
            }

            $result[$group]['options'][$type] = [
                'type'  => $type,
                'label' => $def['label'],
                'icon'  => $def['icon'],
            ];
        }

        return array_values($result);
    }

    /**
     * Returns 6 popular quick-pick types for the Da Nang / Quang Nam market.
     *
     * @return array<int, array{type: string, label: string, group: string, icon: string, description: string}>
     */
    public static function popularOptions(): array
    {
        $defs = self::typeDefs();

        $popular = [
            'Coffee shop' => __('Most popular in this region'),
            'Restaurant'  => __('QR, review & loyalty ready'),
            'Spa'         => __('Booking & reminder ready'),
            'Local store' => __('Coupon & loyalty ready'),
            'Hotel'       => __('Review & booking ready'),
            'Auto repair' => __('Lead & review ready'),
        ];

        $result = [];

        foreach ($popular as $type => $description) {
            $def = $defs[$type] ?? ['label' => $type, 'group' => 'other', 'icon' => 'fa-store'];

            $result[] = [
                'type'        => $type,
                'label'       => $def['label'],
                'group'       => $def['group'],
                'icon'        => $def['icon'],
                'description' => $description,
            ];
        }

        return $result;
    }

    /**
     * Returns runtime metadata for a given type.
     * Used for dashboard preset, template suggestions, campaign goals — not stored in DB.
     *
     * @return array{group: string, group_label: string, icon: string, dashboard_preset: string, template_pack: string, tags: array<string>, recommended_modules: array<string>, first_campaign_goals: array<string>}
     */
    public static function metadataFor(string $type): array
    {
        $def    = self::typeDefs()[$type] ?? null;
        $groups = self::groupDefs();
        $group  = $def['group'] ?? 'other';

        $presets = [
            'food_beverage' => [
                'dashboard_preset'    => 'dashboard_food_beverage',
                'template_pack'       => 'food_beverage_free_starter',
                'tags'                => ['qr', 'coupon', 'loyalty', 'review', 'feedback', 'retention'],
                'recommended_modules' => ['qr_campaigns', 'coupon_campaigns', 'review_booster', 'feedback_forms', 'loyalty_cards'],
                'first_campaign_goals' => ['coupon', 'review', 'feedback'],
            ],
            'beauty_personal_care' => [
                'dashboard_preset'    => 'dashboard_beauty',
                'template_pack'       => 'beauty_free_starter',
                'tags'                => ['booking', 'reminder', 'feedback', 'referral', 'loyalty'],
                'recommended_modules' => ['booking_pages', 'review_booster', 'feedback_forms', 'referral_campaigns'],
                'first_campaign_goals' => ['booking', 'review', 'referral'],
            ],
            'retail_store' => [
                'dashboard_preset'    => 'dashboard_retail',
                'template_pack'       => 'retail_free_starter',
                'tags'                => ['coupon', 'loyalty', 'qr', 'retention', 'review'],
                'recommended_modules' => ['coupon_campaigns', 'loyalty_cards', 'qr_campaigns', 'review_booster'],
                'first_campaign_goals' => ['coupon', 'loyalty', 'review'],
            ],
            'local_repair_service' => [
                'dashboard_preset'    => 'dashboard_local_service',
                'template_pack'       => 'local_service_free_starter',
                'tags'                => ['lead', 'booking', 'review', 'feedback'],
                'recommended_modules' => ['lead_forms', 'booking_pages', 'review_booster', 'feedback_forms'],
                'first_campaign_goals' => ['lead', 'booking', 'review'],
            ],
            'tourism_hospitality' => [
                'dashboard_preset'    => 'dashboard_tourism',
                'template_pack'       => 'tourism_free_starter',
                'tags'                => ['booking', 'review', 'referral', 'seasonal', 'qr'],
                'recommended_modules' => ['booking_pages', 'review_booster', 'referral_campaigns', 'qr_campaigns'],
                'first_campaign_goals' => ['booking', 'review', 'referral'],
            ],
            'education_training' => [
                'dashboard_preset'    => 'dashboard_education',
                'template_pack'       => 'education_free_starter',
                'tags'                => ['lead', 'consultation', 'trial', 'followup'],
                'recommended_modules' => ['lead_forms', 'booking_pages', 'feedback_forms'],
                'first_campaign_goals' => ['lead', 'booking', 'feedback'],
            ],
            'health_dental_fitness' => [
                'dashboard_preset'    => 'dashboard_health',
                'template_pack'       => 'health_free_starter',
                'tags'                => ['booking', 'reminder', 'loyalty', 'review', 'retention'],
                'recommended_modules' => ['booking_pages', 'review_booster', 'loyalty_cards', 'feedback_forms'],
                'first_campaign_goals' => ['booking', 'review', 'loyalty'],
            ],
            'b2b_professional_service' => [
                'dashboard_preset'    => 'dashboard_b2b',
                'template_pack'       => 'b2b_free_starter',
                'tags'                => ['lead', 'crm', 'consultation', 'pipeline'],
                'recommended_modules' => ['lead_forms', 'booking_pages', 'feedback_forms'],
                'first_campaign_goals' => ['lead', 'booking', 'feedback'],
            ],
            'other' => [
                'dashboard_preset'    => 'dashboard_general',
                'template_pack'       => 'general_free_starter',
                'tags'                => ['qr', 'review', 'feedback'],
                'recommended_modules' => ['qr_campaigns', 'review_booster', 'feedback_forms'],
                'first_campaign_goals' => ['review', 'feedback'],
            ],
        ];

        $preset = $presets[$group] ?? $presets['other'];

        return [
            'group'               => $group,
            'group_label'         => $groups[$group]['label'] ?? $group,
            'icon'                => $def['icon'] ?? 'fa-circle-question',
            'dashboard_preset'    => $preset['dashboard_preset'],
            'template_pack'       => $preset['template_pack'],
            'tags'                => $preset['tags'],
            'recommended_modules' => $preset['recommended_modules'],
            'first_campaign_goals' => $preset['first_campaign_goals'],
        ];
    }

    /**
     * Returns alias-to-type map for client-side / server-side search.
     * Keys are lowercase search terms; values are canonical type strings.
     *
     * @return array<string, string>
     */
    public static function searchAliases(): array
    {
        return [
            // Coffee shop
            'cafe'           => 'Coffee shop',
            'cà phê'         => 'Coffee shop',
            'ca phe'         => 'Coffee shop',
            'café'           => 'Coffee shop',
            'trà sữa'        => 'Coffee shop',
            'tra sua'        => 'Coffee shop',
            'milk tea'       => 'Coffee shop',
            'bubble tea'     => 'Coffee shop',
            'coffee'         => 'Coffee shop',
            'trà'            => 'Coffee shop',

            // Restaurant
            'quán ăn'        => 'Restaurant',
            'quan an'        => 'Restaurant',
            'nhà hàng'       => 'Restaurant',
            'nha hang'       => 'Restaurant',
            'hải sản'        => 'Restaurant',
            'hai san'        => 'Restaurant',
            'quán cơm'       => 'Restaurant',
            'quan com'       => 'Restaurant',
            'bún'            => 'Restaurant',
            'phở'            => 'Restaurant',
            'pho'            => 'Restaurant',

            // Bakery
            'bánh'           => 'Bakery',
            'banh'           => 'Bakery',
            'bakery'         => 'Bakery',
            'tiệm bánh'      => 'Bakery',
            'tiem banh'      => 'Bakery',
            'bánh ngọt'      => 'Bakery',

            // Bar / Pub
            'bar'            => 'Bar / Pub',
            'pub'            => 'Bar / Pub',
            'beer'           => 'Bar / Pub',
            'bia'            => 'Bar / Pub',
            'cocktail'       => 'Bar / Pub',

            // Food truck
            'xe đồ ăn'       => 'Food truck',
            'xe do an'       => 'Food truck',
            'food truck'     => 'Food truck',
            'quầy đồ ăn'     => 'Food truck',
            'quay do an'     => 'Food truck',

            // Spa
            'spa'            => 'Spa',
            'massage'        => 'Spa',
            'gội đầu'        => 'Spa',
            'goi dau'        => 'Spa',
            'chăm sóc da'    => 'Spa',
            'cham soc da'    => 'Spa',
            'wax'            => 'Spa',
            'facial'         => 'Spa',

            // Nail studio
            'nail'           => 'Nail studio',
            'mi'             => 'Nail studio',
            'mày'            => 'Nail studio',
            'may'            => 'Nail studio',
            'làm móng'       => 'Nail studio',
            'lam mong'       => 'Nail studio',
            'lông mày'       => 'Nail studio',
            'long may'       => 'Nail studio',

            // Salon / Barbershop
            'tóc'            => 'Salon',
            'toc'            => 'Salon',
            'salon'          => 'Salon',
            'cắt tóc'        => 'Salon',
            'cat toc'        => 'Salon',
            'nhuộm tóc'      => 'Salon',
            'nhom toc'       => 'Salon',
            'barber'         => 'Barbershop',
            'barbershop'     => 'Barbershop',
            'tóc nam'        => 'Barbershop',
            'toc nam'        => 'Barbershop',

            // Clinic
            'phòng khám'     => 'Clinic',
            'phong kham'     => 'Clinic',
            'clinic'         => 'Clinic',
            'bác sĩ'         => 'Clinic',
            'bac si'         => 'Clinic',

            // Dentist
            'nha khoa'       => 'Dentist',
            'răng'           => 'Dentist',
            'rang'           => 'Dentist',
            'dentist'        => 'Dentist',
            'dental'         => 'Dentist',

            // Optical store
            'kính'           => 'Optical store',
            'kinh'           => 'Optical store',
            'optical'        => 'Optical store',
            'kính thuốc'     => 'Optical store',
            'kinh thuoc'     => 'Optical store',
            'mắt'            => 'Optical store',

            // Pharmacy
            'nhà thuốc'      => 'Pharmacy',
            'nha thuoc'      => 'Pharmacy',
            'pharmacy'       => 'Pharmacy',
            'thuốc'          => 'Pharmacy',
            'thuoc'          => 'Pharmacy',
            'dược'           => 'Pharmacy',

            // Gym
            'gym'            => 'Gym',
            'fitness'        => 'Gym',
            'phòng tập'      => 'Gym',
            'phong tap'      => 'Gym',

            // Yoga studio
            'yoga'           => 'Yoga studio',
            'pilates'        => 'Yoga studio',

            // Fitness coach
            'huấn luyện viên' => 'Fitness coach',
            'huan luyen vien' => 'Fitness coach',
            'hlv'            => 'Fitness coach',
            'coach'          => 'Fitness coach',
            'pt'             => 'Fitness coach',

            // Local store
            'tạp hóa'        => 'Local store',
            'tap hoa'        => 'Local store',
            'cửa hàng'       => 'Local store',
            'cua hang'       => 'Local store',
            'đặc sản'        => 'Local store',
            'dac san'        => 'Local store',
            'ocop'           => 'Local store',
            'mini market'    => 'Local store',
            'convenience'    => 'Local store',

            // Boutique
            'thời trang'     => 'Boutique',
            'thoi trang'     => 'Boutique',
            'boutique'       => 'Boutique',
            'phụ kiện'       => 'Boutique',
            'phu kien'       => 'Boutique',
            'quần áo'        => 'Boutique',
            'quan ao'        => 'Boutique',

            // Auto repair
            'sửa xe'         => 'Auto repair',
            'sua xe'         => 'Auto repair',
            'auto repair'    => 'Auto repair',
            'garage'         => 'Auto repair',
            'ô tô'           => 'Auto repair',
            'o to'           => 'Auto repair',
            'xe máy'         => 'Auto repair',
            'xe may'         => 'Auto repair',

            // Car wash
            'rửa xe'         => 'Car wash',
            'rua xe'         => 'Car wash',
            'car wash'       => 'Car wash',
            'chăm sóc xe'    => 'Car wash',
            'cham soc xe'    => 'Car wash',
            'detailing'      => 'Car wash',

            // Hotel
            'khách sạn'      => 'Hotel',
            'khach san'      => 'Hotel',
            'homestay'       => 'Hotel',
            'villa'          => 'Hotel',
            'lưu trú'        => 'Hotel',
            'luu tru'        => 'Hotel',
            'resort'         => 'Hotel',
            'motel'          => 'Hotel',

            // Event venue
            'sự kiện'        => 'Event venue',
            'su kien'        => 'Event venue',
            'cưới hỏi'       => 'Event venue',
            'cuoi hoi'       => 'Event venue',
            'wedding'        => 'Event venue',
            'event'          => 'Event venue',
            'hội trường'     => 'Event venue',
            'hoi truong'     => 'Event venue',
            'workshop'       => 'Event venue',

            // Education center
            'giáo dục'       => 'Education center',
            'giao duc'       => 'Education center',
            'lớp học'        => 'Education center',
            'lop hoc'        => 'Education center',
            'trung tâm'      => 'Education center',
            'trung tam'      => 'Education center',
            'tiếng anh'      => 'Education center',
            'tieng anh'      => 'Education center',
            'học tiếng'      => 'Education center',
            'đào tạo'        => 'Education center',
            'dao tao'        => 'Education center',

            // Pet grooming
            'thú cưng'       => 'Pet grooming',
            'thu cung'       => 'Pet grooming',
            'pet'            => 'Pet grooming',
            'chó mèo'        => 'Pet grooming',
            'cho meo'        => 'Pet grooming',

            // Real estate office
            'bất động sản'   => 'Real estate office',
            'bat dong san'   => 'Real estate office',
            'cho thuê'       => 'Real estate office',
            'cho thue'       => 'Real estate office',
            'môi giới'       => 'Real estate office',
            'moi gioi'       => 'Real estate office',
            'real estate'    => 'Real estate office',
            'property'       => 'Real estate office',

            // Agency client
            'marketing'      => 'Agency client',
            'agency'         => 'Agency client',
            'media'          => 'Agency client',
            'truyền thông'   => 'Agency client',
            'truyen thong'   => 'Agency client',

            // Professional service
            'tư vấn'         => 'Professional service',
            'tu van'         => 'Professional service',
            'kế toán'        => 'Professional service',
            'ke toan'        => 'Professional service',
            'pháp lý'        => 'Professional service',
            'phap ly'        => 'Professional service',
            'luật'           => 'Professional service',
            'luat'           => 'Professional service',
            'dịch vụ chuyên môn' => 'Professional service',
            'dich vu chuyen mon' => 'Professional service',
            'dịch vụ địa phương' => 'Professional service',
            'dich vu dia phuong' => 'Professional service',

            // Other
            'khác'           => 'Other',
            'khac'           => 'Other',
            'chưa biết'      => 'Other',
            'chua biet'      => 'Other',
        ];
    }

    /**
     * Normalizes any type string (including old translated values or aliases)
     * to the canonical English type stored in lb_businesses.type.
     * Falls back to 'Other' if no match found.
     */
    public static function normalizeType(string $type): string
    {
        $type = trim($type);

        // Already a canonical type key.
        if (in_array($type, self::TYPES, true)) {
            return $type;
        }

        // Check against canonical type labels (translated display strings).
        foreach (self::typeDefs() as $key => $def) {
            if (strcasecmp($def['label'], $type) === 0) {
                return $key;
            }
        }

        // Check against old simple translated labels e.g. "Nhà hàng" => "Restaurant"
        foreach (self::TYPES as $canonical) {
            if (strcasecmp(__($canonical), $type) === 0) {
                return $canonical;
            }
        }

        // Check search aliases (case-insensitive).
        $lower   = mb_strtolower($type);
        $aliases = self::searchAliases();

        if (isset($aliases[$lower])) {
            return $aliases[$lower];
        }

        foreach ($aliases as $alias => $canonical) {
            if (mb_stripos($lower, $alias) !== false) {
                return $canonical;
            }
        }

        return 'Other';
    }
}
