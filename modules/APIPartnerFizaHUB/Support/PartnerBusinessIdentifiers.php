<?php

namespace Modules\APIPartnerFizaHUB\Support;

/**
 * Immutable identifier bundle used to scope FizaHUB log purge/residue queries to a
 * single partner business. Never widen these fields with a free-text search value —
 * every field here is matched with an explicit boundary (exact equality, JSON path
 * equality, or anchored LIKE), never a bare `%needle%` substring search.
 */
final class PartnerBusinessIdentifiers
{
    /**
     * @param  list<string>  $requestIds  Onboarding-issued request UUIDs
     *                                    (`partner_onboarding_requests.request_id`).
     *                                    NOT the same value as the per-call
     *                                    `X-Request-Id` header stored on
     *                                    `partner_api_logs.request_id` — those only
     *                                    coincide for the initial onboarding-create call.
     */
    public function __construct(
        public readonly string $partnerCode,
        public readonly string $externalBusinessId,
        public readonly array $requestIds = [],
    ) {}

    public function hasPartnerCode(): bool
    {
        return trim($this->partnerCode) !== '';
    }

    public function hasExternalBusinessId(): bool
    {
        return trim($this->externalBusinessId) !== '';
    }

    /**
     * @return list<string>
     */
    public function normalizedRequestIds(): array
    {
        return array_values(array_unique(array_filter(
            array_map(static fn ($id): string => trim((string) $id), $this->requestIds),
            static fn (string $id): bool => $id !== ''
        )));
    }

    public function hasAnyIdentifier(): bool
    {
        return $this->hasExternalBusinessId() || $this->normalizedRequestIds() !== [];
    }
}
