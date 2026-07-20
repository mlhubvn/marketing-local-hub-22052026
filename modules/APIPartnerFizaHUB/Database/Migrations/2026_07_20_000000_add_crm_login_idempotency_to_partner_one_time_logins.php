<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partner_one_time_logins', function (Blueprint $table): void {
            $table->string('idempotency_key', 128)->nullable()->after('request_id');
            $table->text('token_ciphertext')->nullable()->after('token_hash');
            $table->unique(
                ['partner_integration_id', 'idempotency_key'],
                'partner_one_time_logins_integration_idempotency_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('partner_one_time_logins', function (Blueprint $table): void {
            $table->dropUnique('partner_one_time_logins_integration_idempotency_unique');
            $table->dropColumn(['idempotency_key', 'token_ciphertext']);
        });
    }
};
