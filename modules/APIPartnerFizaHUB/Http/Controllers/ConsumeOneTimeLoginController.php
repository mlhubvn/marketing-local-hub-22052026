<?php

namespace Modules\APIPartnerFizaHUB\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\APIPartnerFizaHUB\Services\OneTimeLoginService;

class ConsumeOneTimeLoginController
{
    public function __construct(
        protected OneTimeLoginService $logins
    ) {}

    /**
     * GET = read-only peek (renders a confirm page, never marks the token used).
     * POST = the actual single-use consume + login, submitted by the confirm page.
     */
    public function __invoke(Request $request, string $token)
    {
        if ($request->isMethod('post')) {
            return $this->logins->consume($token, $request);
        }

        return $this->showConfirmPage($request, $token);
    }

    private function showConfirmPage(Request $request, string $token): Response
    {
        $this->logins->peek($token);

        return response()
            ->view('apipartnerfizahub::one-time-login-confirm', [
                'actionUrl' => $request->fullUrl(),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
