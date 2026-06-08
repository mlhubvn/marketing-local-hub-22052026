<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var list<string> */
    protected array $optionPrefixes = [
        'flutterwave_',
        'paystack_',
        'razorpay_',
        'payu_',
        'paytm_',
        'ccavenue_',
        'instamojo_',
        'sslcommerz_',
        'iyzico_',
        'paytr_',
        'yoomoney_',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('options')) {
            return;
        }

        DB::table('options')
            ->where(function ($query): void {
                foreach ($this->optionPrefixes as $prefix) {
                    $query->orWhere('name', 'like', $prefix.'%');
                }
            })
            ->delete();
    }

    public function down(): void
    {
        // Removed gateways are not restored.
    }
};
