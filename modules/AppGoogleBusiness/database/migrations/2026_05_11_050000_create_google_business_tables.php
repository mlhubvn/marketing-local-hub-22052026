<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lb_google_business_connections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('google_account_email')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('status', 30)->default('connected');
            $table->json('scopes')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->index(['team_id', 'status']);
        });

        Schema::create('lb_google_business_locations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('connection_id')->constrained('lb_google_business_connections')->cascadeOnDelete();
            $table->foreignId('business_id')->nullable()->constrained('lb_businesses')->nullOnDelete();
            $table->string('google_account_id')->nullable();
            $table->string('google_location_id');
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('category')->nullable();
            $table->text('review_url')->nullable();
            $table->json('opening_hours')->nullable();
            $table->boolean('sync_business_info')->default(true);
            $table->boolean('sync_hours')->default(true);
            $table->boolean('sync_reviews')->default(true);
            $table->boolean('sync_insights')->default(false);
            $table->string('status', 30)->default('active');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->unique(['connection_id', 'google_location_id'], 'gb_location_connection_unique');
            $table->index(['team_id', 'business_id']);
        });

        Schema::create('lb_google_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('google_business_location_id')->constrained('lb_google_business_locations')->cascadeOnDelete();
            $table->foreignId('business_id')->nullable()->constrained('lb_businesses')->nullOnDelete();
            $table->string('google_review_id');
            $table->string('reviewer_name')->nullable();
            $table->unsignedTinyInteger('rating')->default(0);
            $table->text('comment')->nullable();
            $table->text('reply')->nullable();
            $table->string('reply_status', 30)->default('none');
            $table->timestamp('review_created_at')->nullable();
            $table->timestamp('review_updated_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->unique(['google_business_location_id', 'google_review_id'], 'gb_review_location_unique');
            $table->index(['team_id', 'business_id', 'rating']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lb_google_reviews');
        Schema::dropIfExists('lb_google_business_locations');
        Schema::dropIfExists('lb_google_business_connections');
    }
};
