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
            'greeting' => [
                'xin chào', 'xin chao', 'chào', 'chao', 'hello', 'helo', 'hallo',
                'alo', 'hi bạn', 'hi ban',
            ],
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
     * Starter questions shown before the first answer.
     *
     * @return list<string>
     */
    public function suggestedPrompts(): array
    {
        return $this->initialPrompts();
    }

    /**
     * @return list<string>
     */
    public function initialPrompts(): array
    {
        return [
            __('Any new customers this week?'),
            __('Summarize running campaigns'),
            __('Are this week\'s reviews good?'),
            __('List my businesses'),
            __('What should I do next? / Suggest a new campaign.'),
        ];
    }

    /**
     * Contextual follow-ups: ~60% drill into the same topic, ~40% explore new directions.
     *
     * @return list<string>
     */
    public function followUps(string $intent): array
    {
        $deepen = $this->deepenPrompts()[$intent] ?? [];

        if ($deepen === []) {
            return $this->initialPrompts();
        }

        $explore = $this->explorePrompts($intent);

        $picked = array_slice($deepen, 0, 3);
        $picked = array_merge($picked, array_slice($explore, 0, 2));

        $picked = array_values(array_unique(array_filter($picked)));

        return $picked === [] ? $this->initialPrompts() : $picked;
    }

    /**
     * Drill-down questions per topic.
     *
     * @return array<string, list<string>>
     */
    protected function deepenPrompts(): array
    {
        return [
            'new_customers' => [
                __('Where did the new customers come from?'),
                __('How does it compare to last week?'),
                __('How can I get more new customers?'),
            ],
            'campaigns' => [
                __('Which campaign performs best?'),
                __('Which campaign needs improvement?'),
                __('How do I create a new campaign?'),
            ],
            'reviews' => [
                __('Which reviews need a reply?'),
                __('How can I get more 5-star reviews?'),
                __('What is my average rating?'),
            ],
            'visits' => [
                __('Where do the visits come from?'),
                __('What is my conversion rate?'),
                __('How can I get more QR scans?'),
            ],
            'businesses' => [
                __('Which business performs best?'),
                __('How do I add a new business?'),
                __('Where do I update business info?'),
            ],
            'next_steps' => [
                __('Suggest a weekend campaign'),
                __('What should I prioritize first?'),
                __('How can I grow revenue quickly?'),
            ],
            'overview' => [
                __('Which metric is dropping?'),
                __('What stood out this week?'),
                __('What should I do next? / Suggest a new campaign.'),
            ],
            'greeting' => [],
        ];
    }

    /**
     * Pool of starter questions for other topics, excluding the current intent.
     *
     * @return list<string>
     */
    protected function explorePrompts(string $intent): array
    {
        $pool = [
            'new_customers' => __('Any new customers this week?'),
            'campaigns' => __('Summarize running campaigns'),
            'reviews' => __('Are this week\'s reviews good?'),
            'businesses' => __('List my businesses'),
            'visits' => __('How are visits doing?'),
            'next_steps' => __('What should I do next? / Suggest a new campaign.'),
        ];

        unset($pool[$intent]);

        return array_values($pool);
    }
}
