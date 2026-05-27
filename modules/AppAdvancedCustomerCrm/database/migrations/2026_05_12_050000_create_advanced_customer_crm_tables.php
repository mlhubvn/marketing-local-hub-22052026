<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lb_customers', function (Blueprint $table): void {
            if (! Schema::hasColumn('lb_customers', 'team_id')) {
                $table->unsignedBigInteger('team_id')->nullable()->after('user_id')->index();
            }
            if (! Schema::hasColumn('lb_customers', 'avatar')) {
                $table->string('avatar')->nullable()->after('email');
            }
            if (! Schema::hasColumn('lb_customers', 'status')) {
                $table->string('status', 40)->default('active')->after('avatar')->index();
            }
            if (! Schema::hasColumn('lb_customers', 'source_type')) {
                $table->string('source_type')->nullable()->after('status')->index();
            }
            if (! Schema::hasColumn('lb_customers', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->after('source_type')->index();
            }
            if (! Schema::hasColumn('lb_customers', 'first_seen_at')) {
                $table->timestamp('first_seen_at')->nullable()->after('metadata');
            }
            if (! Schema::hasColumn('lb_customers', 'last_activity_at')) {
                $table->timestamp('last_activity_at')->nullable()->after('first_seen_at')->index();
            }
            if (! Schema::hasColumn('lb_customers', 'last_contacted_at')) {
                $table->timestamp('last_contacted_at')->nullable()->after('last_activity_at');
            }
            foreach (['total_bookings', 'total_coupon_claims', 'total_coupon_used', 'total_feedback', 'total_reviews', 'total_loyalty_stamps', 'total_referrals', 'score'] as $column) {
                if (! Schema::hasColumn('lb_customers', $column)) {
                    $table->unsignedInteger($column)->default(0)->after('last_contacted_at');
                }
            }
            if (! Schema::hasColumn('lb_customers', 'lifetime_value')) {
                $table->decimal('lifetime_value', 12, 2)->nullable()->after('score');
            }
        });

        Schema::create('lb_customer_activities', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable()->index();
            $table->foreignId('business_id')->nullable()->constrained('lb_businesses')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('lb_customers')->cascadeOnDelete();
            $table->string('type')->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('related_type')->nullable()->index();
            $table->unsignedBigInteger('related_id')->nullable()->index();
            $table->string('source_module')->nullable()->index();
            $table->string('icon')->nullable();
            $table->string('color', 40)->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('occurred_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('lb_customer_tags', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable()->index();
            $table->string('name');
            $table->string('slug');
            $table->string('color', 40)->default('#0f766e');
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
            $table->unique(['team_id', 'slug']);
        });

        Schema::create('lb_customer_tag_maps', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable()->index();
            $table->foreignId('customer_id')->constrained('lb_customers')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('lb_customer_tags')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();
            $table->unique(['customer_id', 'tag_id']);
        });

        Schema::create('lb_customer_notes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable()->index();
            $table->foreignId('business_id')->nullable()->constrained('lb_businesses')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('lb_customers')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('note');
            $table->string('visibility', 40)->default('team');
            $table->boolean('pinned')->default(false);
            $table->timestamps();
        });

        Schema::create('lb_customer_tasks', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable()->index();
            $table->foreignId('business_id')->nullable()->constrained('lb_businesses')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('lb_customers')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type', 40)->default('follow_up');
            $table->string('priority', 40)->default('medium')->index();
            $table->string('status', 40)->default('open')->index();
            $table->timestamp('due_at')->nullable()->index();
            $table->timestamp('completed_at')->nullable();
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('lb_customer_segments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable()->index();
            $table->foreignId('business_id')->nullable()->constrained('lb_businesses')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('filters')->nullable();
            $table->boolean('is_dynamic')->default(true);
            $table->string('color', 40)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('lb_customer_score_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable()->index();
            $table->foreignId('customer_id')->constrained('lb_customers')->cascadeOnDelete();
            $table->integer('old_score')->default(0);
            $table->integer('new_score')->default(0);
            $table->string('reason');
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lb_customer_score_logs');
        Schema::dropIfExists('lb_customer_segments');
        Schema::dropIfExists('lb_customer_tasks');
        Schema::dropIfExists('lb_customer_notes');
        Schema::dropIfExists('lb_customer_tag_maps');
        Schema::dropIfExists('lb_customer_tags');
        Schema::dropIfExists('lb_customer_activities');
    }
};
