<?php

namespace Modules\CustomMLHUB\Support\MLHUBAIAssistant;

class MLHUBAIIntentResolver
{
    protected const MATCH_THRESHOLD = 0.08;

    /**
     * @return array<string, list<string>>
     */
    protected function intentKeywords(): array
    {
        return [
            'new_customers' => [
                'khách mới', 'khach moi', 'customer mới', 'new customer', 'khách hàng mới',
                'tuần này có khách', 'tuan nay co khach', 'có khách mới', 'co khach moi',
            ],
            'campaigns' => [
                'chiến dịch', 'chien dich', 'campaign', 'đang chạy', 'dang chay',
                'tổng hợp chiến dịch', 'tong hop chien dich', 'running campaign',
            ],
            'reviews' => [
                'đánh giá', 'danh gia', 'review', 'sao', 'rating', 'phản hồi review',
                'đánh giá tuần', 'danh gia tuan',
            ],
            'next_steps' => [
                'làm gì', 'lam gi', 'gợi ý', 'goi y', 'next step', 'what should i do',
                'chiến dịch mới', 'chien dich moi', 'đề xuất', 'de xuat', 'nên làm',
            ],
            'overview' => [
                'tổng quan', 'tong quan', 'overview', 'báo cáo', 'bao cao', 'report',
                'tình hình', 'tinh hinh', 'kết quả', 'ket qua',
            ],
            'visits' => [
                'lượt quét', 'luot quet', 'qr scan', 'lượt truy cập', 'luot truy cap',
                'visits', 'traffic',
            ],
            'businesses' => [
                'cơ sở', 'co so', 'danh sách cơ sở', 'danh sach co so', 'doanh nghiệp', 'doanh nghiep',
                'business', 'chi nhánh', 'chi nhanh', 'cửa hàng', 'cua hang', 'địa điểm', 'dia diem',
            ],
        ];
    }

    /**
     * @return array{intent: string, confidence: float}
     */
    public function resolve(string $question): array
    {
        $matches = $this->resolveAll($question);

        if ($matches === []) {
            return ['intent' => 'unknown', 'confidence' => 0.0];
        }

        return $matches[0];
    }

    /**
     * Detect every intent the question touches, strongest first.
     *
     * @return list<array{intent: string, confidence: float}>
     */
    public function resolveAll(string $question): array
    {
        $normalized = mb_strtolower(trim($question));

        if ($normalized === '') {
            return [['intent' => 'overview', 'confidence' => 0.0]];
        }

        $scored = [];

        foreach ($this->intentKeywords() as $intent => $needles) {
            $score = 0.0;

            foreach ($needles as $needle) {
                if (str_contains($normalized, $needle)) {
                    $score += mb_strlen($needle) / max(1, mb_strlen($normalized));
                }
            }

            if ($score >= self::MATCH_THRESHOLD) {
                $scored[] = ['intent' => $intent, 'confidence' => min(1.0, $score * 4)];
            }
        }

        usort($scored, static fn (array $a, array $b): int => $b['confidence'] <=> $a['confidence']);

        return $scored;
    }

    /**
     * @return list<string>
     */
    public function suggestedPrompts(): array
    {
        return [
            __('Any new customers this week?'),
            __('Summarize running campaigns'),
            __('Are this week\'s reviews good?'),
            __('List my businesses'),
            __('What should I do next? / Suggest a new campaign.'),
        ];
    }
}
