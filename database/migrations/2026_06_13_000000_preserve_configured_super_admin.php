<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $email = trim((string) config('custommlhub.first_user.email', ''));

        if ($email === '' || ! Schema::hasTable('users') || ! Schema::hasColumns('users', ['email', 'is_super_admin'])) {
            return;
        }

        DB::table('users')
            ->where('email', $email)
            ->update(['is_super_admin' => true]);
    }

    public function down(): void
    {
        // Never revoke production admin access automatically.
    }
};
