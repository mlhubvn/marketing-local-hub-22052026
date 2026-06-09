<?php

namespace Modules\AppGoogleBusiness\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\AppGoogleBusiness\Models\GoogleBusinessConnection;
use Modules\AppGoogleBusiness\Support\GoogleBusinessAccess;
use Modules\AppGoogleBusiness\Support\GoogleBusinessClient;
use Throwable;

class GoogleBusinessOAuthController extends Controller
{
    public function connect(Request $request, GoogleBusinessClient $client): RedirectResponse
    {
        abort_unless(auth()->user()?->canUsePlanFeature('google_business'), 403);

        try {
            if (! GoogleBusinessAccess::canConnectGoogleAccount()) {
                return redirect()
                    ->route('portal.google-business')
                    ->with('google_business_error', __('Your current plan does not include Google Business connections.'));
            }

            $state = Str::random(40);
            $request->session()->put('google_business_oauth_state', $state);

            return redirect()->away($client->authUrl($state));
        } catch (Throwable $exception) {
            return redirect()->route('portal.google-business')->with('google_business_error', $exception->getMessage());
        }
    }

    public function callback(Request $request, GoogleBusinessClient $client): RedirectResponse
    {
        abort_unless(auth()->user()?->canUsePlanFeature('google_business'), 403);

        if (! hash_equals((string) $request->session()->pull('google_business_oauth_state'), (string) $request->query('state'))) {
            return redirect()->route('portal.google-business')->with('google_business_error', __('Invalid Google OAuth state. Please try again.'));
        }

        if ($request->filled('error')) {
            return redirect()->route('portal.google-business')->with('google_business_error', (string) $request->query('error'));
        }

        try {
            $token = $client->exchangeCode((string) $request->query('code'));
            $profile = $client->userInfo((string) $token['access_token']);
            $googleAccountEmail = (string) ($profile['email'] ?? '');

            if (! GoogleBusinessAccess::canConnectGoogleAccount($googleAccountEmail)) {
                return redirect()
                    ->route('portal.google-business', ['tab' => 'locations'])
                    ->with('google_business_error', __('Your plan allows up to :limit Google account(s). Disconnect an existing account before connecting another.', [
                        'limit' => GoogleBusinessAccess::connectionLimit(),
                    ]));
            }

            GoogleBusinessConnection::query()->updateOrCreate(
                [
                    'team_id' => auth()->id(),
                    'google_account_email' => $googleAccountEmail,
                ],
                [
                    'user_id' => auth()->id(),
                    'access_token' => (string) $token['access_token'],
                    'refresh_token' => (string) ($token['refresh_token'] ?? ''),
                    'expires_at' => now()->addSeconds((int) ($token['expires_in'] ?? 3600)),
                    'status' => 'connected',
                    'scopes' => explode(' ', (string) ($token['scope'] ?? GoogleBusinessClient::SCOPE)),
                    'last_error' => null,
                ]
            );

            return redirect()
                ->route('portal.google-business', ['tab' => 'locations'])
                ->with('google_business_status', __('Google account connected. Click Refresh locations to load your Google Maps listings.'));
        } catch (Throwable $exception) {
            return redirect()->route('portal.google-business')->with('google_business_error', $exception->getMessage());
        }
    }
}
