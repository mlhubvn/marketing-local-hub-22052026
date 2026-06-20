<?php

use Database\Support\IdSequence;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('options') && ! Schema::hasTable('migrations')) {
            return;
        }

        IdSequence::normalizeCoreTables();
    }

    public function down(): void
    {
        //
    }
};
