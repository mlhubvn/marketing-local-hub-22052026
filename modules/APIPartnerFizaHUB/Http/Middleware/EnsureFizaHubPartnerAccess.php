<?php

namespace Modules\APIPartnerFizaHUB\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate for the FizaHUB Partner Reporting Portal (view-only dashboard on the domain
 * configured via `FIZAHUB_DOMAIN`). Access is a plain allowlist of MLHUB user IDs from
 * `FIZAHUB_ADMIN` — no admin role/permission is required, only the exact user ID.
 *
 * This middleware only decides "is this authenticated user allowed in", it does not itself
 * enforce the request host or authentication — those are handled by
 * {@see RestrictFizaHubDomainHost} (host allowlist, registered globally) and the standard
 * `auth`/`verified` middleware already applied on the route group.
 */
class EnsureFizaHubPartnerAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = (int) $request->user()?->getAuthIdentifier();
        $allowedIds = (array) config('modules.apipartnerfizahub.partner_reporting_admin_ids', []);

        if ($userId <= 0 || ! in_array($userId, $allowedIds, true)) {
            abort(403, __('Tài khoản của bạn không có quyền truy cập cổng báo cáo đối tác FizaHUB.'));
        }

        return $next($request);
    }
}
