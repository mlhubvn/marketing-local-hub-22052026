<?php

namespace Modules\CustomDatabaseSequence\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class SetIdSequenceCommand extends Command
{
    protected $signature = 'custom:set-id-sequence
                            {--start= : Override the starting ID (defaults to config value)}
                            {--only=* : Only apply to these tables (space separated)}
                            {--exclude=* : Additional tables to skip on top of the config list}
                            {--dry-run : Show the SQL that would run without executing it}
                            {--quiet-skip : Hide log lines for skipped tables}';

    protected $description = 'Force AUTO_INCREMENT >= configured starting ID on every eligible InnoDB table.';

    public function handle(): int
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if ($driver !== 'mysql') {
            $this->error("This command only supports MySQL / MariaDB. Current driver: {$driver}.");

            return self::FAILURE;
        }

        $start = (int) ($this->option('start')
            ?: config('modules.customdatabasesequence.starting_id', 147123468));

        if ($start < 1) {
            $this->error('Starting ID must be a positive integer.');

            return self::INVALID;
        }

        $excluded = array_unique(array_merge(
            (array) config('modules.customdatabasesequence.excluded_tables', []),
            (array) $this->option('exclude'),
        ));

        $only = (array) $this->option('only');
        $dryRun = (bool) $this->option('dry-run');
        $quietSkip = (bool) $this->option('quiet-skip');

        $database = $connection->getDatabaseName();

        try {
            $rows = $connection->select(
                'SELECT TABLE_NAME, AUTO_INCREMENT
                   FROM information_schema.TABLES
                  WHERE TABLE_SCHEMA = ?
                    AND ENGINE = ?
                    AND AUTO_INCREMENT IS NOT NULL
               ORDER BY TABLE_NAME',
                [$database, 'InnoDB']
            );
        } catch (Throwable $e) {
            $this->error('Failed to read information_schema: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->line('');
        $this->info(sprintf(
            'Database: %s   Starting ID: %s   Mode: %s',
            $database,
            number_format($start),
            $dryRun ? 'DRY RUN' : 'APPLY'
        ));
        $this->line(str_repeat('-', 72));

        $changed = 0;
        $skipped = 0;
        $alreadyOk = 0;

        foreach ($rows as $row) {
            $table = $row->TABLE_NAME;
            $current = (int) ($row->AUTO_INCREMENT ?? 0);

            if (in_array($table, $excluded, true)) {
                if (! $quietSkip) {
                    $this->line(sprintf('  <fg=gray>skip</>     %-40s (excluded)', $table));
                }
                $skipped++;
                continue;
            }

            if ($only !== [] && ! in_array($table, $only, true)) {
                continue;
            }

            if ($current >= $start) {
                if (! $quietSkip) {
                    $this->line(sprintf(
                        '  <fg=gray>ok</>       %-40s (already at %s)',
                        $table,
                        number_format($current)
                    ));
                }
                $alreadyOk++;
                continue;
            }

            $sql = sprintf('ALTER TABLE `%s` AUTO_INCREMENT = %d', $table, $start);

            if ($dryRun) {
                $this->line(sprintf('  <fg=yellow>would</>    %s', $sql));
            } else {
                try {
                    $connection->statement($sql);
                    $this->line(sprintf(
                        '  <fg=green>set</>      %-40s %s → %s',
                        $table,
                        number_format($current),
                        number_format($start)
                    ));
                } catch (Throwable $e) {
                    $this->line(sprintf(
                        '  <fg=red>fail</>     %-40s %s',
                        $table,
                        $e->getMessage()
                    ));
                    continue;
                }
            }

            $changed++;
        }

        $this->line(str_repeat('-', 72));
        $this->info(sprintf(
            '%s   updated: %d   already-ok: %d   skipped: %d',
            $dryRun ? 'Dry run complete.' : 'Done.',
            $changed,
            $alreadyOk,
            $skipped
        ));

        return self::SUCCESS;
    }
}
