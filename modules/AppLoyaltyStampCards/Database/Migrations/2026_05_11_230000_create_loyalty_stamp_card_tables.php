<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lb_loyalty_cards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('business_id')->constrained('lb_businesses')->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('name');
            $table->unsignedInteger('required_stamps')->default(10);
            $table->string('stamp_method')->default('qr_scan');
            $table->string('customer_identifier')->default('phone');
            $table->string('reward_title');
            $table->string('reward_type')->default('free_item');
            $table->string('reward_value')->nullable();
            $table->unsignedInteger('expiry_days')->nullable();
            $table->unsignedInteger('stamp_cooldown_minutes')->default(1440);
            $table->unsignedInteger('max_stamps_per_day')->default(1);
            $table->json('settings')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('lb_loyalty_customers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('card_id')->constrained('lb_loyalty_cards')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('lb_customers')->cascadeOnDelete();
            $table->unsignedInteger('stamps_count')->default(0);
            $table->unsignedInteger('completed_count')->default(0);
            $table->timestamp('last_stamp_at')->nullable();
            $table->timestamps();
            $table->unique(['card_id', 'customer_id']);
        });

        Schema::create('lb_loyalty_stamps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('card_id')->constrained('lb_loyalty_cards')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('lb_customers')->cascadeOnDelete();
            $table->string('source')->default('qr_scan');
            $table->foreignId('staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('lb_loyalty_rewards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('card_id')->constrained('lb_loyalty_cards')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('lb_customers')->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('status')->default('available');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lb_loyalty_rewards');
        Schema::dropIfExists('lb_loyalty_stamps');
        Schema::dropIfExists('lb_loyalty_customers');
        Schema::dropIfExists('lb_loyalty_cards');
    }
};
