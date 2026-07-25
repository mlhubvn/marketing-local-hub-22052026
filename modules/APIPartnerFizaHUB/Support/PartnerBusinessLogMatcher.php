<?php

namespace Modules\APIPartnerFizaHUB\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Schema;

/**
 * Builds the WHERE scope that identifies every `partner_api_logs` /
 * `partner_webhook_outbox` row that belongs to one partner business.
 *
 * This class only ever *identifies* rows — it never calls delete()/update() itself.
 * The purge service (OnboardingAdminService) decides to delete(); the residue
 * inspector (UserDeletionResidueInspector) decides to count()/exists(). Both reuse
 * the exact same matching rules, so a query cannot be correct in one place and wrong
 * in the other.
 *
 * `partner_api_logs` and `partner_webhook_outbox` do NOT share a schema — read the
 * migrations before touching this file:
 *   - partner_api_logs:      partner_code, method, endpoint, request_id,
 *                             request_payload (json), response_payload (json).
 *   - partner_webhook_outbox: partner_code, event_type, dedupe_key, endpoint_path,
 *                             payload (json, single — no separate response).
 * Each `applyTo*` method below only references the columns that actually exist on
 * that table; they are intentionally NOT unified into one generic "log" matcher.
 */
final class PartnerBusinessLogMatcher
{
    /**
     * MySQL/SQLite-portable LIKE escape character. Deliberately not backslash: MySQL
     * string literals interpret `\\` specially while SQLite does not, so a backslash
     * escape char behaves differently per driver. `!` needs no special quoting in
     * either dialect, so escaping stays correct on both the test (sqlite) and
     * production (MySQL) database.
     */
    private const LIKE_ESCAPE_CHAR = '!';

    public static function applyToApiLogs(Builder $query, PartnerBusinessIdentifiers $identifiers): Builder
    {
        return self::scoped($query, 'partner_api_logs', $identifiers, static function (Builder $builder, string $table) use ($identifiers): bool {
            $matched = false;

            if ($identifiers->hasExternalBusinessId()) {
                if (Schema::hasColumn($table, 'endpoint')) {
                    $matched = self::orEndpointBoundary($builder, 'endpoint', $identifiers->externalBusinessId) || $matched;
                }

                if (Schema::hasColumn($table, 'request_payload')) {
                    $matched = self::orJsonPath($builder, 'request_payload', ['body', 'external_business_id'], $identifiers->externalBusinessId) || $matched;
                    $matched = self::orJsonPath($builder, 'request_payload', ['external_business_id'], $identifiers->externalBusinessId) || $matched;
                }

                if (Schema::hasColumn($table, 'response_payload')) {
                    $matched = self::orJsonPath($builder, 'response_payload', ['data', 'external_business_id'], $identifiers->externalBusinessId) || $matched;
                    $matched = self::orJsonPath($builder, 'response_payload', ['data', 'business', 'external_business_id'], $identifiers->externalBusinessId) || $matched;
                }
            }

            if (Schema::hasColumn($table, 'request_id')) {
                $requestIds = $identifiers->normalizedRequestIds();

                if ($requestIds !== []) {
                    $builder->orWhereIn('request_id', $requestIds);
                    $matched = true;
                }
            }

            return $matched;
        });
    }

    public static function applyToWebhookOutbox(Builder $query, PartnerBusinessIdentifiers $identifiers): Builder
    {
        return self::scoped($query, 'partner_webhook_outbox', $identifiers, static function (Builder $builder, string $table) use ($identifiers): bool {
            $matched = false;

            if ($identifiers->hasExternalBusinessId()) {
                if (Schema::hasColumn($table, 'endpoint_path')) {
                    $matched = self::orEndpointBoundary($builder, 'endpoint_path', $identifiers->externalBusinessId) || $matched;
                }

                if (Schema::hasColumn($table, 'payload')) {
                    $matched = self::orJsonPath($builder, 'payload', ['external_business_id'], $identifiers->externalBusinessId) || $matched;
                }
            }

            if (Schema::hasColumn($table, 'dedupe_key')) {
                foreach ($identifiers->normalizedRequestIds() as $requestId) {
                    $builder->orWhere(
                        'dedupe_key',
                        'like',
                        self::escapeLikeValue($requestId).'%'
                    );
                    $matched = true;
                }
            }

            return $matched;
        });
    }

    /**
     * Applies the mandatory `partner_code` scope (when the table has that column),
     * then the caller-supplied OR-group of business identifier conditions. If the
     * caller reports that no identifier condition was added at all (every identifier
     * was empty), a `1 = 0` clause is forced so a mistaken/unscoped call can never
     * match — let alone delete — every row for the table.
     *
     * @param  callable(Builder, string): bool  $addConditions
     */
    private static function scoped(
        Builder $query,
        string $table,
        PartnerBusinessIdentifiers $identifiers,
        callable $addConditions
    ): Builder {
        if (Schema::hasColumn($table, 'partner_code')) {
            if (! $identifiers->hasPartnerCode()) {
                return $query->whereRaw('1 = 0');
            }

            $query->where('partner_code', $identifiers->partnerCode);
        }

        $hasAnyClause = false;

        $query->where(function (Builder $builder) use ($addConditions, $table, &$hasAnyClause): void {
            $hasAnyClause = (bool) $addConditions($builder, $table);
        });

        if (! $hasAnyClause) {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    /**
     * Matches an `endpoint`/`endpoint_path` column containing `/businesses/{id}` as a
     * boundary-anchored path segment. Matches:
     *   .../businesses/155
     *   .../businesses/155/
     *   .../businesses/155/dashboard
     *   .../businesses/155?include=package
     * Never matches .../businesses/1155 or .../businesses/1550, because every LIKE
     * pattern below requires the character immediately after the id to be the end of
     * string, `/`, or `?` — an id that merely starts with the same digits cannot
     * satisfy any of the three patterns.
     */
    private static function orEndpointBoundary(Builder $query, string $column, string $businessId): bool
    {
        $businessId = trim($businessId);

        if ($businessId === '') {
            return false;
        }

        $escaped = self::escapeLikeValue($businessId);

        $query->orWhere(function (Builder $nested) use ($column, $escaped): void {
            self::likeEscaped($nested, $column, '%/businesses/'.$escaped, 'or');
            self::likeEscaped($nested, $column, '%/businesses/'.$escaped.'/%', 'or');
            self::likeEscaped($nested, $column, '%/businesses/'.$escaped.'?%', 'or');
        });

        return true;
    }

    private static function likeEscaped(Builder $query, string $column, string $pattern, string $boolean = 'and'): void
    {
        $method = $boolean === 'or' ? 'orWhereRaw' : 'whereRaw';
        $query->{$method}(
            $column." LIKE ? ESCAPE '".self::LIKE_ESCAPE_CHAR."'",
            [$pattern]
        );
    }

    /**
     * Matches a JSON column at an exact path (never a `%needle%` scan of the whole
     * document). MySQL's `json_unquote(json_extract(...))` always yields plain text,
     * so a JSON string "155" and a JSON number 155 both compare equal to the bound
     * string value there. SQLite's `json_extract()` does NOT unquote/stringify —
     * a JSON number is returned with SQLite storage class INTEGER/REAL, which never
     * compares equal to a bound TEXT value (SQLite orders by storage class, not
     * value). So when the identifier looks numeric we also bind an int/float
     * comparison — redundant but harmless on MySQL, required for the numeric case on
     * SQLite (used by the test suite).
     *
     * @param  list<string>  $path
     */
    private static function orJsonPath(Builder $query, string $column, array $path, string $value): bool
    {
        $value = trim($value);

        if ($value === '' || $path === []) {
            return false;
        }

        $selector = $column.'->'.implode('->', $path);

        $query->orWhere($selector, $value);

        if (is_numeric($value)) {
            $query->orWhere($selector, str_contains($value, '.') ? (float) $value : (int) $value);
        }

        return true;
    }

    private static function escapeLikeValue(string $value): string
    {
        $escape = self::LIKE_ESCAPE_CHAR;

        return str_replace(
            [$escape, '%', '_'],
            [$escape.$escape, $escape.'%', $escape.'_'],
            $value
        );
    }
}
