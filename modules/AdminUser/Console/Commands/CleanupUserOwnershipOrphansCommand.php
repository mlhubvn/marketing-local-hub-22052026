<?php

namespace Modules\AdminUser\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\AdminUser\Support\UserOwnershipOrphanInspector;

class CleanupUserOwnershipOrphansCommand extends Command
{
    protected $signature = 'users:cleanup-orphans
        {--dry-run : Inspect only; this is the default}
        {--execute : Delete only findings with conclusive orphan evidence}
        {--force : Skip the production confirmation prompt}';

    protected $description = 'Safely clean demonstrable user-ownership orphans.';

    public function handle(UserOwnershipOrphanInspector $inspector): int
    {
        $findings = $inspector->inspect();
        $safe = array_values(array_filter(
            $findings,
            fn (array $finding): bool => $finding['safe_to_delete']
        ));

        if (! $this->option('execute')) {
            $this->components->info(sprintf(
                'Dry run: %d finding(s), %d demonstrably safe cleanup candidate(s).',
                count($findings),
                count($safe)
            ));

            return self::SUCCESS;
        }

        if (app()->isProduction()
            && ! $this->option('force')
            && ! $this->confirm(
                'Delete only the records marked safe by ownership evidence?',
                false
            )) {
            $this->warn('Cleanup cancelled.');

            return self::SUCCESS;
        }

        $deleted = DB::transaction(function () use ($safe): array {
            $counts = [];

            foreach (collect($safe)->groupBy('table') as $table => $rows) {
                $ids = $rows->pluck('record_id')->map(fn ($id) => (int) $id)->all();

                if ($ids === []) {
                    continue;
                }

                if ($table === 'lb_customer_tags'
                    && Schema::hasTable('lb_customer_tag_maps')) {
                    DB::table('lb_customer_tag_maps')->whereIn('tag_id', $ids)->delete();
                }

                $counts[$table] = DB::table($table)->whereIn('id', $ids)->delete();
            }

            $this->audit($counts, count($safe));

            return $counts;
        });

        $this->info('Cleanup completed: '.array_sum($deleted).' row(s) deleted.');

        return self::SUCCESS;
    }

    /**
     * @param  array<string, int>  $counts
     */
    private function audit(array $counts, int $safeCandidateCount): void
    {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        $columns = Schema::getColumnListing('audit_logs');
        $payload = array_filter([
            'causer_user_id' => null,
            'event' => 'admin.users.cleanup_orphans',
            'description' => 'Cleaned demonstrable user ownership orphans.',
            'subject_type' => null,
            'subject_id' => null,
            'area' => 'admin',
            'metadata' => json_encode([
                'safe_candidates' => $safeCandidateCount,
                'records_deleted' => $counts,
                'status' => 'completed',
            ], JSON_UNESCAPED_SLASHES),
            'created_at' => now(),
            'updated_at' => now(),
        ], fn (string $column): bool => in_array($column, $columns, true), ARRAY_FILTER_USE_KEY);

        DB::table('audit_logs')->insert($payload);
    }
}
