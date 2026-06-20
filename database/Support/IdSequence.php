<?php

namespace Database\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class IdSequence
{
    /**
     * Tables whose PK is never referenced by FK — safe to renumber contiguously from STARTING_ID.
     *
     * @var list<string>
     */
    public const RESEQUENCE_TABLES = ['options', 'migrations'];
    public const STARTING_ID = 147123468;

    public static function startingId(): int
    {
        static $startingId = null;

        if ($startingId !== null) {
            return $startingId;
        }

        $path = database_path('config/MLHUB.php');

        if (is_file($path)) {
            $config = require $path;
            $startingId = (int) ($config['starting_id'] ?? self::STARTING_ID);
        } elseif (function_exists('config')) {
            $startingId = (int) config('custommlhub.starting_id', self::STARTING_ID);
        } else {
            $startingId = self::STARTING_ID;
        }

        return $startingId;
    }

    /**
     * Deterministic seeded primary key: starting ID + zero-based offset.
     */
    public static function at(int $offset): int
    {
        return self::startingId() + max(0, $offset);
    }

    /**
     * Remap legacy 1-based IDs from author seed packs to the MLHUB sequence.
     */
    public static function fromLegacy(int $legacyId): int
    {
        return self::at(max(0, $legacyId - 1));
    }

    public static function isUpdateMode(): bool
    {
        return config('mlhub.seeding_mode') === 'update';
    }

    /**
     * PK cho bản ghi seed mới: install = cố định theo offset; update = null (MySQL AUTO_INCREMENT).
     *
     * @return int|null
     */
    public static function idForNewSeed(int $installOffset): ?int
    {
        return self::isUpdateMode() ? null : self::at($installOffset);
    }

    /**
     * PK cho template seed từ legacy pack (install) hoặc AUTO_INCREMENT (update).
     */
    public static function idForNewSeedFromLegacy(int $legacyId): ?int
    {
        if (self::isUpdateMode()) {
            return null;
        }

        return $legacyId >= self::startingId()
            ? $legacyId
            : self::fromLegacy($legacyId);
    }

    /**
     * Renumber rows in $table to contiguous IDs beginning at STARTING_ID (ordered by current PK).
     *
     * Uses a two-phase swap through a temporary high band (above both existing and target IDs)
     * so it works on UNSIGNED primary keys — negative temp IDs would overflow unsigned columns.
     */
    public static function resequenceTableFromStartingId(
        string $table,
        ?int $startingId = null,
        string $columnName = 'id'
    ): bool {
        if (DB::getDriverName() !== 'mysql' || ! Schema::hasTable($table)) {
            return false;
        }

        $startingId ??= self::startingId();
        $rows = DB::table($table)->orderBy($columnName)->pluck($columnName);

        if ($rows->isEmpty()) {
            return false;
        }

        $count = $rows->count();
        $expectedLastId = $startingId + $count - 1;
        $maxOldId = (int) $rows->max();
        $needsResequence = $rows->contains(fn ($id) => (int) $id < $startingId)
            || (int) $rows->first() !== $startingId
            || (int) $rows->last() !== $expectedLastId;

        if (! $needsResequence) {
            return false;
        }

        // Temp band sits above every existing id AND the whole target range, so neither phase collides.
        $tempBase = max($maxOldId, $expectedLastId) + 1;

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ($rows->values() as $index => $oldId) {
                DB::table($table)
                    ->where($columnName, $oldId)
                    ->update([$columnName => $tempBase + $index]);
            }

            foreach ($rows->values() as $index => $_) {
                DB::table($table)
                    ->where($columnName, $tempBase + $index)
                    ->update([$columnName => $startingId + $index]);
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        return true;
    }

    /**
     * Align options + migrations PKs, then refresh AUTO_INCREMENT on all eligible tables.
     */
    public static function normalizeCoreTables(?int $startingId = null): void
    {
        $startingId ??= self::startingId();

        foreach (self::RESEQUENCE_TABLES as $table) {
            self::resequenceTableFromStartingId($table, $startingId);
        }

        self::apply($startingId);
    }

    /**
     * Set AUTO_INCREMENT on every eligible MySQL table so the next insert uses >= STARTING_ID.
     */
    public static function apply(?int $startingId = null): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $startingId ??= self::startingId();
        $database = (string) DB::getDatabaseName();
        $configPath = database_path('config/MLHUB.php');
        $excluded = is_file($configPath)
            ? (array) ((require $configPath)['id_sequence_excluded_tables'] ?? [])
            : [];

        $tables = DB::select(
            'SELECT TABLE_NAME AS name FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = ?',
            [$database, 'BASE TABLE']
        );

        foreach ($tables as $row) {
            $table = (string) $row->name;

            if (in_array($table, $excluded, true)) {
                continue;
            }

            $column = DB::selectOne(
                'SELECT COLUMN_NAME AS name FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_KEY = ? AND EXTRA LIKE ? LIMIT 1',
                [$database, $table, 'PRI', '%auto_increment%']
            );

            if ($column === null) {
                continue;
            }

            $columnName = (string) $column->name;
            $maxId = DB::table($table)->max($columnName);
            $next = $maxId !== null
                ? max($startingId, ((int) $maxId) + 1)
                : $startingId;

            DB::statement(sprintf(
                'ALTER TABLE `%s` AUTO_INCREMENT = %d',
                str_replace('`', '``', $table),
                $next
            ));
        }
    }
}
