<?php

namespace Modules\AppGoogleBusiness\Support;

use Modules\AppGoogleBusiness\Models\GoogleBusinessConnection;
use Modules\AppGoogleBusiness\Models\GoogleBusinessLocation;
use Modules\AdminUser\Models\User;

class GoogleBusinessAccess
{
    public static function connectionLimit(?User $user = null): int
    {
        return self::resolveQuotaLimit($user, 'max_google_business_connections');
    }

    public static function connectionCount(?User $user = null): int
    {
        $userId = $user?->id ?? auth()->id();

        return GoogleBusinessConnection::query()->where('team_id', $userId)->count();
    }

    public static function locationLimit(?User $user = null): int
    {
        return self::resolveQuotaLimit($user, 'max_google_business_locations');
    }

    /**
     * Free (no plan): MLHUB_NO_PLAN_GOOGLE_* env. Paid plans: max_businesses (Cơ sở kinh doanh).
     */
    protected static function resolveQuotaLimit(?User $user, string $noPlanPermissionKey): int
    {
        $user ??= auth()->user();

        if ($user?->usesNoPlanFreeAccess()) {
            return (int) ($user->planLimit($noPlanPermissionKey, 1) ?? 1);
        }

        return (int) ($user?->planLimit('max_businesses', 0) ?? 0);
    }

    public static function locationCount(?User $user = null): int
    {
        $userId = $user?->id ?? auth()->id();

        return GoogleBusinessLocation::query()->where('team_id', $userId)->count();
    }

    public static function canConnectGoogleAccount(?string $googleAccountEmail = null, ?User $user = null): bool
    {
        $limit = self::connectionLimit($user);

        if ($limit < 0) {
            return true;
        }

        if ($limit === 0) {
            return false;
        }

        $used = self::connectionCount($user);

        if ($used < $limit) {
            return true;
        }

        if ($googleAccountEmail === null || $googleAccountEmail === '') {
            return true;
        }

        $userId = $user?->id ?? auth()->id();

        return GoogleBusinessConnection::query()
            ->where('team_id', $userId)
            ->where('google_account_email', $googleAccountEmail)
            ->exists();
    }

    public static function canImportGoogleLocation(bool $alreadyImported, ?User $user = null): bool
    {
        if ($alreadyImported) {
            return true;
        }

        $limit = self::locationLimit($user);

        if ($limit < 0) {
            return true;
        }

        return self::locationCount($user) < $limit;
    }
}
