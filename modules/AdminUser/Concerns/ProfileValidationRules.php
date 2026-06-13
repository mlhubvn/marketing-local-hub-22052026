<?php

namespace Modules\AdminUser\Concerns;

use Closure;
use Illuminate\Validation\Rule;
use Modules\AdminUser\Models\User;

trait ProfileValidationRules
{
    protected function profileRules(?int $userId = null): array
    {
        return [
            'name' => $this->nameRules(),
            'username' => $this->usernameRules($userId),
            'email' => $this->emailRules($userId),
        ];
    }

    protected function nameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    protected function emailRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }

    protected function usernameRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'min:3',
            'max:50',
            'alpha_dash',
            Rule::notIn(['admin', 'administrator', 'root', 'system']),
            function (string $attribute, mixed $value, Closure $fail): void {
                if (in_array(strtolower((string) $value), ['admin', 'administrator', 'root', 'system'], true)) {
                    $fail(__('The :attribute is reserved.', ['attribute' => $attribute]));
                }
            },
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }
}
