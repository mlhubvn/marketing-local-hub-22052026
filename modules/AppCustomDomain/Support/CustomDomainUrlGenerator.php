<?php

namespace Modules\AppCustomDomain\Support;

use Modules\AppCustomDomain\Models\AppCustomDomain;

class CustomDomainUrlGenerator
{
    public function landingPageUrl(object $landingPage, string $routeName, array $parameters = []): ?string
    {
        return $this->urlForOwner((int) ($landingPage->user_id ?? 0), route($routeName, $parameters, false));
    }

    public function campaignUrl(object $campaign, string $routeName, array $parameters = []): ?string
    {
        return $this->urlForOwner((int) ($campaign->user_id ?? 0), route($routeName, $parameters, false));
    }

    public function urlForOwner(int $ownerId, string $path): ?string
    {
        if ($ownerId <= 0) {
            return null;
        }

        $domain = AppCustomDomain::query()
            ->where('owner_user_id', $ownerId)
            ->where('status', 'verified')
            ->where('is_default', true)
            ->value('domain');

        if (! is_string($domain) || trim($domain) === '') {
            return null;
        }

        $scheme = parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: (request()->isSecure() ? 'https' : 'http');

        return $scheme.'://'.trim($domain).'/'.ltrim($path, '/');
    }
}
