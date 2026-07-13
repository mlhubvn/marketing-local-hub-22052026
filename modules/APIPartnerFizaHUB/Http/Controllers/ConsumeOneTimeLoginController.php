<?php

namespace Modules\APIPartnerFizaHUB\Http\Controllers;

use Illuminate\Http\Request;
use Modules\APIPartnerFizaHUB\Services\OneTimeLoginService;

class ConsumeOneTimeLoginController
{
    public function __construct(
        protected OneTimeLoginService $logins
    ) {}

    public function __invoke(Request $request, string $token)
    {
        return $this->logins->consume($token, $request);
    }
}
