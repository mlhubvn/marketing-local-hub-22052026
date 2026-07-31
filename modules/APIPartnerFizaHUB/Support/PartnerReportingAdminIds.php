<?php

namespace Modules\APIPartnerFizaHUB\Support;

/**
 * Pure parser for the `FIZAHUB_ADMIN` env value (comma-separated MLHUB `users.id` allowlist
 * for the FizaHUB Partner Reporting Portal). Kept as a standalone, testable class so
 * `config/config.php` stays the only place calling `env()` (required for `config:cache`
 * compatibility) while the parsing rules themselves have direct unit-test coverage.
 */
class PartnerReportingAdminIds
{
    /**
     * @return list<int> unique, positive user IDs, order preserved from the input string.
     *                    Whitespace around each ID is trimmed; empty segments (including a
     *                    fully blank/empty string) and any non-positive-integer value are
     *                    silently dropped. An empty or entirely invalid input yields `[]`,
     *                    which the reporting portal's access middleware treats as
     *                    "no one is allowed in" by design.
     */
    public static function parse(string $raw): array
    {
        $segments = array_map('trim', explode(',', $raw));

        $validIds = array_filter(
            $segments,
            static fn (string $id): bool => $id !== '' && ctype_digit($id)
        );

        $ids = array_map(static fn (string $id): int => (int) $id, $validIds);

        return array_values(array_unique(array_filter(
            $ids,
            static fn (int $id): bool => $id > 0
        )));
    }
}
