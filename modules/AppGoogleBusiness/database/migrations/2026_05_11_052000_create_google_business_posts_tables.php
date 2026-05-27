<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lb_google_business_posts')) {
            Schema::create('lb_google_business_posts', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('team_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('google_business_location_id')->constrained('lb_google_business_locations')->cascadeOnDelete();
                $table->foreignId('business_id')->nullable()->constrained('lb_businesses')->nullOnDelete();
                $table->foreignId('campaign_id')->nullable()->constrained('lb_campaigns')->nullOnDelete();
                $table->foreignId('landing_page_id')->nullable()->constrained('lb_landing_pages')->nullOnDelete();
                $table->string('google_post_id')->nullable();
                $table->string('google_post_name')->nullable();
                $table->string('type', 40)->default('standard');
                $table->string('title')->nullable();
                $table->text('summary');
                $table->string('cta_type', 40)->nullable();
                $table->text('cta_url')->nullable();
                $table->text('media_url')->nullable();
                $table->string('coupon_code')->nullable();
                $table->text('terms')->nullable();
                $table->timestamp('start_at')->nullable();
                $table->timestamp('end_at')->nullable();
                $table->string('status', 40)->default('draft');
                $table->text('search_url')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
                $table->index(['team_id', 'status']);
                $table->index(['google_business_location_id', 'status'], 'gb_posts_location_status_index');
            });
        }

        if (! Schema::hasTable('lb_google_business_post_logs')) {
            Schema::create('lb_google_business_post_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('team_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('post_id')->nullable()->constrained('lb_google_business_posts')->nullOnDelete();
                $table->string('action', 40);
                $table->string('status', 40);
                $table->json('request_payload')->nullable();
                $table->json('response_body')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamps();
                $table->index(['team_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lb_google_business_post_logs');
        Schema::dropIfExists('lb_google_business_posts');
    }
};
