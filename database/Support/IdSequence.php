<?php

namespace Database\Support;

use Illuminate\Support\Facades\DB;

class IdSequence
{
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
            ? (array) ((require $configPath)['id_sequence_excluded_tables'] ?? ['migrations'])
            : ['migrations'];

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
