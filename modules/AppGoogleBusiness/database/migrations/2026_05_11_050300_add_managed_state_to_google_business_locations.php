<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lb_google_business_locations')) {
            return;
        }

        $this->addColumnIfMissing('lb_google_business_locations', 'is_managed', function (Blueprint $table): void {
            $table->boolean('is_managed')->default(false)->after('status');
        });

        $this->addColumnIfMissing('lb_google_business_locations', 'managed_at', function (Blueprint $table): void {
            $after = $this->columnExists('lb_google_business_locations', 'is_managed') ? 'is_managed' : 'status';
            $table->timestamp('managed_at')->nullable()->after($after);
        });

        if (
            $this->columnExists('lb_google_business_locations', 'business_id')
            && $this->columnExists('lb_google_business_locations', 'is_managed')
            && $this->columnExists('lb_google_business_locations', 'managed_at')
        ) {
            DB::table('lb_google_business_locations')
                ->whereNotNull('business_id')
                ->where('is_managed', false)
                ->update([
                    'is_managed' => true,
                    'managed_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        $columns = array_values(array_filter([
            'is_managed',
            'managed_at',
        ], fn (string $column): bool => Schema::hasColumn('lb_google_business_locations', $column)));

        if ($columns !== []) {
            Schema::table('lb_google_business_locations', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }

    protected function addColumnIfMissing(string $table, string $column, callable $definition): void
    {
        if ($this->columnExists($table, $column)) {
            return;
        }

        try {
            Schema::table($table, $definition);
        } catch (QueryException $exception) {
            if (! $this->isDuplicateColumnError($exception)) {
                throw $exception;
            }
        }
    }

    protected function columnExists(string $table, string $column): bool
    {
        $row = DB::selectOne(
            'SELECT COUNT(*) AS aggregate FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [(string) DB::getDatabaseName(), $table, $column]
        );

        return ((int) ($row->aggregate ?? 0)) > 0;
    }

    protected function isDuplicateColumnError(QueryException $exception): bool
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? '');
        $message = strtolower($exception->getMessage());

        return $sqlState === '42S21'
            || str_contains($message, 'duplicate column')
            || str_contains($message, '1060');
    }
};
