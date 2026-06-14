<?php

use Carbon\Carbon;
use Modules\AdminPaymentHistory\Models\PaymentHistory;
use Modules\AdminPlans\Support\CurrencyCatalog;
use Modules\AdminSettings\Support\OptionStore;
use Modules\AdminUser\Models\User;
use Modules\AppPayments\Support\PaymentNotificationService;

beforeEach(function (): void {
    reset_platform_format_settings();
    config(['app.timezone' => 'Asia/Ho_Chi_Minh']);
    app()->setLocale('vi');
});

test('formats the required Vietnamese number money and date samples', function (): void {
    $date = Carbon::create(2026, 6, 13, 12, 0, 0, 'Asia/Ho_Chi_Minh');

    expect(format_number_locale(1234567))->toBe('1.234.567')
        ->and(format_number_locale(0.5, 3, '.', ''))->toBe('0.500')
        ->and(format_money(550000.4, 'VND'))->toBe('550.000 đ')
        ->and(format_date_locale($date))->toBe('13/06/2026');
});

test('accepts current and legacy Vietnamese dong symbols', function (): void {
    expect(CurrencyCatalog::normalizeCode('đ'))->toBe('VND')
        ->and(CurrencyCatalog::normalizeCode('₫'))->toBe('VND')
        ->and(format_money(550000, 'đ'))->toBe('550.000 đ')
        ->and(format_money(550000, '₫'))->toBe('550.000 đ');
});

test('rounds signed percentages to whole numbers', function (): void {
    expect(format_percent_locale(8.7))->toBe('9%')
        ->and(format_percent_locale(-8.7))->toBe('-9%');
});

test('formats payment notification placeholders with the Vietnamese dong symbol', function (): void {
    $payment = new PaymentHistory([
        'amount' => 550000,
        'currency' => 'VND',
    ]);
    $context = (new PaymentNotificationService(new OptionStore))->buildContext(new User, paymentHistory: $payment);

    expect($context['amount'])->toBe('550.000')
        ->and($context['currency'])->toBe('đ');
});
