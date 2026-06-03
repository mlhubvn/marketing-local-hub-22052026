<?php

namespace App\Support\Plans;

class NoPlanAccess
{
    public static function enabled(): bool
    {
        return (bool) config('mlhub.no_plan_access.enabled', true);
    }

    public static function label(): string
    {
        return (string) config('mlhub.no_plan_access.label', 'MLHUB Free');
    }

    /**
     * @return array<string, mixed>
     */
    public static function permissions(): array
    {
        $permissions = config('mlhub.no_plan_access.permissions', []);

        return is_array($permissions) ? $permissions : [];
    }
}
