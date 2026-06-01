<?php

namespace Database\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\AdminFaker\Support\MLHUBDemoSeedProgress;

class MLHUBDemoVolume
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
        int $maxDaysAgo = 28,
        ?string $progressLabel = null,
    ): void {
        if ($count <= 0) {
            return;
        }

        $chunkSize = 1500;
        $maxDaysAgo = max(7, $maxDaysAgo);
        $city = $cities[$campaignIndex % max(1, count($cities))];
        $ipSecond = ($campaignId % 200) + 1;
        $now = time();
        $inserted = 0;
        $reportEvery = 50000;

        for ($offset = 1; $offset <= $count; $offset += $chunkSize) {
            $chunk = [];
            $end = min($count, $offset + $chunkSize - 1);

            for ($i = $offset; $i <= $end; $i++) {
                $daysAgo = $i % $maxDaysAgo;
                $hours = $i % 24;
                $minutes = ((int) floor($daysAgo / 30)) * 3;
                $createdAt = date('Y-m-d H:i:s', $now - ($daysAgo * 86400) - ($hours * 3600) - ($minutes * 60));

                $chunk[] = [
                    'user_id' => $userId,
                    'campaign_id' => $campaignId,
                    'ip_address' => sprintf('103.%d.%d.%d', $ipSecond, ($i % 250) + 1, ($i % 200) + 10),
                    'user_agent' => $i % 3 === 0 ? 'Mobile Safari Demo' : 'Chrome Desktop Demo',
                    'device' => $i % 3 === 0 ? 'mobile' : 'desktop',
                    'city' => $city,
                    'country' => $country,
                    'created_at' => $createdAt,
                ];
            }

            DB::table('lb_qr_scans')->insert($chunk);
            $inserted = $end;

            if ($progressLabel !== null && ($inserted % $reportEvery === 0 || $inserted === $count)) {
                MLHUBDemoSeedProgress::line($progressLabel.' — '.number_format($inserted).'/'.number_format($count).' quét');
            }
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
        int $maxDaysAgo = 21,
    ): void {
        self::bulkEngagement('lb_lead_submissions', $count, $userId, $campaignId, $customers, function (object $customer, int $index) use ($message, $source): array {
            return [
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'message' => $message,
                'payload' => json_encode(['source' => $source], JSON_UNESCAPED_UNICODE),
            ];
        }, $maxDaysAgo);
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
        int $maxDaysAgo = 21,
    ): void {
        $statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        $times = ['09:00', '10:00', '14:00', '15:00', '16:00'];

        self::bulkEngagement('lb_bookings', $count, $userId, $campaignId, $customers, function (object $customer, int $index) use ($serviceId, $note, $statuses, $times): array {
            return [
                'service_id' => $serviceId,
                'status' => $statuses[$index % count($statuses)],
                'booking_date' => date('Y-m-d', time() + (($index % 14) + 1) * 86400),
                'booking_time' => $times[$index % count($times)],
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_email' => $customer->email,
                'note' => $note,
            ];
        }, $maxDaysAgo);
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
        int $maxDaysAgo = 21,
    ): void {
        self::bulkEngagement('lb_coupon_redemptions', $count, $userId, $campaignId, $customers, function (object $customer, int $index) use ($codePrefix, $campaignId): array {
            $used = $index % 5 === 0;

            return [
                'code' => $codePrefix.'-'.$campaignId.'-'.str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT),
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_email' => $customer->email,
                'status' => $used ? 'used' : 'claimed',
                'used_at' => $used ? date('Y-m-d H:i:s', time() - (($index + 1) * 3600)) : null,
            ];
        }, $maxDaysAgo);
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
        int $maxDaysAgo = 21,
    ): void {
        self::bulkEngagement('lb_review_feedbacks', $count, $userId, $campaignId, $customers, function (object $customer, int $index) use ($positiveMessage, $negativeMessage): array {
            $rating = $index % 10 < 8 ? 4 + ($index % 2) : 2 + ($index % 2);

            return [
                'rating' => $rating,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_email' => $customer->email,
                'message' => $rating >= 4 ? $positiveMessage : $negativeMessage,
                'status' => $rating <= 3 ? 'new' : 'replied',
            ];
        }, $maxDaysAgo);
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
        int $maxDaysAgo = 21,
    ): void {
        self::bulkEngagement('lb_feedback_responses', $count, $userId, $campaignId, $customers, function (object $customer, int $index) use ($positiveMessage, $negativeMessage, $source): array {
            $rating = $index % 10 < 7 ? 4 + ($index % 2) : 2 + ($index % 2);

            return [
                'rating' => $rating,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_email' => $customer->email,
                'message' => $rating >= 4 ? $positiveMessage : $negativeMessage,
                'payload' => json_encode(['source' => $source], JSON_UNESCAPED_UNICODE),
                'status' => $rating <= 3 ? 'new' : 'resolved',
                'resolved_at' => $rating >= 4 ? date('Y-m-d H:i:s', time() - 86400) : null,
            ];
        }, $maxDaysAgo);
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
        int $maxDaysAgo = 21,
    ): void {
        if ($count <= 0 || $customers->isEmpty()) {
            return;
        }

        $chunk = [];
        $chunkSize = 800;
        $now = time();
        $customerList = $customers->values();
        $maxDaysAgo = max(7, $maxDaysAgo);
        $customerTotal = max(1, $customerList->count());

        for ($index = 0; $index < $count; $index++) {
            $customer = $customerList[$index % $customerTotal];
            $daysAgo = $index % $maxDaysAgo;
            $timestamp = date('Y-m-d H:i:s', $now - ($daysAgo * 86400) - (($index % 24) * 3600));

            $chunk[] = array_merge([
                'user_id' => $userId,
                'campaign_id' => $campaignId,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
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
