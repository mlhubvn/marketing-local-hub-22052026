<?php

namespace Modules\APIPartnerFizaHUB\Support;

use InvalidArgumentException;

class OnboardingStatusMachine
{
    public const AWAITING_CONSULTANT = 'awaiting_consultant';

    public const NEEDS_REVIEW = 'needs_review';

    public const IN_CONSULTATION = 'in_consultation';

    /** @deprecated Use IN_CONSULTATION. */
    public const CONSULTING = self::IN_CONSULTATION;

    /** @deprecated Legacy stored status mapped publicly to NEEDS_REVIEW. */
    public const NEEDS_INFORMATION = 'needs_information';

    public const CONFIGURING = 'configuring';

    public const READY = 'ready';

    public const COMPLETED = 'completed';

    public const CANCELLED = 'cancelled';

    /** @var list<string> */
    public const PUBLIC_STATUSES = [
        self::AWAITING_CONSULTANT,
        self::NEEDS_REVIEW,
        self::IN_CONSULTATION,
        self::CONFIGURING,
        self::READY,
        self::COMPLETED,
        self::CANCELLED,
    ];

    /** @var list<string> */
    public const LEGACY_STATUSES = [
        'consulting',
        self::NEEDS_INFORMATION,
        'pending_verification',
        'duplicate_review',
    ];

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            self::AWAITING_CONSULTANT => 'Chờ tư vấn viên liên hệ',
            self::NEEDS_REVIEW => 'Cần kiểm tra',
            self::IN_CONSULTATION => 'Đang tư vấn nhu cầu',
            self::CONFIGURING => 'Đang cấu hình Marketing',
            self::READY => 'Sẵn sàng sử dụng',
            self::COMPLETED => 'Hoàn tất',
            self::CANCELLED => 'Đã hủy',
        ];
    }

    public static function publicStatus(string $status): string
    {
        return match ($status) {
            'consulting' => self::IN_CONSULTATION,
            self::NEEDS_INFORMATION, 'pending_verification', 'duplicate_review' => self::NEEDS_REVIEW,
            default => in_array($status, self::PUBLIC_STATUSES, true)
                ? $status
                : self::AWAITING_CONSULTANT,
        };
    }

    public static function label(string $status): string
    {
        $publicStatus = self::publicStatus($status);

        return self::labels()[$publicStatus] ?? $publicStatus;
    }

    public static function defaultStepFor(string $status): string
    {
        return match (self::publicStatus($status)) {
            self::AWAITING_CONSULTANT, self::NEEDS_REVIEW => self::AWAITING_CONSULTANT,
            self::IN_CONSULTATION => self::IN_CONSULTATION,
            self::CONFIGURING => self::CONFIGURING,
            self::READY, self::COMPLETED => self::READY,
            self::CANCELLED => self::CANCELLED,
            default => self::AWAITING_CONSULTANT,
        };
    }

    /** @return array<string, list<string>> */
    public static function transitions(): array
    {
        return [
            self::AWAITING_CONSULTANT => [self::IN_CONSULTATION, self::NEEDS_REVIEW, self::CANCELLED],
            self::NEEDS_REVIEW => [self::IN_CONSULTATION, self::CANCELLED],
            self::IN_CONSULTATION => [self::NEEDS_REVIEW, self::CONFIGURING, self::CANCELLED],
            self::NEEDS_INFORMATION => [self::IN_CONSULTATION, self::CANCELLED],
            self::CONFIGURING => [self::READY, self::NEEDS_REVIEW, self::CANCELLED],
            self::READY => [self::COMPLETED, self::CONFIGURING, self::CANCELLED],
            self::COMPLETED => [],
            self::CANCELLED => [],
            'consulting' => [self::NEEDS_REVIEW, self::CONFIGURING, self::CANCELLED],
            'pending_verification' => [self::AWAITING_CONSULTANT, self::IN_CONSULTATION, self::NEEDS_REVIEW, self::CANCELLED],
            'duplicate_review' => [self::IN_CONSULTATION, self::NEEDS_REVIEW, self::CANCELLED],
        ];
    }

    public static function canTransition(?string $from, string $to): bool
    {
        if ($from === null || $from === '' || $from === $to) {
            return true;
        }

        return in_array($to, self::transitions()[$from] ?? [], true);
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
        return in_array(self::publicStatus($status), [self::READY, self::COMPLETED], true);
    }

    /** @return list<string> */
    public static function allStatuses(): array
    {
        return array_values(array_unique(array_merge(self::PUBLIC_STATUSES, self::LEGACY_STATUSES)));
    }
}
