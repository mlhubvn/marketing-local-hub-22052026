<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lb_template_packs')) {
            Schema::create('lb_template_packs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category', 60)->default('general')->index();
            $table->text('description')->nullable();
            $table->string('preview_image')->nullable();
            $table->string('source', 30)->default('system')->index();
            $table->string('visibility', 30)->default('public')->index();
            $table->string('status', 30)->default('active')->index();
            $table->string('version', 30)->default('1.0.0');
            $table->unsignedInteger('install_count')->default(0);
            $table->timestamps();
            });
        }

        if (! Schema::hasTable('lb_template_pack_items')) {
            Schema::create('lb_template_pack_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pack_id')->constrained('lb_template_packs')->cascadeOnDelete();
            $table->foreignId('template_id')->constrained('lb_marketing_templates')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['pack_id', 'template_id']);
            });
        }

        if (! Schema::hasTable('lb_template_usages')) {
            Schema::create('lb_template_usages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('template_id')->constrained('lb_marketing_templates')->cascadeOnDelete();
            $table->foreignId('business_id')->nullable()->constrained('lb_businesses')->nullOnDelete();
            $table->string('created_object_type', 80);
            $table->unsignedBigInteger('created_object_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['template_id', 'created_object_type']);
            });
        }

        if (! Schema::hasTable('lb_template_imports')) {
            Schema::create('lb_template_imports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->string('file_name');
            $table->string('status', 30)->default('pending')->index();
            $table->unsignedInteger('imported_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->json('error_log')->nullable();
            $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lb_template_imports');
        Schema::dropIfExists('lb_template_usages');
        Schema::dropIfExists('lb_template_pack_items');
        Schema::dropIfExists('lb_template_packs');
    }
};
