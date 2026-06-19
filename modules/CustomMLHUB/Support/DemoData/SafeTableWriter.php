<?php

namespace Modules\CustomMLHUB\Support\DemoData;

use BackedEnum;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SafeTableWriter
{
    /** @var array<string, array<string, true>> */
    protected array $columns = [];

    public function hasTable(string $table): bool
    {
        return Schema::hasTable($table);
    }

    public function hasColumn(string $table, string $column): bool
    {
        if (! $this->hasTable($table)) {
            return false;
        }

        if (array_key_exists($table, $this->columns)) {
            return isset($this->columns[$table][$column]);
        }

        return Schema::hasColumn($table, $column);
    }

    public function insert(string $table, array $row): ?int
    {
        if (! $this->hasTable($table)) {
            return null;
        }

        $row = $this->filterRow($table, $row);

        if ($row === []) {
            return null;
        }

        return (int) DB::table($table)->insertGetId($row);
    }

    public function insertRows(string $table, array $rows, int $chunkSize = 500): int
    {
        if (! $this->hasTable($table)) {
            return 0;
        }

        $inserted = 0;
        $buffer = [];

        foreach ($rows as $row) {
            $filtered = $this->filterRow($table, (array) $row);

            if ($filtered === []) {
                continue;
            }

            $buffer[] = $filtered;

            if (count($buffer) >= $chunkSize) {
                DB::table($table)->insert($buffer);
                $inserted += count($buffer);
                $buffer = [];
            }
        }

        if ($buffer !== []) {
            DB::table($table)->insert($buffer);
            $inserted += count($buffer);
        }

        return $inserted;
    }

    public function updateWhere(string $table, array $where, array $row): int
    {
        if (! $this->hasTable($table)) {
            return 0;
        }

        $where = $this->filterRow($table, $where);
        $row = $this->filterRow($table, $row);

        if ($where === [] || $row === []) {
            return 0;
        }

        return DB::table($table)->where($where)->update($row);
    }

    public function filterRow(string $table, array $row): array
    {
        $columns = $this->columns($table);
        $filtered = [];

        foreach ($row as $column => $value) {
            if (! isset($columns[$column])) {
                continue;
            }

            $filtered[$column] = $this->normalizeValue($value);
        }

        return $filtered;
    }

    /**
     * @return array<string, true>
     */
    protected function columns(string $table): array
    {
        if (! array_key_exists($table, $this->columns)) {
            $this->columns[$table] = $this->hasTable($table)
                ? array_fill_keys(Schema::getColumnListing($table), true)
                : [];
        }

        return $this->columns[$table];
    }

    protected function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        return $value;
    }
}
