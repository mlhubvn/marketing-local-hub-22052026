<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lb_businesses', function (Blueprint $table): void {
            if (! Schema::hasColumn('lb_businesses', 'industry_group_code')) {
                $table->string('industry_group_code', 64)->nullable()->after('type');
            }

            if (! Schema::hasColumn('lb_businesses', 'industry_category_code')) {
                $table->string('industry_category_code', 80)->nullable()->after('industry_group_code');
            }

            if (! Schema::hasColumn('lb_businesses', 'industry_metadata')) {
                $table->json('industry_metadata')->nullable()->after('industry_category_code');
            }

            if (! Schema::hasColumn('lb_businesses', 'taxonomy_version')) {
                $table->string('taxonomy_version', 20)->nullable()->after('industry_metadata');
            }
        });

        Schema::table('lb_businesses', function (Blueprint $table): void {
            if (! $this->indexExists('lb_businesses', 'lb_businesses_industry_group_code_industry_category_code_index')) {
                $table->index(['industry_group_code', 'industry_category_code']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('lb_businesses', function (Blueprint $table): void {
            if ($this->indexExists('lb_businesses', 'lb_businesses_industry_group_code_industry_category_code_index')) {
                $table->dropIndex(['industry_group_code', 'industry_category_code']);
            }

            foreach (['taxonomy_version', 'industry_metadata', 'industry_category_code', 'industry_group_code'] as $column) {
                if (Schema::hasColumn('lb_businesses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        foreach (Schema::getIndexes($table) as $schemaIndex) {
            if (($schemaIndex['name'] ?? null) === $index
                || ($schemaIndex['columns'] ?? []) === ['industry_group_code', 'industry_category_code']) {
                return true;
            }
        }

        return false;
    }
};
