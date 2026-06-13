<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Modules\AdminUser\Concerns\ProfileValidationRules;
use Modules\AdminUser\Models\User;

test('the admin username does not grant super admin access', function (): void {
    $user = new User([
        'username' => 'admin',
        'is_super_admin' => false,
    ]);
    $user->setRelation('role', null);

    expect($user->isSuperAdmin())->toBeFalse()
        ->and($user->canAccessAdmin())->toBeFalse();
});

test('the explicit super admin flag still grants super admin access', function (): void {
    $user = new User([
        'username' => 'owner',
        'is_super_admin' => true,
    ]);
    $user->setRelation('role', null);

    expect($user->isSuperAdmin())->toBeTrue()
        ->and($user->canAccessAdmin())->toBeTrue();
});

test('privileged usernames are reserved by profile validation', function (): void {
    $rules = new class
    {
        use ProfileValidationRules;

        public function usernameRulesForTest(): array
        {
            return $this->usernameRules();
        }
    };

    $serializedRules = collect($rules->usernameRulesForTest())
        ->filter(fn (mixed $rule): bool => is_string($rule) || $rule instanceof Stringable)
        ->map(fn (mixed $rule): string => (string) $rule)
        ->implode('|');

    expect($serializedRules)->toContain('not_in:"admin","administrator","root","system"');
});

test('privileged usernames are reserved case insensitively', function (): void {
    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('username')->unique();
    });

    $rules = new class
    {
        use ProfileValidationRules;

        public function usernameRulesForTest(): array
        {
            return $this->usernameRules();
        }
    };

    expect(Validator::make(
        ['username' => 'Admin'],
        ['username' => $rules->usernameRulesForTest()],
    )->fails())->toBeTrue();
});
