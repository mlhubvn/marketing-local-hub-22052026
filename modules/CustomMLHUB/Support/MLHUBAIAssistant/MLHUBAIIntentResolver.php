<?php

namespace Modules\CustomMLHUB\Support\MLHUBAIAssistant;

class MLHUBAIIntentResolver
{
    protected const MATCH_THRESHOLD = 0.08;

    /**
     * @return array{intent: string, confidence: float, matched_keywords: list<string>}
     */
    public function resolve(string $question): array
    {
        $matches = $this->resolveAll($question);

        if ($matches === []) {
            return ['intent' => 'unknown', 'confidence' => 0.0, 'matched_keywords' => []];
        }

        return $matches[0];
    }

    /**
     * Detect every intent the question touches, strongest first.
     *
     * @return list<array{intent: string, confidence: float, matched_keywords: list<string>}>
     */
    public function resolveAll(string $question): array
    {
        $normalized = $this->normalize($question);

        if ($normalized === '') {
            return [['intent' => 'overview', 'confidence' => 0.0, 'matched_keywords' => []]];
        }

        $scored = [];

        foreach (MLHUBAIKnowledgeBase::intentKeywords() as $intent => $needles) {
            $score = 0.0;
            $matched = [];
            $seenNeedles = [];

            foreach ($needles as $needle) {
                $normalizedNeedle = $this->normalize($needle);

                if ($normalizedNeedle === '' || isset($seenNeedles[$normalizedNeedle])) {
                    continue;
                }

                $seenNeedles[$normalizedNeedle] = true;

                if (str_contains($normalized, $normalizedNeedle)) {
                    $matched[] = mb_strtolower($needle);
                    $score += max(0.08, mb_strlen($normalizedNeedle) / max(1, mb_strlen($normalized)));
                }
            }

            if ($score >= self::MATCH_THRESHOLD) {
                $scored[] = [
                    'intent' => $intent,
                    'confidence' => min(1.0, $score * 4),
                    'matched_keywords' => array_values(array_unique($matched)),
                ];
            }
        }

        $scored = $this->focusEverydayQuestion($scored, $normalized);
        $scored = $this->removeGenericNextSteps($scored);
        $scored = $this->removeLooseDailyBriefing($scored);
        $scored = $this->removeReviewDuplication($scored);

        $this->sortMatches($scored, $this->isNavigationQuestion($normalized));

        if ($this->isNavigationQuestion($normalized) && ! $this->asksForMetricBlend($normalized)) {
            $scored = array_slice($scored, 0, 1);
        }

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
        return MLHUBAIKnowledgeBase::initialPrompts();
    }

    /**
     * Contextual follow-ups: ~60% drill into the same topic, ~40% explore new directions.
     *
     * @return list<string>
     */
    public function followUps(string $intent): array
    {
        $deepen = MLHUBAIKnowledgeBase::deepenPrompts()[$intent] ?? [];

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
     * Pool of starter questions for other topics, excluding the current intent.
     *
     * @return list<string>
     */
    protected function explorePrompts(string $intent): array
    {
        $pool = MLHUBAIKnowledgeBase::explorePrompts();

        unset($pool[$intent]);

        return array_values($pool);
    }

    /**
     * @param  list<array{intent: string, confidence: float, matched_keywords: list<string>}>  $matches
     * @return list<array{intent: string, confidence: float, matched_keywords: list<string>}>
     */
    protected function removeGenericNextSteps(array $matches): array
    {
        $hasSpecificIntent = collect($matches)
            ->contains(fn (array $match): bool => ! in_array($match['intent'], ['greeting', 'next_steps'], true));

        if (! $hasSpecificIntent) {
            return $matches;
        }

        $genericNextStepKeywords = ['làm gì', 'lam gi', 'nên làm', 'nen lam', 'gợi ý', 'goi y', 'đề xuất', 'de xuat', 'what should i do'];

        return array_values(array_filter($matches, function (array $match) use ($genericNextStepKeywords): bool {
            if ($match['intent'] !== 'next_steps') {
                return true;
            }

            $matched = array_map(fn (string $keyword): string => $this->normalize($keyword), (array) ($match['matched_keywords'] ?? []));

            return array_diff($matched, array_map(fn (string $keyword): string => $this->normalize($keyword), $genericNextStepKeywords)) !== [];
        }));
    }

    /**
     * @param  list<array{intent: string, confidence: float, matched_keywords: list<string>}>  $matches
     * @return list<array{intent: string, confidence: float, matched_keywords: list<string>}>
     */
    protected function focusEverydayQuestion(array $matches, string $normalized): array
    {
        $focus = match (true) {
            $this->containsAny($normalized, [
                'tao co so truoc hay tao chien dich truoc',
                'tao co so kinh doanh truoc hay tao chien dich truoc',
                'nen dung tinh nang nao dau tien',
                'quan ca phe',
                'quan cafe',
                'quan an',
                'nha hang',
                'spa',
                'salon',
                'ban le',
            ]) => ['onboarding'],
            $this->containsAny($normalized, [
                'de lai so dien thoai',
                'lay so dien thoai',
                'thu so dien thoai',
                'form tu van',
            ]) => ['leads'],
            $this->containsAny($normalized, [
                'kenh nao mang khach',
                'nguon nao mang khach',
                'kenh hieu qua',
                'nguon hieu qua',
            ]) => ['conversion'],
            $this->containsAny($normalized, [
                'danh gia thap',
                'rating thap',
                'khach khong hai long',
            ]) => ['feedback'],
            $this->containsAny($normalized, [
                'it khach quay lai',
                'khach it quay lai',
                'khach cu quay lai',
                'cham soc khach cu',
            ]) => ['crm_segments', 'coupon'],
            default => [],
        };

        if ($focus === []) {
            return $matches;
        }

        $focused = array_values(array_filter(
            $matches,
            static fn (array $match): bool => in_array($match['intent'], $focus, true),
        ));

        return $focused === [] ? $matches : $focused;
    }

    /**
     * @param  list<array{intent: string, confidence: float, matched_keywords: list<string>}>  $matches
     * @return list<array{intent: string, confidence: float, matched_keywords: list<string>}>
     */
    protected function removeReviewDuplication(array $matches): array
    {
        $hasGoogleReviews = collect($matches)->contains(fn (array $match): bool => $match['intent'] === 'google_reviews');

        if (! $hasGoogleReviews) {
            return $matches;
        }

        return array_values(array_filter(
            $matches,
            static fn (array $match): bool => ! in_array($match['intent'], ['reviews', 'review_booster'], true),
        ));
    }

    /**
     * @param  list<array{intent: string, confidence: float, matched_keywords: list<string>}>  $matches
     * @return list<array{intent: string, confidence: float, matched_keywords: list<string>}>
     */
    protected function removeLooseDailyBriefing(array $matches): array
    {
        $hasSpecificOperationalIntent = collect($matches)
            ->contains(fn (array $match): bool => ! in_array($match['intent'], ['daily_briefing', 'overview', 'greeting', 'next_steps'], true));

        if (! $hasSpecificOperationalIntent) {
            return $matches;
        }

        return array_values(array_filter($matches, function (array $match): bool {
            if ($match['intent'] !== 'daily_briefing') {
                return true;
            }

            $matched = array_map(fn (string $keyword): string => $this->normalize($keyword), (array) ($match['matched_keywords'] ?? []));
            $strongDailyKeywords = ['hom nay', 'sang nay', 'bao cao ngay', 'daily', 'briefing', 'tinh hinh hom nay'];

            return array_intersect($matched, $strongDailyKeywords) !== [];
        }));
    }

    /**
     * @param  list<array{intent: string, confidence: float, matched_keywords: list<string>}>  $matches
     */
    protected function sortMatches(array &$matches, bool $preferConfidence): void
    {
        usort($matches, function (array $a, array $b) use ($preferConfidence): int {
            if ($preferConfidence) {
                $confidence = $b['confidence'] <=> $a['confidence'];

                if ($confidence !== 0) {
                    return $confidence;
                }
            }

            $priority = MLHUBAIKnowledgeBase::intentPriority((string) $a['intent'])
                <=> MLHUBAIKnowledgeBase::intentPriority((string) $b['intent']);

            if ($priority !== 0) {
                return $priority;
            }

            return $b['confidence'] <=> $a['confidence'];
        });
    }

    protected function isNavigationQuestion(string $normalized): bool
    {
        foreach ([
            'o dau',
            'vao dau',
            'mo cho nao',
            'nam o dau',
            'xem ',
            'tao ',
            'lam sao tao',
            'lam sao mo',
            'cap nhat ',
            'dung tinh nang nao',
            'dung tinh nang nao dau tien',
        ] as $pattern) {
            if (str_contains($normalized, $pattern)) {
                return true;
            }
        }

        return false;
    }

    protected function asksForMetricBlend(string $normalized): bool
    {
        foreach ([
            'bao cao tong quan',
            'tong quan',
            'tat ca chi so',
            'toan bo chi so',
            'nhieu chi so',
        ] as $pattern) {
            if (str_contains($normalized, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<string>  $needles
     */
    protected function containsAny(string $normalized, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($normalized, $needle)) {
                return true;
            }
        }

        return false;
    }

    protected function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = strtr($value, [
            'à' => 'a', 'á' => 'a', 'ạ' => 'a', 'ả' => 'a', 'ã' => 'a',
            'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ậ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a',
            'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ặ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a',
            'è' => 'e', 'é' => 'e', 'ẹ' => 'e', 'ẻ' => 'e', 'ẽ' => 'e',
            'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ệ' => 'e', 'ể' => 'e', 'ễ' => 'e',
            'ì' => 'i', 'í' => 'i', 'ị' => 'i', 'ỉ' => 'i', 'ĩ' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ọ' => 'o', 'ỏ' => 'o', 'õ' => 'o',
            'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ộ' => 'o', 'ổ' => 'o', 'ỗ' => 'o',
            'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ợ' => 'o', 'ở' => 'o', 'ỡ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'ụ' => 'u', 'ủ' => 'u', 'ũ' => 'u',
            'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ự' => 'u', 'ử' => 'u', 'ữ' => 'u',
            'ỳ' => 'y', 'ý' => 'y', 'ỵ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y',
            'đ' => 'd',
        ]);

        return trim((string) preg_replace('/\s+/u', ' ', $value));
    }
}
