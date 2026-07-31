<?php

namespace Modules\APIPartnerFizaHUB\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applied globally on the `web` middleware group (see
 * APIPartnerFizaHUBServiceProvider::boot()) so it runs on EVERY browser request in the
 * app, not just on FizaHUB module routes. On any host other than `FIZAHUB_DOMAIN` this is a
 * complete no-op — the rest of MLHUB (mlhub.vn) is completely unaffected.
 *
 * On `FIZAHUB_DOMAIN`, only the small allowlist below may be reached: sign in/out, the
 * 2FA challenge and email verification screens (so an allowlisted admin is never locked
 * out), Livewire's own asset/update endpoints (the reporting dashboard is a Livewire page),
 * and the two FizaHUB reporting routes themselves. Every other MLHUB route (portal, admin,
 * marketing pages, the Partner API, other module screens...) is blocked so this reporting
 * domain can never be used as an alternate door into the rest of MLHUB.
 */
class RestrictFizaHubDomainHost
{
    /** @var list<string> */
    private const ALLOWED_ROUTE_NAMES = [
        'login',
        'login.store',
        'logout',
        'two-factor.login',
        'two-factor.login.store',
        'password.request',
        'password.email',
        'password.reset',
        'password.update',
        'verification.notice',
        'verification.verify',
        'verification.send',
        'fizahub-partner.dashboard',
        'fizahub-partner.onboarding.show',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $domain = strtolower(trim((string) config('modules.apipartnerfizahub.partner_reporting_domain', '')));
        $host = strtolower((string) $request->getHost());

        if ($domain === '' || $host !== $domain) {
            return $next($request);
        }

        // Livewire's own asset/update/upload-file endpoints live under a per-install
        // random path prefix (`livewire-xxxxxxxx/*`) and have no stable route name across
        // environments — the reporting dashboard is a Livewire page and cannot work
        // without these, so allow them by path instead of by route name.
        if ($request->is('livewire*')) {
            return $next($request);
        }

        $routeName = (string) ($request->route()?->getName() ?? '');

        if ($routeName !== '' && in_array($routeName, self::ALLOWED_ROUTE_NAMES, true)) {
            return $next($request);
        }

        // An already signed-in visitor who lands on an unrelated MLHUB URL (e.g. the
        // hard-coded post-login redirect to /portal/dashboard) is sent back to the
        // reporting dashboard instead of a bare 404 — one forward hop, never a loop since
        // the dashboard route itself is always in the allowlist above.
        if ($request->user()) {
            return redirect()->route('fizahub-partner.dashboard');
        }

        abort(404);
    }
}
