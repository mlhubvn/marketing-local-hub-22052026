<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lb_google_business_locations', function (Blueprint $table): void {
            if (! Schema::hasColumn('lb_google_business_locations', 'is_managed')) {
                $table->boolean('is_managed')->default(false)->after('status');
            }

            if (! Schema::hasColumn('lb_google_business_locations', 'managed_at')) {
                $table->timestamp('managed_at')->nullable()->after('is_managed');
            }
        });

        if (Schema::hasColumn('lb_google_business_locations', 'business_id')) {
            DB::table('lb_google_business_locations')
                ->whereNotNull('business_id')
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
};
