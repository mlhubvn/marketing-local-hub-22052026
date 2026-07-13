<?php

namespace Modules\APIPartnerFizaHUB\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\APIPartnerFizaHUB\Support\PartnerApiResponse;
use Symfony\Component\HttpFoundation\Response;

class VerifyPartnerToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $partnerHeader = strtolower(trim((string) $request->headers->get('X-Partner', '')));
        $expectedPartner = strtolower((string) config('modules.apipartnerfizahub.partner_code', 'fizahub'));

        if ($partnerHeader === '' || ! hash_equals($expectedPartner, $partnerHeader)) {
            return PartnerApiResponse::error(
                'invalid_partner_header',
                'The X-Partner header must be fizahub.',
                400
            );
        }

        $configuredToken = (string) config('modules.apipartnerfizahub.token', '');
        $authorization = (string) $request->headers->get('Authorization', '');
        $providedToken = '';

        if (preg_match('/^Bearer\s+(\S+)$/i', $authorization, $matches) === 1) {
            $providedToken = $matches[1];
        }

        if ($configuredToken === '' || $providedToken === '' || ! hash_equals($configuredToken, $providedToken)) {
            return PartnerApiResponse::error(
                'invalid_partner_token',
                'The partner bearer token is invalid.',
                401
            );
        }

        return $next($request);
    }
}
