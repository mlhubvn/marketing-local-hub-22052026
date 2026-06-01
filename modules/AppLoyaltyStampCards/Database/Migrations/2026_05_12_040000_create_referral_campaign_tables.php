<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lb_referral_campaigns', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('business_id')->constrained('lb_businesses')->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('reward_title');
            $table->string('reward_type')->default('coupon');
            $table->string('reward_value')->nullable();
            $table->unsignedInteger('required_referrals')->default(1);
            $table->string('target_action')->default('lead');
            $table->unsignedInteger('expiry_days')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('settings')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('lb_referral_links', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_id')->constrained('lb_referral_campaigns')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('lb_customers')->cascadeOnDelete();
            $table->string('code')->unique();
            $table->unsignedInteger('clicks_count')->default(0);
            $table->unsignedInteger('conversions_count')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->unique(['campaign_id', 'customer_id']);
        });

        Schema::create('lb_referrals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_id')->constrained('lb_referral_campaigns')->cascadeOnDelete();
            $table->foreignId('referral_link_id')->constrained('lb_referral_links')->cascadeOnDelete();
            $table->foreignId('referrer_customer_id')->constrained('lb_customers')->cascadeOnDelete();
            $table->foreignId('referred_customer_id')->constrained('lb_customers')->cascadeOnDelete();
            $table->string('target_action')->default('lead');
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('status')->default('converted');
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('lb_referral_rewards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_id')->constrained('lb_referral_campaigns')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('lb_customers')->cascadeOnDelete();
            $table->foreignId('referral_id')->nullable()->constrained('lb_referrals')->nullOnDelete();
            $table->string('code')->unique();
            $table->string('status')->default('available');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lb_referral_rewards');
        Schema::dropIfExists('lb_referrals');
        Schema::dropIfExists('lb_referral_links');
        Schema::dropIfExists('lb_referral_campaigns');
    }
};
