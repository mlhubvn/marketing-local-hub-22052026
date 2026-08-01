<?php

namespace Modules\AppBusinessProfiles\Support;

/**
 * MLHUB industry taxonomy catalog (Alternative Data foundation).
 *
 * This class is the single source of truth for the business industry tree.
 * The DB only persists the selected codes + a snapshot (industry_metadata);
 * all labels, metadata, signals and legacy mapping are resolved from here at
 * runtime so the taxonomy can evolve without data migrations.
 *
 * Legacy compatibility: lb_businesses.type still stores the nearest old
 * canonical type so modules that read `type` keep working.
 */
class BusinessTypeCatalog
{
    /**
     * Bump when the taxonomy structure/codes change. Stored on each business
     * so we can tell which version classified it.
     */
    public const TAXONOMY_VERSION = '2026.06';

    /**
     * Fallback group/category used when nothing can be resolved.
     */
    public const FALLBACK_GROUP = 'other_needs_classification';

    public const FALLBACK_CATEGORY = 'other_not_sure';

    /**
     * Whitelisted alternative-data signal keys.
     */
    public const SIGNALS = [
        'qr_scan', 'review', 'lead_form', 'booking', 'coupon', 'loyalty',
        'referral', 'crm_activity', 'customer_profile', 'supplier_order',
        'delivery_log', 'inventory_light', 'payment_signal', 'invoice_signal',
        'location_density', 'seasonality', 'staff_capacity', 'price_band',
        'repeat_rate', 'risk_flag',
    ];

    /**
     * Priority groups shown first on onboarding (ordered).
     */
    public const PRIORITY_GROUPS = [
        'food_beverage',
        'retail_goods',
        'beauty_personal_care',
        'tourism_hospitality_experience',
        'health_dental_fitness',
        'technical_repair_maintenance',
        'education_training_coaching',
        'wholesale_distribution',
        'professional_b2b_services',
    ];

    /**
     * Canonical legacy type keys — values stored in lb_businesses.type.
     */
    private const LEGACY_TYPES = [
        'Restaurant', 'Coffee shop', 'Bakery', 'Bar / Pub', 'Food truck',
        'Salon', 'Barbershop', 'Spa', 'Nail studio', 'Clinic', 'Dentist',
        'Chiropractor', 'Optical store', 'Pharmacy', 'Gym', 'Yoga studio',
        'Fitness coach', 'Local store', 'Boutique', 'Auto repair', 'Car wash',
        'Real estate office', 'Hotel', 'Event venue', 'Education center',
        'Pet grooming', 'Agency client', 'Professional service', 'Other',
    ];

    /**
     * Group definitions in display order. Metadata is resolved here and only
     * snapshotted into the DB when a business is saved.
     *
     * @return array<string, array<string, mixed>>
     */
    private static function groupDefs(): array
    {
        return [
            'food_beverage' => [
                'label' => __('Food & Beverage'),
                'icon'  => 'fa-utensils',
                'meta'  => [
                    'recommended_modules'   => ['qr_campaigns', 'review_booster', 'coupon_campaigns', 'loyalty_cards', 'feedback_forms'],
                    'default_campaign_goals' => ['review', 'coupon', 'feedback', 'loyalty'],
                    'signals'               => ['qr_scan', 'review', 'coupon', 'loyalty', 'repeat_rate', 'seasonality'],
                    'dashboard_preset'      => 'dashboard_food_beverage',
                    'template_pack'         => 'food_beverage_starter_pack',
                    'compliance_sensitive'  => false,
                ],
            ],
            'retail_goods' => [
                'label' => __('Retail Goods'),
                'icon'  => 'fa-bag-shopping',
                'meta'  => [
                    'recommended_modules'   => ['coupon_campaigns', 'loyalty_cards', 'lead_forms', 'crm'],
                    'default_campaign_goals' => ['coupon', 'loyalty', 'lead', 'retention'],
                    'signals'               => ['coupon', 'loyalty', 'customer_profile', 'inventory_light', 'supplier_order', 'repeat_rate'],
                    'dashboard_preset'      => 'dashboard_retail_goods',
                    'template_pack'         => 'retail_goods_starter_pack',
                    'compliance_sensitive'  => false,
                ],
            ],
            'beauty_personal_care' => [
                'label' => __('Beauty & Personal Care'),
                'icon'  => 'fa-scissors',
                'meta'  => [
                    'recommended_modules'   => ['booking_pages', 'review_booster', 'coupon_campaigns', 'crm', 'email_automation'],
                    'default_campaign_goals' => ['booking', 'review', 'retention', 'referral'],
                    'signals'               => ['booking', 'review', 'coupon', 'crm_activity', 'repeat_rate'],
                    'dashboard_preset'      => 'dashboard_beauty_personal_care',
                    'template_pack'         => 'beauty_personal_care_starter_pack',
                    'compliance_sensitive'  => false,
                ],
            ],
            'tourism_hospitality_experience' => [
                'label' => __('Tourism, Hospitality & Experiences'),
                'icon'  => 'fa-umbrella-beach',
                'meta'  => [
                    'recommended_modules'   => ['booking_pages', 'lead_forms', 'review_booster', 'referral_campaigns', 'crm'],
                    'default_campaign_goals' => ['booking', 'review', 'lead', 'referral'],
                    'signals'               => ['booking', 'review', 'lead_form', 'seasonality', 'referral'],
                    'dashboard_preset'      => 'dashboard_tourism_hospitality',
                    'template_pack'         => 'tourism_hospitality_starter_pack',
                    'compliance_sensitive'  => false,
                ],
            ],
            'health_dental_fitness' => [
                'label' => __('Health, Dental & Fitness'),
                'icon'  => 'fa-heart-pulse',
                'meta'  => [
                    'recommended_modules'   => ['booking_pages', 'feedback_forms', 'review_booster', 'crm', 'email_automation'],
                    'default_campaign_goals' => ['booking', 'feedback', 'retention'],
                    'signals'               => ['booking', 'review', 'crm_activity', 'repeat_rate', 'risk_flag'],
                    'dashboard_preset'      => 'dashboard_health_dental_fitness',
                    'template_pack'         => 'health_compliance_safe_starter_pack',
                    'compliance_sensitive'  => true,
                    'avoid_medical_claims'  => true,
                ],
            ],
            'technical_repair_maintenance' => [
                'label' => __('Technical, Repair & Maintenance'),
                'icon'  => 'fa-screwdriver-wrench',
                'meta'  => [
                    'recommended_modules'   => ['lead_forms', 'booking_pages', 'review_booster', 'crm_tasks', 'email_automation'],
                    'default_campaign_goals' => ['lead', 'booking', 'review'],
                    'signals'               => ['lead_form', 'booking', 'crm_activity', 'review', 'repeat_rate'],
                    'dashboard_preset'      => 'dashboard_technical_repair',
                    'template_pack'         => 'technical_repair_starter_pack',
                    'compliance_sensitive'  => false,
                ],
            ],
            'education_training_coaching' => [
                'label' => __('Education, Training & Coaching'),
                'icon'  => 'fa-graduation-cap',
                'meta'  => [
                    'recommended_modules'   => ['lead_forms', 'booking_pages', 'crm_tasks', 'email_automation'],
                    'default_campaign_goals' => ['lead', 'booking', 'retention'],
                    'signals'               => ['lead_form', 'booking', 'crm_activity', 'customer_profile'],
                    'dashboard_preset'      => 'dashboard_education_training',
                    'template_pack'         => 'education_training_starter_pack',
                    'compliance_sensitive'  => false,
                ],
            ],
            'wholesale_distribution' => [
                'label' => __('Wholesale & Distribution'),
                'icon'  => 'fa-truck-ramp-box',
                'meta'  => [
                    'recommended_modules'   => ['lead_forms', 'crm', 'referral_campaigns', 'coupon_campaigns'],
                    'default_campaign_goals' => ['lead', 'referral', 'retention'],
                    'signals'               => ['supplier_order', 'delivery_log', 'location_density', 'inventory_light', 'payment_signal'],
                    'dashboard_preset'      => 'dashboard_wholesale_distribution',
                    'template_pack'         => 'wholesale_distribution_starter_pack',
                    'compliance_sensitive'  => false,
                ],
            ],
            'professional_b2b_services' => [
                'label' => __('Professional & B2B Services'),
                'icon'  => 'fa-briefcase',
                'meta'  => [
                    'recommended_modules'   => ['lead_forms', 'booking_pages', 'crm', 'crm_tasks', 'email_automation'],
                    'default_campaign_goals' => ['lead', 'booking', 'retention'],
                    'signals'               => ['lead_form', 'crm_activity', 'customer_profile', 'price_band'],
                    'dashboard_preset'      => 'dashboard_professional_b2b',
                    'template_pack'         => 'professional_b2b_starter_pack',
                    'compliance_sensitive'  => false,
                ],
            ],
            'home_construction_interior' => [
                'label' => __('Home, Construction & Interior'),
                'icon'  => 'fa-house-chimney',
                'meta'  => [
                    'recommended_modules'   => ['lead_forms', 'crm', 'review_booster', 'crm_tasks'],
                    'default_campaign_goals' => ['lead', 'review', 'retention'],
                    'signals'               => ['lead_form', 'crm_activity', 'review', 'price_band', 'seasonality'],
                    'dashboard_preset'      => 'dashboard_home_construction',
                    'template_pack'         => 'home_construction_starter_pack',
                    'compliance_sensitive'  => false,
                ],
            ],
            'transport_delivery_logistics' => [
                'label' => __('Transport, Delivery & Logistics'),
                'icon'  => 'fa-truck-fast',
                'meta'  => [
                    'recommended_modules'   => ['lead_forms', 'crm', 'crm_tasks'],
                    'default_campaign_goals' => ['lead', 'retention'],
                    'signals'               => ['lead_form', 'delivery_log', 'location_density', 'repeat_rate', 'payment_signal'],
                    'dashboard_preset'      => 'dashboard_transport_logistics',
                    'template_pack'         => 'transport_logistics_starter_pack',
                    'compliance_sensitive'  => false,
                ],
            ],
            'real_estate_rental_property' => [
                'label' => __('Real Estate, Rental & Property'),
                'icon'  => 'fa-building',
                'meta'  => [
                    'recommended_modules'   => ['lead_forms', 'crm', 'booking_pages', 'crm_tasks'],
                    'default_campaign_goals' => ['lead', 'booking', 'retention'],
                    'signals'               => ['lead_form', 'crm_activity', 'customer_profile', 'price_band', 'location_density'],
                    'dashboard_preset'      => 'dashboard_real_estate',
                    'template_pack'         => 'real_estate_starter_pack',
                    'compliance_sensitive'  => false,
                ],
            ],
            'digital_creator_online_business' => [
                'label' => __('Digital, Creator & Online Business'),
                'icon'  => 'fa-laptop-mobile',
                'meta'  => [
                    'recommended_modules'   => ['lead_forms', 'email_automation', 'referral_campaigns', 'crm'],
                    'default_campaign_goals' => ['lead', 'referral', 'retention'],
                    'signals'               => ['lead_form', 'customer_profile', 'referral', 'repeat_rate', 'payment_signal'],
                    'dashboard_preset'      => 'dashboard_digital_creator',
                    'template_pack'         => 'digital_creator_starter_pack',
                    'compliance_sensitive'  => false,
                ],
            ],
            'small_manufacturing_processing_ocop' => [
                'label' => __('Manufacturing, Processing & OCOP'),
                'icon'  => 'fa-industry',
                'meta'  => [
                    'recommended_modules'   => ['lead_forms', 'crm', 'referral_campaigns'],
                    'default_campaign_goals' => ['lead', 'referral', 'retention'],
                    'signals'               => ['supplier_order', 'delivery_log', 'inventory_light', 'invoice_signal', 'price_band'],
                    'dashboard_preset'      => 'dashboard_manufacturing_ocop',
                    'template_pack'         => 'manufacturing_ocop_starter_pack',
                    'compliance_sensitive'  => false,
                ],
            ],
            'agriculture_fisheries_local_supply' => [
                'label' => __('Agriculture, Fisheries & Local Supply'),
                'icon'  => 'fa-seedling',
                'meta'  => [
                    'recommended_modules'   => ['lead_forms', 'crm', 'referral_campaigns'],
                    'default_campaign_goals' => ['lead', 'referral', 'retention'],
                    'signals'               => ['supplier_order', 'delivery_log', 'seasonality', 'inventory_light', 'location_density'],
                    'dashboard_preset'      => 'dashboard_agriculture_supply',
                    'template_pack'         => 'agriculture_supply_starter_pack',
                    'compliance_sensitive'  => false,
                ],
            ],
            'culture_entertainment_sports_community' => [
                'label' => __('Culture, Entertainment & Sports'),
                'icon'  => 'fa-masks-theater',
                'meta'  => [
                    'recommended_modules'   => ['booking_pages', 'qr_campaigns', 'review_booster', 'loyalty_cards'],
                    'default_campaign_goals' => ['booking', 'review', 'loyalty'],
                    'signals'               => ['booking', 'qr_scan', 'review', 'seasonality', 'repeat_rate'],
                    'dashboard_preset'      => 'dashboard_culture_entertainment',
                    'template_pack'         => 'culture_entertainment_starter_pack',
                    'compliance_sensitive'  => false,
                ],
            ],
            'organization_association_public_community' => [
                'label' => __('Organizations, Associations & Public'),
                'icon'  => 'fa-people-group',
                'meta'  => [
                    'recommended_modules'   => ['lead_forms', 'email_automation', 'crm'],
                    'default_campaign_goals' => ['lead', 'retention'],
                    'signals'               => ['lead_form', 'crm_activity', 'customer_profile', 'location_density'],
                    'dashboard_preset'      => 'dashboard_organization_public',
                    'template_pack'         => 'organization_public_starter_pack',
                    'compliance_sensitive'  => false,
                ],
            ],
            'other_needs_classification' => [
                'label' => __('Other / Needs Classification'),
                'icon'  => 'fa-circle-question',
                'meta'  => [
                    'recommended_modules'   => ['qr_campaigns', 'review_booster', 'feedback_forms'],
                    'default_campaign_goals' => ['review', 'feedback'],
                    'signals'               => ['qr_scan', 'review', 'lead_form', 'customer_profile'],
                    'dashboard_preset'      => 'dashboard_general',
                    'template_pack'         => 'general_starter_pack',
                    'compliance_sensitive'  => false,
                ],
            ],
        ];
    }

    /**
     * Sub-category definitions grouped by parent group (display order matters).
     * Each entry: group, label, aliases, legacy type, optional compliance flag.
     *
     * @return array<string, array<string, mixed>>
     */
    private static function categoryDefs(): array
    {
        return [
            // 1. Food & Beverage
            'restaurant_eatery' => ['group' => 'food_beverage', 'label' => __('Restaurant / Eatery'), 'legacy' => 'Restaurant', 'aliases' => ['quán ăn', 'quan an', 'nhà hàng', 'nha hang', 'eatery', 'cơm', 'com', 'bún', 'phở', 'pho', 'restaurant']],
            'cafe_milk_tea' => ['group' => 'food_beverage', 'label' => __('Café / Milk Tea'), 'legacy' => 'Coffee shop', 'aliases' => ['cafe', 'cà phê', 'ca phe', 'trà sữa', 'tra sua', 'milk tea', 'bubble tea', 'coffee', 'trà', 'tra']],
            'bakery_pastry' => ['group' => 'food_beverage', 'label' => __('Bakery / Pastry'), 'legacy' => 'Bakery', 'aliases' => ['bánh', 'banh', 'bakery', 'tiệm bánh', 'tiem banh', 'bánh ngọt', 'pastry']],
            'street_food_kiosk' => ['group' => 'food_beverage', 'label' => __('Street Food / Food Cart / Kiosk'), 'legacy' => 'Food truck', 'aliases' => ['ăn vặt', 'an vat', 'xe đẩy', 'xe day', 'kiosk', 'street food', 'quầy đồ ăn']],
            'bar_pub_beer' => ['group' => 'food_beverage', 'label' => __('Pub / Bar / Beer Club'), 'legacy' => 'Bar / Pub', 'aliases' => ['bar', 'pub', 'beer', 'bia', 'nhậu', 'nhau', 'cocktail', 'beer club']],
            'cloud_kitchen_delivery' => ['group' => 'food_beverage', 'label' => __('Cloud Kitchen / Delivery-only'), 'legacy' => 'Food truck', 'aliases' => ['bếp online', 'bep online', 'cloud kitchen', 'grab food', 'shopee food', 'giao đồ ăn', 'delivery']],
            'catering_party_food' => ['group' => 'food_beverage', 'label' => __('Catering / Party Food'), 'legacy' => 'Restaurant', 'aliases' => ['nấu tiệc', 'nau tiec', 'catering', 'đặt tiệc', 'dat tiec', 'party food']],
            'seafood_local_restaurant' => ['group' => 'food_beverage', 'label' => __('Seafood / Local Specialty Restaurant'), 'legacy' => 'Restaurant', 'aliases' => ['hải sản', 'hai san', 'đặc sản', 'dac san', 'seafood', 'local specialty']],
            'juice_beverage_shop' => ['group' => 'food_beverage', 'label' => __('Juice / Smoothie / Beverage Shop'), 'legacy' => 'Coffee shop', 'aliases' => ['nước ép', 'nuoc ep', 'sinh tố', 'sinh to', 'juice', 'smoothie', 'beverage']],
            'dessert_snack_shop' => ['group' => 'food_beverage', 'label' => __('Dessert / Ice Cream / Snacks'), 'legacy' => 'Bakery', 'aliases' => ['chè', 'che', 'kem', 'đồ ngọt', 'do ngot', 'snack', 'dessert', 'ice cream']],

            // 2. Retail Goods
            'grocery_minimart' => ['group' => 'retail_goods', 'label' => __('Grocery / Minimart / Convenience Store'), 'legacy' => 'Local store', 'aliases' => ['tạp hóa', 'tap hoa', 'minimart', 'cửa hàng tiện lợi', 'cua hang tien loi', 'convenience', 'grocery']],
            'fmcg_local_store' => ['group' => 'retail_goods', 'label' => __('FMCG / Daily Goods Store'), 'legacy' => 'Local store', 'aliases' => ['tiêu dùng nhanh', 'tieu dung nhanh', 'fmcg', 'hàng tiêu dùng', 'daily goods']],
            'cosmetics_retail' => ['group' => 'retail_goods', 'label' => __('Cosmetics / Personal Care Retail'), 'legacy' => 'Local store', 'aliases' => ['mỹ phẩm', 'my pham', 'cosmetics', 'chăm sóc cá nhân', 'skincare retail']],
            'mother_baby_retail' => ['group' => 'retail_goods', 'label' => __('Mom & Baby Store'), 'legacy' => 'Local store', 'aliases' => ['mẹ và bé', 'me va be', 'mẹ bé', 'mom baby', 'đồ trẻ em', 'do tre em']],
            'fashion_accessories' => ['group' => 'retail_goods', 'label' => __('Fashion / Accessories'), 'legacy' => 'Boutique', 'aliases' => ['thời trang', 'thoi trang', 'phụ kiện', 'phu kien', 'quần áo', 'quan ao', 'fashion', 'boutique']],
            'electronics_mobile_accessories' => ['group' => 'retail_goods', 'label' => __('Phones / Electronics / Accessories'), 'legacy' => 'Local store', 'aliases' => ['điện thoại', 'dien thoai', 'phụ kiện', 'điện máy', 'dien may', 'electronics', 'mobile']],
            'homeware_household' => ['group' => 'retail_goods', 'label' => __('Homeware / Household Goods'), 'legacy' => 'Local store', 'aliases' => ['gia dụng', 'gia dung', 'đồ nhà bếp', 'do nha bep', 'homeware', 'household']],
            'pharmacy_retail' => ['group' => 'retail_goods', 'label' => __('Pharmacy (Retail)'), 'legacy' => 'Pharmacy', 'compliance' => true, 'aliases' => ['nhà thuốc', 'nha thuoc', 'pharmacy', 'thuốc', 'thuoc', 'dược', 'duoc']],
            'optical_retail' => ['group' => 'retail_goods', 'label' => __('Optical / Eyewear Store'), 'legacy' => 'Optical store', 'aliases' => ['kính', 'kinh', 'kính thuốc', 'kinh thuoc', 'mắt kính', 'mat kinh', 'optical', 'eyewear']],
            'souvenir_ocop_gifts' => ['group' => 'retail_goods', 'label' => __('Souvenirs / OCOP / Gifts'), 'legacy' => 'Local store', 'aliases' => ['đặc sản', 'dac san', 'ocop', 'quà tặng', 'qua tang', 'lưu niệm', 'luu niem', 'souvenir', 'gifts']],
            'books_stationery_toys' => ['group' => 'retail_goods', 'label' => __('Books / Stationery / Toys'), 'legacy' => 'Local store', 'aliases' => ['sách', 'sach', 'văn phòng phẩm', 'van phong pham', 'đồ chơi', 'do choi', 'books', 'stationery', 'toys']],
            'pet_shop' => ['group' => 'retail_goods', 'label' => __('Pet Shop'), 'legacy' => 'Pet grooming', 'aliases' => ['thú cưng', 'thu cung', 'pet shop', 'chó mèo', 'cho meo', 'pet']],
            'hardware_electrical_water' => ['group' => 'retail_goods', 'label' => __('Hardware / Electrical & Water Supplies'), 'legacy' => 'Local store', 'aliases' => ['điện nước', 'dien nuoc', 'kim khí', 'kim khi', 'vật tư', 'vat tu', 'hardware']],
            'agri_food_specialty_retail' => ['group' => 'retail_goods', 'label' => __('Fresh / Organic / Regional Specialty Foods'), 'legacy' => 'Local store', 'aliases' => ['nông sản', 'nong san', 'thực phẩm sạch', 'thuc pham sach', 'đặc sản', 'organic food']],

            // 3. Beauty & Personal Care
            'hair_salon' => ['group' => 'beauty_personal_care', 'label' => __('Hair Salon'), 'legacy' => 'Salon', 'aliases' => ['tóc', 'toc', 'salon', 'cắt tóc', 'cat toc', 'nhuộm tóc', 'hair']],
            'barbershop' => ['group' => 'beauty_personal_care', 'label' => __('Barbershop'), 'legacy' => 'Barbershop', 'aliases' => ['barber', 'barbershop', 'tóc nam', 'toc nam', 'cắt tóc nam']],
            'spa_massage' => ['group' => 'beauty_personal_care', 'label' => __('Spa / Massage / Wellness'), 'legacy' => 'Spa', 'aliases' => ['spa', 'massage', 'gội đầu', 'goi dau', 'dưỡng sinh', 'duong sinh', 'wellness']],
            'nail_lash_brow' => ['group' => 'beauty_personal_care', 'label' => __('Nail / Lash / Brow Studio'), 'legacy' => 'Nail studio', 'aliases' => ['nail', 'mi', 'mày', 'may', 'làm móng', 'lam mong', 'lông mày', 'long may', 'phun xăm', 'lash', 'brow']],
            'skin_care_facial' => ['group' => 'beauty_personal_care', 'label' => __('Skin Care / Facial'), 'legacy' => 'Spa', 'aliases' => ['chăm sóc da', 'cham soc da', 'facial', 'skin care', 'da']],
            'makeup_bridal' => ['group' => 'beauty_personal_care', 'label' => __('Makeup / Bridal'), 'legacy' => 'Salon', 'aliases' => ['trang điểm', 'trang diem', 'makeup', 'make up', 'bridal', 'cô dâu', 'co dau']],
            'aesthetic_beauty_service' => ['group' => 'beauty_personal_care', 'label' => __('Non-surgical Aesthetic Service'), 'legacy' => 'Spa', 'aliases' => ['thẩm mỹ', 'tham my', 'aesthetic', 'làm đẹp', 'lam dep']],
            'wellness_non_medical' => ['group' => 'beauty_personal_care', 'label' => __('Wellness / Relaxation (Non-medical)'), 'legacy' => 'Spa', 'aliases' => ['wellness', 'thư giãn', 'thu gian', 'relax', 'trị liệu thư giãn']],
            'beauty_training_studio' => ['group' => 'beauty_personal_care', 'label' => __('Beauty Training Studio'), 'legacy' => 'Salon', 'aliases' => ['đào tạo làm đẹp', 'dao tao lam dep', 'beauty academy', 'dạy nghề làm đẹp']],
            'beauty_product_service_combo' => ['group' => 'beauty_personal_care', 'label' => __('Beauty Products + Service Combo'), 'legacy' => 'Spa', 'aliases' => ['combo làm đẹp', 'mỹ phẩm dịch vụ', 'beauty combo']],

            // 4. Tourism, Hospitality & Experiences
            'hotel_accommodation' => ['group' => 'tourism_hospitality_experience', 'label' => __('Hotel / Accommodation'), 'legacy' => 'Hotel', 'aliases' => ['khách sạn', 'khach san', 'nhà nghỉ', 'nha nghi', 'hotel', 'motel']],
            'homestay_villa_apartment' => ['group' => 'tourism_hospitality_experience', 'label' => __('Homestay / Villa / Holiday Apartment'), 'legacy' => 'Hotel', 'aliases' => ['homestay', 'villa', 'căn hộ du lịch', 'can ho du lich', 'holiday apartment']],
            'serviced_apartment' => ['group' => 'tourism_hospitality_experience', 'label' => __('Serviced Apartment'), 'legacy' => 'Hotel', 'aliases' => ['căn hộ dịch vụ', 'can ho dich vu', 'serviced apartment', 'apartment']],
            'travel_agency_tour' => ['group' => 'tourism_hospitality_experience', 'label' => __('Travel Agency / Tour Operator'), 'legacy' => 'Other', 'aliases' => ['tour', 'lữ hành', 'lu hanh', 'travel agency', 'du lịch', 'du lich']],
            'local_experience_tour' => ['group' => 'tourism_hospitality_experience', 'label' => __('Local Experience / Activities'), 'legacy' => 'Other', 'aliases' => ['trải nghiệm', 'trai nghiem', 'experience', 'hoạt động địa phương']],
            'vehicle_rental_tourism' => ['group' => 'tourism_hospitality_experience', 'label' => __('Vehicle Rental / Tourist Transport'), 'legacy' => 'Other', 'aliases' => ['thuê xe', 'thue xe', 'xe du lịch', 'xe du lich', 'vehicle rental', 'car rental']],
            'event_venue' => ['group' => 'tourism_hospitality_experience', 'label' => __('Event Venue'), 'legacy' => 'Event venue', 'aliases' => ['sự kiện', 'su kien', 'hội trường', 'hoi truong', 'event venue']],
            'wedding_event_service' => ['group' => 'tourism_hospitality_experience', 'label' => __('Wedding / Event Service'), 'legacy' => 'Event venue', 'aliases' => ['cưới hỏi', 'cuoi hoi', 'wedding', 'workshop', 'event service']],
            'tour_guide_photo_service' => ['group' => 'tourism_hospitality_experience', 'label' => __('Tour Guide / Travel Photography'), 'legacy' => 'Other', 'aliases' => ['hướng dẫn viên', 'huong dan vien', 'chụp ảnh du lịch', 'tour guide', 'photography']],
            'ticketing_attraction_service' => ['group' => 'tourism_hospitality_experience', 'label' => __('Tickets / Attractions'), 'legacy' => 'Other', 'aliases' => ['vé tham quan', 've tham quan', 'attraction', 'điểm vui chơi', 'ticket']],

            // 5. Health, Dental & Fitness (compliance sensitive group)
            'medical_clinic' => ['group' => 'health_dental_fitness', 'label' => __('Medical Clinic'), 'legacy' => 'Clinic', 'aliases' => ['phòng khám', 'phong kham', 'clinic', 'bác sĩ', 'bac si']],
            'dental_clinic' => ['group' => 'health_dental_fitness', 'label' => __('Dental Clinic'), 'legacy' => 'Dentist', 'aliases' => ['nha khoa', 'răng', 'rang', 'dentist', 'dental']],
            'therapy_rehabilitation' => ['group' => 'health_dental_fitness', 'label' => __('Therapy / Rehabilitation'), 'legacy' => 'Chiropractor', 'aliases' => ['trị liệu', 'tri lieu', 'phục hồi', 'phuc hoi', 'rehabilitation', 'vật lý trị liệu', 'physiotherapy']],
            'traditional_medicine_clinic' => ['group' => 'health_dental_fitness', 'label' => __('Traditional Medicine / Health Care'), 'legacy' => 'Clinic', 'aliases' => ['y học cổ truyền', 'y hoc co truyen', 'đông y', 'dong y', 'traditional medicine']],
            'pharmacy_health_retail' => ['group' => 'health_dental_fitness', 'label' => __('Pharmacy / Health Products'), 'legacy' => 'Pharmacy', 'aliases' => ['nhà thuốc', 'nha thuoc', 'pharmacy', 'thực phẩm chức năng', 'health products']],
            'optical_eye_care' => ['group' => 'health_dental_fitness', 'label' => __('Optical / Eye Care'), 'legacy' => 'Optical store', 'aliases' => ['kính thuốc', 'kinh thuoc', 'chăm sóc mắt', 'cham soc mat', 'optical', 'eye care']],
            'gym_fitness_center' => ['group' => 'health_dental_fitness', 'label' => __('Gym / Fitness Center'), 'legacy' => 'Gym', 'aliases' => ['gym', 'fitness', 'phòng tập', 'phong tap']],
            'yoga_pilates_studio' => ['group' => 'health_dental_fitness', 'label' => __('Yoga / Pilates Studio'), 'legacy' => 'Yoga studio', 'aliases' => ['yoga', 'pilates']],
            'personal_trainer' => ['group' => 'health_dental_fitness', 'label' => __('Personal Trainer'), 'legacy' => 'Fitness coach', 'aliases' => ['huấn luyện viên', 'huan luyen vien', 'hlv', 'personal trainer', 'pt']],
            'nutrition_wellness_coach' => ['group' => 'health_dental_fitness', 'label' => __('Nutrition / Wellness Coach'), 'legacy' => 'Fitness coach', 'compliance' => true, 'aliases' => ['dinh dưỡng', 'dinh duong', 'nutrition', 'wellness coach']],
            'elderly_care_service' => ['group' => 'health_dental_fitness', 'label' => __('Elderly Care Service'), 'legacy' => 'Clinic', 'aliases' => ['chăm sóc người già', 'người cao tuổi', 'nguoi cao tuoi', 'elderly care']],
            'health_membership_service' => ['group' => 'health_dental_fitness', 'label' => __('Health Membership / Care Plans'), 'legacy' => 'Gym', 'aliases' => ['hội viên sức khỏe', 'hoi vien suc khoe', 'gói chăm sóc', 'health membership']],

            // 6. Technical, Repair & Maintenance
            'auto_motorbike_repair' => ['group' => 'technical_repair_maintenance', 'label' => __('Auto / Motorbike Repair'), 'legacy' => 'Auto repair', 'aliases' => ['sửa xe', 'sua xe', 'ô tô', 'o to', 'xe máy', 'xe may', 'garage', 'auto repair', 'motorbike']],
            'car_wash_detailing' => ['group' => 'technical_repair_maintenance', 'label' => __('Car Wash / Detailing'), 'legacy' => 'Car wash', 'aliases' => ['rửa xe', 'rua xe', 'car wash', 'detailing', 'chăm sóc xe']],
            'electronics_repair' => ['group' => 'technical_repair_maintenance', 'label' => __('Electronics / Device Repair'), 'legacy' => 'Auto repair', 'aliases' => ['sửa điện tử', 'sua dien tu', 'electronics repair', 'thiết bị']],
            'phone_computer_repair' => ['group' => 'technical_repair_maintenance', 'label' => __('Phone / Computer Repair'), 'legacy' => 'Auto repair', 'aliases' => ['sửa điện thoại', 'sua dien thoai', 'máy tính', 'may tinh', 'phone repair', 'computer repair']],
            'appliance_ac_repair' => ['group' => 'technical_repair_maintenance', 'label' => __('Appliance / AC Repair'), 'legacy' => 'Auto repair', 'aliases' => ['điện lạnh', 'dien lanh', 'máy lạnh', 'may lanh', 'máy giặt', 'ac repair', 'appliance']],
            'plumbing_electrical_service' => ['group' => 'technical_repair_maintenance', 'label' => __('Plumbing / Electrical Service'), 'legacy' => 'Auto repair', 'aliases' => ['điện nước', 'dien nuoc', 'plumbing', 'electrical', 'thợ điện', 'tho dien']],
            'locksmith_home_security' => ['group' => 'technical_repair_maintenance', 'label' => __('Locksmith / Home Security'), 'legacy' => 'Auto repair', 'aliases' => ['khóa', 'khoa', 'locksmith', 'an ninh', 'camera', 'security']],
            'laundry_dry_cleaning' => ['group' => 'technical_repair_maintenance', 'label' => __('Laundry / Dry Cleaning'), 'legacy' => 'Other', 'aliases' => ['giặt ủi', 'giat ui', 'laundry', 'dry cleaning', 'giặt là']],
            'pet_grooming_care' => ['group' => 'technical_repair_maintenance', 'label' => __('Pet Grooming / Care'), 'legacy' => 'Pet grooming', 'aliases' => ['thú cưng', 'thu cung', 'pet grooming', 'spa thú cưng', 'chó mèo']],
            'cleaning_maid_service' => ['group' => 'technical_repair_maintenance', 'label' => __('Cleaning / Housekeeping'), 'legacy' => 'Other', 'aliases' => ['vệ sinh', 've sinh', 'dọn nhà', 'don nha', 'giúp việc', 'giup viec', 'cleaning', 'maid']],
            'printing_photocopy' => ['group' => 'technical_repair_maintenance', 'label' => __('Printing / Photocopy'), 'legacy' => 'Other', 'aliases' => ['in ấn', 'in an', 'photocopy', 'printing', 'copy']],
            'maintenance_contract_service' => ['group' => 'technical_repair_maintenance', 'label' => __('Scheduled Maintenance Service'), 'legacy' => 'Other', 'aliases' => ['bảo trì', 'bao tri', 'maintenance', 'định kỳ', 'dinh ky']],

            // 7. Education, Training & Coaching
            'language_center' => ['group' => 'education_training_coaching', 'label' => __('Language Center'), 'legacy' => 'Education center', 'aliases' => ['ngoại ngữ', 'ngoai ngu', 'tiếng anh', 'tieng anh', 'language center', 'ielts']],
            'tutoring_private_class' => ['group' => 'education_training_coaching', 'label' => __('Tutoring / Private Class'), 'legacy' => 'Education center', 'aliases' => ['gia sư', 'gia su', 'dạy thêm', 'day them', 'tutoring', 'lớp học']],
            'skill_training_center' => ['group' => 'education_training_coaching', 'label' => __('Skills / Personal Development'), 'legacy' => 'Education center', 'aliases' => ['kỹ năng', 'ky nang', 'phát triển cá nhân', 'skills', 'soft skills']],
            'art_music_class' => ['group' => 'education_training_coaching', 'label' => __('Art / Music / Drawing Class'), 'legacy' => 'Education center', 'aliases' => ['nghệ thuật', 'nghe thuat', 'âm nhạc', 'am nhac', 'vẽ', 've', 'art', 'music', 'piano', 'guitar']],
            'vocational_training' => ['group' => 'education_training_coaching', 'label' => __('Vocational Training'), 'legacy' => 'Education center', 'aliases' => ['đào tạo nghề', 'dao tao nghe', 'vocational', 'dạy nghề']],
            'test_prep_center' => ['group' => 'education_training_coaching', 'label' => __('Test Prep / Certification'), 'legacy' => 'Education center', 'aliases' => ['luyện thi', 'luyen thi', 'chứng chỉ', 'chung chi', 'test prep', 'certification']],
            'online_course_creator' => ['group' => 'education_training_coaching', 'label' => __('Online Course'), 'legacy' => 'Education center', 'aliases' => ['khóa học online', 'khoa hoc online', 'online course', 'e-learning']],
            'coaching_mentoring' => ['group' => 'education_training_coaching', 'label' => __('Coaching / Mentoring'), 'legacy' => 'Education center', 'aliases' => ['coaching', 'mentoring', 'cố vấn', 'co van']],
            'education_consulting' => ['group' => 'education_training_coaching', 'label' => __('Study Abroad / Education Consulting'), 'legacy' => 'Education center', 'aliases' => ['du học', 'du hoc', 'tư vấn giáo dục', 'study abroad', 'education consulting']],
            'kids_activity_center' => ['group' => 'education_training_coaching', 'label' => __('Kids Skills / Activity Center'), 'legacy' => 'Education center', 'aliases' => ['trẻ em', 'tre em', 'kids', 'hoạt động trẻ em', 'kỹ năng trẻ']],
            'corporate_training' => ['group' => 'education_training_coaching', 'label' => __('Corporate Training'), 'legacy' => 'Education center', 'aliases' => ['đào tạo doanh nghiệp', 'dao tao doanh nghiep', 'corporate training', 'in-house']],
            'edtech_micro_school' => ['group' => 'education_training_coaching', 'label' => __('EdTech / Micro School'), 'legacy' => 'Education center', 'aliases' => ['edtech', 'lớp học số', 'micro school', 'công nghệ giáo dục']],

            // 8. Wholesale & Distribution
            'fmcg_distributor' => ['group' => 'wholesale_distribution', 'label' => __('FMCG Distributor'), 'legacy' => 'Other', 'aliases' => ['nhà phân phối', 'nha phan phoi', 'fmcg', 'phân phối', 'phan phoi', 'distributor']],
            'food_beverage_wholesale' => ['group' => 'wholesale_distribution', 'label' => __('Food & Beverage Wholesale'), 'legacy' => 'Other', 'aliases' => ['bán buôn', 'ban buon', 'sỉ', 'si', 'thực phẩm', 'food wholesale']],
            'cosmetics_mother_baby_wholesale' => ['group' => 'wholesale_distribution', 'label' => __('Cosmetics / Mom & Baby Wholesale'), 'legacy' => 'Other', 'aliases' => ['sỉ mỹ phẩm', 'si my pham', 'mẹ và bé', 'cosmetics wholesale']],
            'pharma_medical_wholesale' => ['group' => 'wholesale_distribution', 'label' => __('Pharma / Medical Supplies Wholesale'), 'legacy' => 'Other', 'compliance' => true, 'aliases' => ['dược phẩm', 'duoc pham', 'thiết bị y tế', 'thiet bi y te', 'pharma wholesale']],
            'construction_material_wholesale' => ['group' => 'wholesale_distribution', 'label' => __('Construction Materials Wholesale'), 'legacy' => 'Other', 'aliases' => ['vật liệu xây dựng', 'vat lieu xay dung', 'vlxd', 'construction materials']],
            'electronics_appliance_wholesale' => ['group' => 'wholesale_distribution', 'label' => __('Electronics / Appliance Wholesale'), 'legacy' => 'Other', 'aliases' => ['điện máy', 'dien may', 'đồ gia dụng', 'electronics wholesale']],
            'agri_food_supply' => ['group' => 'wholesale_distribution', 'label' => __('Agricultural / Food Supply (Bulk)'), 'legacy' => 'Other', 'aliases' => ['nông sản', 'nong san', 'đầu mối', 'dau moi', 'agri supply', 'bulk food']],
            'ocop_specialty_distribution' => ['group' => 'wholesale_distribution', 'label' => __('OCOP / Specialty Distribution'), 'legacy' => 'Other', 'aliases' => ['ocop', 'đặc sản', 'dac san', 'phân phối đặc sản']],
            'dealer_agent_network' => ['group' => 'wholesale_distribution', 'label' => __('Dealer / Agent Network'), 'legacy' => 'Other', 'aliases' => ['đại lý', 'dai ly', 'điểm bán', 'diem ban', 'dealer', 'agent']],
            'b2b_trade_supply' => ['group' => 'wholesale_distribution', 'label' => __('B2B Goods Supply'), 'legacy' => 'Other', 'aliases' => ['b2b', 'cung ứng', 'cung ung', 'trade supply']],

            // 9. Professional & B2B Services
            'accounting_tax_service' => ['group' => 'professional_b2b_services', 'label' => __('Accounting / Tax Service'), 'legacy' => 'Professional service', 'aliases' => ['kế toán', 'ke toan', 'thuế', 'thue', 'accounting', 'tax']],
            'legal_consulting' => ['group' => 'professional_b2b_services', 'label' => __('Legal / Law Consulting'), 'legacy' => 'Professional service', 'aliases' => ['pháp lý', 'phap ly', 'luật', 'luat', 'legal', 'law']],
            'marketing_agency' => ['group' => 'professional_b2b_services', 'label' => __('Marketing Agency'), 'legacy' => 'Agency client', 'aliases' => ['marketing', 'agency', 'truyền thông', 'truyen thong', 'quảng cáo', 'quang cao']],
            'design_branding_service' => ['group' => 'professional_b2b_services', 'label' => __('Design / Branding Service'), 'legacy' => 'Professional service', 'aliases' => ['thiết kế', 'thiet ke', 'branding', 'nhận diện', 'design']],
            'photo_video_production' => ['group' => 'professional_b2b_services', 'label' => __('Photo / Video Production'), 'legacy' => 'Professional service', 'aliases' => ['chụp ảnh', 'chup anh', 'quay phim', 'video', 'media', 'production']],
            'it_software_web_service' => ['group' => 'professional_b2b_services', 'label' => __('IT / Software / Web Service'), 'legacy' => 'Professional service', 'aliases' => ['it', 'phần mềm', 'phan mem', 'website', 'software', 'web']],
            'hr_recruitment_service' => ['group' => 'professional_b2b_services', 'label' => __('HR / Recruitment Service'), 'legacy' => 'Professional service', 'aliases' => ['nhân sự', 'nhan su', 'tuyển dụng', 'tuyen dung', 'hr', 'recruitment']],
            'business_consulting' => ['group' => 'professional_b2b_services', 'label' => __('Business Consulting'), 'legacy' => 'Professional service', 'aliases' => ['tư vấn', 'tu van', 'kinh doanh', 'business consulting']],
            'architecture_engineering_service' => ['group' => 'professional_b2b_services', 'label' => __('Architecture / Engineering Service'), 'legacy' => 'Professional service', 'aliases' => ['kiến trúc', 'kien truc', 'kỹ thuật', 'ky thuat', 'công trình', 'architecture', 'engineering']],
            'insurance_agent_service' => ['group' => 'professional_b2b_services', 'label' => __('Insurance Agent'), 'legacy' => 'Professional service', 'aliases' => ['bảo hiểm', 'bao hiem', 'insurance', 'đại lý bảo hiểm']],
            'financial_advisory_service' => ['group' => 'professional_b2b_services', 'label' => __('Financial Advisory'), 'legacy' => 'Professional service', 'aliases' => ['tài chính', 'tai chinh', 'financial', 'đầu tư', 'dau tu', 'advisory']],
            'agency_client_business' => ['group' => 'professional_b2b_services', 'label' => __('Agency Client / Service Business'), 'legacy' => 'Agency client', 'aliases' => ['khách hàng agency', 'doanh nghiệp dịch vụ', 'agency client']],

            // 10. Home, Construction & Interior
            'small_contractor' => ['group' => 'home_construction_interior', 'label' => __('Small Contractor / Civil Works'), 'legacy' => 'Other', 'aliases' => ['nhà thầu', 'nha thau', 'thi công', 'thi cong', 'contractor', 'xây dựng', 'xay dung']],
            'renovation_painting' => ['group' => 'home_construction_interior', 'label' => __('Renovation / Painting'), 'legacy' => 'Other', 'aliases' => ['sửa nhà', 'sua nha', 'sơn', 'son', 'cải tạo', 'cai tao', 'renovation', 'painting']],
            'interior_design_fitout' => ['group' => 'home_construction_interior', 'label' => __('Interior Design / Fit-out'), 'legacy' => 'Other', 'aliases' => ['nội thất', 'noi that', 'thiết kế nội thất', 'interior design', 'fit-out']],
            'furniture_store_workshop' => ['group' => 'home_construction_interior', 'label' => __('Furniture Store / Workshop'), 'legacy' => 'Other', 'aliases' => ['đồ gỗ', 'do go', 'nội thất', 'furniture', 'xưởng gỗ']],
            'aluminum_glass_iron' => ['group' => 'home_construction_interior', 'label' => __('Aluminum / Glass / Iron / Doors'), 'legacy' => 'Other', 'aliases' => ['nhôm kính', 'nhom kinh', 'sắt', 'sat', 'cửa', 'cua', 'aluminum', 'glass']],
            'electrical_water_materials' => ['group' => 'home_construction_interior', 'label' => __('Electrical & Water Materials'), 'legacy' => 'Other', 'aliases' => ['vật tư điện nước', 'vat tu dien nuoc', 'electrical materials']],
            'construction_material_retail' => ['group' => 'home_construction_interior', 'label' => __('Construction Materials Retail'), 'legacy' => 'Local store', 'aliases' => ['vlxd', 'vật liệu xây dựng', 'construction materials retail']],
            'landscape_garden' => ['group' => 'home_construction_interior', 'label' => __('Landscaping / Garden'), 'legacy' => 'Other', 'aliases' => ['cây xanh', 'cay xanh', 'sân vườn', 'san vuon', 'landscape', 'garden']],
            'solar_equipment_install' => ['group' => 'home_construction_interior', 'label' => __('Energy Equipment / Solar Install'), 'legacy' => 'Other', 'aliases' => ['năng lượng', 'nang luong', 'điện mặt trời', 'dien mat troi', 'solar', 'lắp đặt']],
            'architecture_engineering_consulting' => ['group' => 'home_construction_interior', 'label' => __('Architecture / Engineering Consulting'), 'legacy' => 'Professional service', 'aliases' => ['tư vấn thiết kế', 'tu van thiet ke', 'kỹ thuật công trình', 'architecture consulting']],

            // 11. Transport, Delivery & Logistics
            'taxi_private_car' => ['group' => 'transport_delivery_logistics', 'label' => __('Taxi / Private Car Service'), 'legacy' => 'Other', 'aliases' => ['taxi', 'xe hợp đồng', 'xe hop dong', 'private car', 'xe dịch vụ']],
            'tourist_shuttle_transport' => ['group' => 'transport_delivery_logistics', 'label' => __('Tourist Shuttle / Transport'), 'legacy' => 'Other', 'aliases' => ['xe du lịch', 'xe du lich', 'xe đưa đón', 'dua don', 'shuttle']],
            'motorbike_delivery' => ['group' => 'transport_delivery_logistics', 'label' => __('Motorbike Delivery'), 'legacy' => 'Other', 'aliases' => ['giao hàng', 'giao hang', 'ship', 'shipper', 'delivery xe máy']],
            'freight_trucking' => ['group' => 'transport_delivery_logistics', 'label' => __('Freight / Trucking'), 'legacy' => 'Other', 'aliases' => ['vận tải', 'van tai', 'xe tải', 'xe tai', 'freight', 'trucking']],
            'warehouse_storage' => ['group' => 'transport_delivery_logistics', 'label' => __('Warehouse / Storage'), 'legacy' => 'Other', 'aliases' => ['kho bãi', 'kho bai', 'lưu kho', 'luu kho', 'warehouse', 'storage']],
            'courier_postal_service' => ['group' => 'transport_delivery_logistics', 'label' => __('Courier / Postal Service'), 'legacy' => 'Other', 'aliases' => ['chuyển phát', 'chuyen phat', 'bưu chính', 'buu chinh', 'courier', 'postal']],
            'cold_chain_delivery' => ['group' => 'transport_delivery_logistics', 'label' => __('Cold Chain / Food Delivery'), 'legacy' => 'Other', 'aliases' => ['giao hàng lạnh', 'giao hang lanh', 'cold chain', 'hàng lạnh']],
            'moving_service' => ['group' => 'transport_delivery_logistics', 'label' => __('Moving Service'), 'legacy' => 'Other', 'aliases' => ['chuyển nhà', 'chuyen nha', 'chuyển văn phòng', 'moving']],
            'port_logistics_agent' => ['group' => 'transport_delivery_logistics', 'label' => __('Port Logistics / Forwarding Agent'), 'legacy' => 'Other', 'aliases' => ['logistics', 'cảng', 'cang', 'giao nhận', 'giao nhan', 'forwarding']],
            'last_mile_delivery_network' => ['group' => 'transport_delivery_logistics', 'label' => __('Last-mile Delivery Network'), 'legacy' => 'Other', 'aliases' => ['last mile', 'chặng cuối', 'chang cuoi', 'giao hàng chặng cuối']],

            // 12. Real Estate, Rental & Property
            'real_estate_brokerage' => ['group' => 'real_estate_rental_property', 'label' => __('Real Estate Brokerage'), 'legacy' => 'Real estate office', 'aliases' => ['môi giới', 'moi gioi', 'bất động sản', 'bat dong san', 'real estate', 'brokerage']],
            'property_rental' => ['group' => 'real_estate_rental_property', 'label' => __('Property / Space Rental'), 'legacy' => 'Real estate office', 'aliases' => ['cho thuê', 'cho thue', 'mặt bằng', 'mat bang', 'căn hộ', 'rental']],
            'boarding_house_room_rental' => ['group' => 'real_estate_rental_property', 'label' => __('Boarding House / Room Rental'), 'legacy' => 'Real estate office', 'aliases' => ['nhà trọ', 'nha tro', 'phòng trọ', 'phong tro', 'boarding house', 'room rental']],
            'short_stay_rental' => ['group' => 'real_estate_rental_property', 'label' => __('Short-stay Rental'), 'legacy' => 'Hotel', 'aliases' => ['cho thuê ngắn ngày', 'short stay', 'airbnb']],
            'property_management' => ['group' => 'real_estate_rental_property', 'label' => __('Property Management'), 'legacy' => 'Real estate office', 'aliases' => ['quản lý tài sản', 'quan ly tai san', 'vận hành tòa nhà', 'property management']],
            'serviced_office_coworking' => ['group' => 'real_estate_rental_property', 'label' => __('Serviced Office / Coworking'), 'legacy' => 'Real estate office', 'aliases' => ['văn phòng dịch vụ', 'van phong dich vu', 'coworking', 'office']],
            'warehouse_rental' => ['group' => 'real_estate_rental_property', 'label' => __('Warehouse / Yard Rental'), 'legacy' => 'Real estate office', 'aliases' => ['cho thuê kho', 'kho bãi', 'warehouse rental']],
            'equipment_rental' => ['group' => 'real_estate_rental_property', 'label' => __('Equipment Rental'), 'legacy' => 'Other', 'aliases' => ['cho thuê thiết bị', 'thiết bị', 'thiet bi', 'equipment rental']],
            'real_estate_consulting' => ['group' => 'real_estate_rental_property', 'label' => __('Real Estate / Transaction Consulting'), 'legacy' => 'Real estate office', 'aliases' => ['tư vấn bất động sản', 'pháp lý giao dịch', 'real estate consulting']],
            'rental_agency_network' => ['group' => 'real_estate_rental_property', 'label' => __('Rental Agency Network'), 'legacy' => 'Real estate office', 'aliases' => ['đại lý cho thuê', 'dai ly cho thue', 'rental agency']],

            // 13. Digital, Creator & Online Business
            'ecommerce_seller' => ['group' => 'digital_creator_online_business', 'label' => __('E-commerce Seller'), 'legacy' => 'Other', 'aliases' => ['bán hàng online', 'ban hang online', 'tmđt', 'tmdt', 'ecommerce', 'shopee', 'lazada', 'tiktok shop']],
            'livestream_seller' => ['group' => 'digital_creator_online_business', 'label' => __('Livestream Seller'), 'legacy' => 'Other', 'aliases' => ['livestream', 'live', 'bán hàng live', 'ban hang live']],
            'content_creator_kol' => ['group' => 'digital_creator_online_business', 'label' => __('Creator / KOL / Influencer'), 'legacy' => 'Other', 'aliases' => ['creator', 'kol', 'influencer', 'sáng tạo nội dung', 'tiktoker', 'youtuber']],
            'digital_product_seller' => ['group' => 'digital_creator_online_business', 'label' => __('Digital Product Seller'), 'legacy' => 'Other', 'aliases' => ['tài nguyên số', 'tai nguyen so', 'template', 'ebook', 'digital product']],
            'online_course_solo' => ['group' => 'digital_creator_online_business', 'label' => __('Online Course Solopreneur'), 'legacy' => 'Other', 'aliases' => ['khóa học online', 'solopreneur', 'online course']],
            'freelancer_service' => ['group' => 'digital_creator_online_business', 'label' => __('Freelancer Service'), 'legacy' => 'Professional service', 'aliases' => ['freelancer', 'làm tự do', 'lam tu do', 'dịch vụ freelance']],
            'saas_microbusiness' => ['group' => 'digital_creator_online_business', 'label' => __('SaaS / Micro Software Business'), 'legacy' => 'Other', 'aliases' => ['saas', 'phần mềm', 'software', 'micro saas']],
            'affiliate_marketing_business' => ['group' => 'digital_creator_online_business', 'label' => __('Affiliate / Online Partner'), 'legacy' => 'Other', 'aliases' => ['affiliate', 'cộng tác viên', 'ctv', 'tiếp thị liên kết']],
            'online_community_membership' => ['group' => 'digital_creator_online_business', 'label' => __('Paid Community / Membership'), 'legacy' => 'Other', 'aliases' => ['cộng đồng trả phí', 'cong dong tra phi', 'membership', 'group trả phí']],
            'remote_consultant' => ['group' => 'digital_creator_online_business', 'label' => __('Online Consultant / Expert'), 'legacy' => 'Professional service', 'aliases' => ['tư vấn online', 'tu van online', 'chuyên gia', 'remote consultant']],
            'dropship_pod_seller' => ['group' => 'digital_creator_online_business', 'label' => __('Dropship / Print-on-demand'), 'legacy' => 'Other', 'aliases' => ['dropship', 'print on demand', 'pod']],
            'ai_automation_service' => ['group' => 'digital_creator_online_business', 'label' => __('AI / Automation Service'), 'legacy' => 'Other', 'aliases' => ['ai', 'automation', 'tự động hóa', 'tu dong hoa']],

            // 14. Manufacturing, Processing & OCOP
            'food_processing' => ['group' => 'small_manufacturing_processing_ocop', 'label' => __('Food Processing'), 'legacy' => 'Other', 'aliases' => ['chế biến', 'che bien', 'thực phẩm', 'food processing']],
            'beverage_production_micro' => ['group' => 'small_manufacturing_processing_ocop', 'label' => __('Micro Beverage Production'), 'legacy' => 'Other', 'aliases' => ['sản xuất đồ uống', 'san xuat do uong', 'beverage production']],
            'bakery_food_production' => ['group' => 'small_manufacturing_processing_ocop', 'label' => __('Bakery / Packaged Food Production'), 'legacy' => 'Other', 'aliases' => ['xưởng bánh', 'xuong banh', 'thực phẩm đóng gói', 'food production']],
            'garment_tailor_workshop' => ['group' => 'small_manufacturing_processing_ocop', 'label' => __('Garment / Tailor Workshop'), 'legacy' => 'Other', 'aliases' => ['may mặc', 'may mac', 'xưởng may', 'xuong may', 'tailor', 'garment']],
            'handicraft_artisan' => ['group' => 'small_manufacturing_processing_ocop', 'label' => __('Handicraft / Artisan'), 'legacy' => 'Other', 'aliases' => ['thủ công', 'thu cong', 'mỹ nghệ', 'my nghe', 'handicraft', 'artisan']],
            'furniture_workshop' => ['group' => 'small_manufacturing_processing_ocop', 'label' => __('Furniture / Woodwork Workshop'), 'legacy' => 'Other', 'aliases' => ['xưởng nội thất', 'đồ gỗ', 'do go', 'furniture workshop']],
            'printing_packaging_workshop' => ['group' => 'small_manufacturing_processing_ocop', 'label' => __('Printing / Packaging'), 'legacy' => 'Other', 'aliases' => ['in ấn', 'in an', 'bao bì', 'bao bi', 'printing', 'packaging']],
            'cosmetics_production_small' => ['group' => 'small_manufacturing_processing_ocop', 'label' => __('Small Cosmetics Production'), 'legacy' => 'Other', 'aliases' => ['sản xuất mỹ phẩm', 'san xuat my pham', 'cosmetics production']],
            'mechanical_workshop' => ['group' => 'small_manufacturing_processing_ocop', 'label' => __('Mechanical / Machining Workshop'), 'legacy' => 'Other', 'aliases' => ['cơ khí', 'co khi', 'gia công', 'gia cong', 'mechanical', 'machining']],
            'electronics_assembly_service' => ['group' => 'small_manufacturing_processing_ocop', 'label' => __('Electronics Assembly'), 'legacy' => 'Other', 'aliases' => ['lắp ráp', 'lap rap', 'gia công thiết bị', 'electronics assembly']],
            'ocop_local_producer' => ['group' => 'small_manufacturing_processing_ocop', 'label' => __('OCOP / Local Producer'), 'legacy' => 'Other', 'aliases' => ['ocop', 'đặc sản', 'dac san', 'nhà sản xuất', 'local producer']],
            'private_label_production' => ['group' => 'small_manufacturing_processing_ocop', 'label' => __('Private Label Production'), 'legacy' => 'Other', 'aliases' => ['nhãn riêng', 'nhan rieng', 'private label', 'oem']],

            // 15. Agriculture, Fisheries & Local Supply
            'farm_produce_supplier' => ['group' => 'agriculture_fisheries_local_supply', 'label' => __('Farm Produce Supplier'), 'legacy' => 'Other', 'aliases' => ['nông sản', 'nong san', 'rau củ', 'rau cu', 'trái cây', 'trai cay', 'produce', 'farm']],
            'seafood_supplier' => ['group' => 'agriculture_fisheries_local_supply', 'label' => __('Seafood Supplier'), 'legacy' => 'Other', 'aliases' => ['hải sản', 'hai san', 'thủy sản', 'thuy san', 'seafood']],
            'livestock_poultry_supplier' => ['group' => 'agriculture_fisheries_local_supply', 'label' => __('Livestock / Poultry Supplier'), 'legacy' => 'Other', 'aliases' => ['chăn nuôi', 'chan nuoi', 'gia cầm', 'gia cam', 'livestock', 'poultry']],
            'aquaculture_farm' => ['group' => 'agriculture_fisheries_local_supply', 'label' => __('Aquaculture Farm'), 'legacy' => 'Other', 'aliases' => ['nuôi trồng thủy sản', 'nuoi trong thuy san', 'aquaculture', 'nuôi tôm', 'nuôi cá']],
            'flower_ornamental_plant' => ['group' => 'agriculture_fisheries_local_supply', 'label' => __('Flowers / Ornamental Plants'), 'legacy' => 'Other', 'aliases' => ['hoa', 'cây cảnh', 'cay canh', 'flower', 'ornamental plant']],
            'organic_farm' => ['group' => 'agriculture_fisheries_local_supply', 'label' => __('Organic Farm'), 'legacy' => 'Other', 'aliases' => ['hữu cơ', 'huu co', 'organic farm', 'nông trại']],
            'agricultural_inputs' => ['group' => 'agriculture_fisheries_local_supply', 'label' => __('Agricultural Inputs'), 'legacy' => 'Other', 'aliases' => ['vật tư nông nghiệp', 'vat tu nong nghiep', 'phân bón', 'phan bon', 'thuốc trừ sâu', 'agricultural inputs']],
            'farm_to_table_supplier' => ['group' => 'agriculture_fisheries_local_supply', 'label' => __('Farm-to-table Supplier'), 'legacy' => 'Other', 'aliases' => ['farm to table', 'cung ứng nhà hàng', 'restaurant supply']],
            'agricultural_service' => ['group' => 'agriculture_fisheries_local_supply', 'label' => __('Agricultural Service'), 'legacy' => 'Other', 'aliases' => ['dịch vụ nông nghiệp', 'dich vu nong nghiep', 'agricultural service', 'máy nông nghiệp']],
            'local_produce_collection' => ['group' => 'agriculture_fisheries_local_supply', 'label' => __('Local Produce Collection'), 'legacy' => 'Other', 'aliases' => ['thu mua', 'gom hàng', 'gom hang', 'produce collection', 'đầu mối']],

            // 16. Culture, Entertainment, Sports & Community
            'karaoke_music_lounge' => ['group' => 'culture_entertainment_sports_community', 'label' => __('Karaoke / Music Lounge'), 'legacy' => 'Other', 'aliases' => ['karaoke', 'phòng nhạc', 'phong nhac', 'lounge', 'hát', 'hat']],
            'sports_venue' => ['group' => 'culture_entertainment_sports_community', 'label' => __('Sports Venue / Field'), 'legacy' => 'Other', 'aliases' => ['sân bóng', 'san bong', 'thể thao', 'the thao', 'pickleball', 'sân tennis', 'sports field']],
            'kids_play_center' => ['group' => 'culture_entertainment_sports_community', 'label' => __('Kids Play Center'), 'legacy' => 'Other', 'aliases' => ['khu vui chơi', 'khu vui choi', 'vui chơi trẻ em', 'kids play', 'playground']],
            'art_studio_workshop' => ['group' => 'culture_entertainment_sports_community', 'label' => __('Art Studio / Workshop'), 'legacy' => 'Other', 'aliases' => ['studio nghệ thuật', 'art studio', 'workshop nghệ thuật']],
            'dance_music_studio' => ['group' => 'culture_entertainment_sports_community', 'label' => __('Dance / Music Studio'), 'legacy' => 'Other', 'aliases' => ['nhảy', 'nhay', 'dance', 'âm nhạc', 'am nhac', 'music studio']],
            'event_production_service' => ['group' => 'culture_entertainment_sports_community', 'label' => __('Event Production Service'), 'legacy' => 'Event venue', 'aliases' => ['tổ chức sự kiện', 'to chuc su kien', 'event production', 'chương trình']],
            'content_studio' => ['group' => 'culture_entertainment_sports_community', 'label' => __('Content / Podcast / Livestream Studio'), 'legacy' => 'Other', 'aliases' => ['studio nội dung', 'podcast', 'livestream studio', 'content studio']],
            'community_club' => ['group' => 'culture_entertainment_sports_community', 'label' => __('Club / Community Group'), 'legacy' => 'Other', 'aliases' => ['clb', 'câu lạc bộ', 'cau lac bo', 'cộng đồng', 'cong dong', 'hội nhóm', 'club']],
            'recreation_attraction' => ['group' => 'culture_entertainment_sports_community', 'label' => __('Recreation / Attraction'), 'legacy' => 'Other', 'aliases' => ['vui chơi', 'vui choi', 'giải trí', 'giai tri', 'recreation', 'attraction']],
            'ticketed_activity' => ['group' => 'culture_entertainment_sports_community', 'label' => __('Ticketed Activity / Experience Class'), 'legacy' => 'Other', 'aliases' => ['bán vé', 'ban ve', 'lớp trải nghiệm', 'ticketed activity']],

            // 17. Organizations, Associations & Public Community
            'business_association' => ['group' => 'organization_association_public_community', 'label' => __('Business Association'), 'legacy' => 'Other', 'aliases' => ['hội doanh nghiệp', 'hoi doanh nghiep', 'hiệp hội', 'hiep hoi', 'business association']],
            'trade_association' => ['group' => 'organization_association_public_community', 'label' => __('Trade Association'), 'legacy' => 'Other', 'aliases' => ['hội ngành nghề', 'hoi nganh nghe', 'ngành nghề', 'trade association']],
            'community_organization' => ['group' => 'organization_association_public_community', 'label' => __('Community Organization'), 'legacy' => 'Other', 'aliases' => ['tổ chức cộng đồng', 'to chuc cong dong', 'community organization']],
            'ngo_social_project' => ['group' => 'organization_association_public_community', 'label' => __('NGO / Social Project'), 'legacy' => 'Other', 'aliases' => ['ngo', 'dự án xã hội', 'du an xa hoi', 'phi lợi nhuận', 'social project']],
            'government_program_partner' => ['group' => 'organization_association_public_community', 'label' => __('Public Sector Program Partner'), 'legacy' => 'Other', 'aliases' => ['khu vực công', 'khu vuc cong', 'chương trình nhà nước', 'government program']],
            'startup_incubator' => ['group' => 'organization_association_public_community', 'label' => __('Startup Incubator'), 'legacy' => 'Other', 'aliases' => ['vườn ươm', 'vuon uom', 'khởi nghiệp', 'khoi nghiep', 'incubator', 'startup']],
            'training_program_operator' => ['group' => 'organization_association_public_community', 'label' => __('Training Program Operator'), 'legacy' => 'Education center', 'aliases' => ['chương trình đào tạo', 'training program', 'vận hành đào tạo']],
            'market_management_partner' => ['group' => 'organization_association_public_community', 'label' => __('Market / Commercial Area Management'), 'legacy' => 'Other', 'aliases' => ['ban quản lý chợ', 'ban quan ly cho', 'khu thương mại', 'market management']],
            'cooperative_union' => ['group' => 'organization_association_public_community', 'label' => __('Cooperative / Union'), 'legacy' => 'Other', 'aliases' => ['hợp tác xã', 'hop tac xa', 'htx', 'liên minh', 'cooperative']],
            'public_event_campaign' => ['group' => 'organization_association_public_community', 'label' => __('Public Event / Community Campaign'), 'legacy' => 'Other', 'aliases' => ['chiến dịch cộng đồng', 'chien dich cong dong', 'sự kiện công', 'public event', 'community campaign']],

            // 18. Other / Needs Classification
            'other_not_sure' => ['group' => 'other_needs_classification', 'label' => __('Other / Not Sure'), 'legacy' => 'Other', 'aliases' => ['khác', 'khac', 'chưa rõ', 'chua ro', 'other', 'not sure']],
            'multi_industry_business' => ['group' => 'other_needs_classification', 'label' => __('Multi-industry Business'), 'legacy' => 'Other', 'aliases' => ['đa ngành', 'da nganh', 'multi industry', 'nhiều ngành']],
            'new_business_registering' => ['group' => 'other_needs_classification', 'label' => __('Newly Registered / Pre-launch'), 'legacy' => 'Other', 'aliases' => ['mới đăng ký', 'moi dang ky', 'chưa vận hành', 'new business', 'pre-launch']],
            'legacy_business_uncategorized' => ['group' => 'other_needs_classification', 'label' => __('Legacy Business (Uncategorized)'), 'legacy' => 'Other', 'aliases' => ['hộ cũ', 'ho cu', 'chưa phân loại', 'chua phan loai', 'uncategorized', 'legacy']],
            'needs_manual_review' => ['group' => 'other_needs_classification', 'label' => __('Needs MKT Manual Review'), 'legacy' => 'Other', 'aliases' => ['rà soát', 'ra soat', 'manual review', 'cần kiểm tra']],
            'restricted_sensitive_review' => ['group' => 'other_needs_classification', 'label' => __('Restricted / Sensitive (Needs Review)'), 'legacy' => 'Other', 'compliance' => true, 'aliases' => ['nhạy cảm', 'nhay cam', 'restricted', 'sensitive', 'cấm', 'cam']],
        ];
    }

    /**
     * Ordered list of group codes (priority first, then extended).
     *
     * @return array<int, string>
     */
    public static function groupCodes(): array
    {
        return array_keys(self::groupDefs());
    }

    /**
     * @return array<int, string>
     */
    public static function priorityGroupCodes(): array
    {
        return self::PRIORITY_GROUPS;
    }

    /**
     * @return array<int, string>
     */
    public static function categoryCodes(): array
    {
        return array_keys(self::categoryDefs());
    }

    public static function isValidGroup(string $code): bool
    {
        return array_key_exists($code, self::groupDefs());
    }

    public static function isValidCategory(string $code): bool
    {
        return array_key_exists($code, self::categoryDefs());
    }

    /**
     * Full ordered taxonomy tree for the UI. Each group carries its categories,
     * priority flag, ordering, compliance flag, and a flattened alias string.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function taxonomyTree(): array
    {
        $groups     = self::groupDefs();
        $categories = self::categoryDefs();
        $priority   = array_flip(self::PRIORITY_GROUPS);

        $tree         = [];
        $displayOrder = 0;

        foreach ($groups as $groupCode => $group) {
            $displayOrder++;
            $meta = $group['meta'];

            $groupCategories = [];

            foreach ($categories as $categoryCode => $category) {
                if ($category['group'] !== $groupCode) {
                    continue;
                }

                $groupCategories[] = [
                    'code'                 => $categoryCode,
                    'label'                => $category['label'],
                    'icon'                 => $group['icon'],
                    'legacy_type'          => $category['legacy'],
                    'compliance_sensitive' => (bool) ($category['compliance'] ?? $meta['compliance_sensitive'] ?? false),
                    'aliases'              => implode(' ', $category['aliases'] ?? []),
                ];
            }

            $tree[] = [
                'code'                 => $groupCode,
                'label'                => $group['label'],
                'icon'                 => $group['icon'],
                'is_priority'          => isset($priority[$groupCode]),
                'priority_order'       => $priority[$groupCode] ?? null,
                'display_order'        => $displayOrder,
                'compliance_sensitive' => (bool) ($meta['compliance_sensitive'] ?? false),
                'avoid_medical_claims' => (bool) ($meta['avoid_medical_claims'] ?? false),
                'recommended_modules'  => $meta['recommended_modules'],
                'default_campaign_goals' => $meta['default_campaign_goals'],
                'signals'              => $meta['signals'],
                'categories'           => $groupCategories,
            ];
        }

        return $tree;
    }

    /**
     * Group-level metadata used for recommendations and snapshots.
     *
     * @return array<string, mixed>
     */
    public static function groupMeta(string $groupCode): array
    {
        $groups = self::groupDefs();
        $group  = $groups[$groupCode] ?? $groups[self::FALLBACK_GROUP];
        $meta   = $group['meta'];

        return [
            'group_code'            => self::isValidGroup($groupCode) ? $groupCode : self::FALLBACK_GROUP,
            'group_label'           => $group['label'],
            'icon'                  => $group['icon'],
            'recommended_modules'   => $meta['recommended_modules'],
            'default_campaign_goals' => $meta['default_campaign_goals'],
            'alternative_data_signals' => $meta['signals'],
            'dashboard_preset'      => $meta['dashboard_preset'],
            'template_pack'         => $meta['template_pack'],
            'compliance_sensitive'  => (bool) ($meta['compliance_sensitive'] ?? false),
            'avoid_medical_claims'  => (bool) ($meta['avoid_medical_claims'] ?? false),
        ];
    }

    /**
     * Category-level metadata (inherits group metadata + own legacy/compliance).
     *
     * @return array<string, mixed>
     */
    public static function categoryMeta(string $categoryCode): array
    {
        $categories = self::categoryDefs();
        $category   = $categories[$categoryCode] ?? null;

        if ($category === null) {
            $groupMeta = self::groupMeta(self::FALLBACK_GROUP);

            return array_merge($groupMeta, [
                'category_code'        => self::FALLBACK_CATEGORY,
                'category_label'       => $categories[self::FALLBACK_CATEGORY]['label'] ?? $categoryCode,
                'legacy_type'          => 'Other',
                'compliance_sensitive' => $groupMeta['compliance_sensitive'],
            ]);
        }

        $groupMeta            = self::groupMeta($category['group']);
        $compliance           = (bool) ($category['compliance'] ?? $groupMeta['compliance_sensitive']);
        $groupMeta['compliance_sensitive'] = $compliance;

        return array_merge($groupMeta, [
            'category_code'  => $categoryCode,
            'category_label' => $category['label'],
            'legacy_type'    => $category['legacy'],
        ]);
    }

    /**
     * Resolve a (group, category) selection into a normalized, validated set.
     * Falls back gracefully to other_needs_classification / other_not_sure.
     *
     * @return array<string, mixed>
     */
    public static function resolveSelection(?string $groupCode, ?string $categoryCode): array
    {
        $categoryCode = trim((string) $categoryCode);
        $groupCode    = trim((string) $groupCode);

        if (! self::isValidCategory($categoryCode)) {
            $categoryCode = self::FALLBACK_CATEGORY;
        }

        $categories = self::categoryDefs();
        $resolvedGroup = $categories[$categoryCode]['group'];

        // Trust the category's real group over a mismatched submitted group.
        $groupCode = $resolvedGroup;

        $meta = self::categoryMeta($categoryCode);

        return [
            'group_code'       => $groupCode,
            'category_code'    => $categoryCode,
            'legacy_type'      => $meta['legacy_type'],
            'taxonomy_version' => self::TAXONOMY_VERSION,
            'metadata_snapshot' => [
                'group_code'             => $groupCode,
                'category_code'          => $categoryCode,
                'legacy_type'            => $meta['legacy_type'],
                'recommended_modules'    => $meta['recommended_modules'],
                'default_campaign_goals' => $meta['default_campaign_goals'],
                'alternative_data_signals' => $meta['alternative_data_signals'],
                'dashboard_preset'       => $meta['dashboard_preset'],
                'template_pack'          => $meta['template_pack'],
                'compliance_sensitive'   => $meta['compliance_sensitive'],
                'avoid_medical_claims'   => $meta['avoid_medical_claims'],
                'taxonomy_version'       => self::TAXONOMY_VERSION,
                'captured_at'            => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * Infer the nearest new group/category for a legacy business that only has
     * lb_businesses.type. Never throws — defaults to the fallback category.
     *
     * @return array{group_code: string, category_code: string}
     */
    public static function inferFromLegacyType(?string $legacyType): array
    {
        $normalized = self::normalizeType((string) $legacyType);

        // 'Other' is ambiguous (many categories map to it) — go straight to fallback.
        if ($normalized === 'Other') {
            return [
                'group_code'    => self::FALLBACK_GROUP,
                'category_code' => self::FALLBACK_CATEGORY,
            ];
        }

        foreach (self::categoryDefs() as $categoryCode => $category) {
            if ($category['legacy'] === $normalized) {
                return [
                    'group_code'    => $category['group'],
                    'category_code' => $categoryCode,
                ];
            }
        }

        return [
            'group_code'    => self::FALLBACK_GROUP,
            'category_code' => self::FALLBACK_CATEGORY,
        ];
    }

    // ---------------------------------------------------------------------
    // Legacy-compatible API (kept so existing callers keep working).
    // ---------------------------------------------------------------------

    /**
     * Flat legacy type => display label (legacy compatibility).
     *
     * @return array<string, string>
     */
    public static function typeOptions(): array
    {
        $result = [];

        foreach (self::LEGACY_TYPES as $type) {
            $result[$type] = __($type);
        }

        return $result;
    }

    /**
     * Legacy grouped options for older callers that still render the previous
     * type picker shape. Backed by the new taxonomy source of truth.
     *
     * @return array<int, array{group: string, label: string, icon: string, options: array<string, array{type: string, label: string, icon: string}>}>
     */
    public static function groupedOptions(): array
    {
        $result = [];

        foreach (self::LEGACY_TYPES as $type) {
            $meta = self::metadataFor($type);
            $group = $meta['group'];

            if (! isset($result[$group])) {
                $result[$group] = [
                    'group' => $group,
                    'label' => $meta['group_label'],
                    'icon' => $meta['icon'],
                    'options' => [],
                ];
            }

            $result[$group]['options'][$type] = [
                'type' => $type,
                'label' => __($type),
                'icon' => $meta['icon'],
            ];
        }

        return array_values($result);
    }

    /**
     * Legacy popular quick-pick options for older callers.
     *
     * @return array<int, array{type: string, label: string, group: string, icon: string, description: string}>
     */
    public static function popularOptions(): array
    {
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
            $meta = self::metadataFor($type);

            $result[] = [
                'type'        => $type,
                'label'       => __($type),
                'group'       => $meta['group'],
                'icon'        => $meta['icon'],
                'description' => $description,
            ];
        }

        return $result;
    }

    /**
     * Runtime metadata for a legacy type (legacy compatibility shim).
     *
     * @return array<string, mixed>
     */
    public static function metadataFor(string $type): array
    {
        $normalized = self::normalizeType($type);

        if ($normalized === 'Other') {
            $meta = self::groupMeta(self::FALLBACK_GROUP);

            return [
                'group'                => $meta['group_code'],
                'group_label'          => $meta['group_label'],
                'icon'                 => $meta['icon'],
                'dashboard_preset'     => $meta['dashboard_preset'],
                'template_pack'        => $meta['template_pack'],
                'tags'                 => $meta['alternative_data_signals'],
                'recommended_modules'  => $meta['recommended_modules'],
                'first_campaign_goals' => $meta['default_campaign_goals'],
                'compliance_sensitive' => $meta['compliance_sensitive'],
            ];
        }

        foreach (self::categoryDefs() as $category) {
            if ($category['legacy'] === $normalized) {
                $meta = self::groupMeta($category['group']);

                return [
                    'group'                => $meta['group_code'],
                    'group_label'          => $meta['group_label'],
                    'icon'                 => $meta['icon'],
                    'dashboard_preset'     => $meta['dashboard_preset'],
                    'template_pack'        => $meta['template_pack'],
                    'tags'                 => $meta['alternative_data_signals'],
                    'recommended_modules'  => $meta['recommended_modules'],
                    'first_campaign_goals' => $meta['default_campaign_goals'],
                    'compliance_sensitive' => $meta['compliance_sensitive'],
                ];
            }
        }

        $meta = self::groupMeta(self::FALLBACK_GROUP);

        return [
            'group'                => $meta['group_code'],
            'group_label'          => $meta['group_label'],
            'icon'                 => $meta['icon'],
            'dashboard_preset'     => $meta['dashboard_preset'],
            'template_pack'        => $meta['template_pack'],
            'tags'                 => $meta['alternative_data_signals'],
            'recommended_modules'  => $meta['recommended_modules'],
            'first_campaign_goals' => $meta['default_campaign_goals'],
            'compliance_sensitive' => $meta['compliance_sensitive'],
        ];
    }

    /**
     * Legacy alias map (alias => canonical legacy type) used by normalizeType().
     *
     * @return array<string, string>
     */
    public static function searchAliases(): array
    {
        $aliases = [];

        foreach (self::categoryDefs() as $category) {
            foreach ($category['aliases'] ?? [] as $alias) {
                $alias = mb_strtolower($alias);
                if (! isset($aliases[$alias])) {
                    $aliases[$alias] = $category['legacy'];
                }
            }
        }

        return $aliases;
    }

    /**
     * Normalizes any type string (legacy translated values or aliases) to the
     * canonical English legacy type stored in lb_businesses.type.
     */
    public static function normalizeType(string $type): string
    {
        $type = trim($type);

        if ($type === '') {
            return 'Other';
        }

        if (in_array($type, self::LEGACY_TYPES, true)) {
            return $type;
        }

        // Old simple translated labels e.g. "Nhà hàng" => "Restaurant".
        foreach (self::LEGACY_TYPES as $canonical) {
            if (strcasecmp(__($canonical), $type) === 0) {
                return $canonical;
            }
        }

        // A new category code passed directly.
        $categories = self::categoryDefs();
        if (isset($categories[$type])) {
            return $categories[$type]['legacy'];
        }

        // Alias match (case-insensitive).
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

    /**
     * User-facing labels for recommended campaign goals (UI chips).
     *
     * @return array<string, string>
     */
    public static function campaignGoalLabels(): array
    {
        return [
            'review'    => __('Industry goal: Review'),
            'coupon'    => __('Industry goal: Coupon'),
            'feedback'  => __('Industry goal: Feedback'),
            'loyalty'   => __('Industry goal: Loyalty'),
            'lead'      => __('Industry goal: Lead'),
            'retention' => __('Industry goal: Retention'),
            'booking'   => __('Industry goal: Booking'),
            'referral'  => __('Industry goal: Referral'),
        ];
    }

    /**
     * User-facing labels for alternative-data signal chips.
     *
     * @return array<string, string>
     */
    public static function alternativeDataSignalLabels(): array
    {
        return [
            'qr_scan'          => __('Industry signal: QR scan'),
            'review'             => __('Industry signal: Review'),
            'lead_form'          => __('Industry signal: Lead form'),
            'booking'            => __('Industry signal: Booking'),
            'coupon'             => __('Industry signal: Coupon'),
            'loyalty'            => __('Industry signal: Loyalty'),
            'referral'           => __('Industry signal: Referral'),
            'crm_activity'       => __('Industry signal: CRM activity'),
            'customer_profile'   => __('Industry signal: Customer profile'),
            'supplier_order'     => __('Industry signal: Supplier order'),
            'delivery_log'       => __('Industry signal: Delivery log'),
            'inventory_light'    => __('Industry signal: Inventory snapshot'),
            'payment_signal'     => __('Industry signal: Payment signal'),
            'invoice_signal'     => __('Industry signal: Invoice signal'),
            'location_density'   => __('Industry signal: Location density'),
            'seasonality'        => __('Industry signal: Seasonality'),
            'staff_capacity'     => __('Industry signal: Staff capacity'),
            'price_band'         => __('Industry signal: Price band'),
            'repeat_rate'        => __('Industry signal: Repeat rate'),
            'risk_flag'          => __('Industry signal: Risk flag'),
        ];
    }
}
