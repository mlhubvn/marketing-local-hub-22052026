<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lb_google_business_connections', function (Blueprint $table): void {
            if (! Schema::hasColumn('lb_google_business_connections', 'auto_sync')) {
                $table->boolean('auto_sync')->default(true)->after('status');
            }
            if (! Schema::hasColumn('lb_google_business_connections', 'sync_interval')) {
                $table->unsignedSmallInteger('sync_interval')->default(15)->after('auto_sync');
            }
            if (! Schema::hasColumn('lb_google_business_connections', 'last_error')) {
                $table->text('last_error')->nullable()->after('last_synced_at');
            }
        });

        Schema::table('lb_google_business_locations', function (Blueprint $table): void {
            if (! Schema::hasColumn('lb_google_business_locations', 'auto_reply_enabled')) {
                $table->boolean('auto_reply_enabled')->default(false)->after('sync_insights');
            }
            if (! Schema::hasColumn('lb_google_business_locations', 'last_reviews_synced_at')) {
                $table->timestamp('last_reviews_synced_at')->nullable()->after('last_synced_at');
            }
            if (! Schema::hasColumn('lb_google_business_locations', 'last_info_synced_at')) {
                $table->timestamp('last_info_synced_at')->nullable()->after('last_reviews_synced_at');
            }
            if (! Schema::hasColumn('lb_google_business_locations', 'last_hours_synced_at')) {
                $table->timestamp('last_hours_synced_at')->nullable()->after('last_info_synced_at');
            }
        });

        Schema::table('lb_google_reviews', function (Blueprint $table): void {
            if (! Schema::hasColumn('lb_google_reviews', 'local_reply')) {
                $table->text('local_reply')->nullable()->after('reply');
            }
            if (! Schema::hasColumn('lb_google_reviews', 'auto_reply_status')) {
                $table->string('auto_reply_status', 30)->default('none')->after('reply_status');
            }
            if (! Schema::hasColumn('lb_google_reviews', 'replied_at')) {
                $table->timestamp('replied_at')->nullable()->after('auto_reply_status');
            }
        });

        if (! Schema::hasTable('lb_google_auto_reply_rules')) {
            Schema::create('lb_google_auto_reply_rules', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('team_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('business_id')->nullable()->constrained('lb_businesses')->nullOnDelete();
                $table->foreignId('google_business_location_id')->nullable()->constrained('lb_google_business_locations')->cascadeOnDelete();
                $table->string('name');
                $table->string('rating_condition', 30)->default('positive');
                $table->string('text_condition', 30)->default('any');
                $table->string('keyword')->nullable();
                $table->string('reply_mode', 30)->default('draft');
                $table->text('template_reply')->nullable();
                $table->string('tone', 40)->default('professional');
                $table->string('language', 40)->default('same');
                $table->unsignedInteger('delay_minutes')->default(0);
                $table->string('status', 20)->default('draft');
                $table->timestamps();
                $table->index(['team_id', 'status']);
            });
        }

        if (! Schema::hasTable('lb_google_auto_reply_logs')) {
            Schema::create('lb_google_auto_reply_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('team_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('rule_id')->nullable()->constrained('lb_google_auto_reply_rules')->nullOnDelete();
                $table->foreignId('review_id')->nullable()->constrained('lb_google_reviews')->cascadeOnDelete();
                $table->string('action', 40);
                $table->text('generated_reply')->nullable();
                $table->string('publish_status', 30)->default('draft');
                $table->text('error_message')->nullable();
                $table->timestamps();
                $table->index(['team_id', 'publish_status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lb_google_auto_reply_logs');
        Schema::dropIfExists('lb_google_auto_reply_rules');

        $reviewColumns = array_values(array_filter([
            'local_reply',
            'auto_reply_status',
            'replied_at',
        ], fn (string $column): bool => Schema::hasColumn('lb_google_reviews', $column)));

        if ($reviewColumns !== []) {
            Schema::table('lb_google_reviews', function (Blueprint $table) use ($reviewColumns): void {
                $table->dropColumn($reviewColumns);
            });
        }

        $locationColumns = array_values(array_filter([
            'auto_reply_enabled',
            'last_reviews_synced_at',
            'last_info_synced_at',
            'last_hours_synced_at',
        ], fn (string $column): bool => Schema::hasColumn('lb_google_business_locations', $column)));

        if ($locationColumns !== []) {
            Schema::table('lb_google_business_locations', function (Blueprint $table) use ($locationColumns): void {
                $table->dropColumn($locationColumns);
            });
        }

        $connectionColumns = array_values(array_filter([
            'auto_sync',
            'sync_interval',
            'last_error',
        ], fn (string $column): bool => Schema::hasColumn('lb_google_business_connections', $column)));

        if ($connectionColumns !== []) {
            Schema::table('lb_google_business_connections', function (Blueprint $table) use ($connectionColumns): void {
                $table->dropColumn($connectionColumns);
            });
        }
    }
};
