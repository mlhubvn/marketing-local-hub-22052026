<?php

namespace Modules\APIPartnerFizaHUB\Support;

use InvalidArgumentException;

class OnboardingStatusMachine
{
    public const AWAITING_CONSULTANT = 'awaiting_consultant';

    public const CONSULTING = 'consulting';

    public const NEEDS_INFORMATION = 'needs_information';

    public const CONFIGURING = 'configuring';

    public const READY = 'ready';

    public const COMPLETED = 'completed';

    public const NEEDS_REVIEW = 'needs_review';

    public const CANCELLED = 'cancelled';

    /** @var list<string> */
    public const LEGACY_STATUSES = [
        'pending_verification',
        'duplicate_review',
    ];

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::AWAITING_CONSULTANT => 'Chờ tư vấn viên liên hệ',
            self::CONSULTING => 'Đang tư vấn',
            self::NEEDS_INFORMATION => 'Cần bổ sung thông tin',
            self::CONFIGURING => 'Đang cấu hình',
            self::READY => 'Sẵn sàng sử dụng',
            self::COMPLETED => 'Hoàn tất',
            self::NEEDS_REVIEW => 'Cần kiểm tra',
            self::CANCELLED => 'Đã hủy',
            'pending_verification' => 'Chờ xác minh',
            'duplicate_review' => 'Cần kiểm tra trùng',
        ];
    }

    public static function label(string $status): string
    {
        return self::labels()[$status] ?? $status;
    }

    public static function defaultStepFor(string $status): string
    {
        return match ($status) {
            self::AWAITING_CONSULTANT => 'consultant_contact',
            self::CONSULTING => 'consulting',
            self::NEEDS_INFORMATION => 'needs_information',
            self::CONFIGURING => 'configuring',
            self::READY => 'ready',
            self::COMPLETED => 'completed',
            self::NEEDS_REVIEW => 'needs_review',
            self::CANCELLED => 'cancelled',
            'pending_verification' => 'verification',
            'duplicate_review' => 'duplicate_review',
            default => 'consultant_contact',
        };
    }

    /**
     * @return array<string, list<string>>
     */
    public static function transitions(): array
    {
        return [
            self::AWAITING_CONSULTANT => [self::CONSULTING, self::NEEDS_REVIEW, self::CANCELLED],
            self::CONSULTING => [
                self::NEEDS_INFORMATION,
                self::CONFIGURING,
                self::CANCELLED,
            ],
            self::NEEDS_INFORMATION => [self::CONSULTING, self::CANCELLED],
            self::NEEDS_REVIEW => [self::CONSULTING, self::CANCELLED],
            self::CONFIGURING => [self::READY, self::NEEDS_INFORMATION, self::CANCELLED],
            self::READY => [self::COMPLETED, self::CONFIGURING, self::CANCELLED],
            self::COMPLETED => [],
            self::CANCELLED => [],
            'pending_verification' => [self::AWAITING_CONSULTANT, self::CONSULTING, self::NEEDS_REVIEW, self::CANCELLED],
            'duplicate_review' => [self::CONSULTING, self::NEEDS_REVIEW, self::CANCELLED],
        ];
    }

    public static function canTransition(?string $from, string $to): bool
    {
        if ($from === null || $from === '') {
            return true;
        }

        if ($from === $to) {
            return true;
        }

        $allowed = self::transitions()[$from] ?? [];

        return in_array($to, $allowed, true);
    }

    public static function assertCanTransition(?string $from, string $to): void
    {
        if (! self::canTransition($from, $to)) {
            throw new InvalidArgumentException(
                sprintf('Invalid onboarding status transition from [%s] to [%s].', (string) $from, $to)
            );
        }
    }

    public static function allowsOneTimeLogin(string $status): bool
    {
        return in_array($status, [self::READY, self::COMPLETED], true);
    }

    /**
     * @return list<string>
     */
    public static function allStatuses(): array
    {
        return array_values(array_unique(array_merge(
            array_keys(self::labels()),
            self::LEGACY_STATUSES
        )));
    }
}
