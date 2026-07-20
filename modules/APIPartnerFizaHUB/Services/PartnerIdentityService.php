<?php

namespace Modules\APIPartnerFizaHUB\Services;

use Illuminate\Support\Str;
use Modules\AdminUser\Models\User;

class PartnerIdentityService
{
    private const USERNAME_MAX_LENGTH = 255;

    public function usernameFromEmail(string $email): string
    {
        $email = Str::lower(trim($email));
        $localPart = Str::before($email, '@');
        $username = preg_replace('/[^a-z0-9]/', '', Str::lower(Str::ascii($localPart))) ?: '';

        if ($username === '') {
            $username = 'user'.substr(hash('sha256', $email), 0, 8);
        }

        return substr($username, 0, self::USERNAME_MAX_LENGTH);
    }

    public function availableUsernameFromEmail(string $email): string
    {
        $email = Str::lower(trim($email));
        $base = $this->usernameFromEmail($email);
        $owner = User::query()->where('username', $base)->first();

        if (! $owner || hash_equals(Str::lower((string) $owner->email), $email)) {
            return $base;
        }

        $suffix = substr(hash('sha256', $email), 0, 10);

        return substr($base, 0, self::USERNAME_MAX_LENGTH - strlen($suffix)).$suffix;
    }
}
