<?php

namespace Modules\AppGoogleBusiness\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\AppGoogleBusiness\Models\GoogleBusinessConnection;
use Modules\AppGoogleBusiness\Support\GoogleBusinessClient;
use Throwable;

class GoogleBusinessOAuthController extends Controller
{
    public function connect(Request $request, GoogleBusinessClient $client): RedirectResponse
    {
        abort_unless(auth()->user()?->canUsePlanFeature('google_business'), 403);

        try {
            $limit = (int) (auth()->user()?->planLimit('max_google_business_connections', -1) ?? -1);
            $used = GoogleBusinessConnection::query()->where('team_id', auth()->id())->count();

            if ($limit >= 0 && $used >= $limit) {
                return redirect()->route('portal.google-business')->with('google_business_error', __('Your current plan allows up to :limit Google connections.', ['limit' => $limit]));
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

            $connection = GoogleBusinessConnection::query()->updateOrCreate(
                [
                    'team_id' => auth()->id(),
                    'google_account_email' => (string) ($profile['email'] ?? ''),
                ],
                [
                    'user_id' => auth()->id(),
                    'access_token' => (string) $token['access_token'],
                    'refresh_token' => (string) ($token['refresh_token'] ?? ''),
                    'expires_at' => now()->addSeconds((int) ($token['expires_in'] ?? 3600)),
                    'status' => 'connected',
                    'scopes' => explode(' ', (string) ($token['scope'] ?? GoogleBusinessClient::SCOPE)),
                ]
            );

            $candidates = $client->fetchLocationCandidates($connection);
            $request->session()->put('google_business_location_candidates', [
                'connection_id' => $connection->id,
                'locations' => $candidates,
            ]);

            return redirect()
                ->route('portal.google-business', ['tab' => 'locations'])
                ->with('google_business_status', __('Google Business Profile connected. Choose which locations you want to add and manage. :count locations are available.', ['count' => count($candidates)]));
        } catch (Throwable $exception) {
            return redirect()->route('portal.google-business')->with('google_business_error', $exception->getMessage());
        }
    }
}
