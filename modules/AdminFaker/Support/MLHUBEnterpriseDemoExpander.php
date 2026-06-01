<?php

namespace Modules\AdminFaker\Support;

/**
 * Expands SOHO Đà Nẵng demo for load-test: 11 cơ sở, đủ growth-tool/campaign, target QR traffic.
 */
final class MLHUBEnterpriseDemoExpander
{
    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    public static function expand(array $config): array
    {
        $meta = $config['meta'] ?? [];
        $siteCount = max(1, (int) ($meta['site_count'] ?? 0));
        $minCampaigns = max(1, (int) ($meta['min_campaigns_per_site'] ?? 5));
        $targetCustomers = (int) ($meta['customer_target'] ?? 0);
        $targetQr = (int) ($meta['target_qr_visits'] ?? 0);

        if ($siteCount < 2 && $minCampaigns < 5 && $targetCustomers < 5000 && $targetQr < 5_000_000) {
            return $config;
        }

        $config['businesses'] = self::ensureBusinesses($config['businesses'] ?? [], $siteCount);
        $config['locations'] = self::ensureLocations($config['locations'] ?? [], $config['businesses']);
        $config = self::ensureCampaigns($config, $minCampaigns);
        $config['standalone_landing_pages'] = self::ensureStandaloneLandings(
            $config['standalone_landing_pages'] ?? [],
            $config['businesses'],
        );

        return $config;
    }

    /**
     * @param  array<string, array<string, mixed>>  $businesses
     * @return array<string, array<string, mixed>>
     */
    protected static function ensureBusinesses(array $businesses, int $siteCount): array
    {
        $districts = [
            'Hải Châu' => 'Hải Châu, Đà Nẵng',
            'Sơn Trà' => 'Sơn Trà, Đà Nẵng',
            'Thanh Khê' => 'Thanh Khê, Đà Nẵng',
            'Ngũ Hành Sơn' => 'Ngũ Hành Sơn, Đà Nẵng',
            'Liên Chiểu' => 'Liên Chiểu, Đà Nẵng',
            'Cẩm Lệ' => 'Cẩm Lệ, Đà Nẵng',
        ];
        $types = ['retail', 'restaurant', 'cafe', 'spa', 'clinic', 'gym'];
        $names = [
            'Hộ Kinh Doanh Chợ Cồn — Thực phẩm',
            'Tiệm Tạp Hóa SOHO — Hòa Cường',
            'Quán Cơm Hộ Kinh Doanh — Thanh Khê',
            'Studio Nail & Làm đẹp SOHO',
            'Cửa hàng Điện thoại — Liên Chiểu',
            'Xưởng may SOHO — Cẩm Lệ',
        ];

        $index = count($businesses);

        while (count($businesses) < $siteCount) {
            $key = 'enterprise_site_'.($index + 1);
            $district = array_keys($districts)[$index % count($districts)];
            $label = $names[$index % count($names)] ?? 'Hộ kinh doanh SOHO Đà Nẵng '.($index + 1);
            $phone = sprintf('0236 3%03d %03d', 600 + $index, 100 + $index);

            $businesses[$key] = [
                'slug' => 'admin-faker-enterprise-'.($index + 1),
                'name' => $label,
                'type' => $types[$index % count($types)],
                'phone' => $phone,
                'email' => 'lienhe@demo.enterprise-'.($index + 1).'.dn',
                'website' => 'https://demo.enterprise-'.($index + 1).'.mlhub.vn',
                'address' => 'Số '.(10 + $index).' đường '.['Lê Duẩn', 'Trần Phú', 'Nguyễn Văn Linh', 'Võ Văn Kiệt'][$index % 4].', '.$districts[$district],
                'google_maps_url' => 'https://maps.google.com/?q=Da+Nang+enterprise+'.($index + 1),
                'zalo' => sprintf('0906%07d', 1000000 + $index),
            ];

            $index++;
        }

        return $businesses;
    }

    /**
     * @param  list<array<string, mixed>>  $locations
     * @param  array<string, array<string, mixed>>  $businesses
     * @return list<array<string, mixed>>
     */
    protected static function ensureLocations(array $locations, array $businesses): array
    {
        $existing = collect($locations)->pluck('business')->unique()->all();

        foreach (array_keys($businesses) as $businessKey) {
            if (in_array($businessKey, $existing, true)) {
                continue;
            }

            $business = $businesses[$businessKey];
            $locations[] = [
                'business' => $businessKey,
                'name' => $business['name'].' — Chi nhánh chính',
                'phone' => $business['phone'],
                'email' => $business['email'],
                'address' => $business['address'],
                'google_maps_url' => $business['google_maps_url'],
                'template' => 'clean_card',
            ];
        }

        return $locations;
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    protected static function ensureCampaigns(array $config, int $minCampaigns): array
    {
        $campaigns = $config['campaigns'] ?? [];
        $metrics = $config['campaign_metrics'] ?? [];
        $businesses = $config['businesses'] ?? [];
        $countsByBusiness = [];

        foreach ($campaigns as $campaign) {
            $businessKey = (string) ($campaign['business'] ?? '');
            $countsByBusiness[$businessKey] = ($countsByBusiness[$businessKey] ?? 0) + 1;
        }

        $typeQueue = ['review', 'lead', 'coupon', 'feedback', 'booking', 'url'];
        $typeLabels = [
            'review' => 'Đánh giá Google',
            'lead' => 'Form lead / tư vấn',
            'coupon' => 'Ưu đãi QR',
            'feedback' => 'Phản hồi khách hàng',
            'booking' => 'Đặt lịch / đặt bàn',
            'url' => 'Liên kết QR',
        ];

        foreach (array_keys($businesses) as $businessKey) {
            $presentTypes = collect($campaigns)
                ->where('business', $businessKey)
                ->pluck('type')
                ->unique()
                ->all();

            $needed = max(0, $minCampaigns - (int) ($countsByBusiness[$businessKey] ?? 0));

            foreach ($typeQueue as $type) {
                if ($needed <= 0) {
                    break;
                }

                if (in_array($type, $presentTypes, true)) {
                    continue;
                }

                $slug = 'admin-faker-'.$businessKey.'-'.$type.'-enterprise';
                $campaigns[] = [
                    'key' => str_replace('-', '_', $slug),
                    'business' => $businessKey,
                    'type' => $type,
                    'slug' => $slug,
                    'name' => $typeLabels[$type].' — '.$businesses[$businessKey]['name'],
                    'age_days' => 90 + (abs(crc32($slug)) % 540),
                    'settings' => self::settingsForType($type, $businessKey),
                ];

                $metrics[$slug] = [
                    'visits' => 280 + (abs(crc32($slug.'-v')) % 320),
                    'conversions' => 70 + (abs(crc32($slug.'-c')) % 130),
                ];

                $presentTypes[] = $type;
                $needed--;
                $countsByBusiness[$businessKey] = ($countsByBusiness[$businessKey] ?? 0) + 1;
            }
        }

        $config['campaigns'] = $campaigns;
        $config['campaign_metrics'] = $metrics;

        return $config;
    }

    /**
     * @param  list<array<string, mixed>>  $pages
     * @param  array<string, array<string, mixed>>  $businesses
     * @return list<array<string, mixed>>
     */
    protected static function ensureStandaloneLandings(array $pages, array $businesses): array
    {
        $existing = collect($pages)->pluck('business')->unique()->all();
        $types = [
            ['type' => 'review', 'template' => 'review_google_focus', 'headline' => 'Trải nghiệm hôm nay thế nào?'],
            ['type' => 'lead', 'template' => 'lead_quote_request', 'headline' => 'Để lại thông tin — chúng tôi gọi lại'],
        ];

        foreach (array_keys($businesses) as $index => $businessKey) {
            if (in_array($businessKey, $existing, true)) {
                continue;
            }

            $business = $businesses[$businessKey];
            $variant = $types[$index % count($types)];
            $slug = 'admin-faker-lp-'.$businessKey.'-'.$variant['type'];

            $pages[] = [
                'business' => $businessKey,
                'slug' => $slug,
                'title' => $variant['headline'].' — '.$business['name'],
                'type' => $variant['type'],
                'template' => $variant['template'],
                'headline' => $variant['headline'],
                'subheadline' => 'Hộ kinh doanh SOHO tại Đà Nẵng — trang demo MLHUB.',
                'cta' => $variant['type'] === 'review' ? 'Gửi đánh giá' : 'Gửi yêu cầu',
                'benefits' => ['Nhanh', 'Tiếng Việt', 'Theo dõi trên dashboard'],
                'age_days' => 180 + ($index % 120),
                'visits' => 160 + ($index % 90),
                'conversions' => 40 + ($index % 35),
            ];
        }

        return $pages;
    }

    /**
     * @return array<string, mixed>
     */
    protected static function settingsForType(string $type, string $businessKey): array
    {
        return match ($type) {
            'review' => [
                'landing_template' => 'review_google_focus',
                'positive_threshold' => 4,
                'preferred_destination' => 'google',
                'thank_you_message' => 'Cảm ơn bạn đã ghé chúng tôi.',
                'negative_feedback_message' => 'Hãy cho chúng tôi biết điều cần cải thiện.',
            ],
            'lead' => [
                'landing_template' => 'lead_quote_request',
                'headline' => 'Liên hệ hộ kinh doanh',
            ],
            'coupon' => [
                'landing_template' => 'coupon_weekend_deal',
                'discount_type' => 'percentage',
                'discount_value' => '15',
                'coupon_code' => strtoupper(substr(preg_replace('/[^a-z]/', '', $businessKey), 0, 6)),
                'usage_limit' => 500,
                'expiry_date' => null,
                'terms' => 'Áp dụng tại Đà Nẵng. Mỗi khách một mã.',
            ],
            'feedback' => [
                'landing_template' => 'feedback_private',
                'headline' => 'Góp ý dịch vụ',
                'thank_you_message' => 'Cảm ơn góp ý — chúng tôi sẽ cải thiện.',
                'rating_required' => false,
                'contact_required' => false,
            ],
            'booking' => [
                'landing_template' => 'booking_restaurant',
                'headline' => 'Đặt lịch / đặt bàn',
            ],
            default => [
                'landing_template' => 'url_simple',
                'target_url' => 'https://mlhub.vn',
                'headline' => 'Xem thêm',
            ],
        };
    }
}
