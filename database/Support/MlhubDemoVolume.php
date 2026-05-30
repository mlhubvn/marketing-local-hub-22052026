<?php

namespace Database\Support;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MlhubDemoVolume
{
    public static function demoConfig(): array
    {
        static $config;

        return $config ??= require database_path('seeders/data/mlhub_demo_vn.php');
    }

    /**
     * @return array{visits: int, conversions: int}
     */
    public static function metricsForSlug(string $slug): array
    {
        $configured = self::demoConfig()['campaign_metrics'][$slug] ?? null;

        if (is_array($configured)) {
            return [
                'visits' => (int) ($configured['visits'] ?? 2000),
                'conversions' => (int) ($configured['conversions'] ?? (int) round(($configured['visits'] ?? 2000) * 0.09)),
            ];
        }

        $visits = 1200 + (abs(crc32($slug)) % 3801);

        return [
            'visits' => $visits,
            'conversions' => (int) round($visits * (0.07 + (abs(crc32($slug.'-c')) % 4) / 100)),
        ];
    }

    public static function insertQrScans(
        int $userId,
        int $campaignId,
        int $count,
        array $cities,
        string $country,
        int $campaignIndex = 0,
    ): void {
        if ($count <= 0) {
            return;
        }

        $chunk = [];
        $chunkSize = 300;
        $now = Carbon::now();

        for ($i = 1; $i <= $count; $i++) {
            $chunk[] = [
                'user_id' => $userId,
                'campaign_id' => $campaignId,
                'ip_address' => sprintf('103.%d.%d.%d', ($campaignId % 200) + 1, ($i % 250) + 1, ($i % 200) + 10),
                'user_agent' => $i % 3 === 0 ? 'Mobile Safari Demo' : 'Chrome Desktop Demo',
                'device' => $i % 3 === 0 ? 'mobile' : 'desktop',
                'city' => $cities[$campaignIndex % max(1, count($cities))],
                'country' => $country,
                'created_at' => $now->copy()->subDays($i % 28)->subHours($i % 24),
            ];

            if (count($chunk) >= $chunkSize) {
                DB::table('lb_qr_scans')->insert($chunk);
                $chunk = [];
            }
        }

        if ($chunk !== []) {
            DB::table('lb_qr_scans')->insert($chunk);
        }
    }

    /**
     * @param  Collection<int, object{name: string, phone: string, email: string}>  $customers
     */
    public static function insertLeads(
        int $userId,
        int $campaignId,
        int $count,
        Collection $customers,
        string $message,
        string $source = 'mlhub_demo_vn',
    ): void {
        self::bulkEngagement('lb_lead_submissions', $count, $userId, $campaignId, $customers, function (object $customer, int $index) use ($message, $source): array {
            return [
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'message' => $message,
                'payload' => json_encode(['source' => $source], JSON_UNESCAPED_UNICODE),
            ];
        });
    }

    /**
     * @param  Collection<int, object{name: string, phone: string, email: string}>  $customers
     */
    public static function insertBookings(
        int $userId,
        int $campaignId,
        int $serviceId,
        int $count,
        Collection $customers,
        string $note,
    ): void {
        $statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        $times = ['09:00', '10:00', '14:00', '15:00', '16:00'];

        self::bulkEngagement('lb_bookings', $count, $userId, $campaignId, $customers, function (object $customer, int $index) use ($serviceId, $note, $statuses, $times): array {
            return [
                'service_id' => $serviceId,
                'status' => $statuses[$index % count($statuses)],
                'booking_date' => Carbon::now()->addDays(($index % 14) + 1)->toDateString(),
                'booking_time' => $times[$index % count($times)],
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_email' => $customer->email,
                'note' => $note,
            ];
        });
    }

    /**
     * @param  Collection<int, object{name: string, phone: string, email: string}>  $customers
     */
    public static function insertCouponRedemptions(
        int $userId,
        int $campaignId,
        int $count,
        Collection $customers,
        string $codePrefix,
    ): void {
        self::bulkEngagement('lb_coupon_redemptions', $count, $userId, $campaignId, $customers, function (object $customer, int $index) use ($codePrefix, $campaignId): array {
            $used = $index % 5 === 0;

            return [
                'code' => $codePrefix.'-'.$campaignId.'-'.str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT),
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_email' => $customer->email,
                'status' => $used ? 'used' : 'claimed',
                'used_at' => $used ? Carbon::now()->subHours($index + 1) : null,
            ];
        });
    }

    /**
     * @param  Collection<int, object{name: string, phone: string, email: string}>  $customers
     */
    public static function insertReviewFeedback(
        int $userId,
        int $campaignId,
        int $count,
        Collection $customers,
        string $positiveMessage,
        string $negativeMessage,
    ): void {
        self::bulkEngagement('lb_review_feedbacks', $count, $userId, $campaignId, $customers, function (object $customer, int $index) use ($positiveMessage, $negativeMessage): array {
            $rating = $index % 10 < 8 ? random_int(4, 5) : random_int(2, 3);

            return [
                'rating' => $rating,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_email' => $customer->email,
                'message' => $rating >= 4 ? $positiveMessage : $negativeMessage,
                'status' => $rating <= 3 ? 'new' : 'replied',
            ];
        });
    }

    /**
     * @param  Collection<int, object{name: string, phone: string, email: string}>  $customers
     */
    public static function insertFeedbackResponses(
        int $userId,
        int $campaignId,
        int $count,
        Collection $customers,
        string $positiveMessage,
        string $negativeMessage,
        string $source = 'mlhub_demo_vn',
    ): void {
        self::bulkEngagement('lb_feedback_responses', $count, $userId, $campaignId, $customers, function (object $customer, int $index) use ($positiveMessage, $negativeMessage, $source): array {
            $rating = $index % 10 < 7 ? random_int(4, 5) : random_int(2, 3);

            return [
                'rating' => $rating,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_email' => $customer->email,
                'message' => $rating >= 4 ? $positiveMessage : $negativeMessage,
                'payload' => json_encode(['source' => $source], JSON_UNESCAPED_UNICODE),
                'status' => $rating <= 3 ? 'new' : 'resolved',
                'resolved_at' => $rating >= 4 ? Carbon::now()->subDays(1) : null,
            ];
        });
    }

    /**
     * @param  callable(object, int): array<string, mixed>  $extraColumns
     * @param  Collection<int, object{name: string, phone: string, email: string}>  $customers
     */
    protected static function bulkEngagement(
        string $table,
        int $count,
        int $userId,
        int $campaignId,
        Collection $customers,
        callable $extraColumns,
    ): void {
        if ($count <= 0 || $customers->isEmpty()) {
            return;
        }

        $chunk = [];
        $chunkSize = 150;
        $now = Carbon::now();
        $customerList = $customers->values();

        for ($index = 0; $index < $count; $index++) {
            $customer = $customerList[$index % $customerList->count()];
            $chunk[] = array_merge([
                'user_id' => $userId,
                'campaign_id' => $campaignId,
                'created_at' => $now->copy()->subDays($index % 21)->subHours($index % 24),
                'updated_at' => $now->copy()->subDays($index % 21)->subHours($index % 24),
            ], $extraColumns($customer, $index));

            if (count($chunk) >= $chunkSize) {
                DB::table($table)->insert($chunk);
                $chunk = [];
            }
        }

        if ($chunk !== []) {
            DB::table($table)->insert($chunk);
        }
    }
}
