<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var list<string> */
    private array $tables = [
        'lb_customer_activities',
        'lb_customer_tags',
        'lb_customer_tag_maps',
        'lb_customer_notes',
        'lb_customer_tasks',
        'lb_customer_segments',
        'lb_customer_score_logs',
        'lb_crm_automations',
        'lb_crm_automation_logs',
        'lb_customer_merge_logs',
        'lb_crm_automation_jobs',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, 'owner_user_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->foreignId('owner_user_id')
                    ->nullable()
                    ->after('team_id')
                    ->constrained('users')
                    ->cascadeOnDelete();
            });
        }

        /*
         * Every producer in this CRM module historically wrote auth()->id()
         * (users.id), never teams.id, into these team_id columns. Only copy a
         * value while the referenced user still exists. Rows whose user was
         * already deleted intentionally remain unresolved for the orphan
         * inspector instead of being guessed from names or slugs.
         */
        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table)
                || ! Schema::hasColumn($table, 'team_id')
                || ! Schema::hasColumn($table, 'owner_user_id')) {
                continue;
            }

            DB::table($table)
                ->select(['id', 'team_id'])
                ->whereNull('owner_user_id')
                ->whereNotNull('team_id')
                ->orderBy('id')
                ->chunkById(500, function ($rows) use ($table): void {
                    $candidateUserIds = $rows
                        ->pluck('team_id')
                        ->map(fn ($id): int => (int) $id)
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();
                    $existingUserIds = DB::table('users')
                        ->whereIn('id', $candidateUserIds)
                        ->pluck('id')
                        ->map(fn ($id): int => (int) $id)
                        ->all();

                    foreach ($rows->groupBy(fn ($row): int => (int) $row->team_id) as $userId => $ownedRows) {
                        if (! in_array((int) $userId, $existingUserIds, true)) {
                            continue;
                        }

                        DB::table($table)
                            ->whereIn('id', $ownedRows->pluck('id')->all())
                            ->update(['owner_user_id' => (int) $userId]);
                    }
                });
        }

        $this->backfillFromRelatedOwner(
            'lb_customer_tag_maps',
            'lb_customer_tags',
            'tag_id',
            'owner_user_id'
        );

        foreach ([
            'lb_customer_activities',
            'lb_customer_tag_maps',
            'lb_customer_notes',
            'lb_customer_tasks',
            'lb_customer_score_logs',
            'lb_crm_automation_logs',
            'lb_crm_automation_jobs',
        ] as $table) {
            $this->backfillFromRelatedOwner($table, 'lb_customers', 'customer_id', 'user_id');
        }

        foreach (['lb_crm_automation_logs', 'lb_crm_automation_jobs'] as $table) {
            $this->backfillFromRelatedOwner(
                $table,
                'lb_crm_automations',
                'automation_id',
                'owner_user_id'
            );
        }

        $this->backfillFromRelatedOwner(
            'lb_customer_merge_logs',
            'lb_customers',
            'primary_customer_id',
            'user_id'
        );

        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table)
                || ! Schema::hasColumn($table, 'team_id')
                || ! Schema::hasColumn($table, 'owner_user_id')) {
                continue;
            }

            DB::table($table)
                ->whereNotNull('owner_user_id')
                ->whereColumn('team_id', 'owner_user_id')
                ->update(['team_id' => null]);
        }

        if (Schema::hasTable('lb_customer_tags')
            && Schema::hasColumn('lb_customer_tags', 'owner_user_id')) {
            Schema::table('lb_customer_tags', function (Blueprint $table): void {
                $table->unique(
                    ['owner_user_id', 'slug'],
                    'lb_customer_tags_owner_slug_unique'
                );
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lb_customer_tags')
            && Schema::hasColumn('lb_customer_tags', 'owner_user_id')) {
            /*
             * MySQL may use the composite unique index as the supporting index
             * for the owner_user_id foreign key and discard its implicit index.
             * Give the FK a dedicated temporary index before removing the
             * composite unique, otherwise rollback fails with SQLSTATE 1553.
             */
            Schema::table('lb_customer_tags', function (Blueprint $table): void {
                $table->index(
                    'owner_user_id',
                    'lb_customer_tags_owner_rollback_index'
                );
            });

            Schema::table('lb_customer_tags', function (Blueprint $table): void {
                $table->dropUnique('lb_customer_tags_owner_slug_unique');
            });
        }

        foreach (array_reverse($this->tables) as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'owner_user_id')) {
                continue;
            }

            if (Schema::hasColumn($table, 'team_id')) {
                DB::table($table)
                    ->whereNull('team_id')
                    ->whereNotNull('owner_user_id')
                    ->update(['team_id' => DB::raw('owner_user_id')]);
            }

            if ($table === 'lb_customer_tags') {
                Schema::table($table, function (Blueprint $blueprint): void {
                    $blueprint->dropForeign(['owner_user_id']);
                });
                Schema::table($table, function (Blueprint $blueprint): void {
                    $blueprint->dropIndex('lb_customer_tags_owner_rollback_index');
                    $blueprint->dropColumn('owner_user_id');
                });

                continue;
            }

            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->dropConstrainedForeignId('owner_user_id');
            });
        }
    }

    private function backfillFromRelatedOwner(
        string $table,
        string $relatedTable,
        string $foreignKey,
        string $ownerColumn
    ): void {
        if (! Schema::hasTable($table)
            || ! Schema::hasTable($relatedTable)
            || ! Schema::hasColumn($table, 'owner_user_id')
            || ! Schema::hasColumn($table, $foreignKey)
            || ! Schema::hasColumn($relatedTable, $ownerColumn)) {
            return;
        }

        DB::table($table)
            ->whereNull('owner_user_id')
            ->whereExists(function ($query) use (
                $table,
                $relatedTable,
                $foreignKey,
                $ownerColumn
            ): void {
                $query->selectRaw('1')
                    ->from($relatedTable)
                    ->whereColumn($relatedTable.'.id', $table.'.'.$foreignKey)
                    ->whereNotNull($relatedTable.'.'.$ownerColumn);
            })
            ->update([
                'owner_user_id' => DB::table($relatedTable)
                    ->select($ownerColumn)
                    ->whereColumn($relatedTable.'.id', $table.'.'.$foreignKey)
                    ->limit(1),
            ]);
    }
};
