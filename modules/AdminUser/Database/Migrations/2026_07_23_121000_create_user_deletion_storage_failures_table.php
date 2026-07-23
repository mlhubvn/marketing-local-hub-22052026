<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_deletion_storage_failures')) {
            return;
        }

        Schema::create('user_deletion_storage_failures', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('deleted_user_id')->nullable()->index();
            $table->string('asset_fingerprint', 64)->unique();
            $table->string('disk', 80);
            $table->text('encrypted_path');
            $table->boolean('is_directory')->default(false);
            $table->unsignedInteger('attempts')->default(1);
            $table->string('last_error_class')->nullable();
            $table->timestamp('last_attempted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_deletion_storage_failures');
    }
};
