<?php

namespace Modules\APIPartnerFizaHUB\Services;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Models\PartnerOneTimeLogin;
use Modules\APIPartnerFizaHUB\Support\PartnerApiException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OneTimeLoginService
{
    public function __construct(
        protected SupportTicketBridge $integrations
    ) {}

    /**
     * @return array{url: string, expires_at: string, expires_in_seconds: int}
     */
    public function issue(
        PartnerIntegration $integration,
        string $idempotencyKey,
        string $requestId
    ): array {
        if (! $integration->mlhub_user_id || ! $integration->mlhub_workspace_id || ! $integration->mlhub_business_id) {
            throw (new ModelNotFoundException)
                ->setModel(PartnerIntegration::class, [$integration->external_business_id]);
        }

        $user = User::query()->find($integration->mlhub_user_id);

        if (! $user) {
            throw (new ModelNotFoundException)
                ->setModel(User::class, [(string) $integration->mlhub_user_id]);
        }

        $issued = DB::transaction(function () use ($integration, $user, $idempotencyKey, $requestId): array {
            $existing = PartnerOneTimeLogin::query()
                ->where('partner_integration_id', $integration->id)
                ->where('idempotency_key', $idempotencyKey)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                if ($existing->used_at !== null || $existing->expires_at->isPast() || ! $existing->token_ciphertext) {
                    throw PartnerApiException::make(
                        'crm_login_link_not_reusable',
                        'Liên kết đăng nhập CRM đã hết hạn hoặc đã được sử dụng.',
                        409,
                        ['next_action' => 'new_idempotency_key']
                    );
                }

                return [$existing, Crypt::decryptString((string) $existing->token_ciphertext)];
            }

            $ttl = max(1, (int) config('modules.apipartnerfizahub.one_time_login_ttl_minutes', 5));
            $expiresAt = now()->addMinutes($ttl);
            $plainToken = bin2hex(random_bytes(32));
            $login = PartnerOneTimeLogin::query()->create([
                'partner_integration_id' => $integration->id,
                'user_id' => $user->id,
                'request_id' => $requestId,
                'idempotency_key' => $idempotencyKey,
                'token_hash' => hash('sha256', $plainToken),
                'token_ciphertext' => Crypt::encryptString($plainToken),
                'expires_at' => $expiresAt,
                'used_at' => null,
            ]);

            return [$login, $plainToken];
        });

        /** @var PartnerOneTimeLogin $login */
        [$login, $plainToken] = $issued;
        $expiresAt = $login->expires_at;
        $url = $this->signedUrl($plainToken, $expiresAt);

        $this->safeLog('partner.fizahub.login.issue', 'Issued a FizaHUB one-time login.', [
            'subject_type' => PartnerOneTimeLogin::class,
            'subject_id' => null,
            'area' => 'admin',
            'causer_user_id' => $user->id,
            'metadata' => [
                'external_business_id' => $integration->external_business_id,
                'request_id' => $requestId,
                'expires_at' => $expiresAt->toIso8601String(),
                // Never log the raw one-time login URL.
                'url' => '[REDACTED]',
            ],
        ]);

        return [
            'url' => $url,
            'expires_at' => $expiresAt->utc()->toIso8601String(),
            'expires_in_seconds' => max(0, (int) now()->diffInSeconds($expiresAt, false)),
        ];
    }

    private function signedUrl(string $plainToken, \DateTimeInterface $expiresAt): string
    {
        return URL::temporarySignedRoute(
            'partner.fizahub.login.consume',
            $expiresAt,
            ['token' => $plainToken]
        );
    }

    /**
     * Read-only check used by the GET confirm page. Deliberately performs zero writes
     * (no lock, no used_at mutation) so that a chat-app link-preview crawler fetching the
     * raw URL (Zalo/Messenger/Telegram scrape a pasted link to build a preview card,
     * before any human opens it) can never burn a single-use login link. Throws the same
     * generic 403 as consume() when the token is not currently consumable.
     */
    public function peek(string $plainToken): void
    {
        $login = PartnerOneTimeLogin::query()
            ->where('token_hash', hash('sha256', $plainToken))
            ->first();

        $this->rejectUnlessConsumable($login);
    }

    public function consume(string $plainToken, Request $request)
    {
        $hash = hash('sha256', $plainToken);

        return DB::transaction(function () use ($hash, $request) {
            $login = PartnerOneTimeLogin::query()
                ->where('token_hash', $hash)
                ->lockForUpdate()
                ->first();

            $this->rejectUnlessConsumable($login);

            $integration = PartnerIntegration::query()->find($login->partner_integration_id);
            $user = User::query()->find($login->user_id);

            $login->forceFill([
                'used_at' => now(),
            ])->save();

            Auth::guard('web')->login($user);
            $request->session()->regenerate();
            $request->session()->put('portal_team_id', (int) $integration->mlhub_workspace_id);

            $this->safeLog('partner.fizahub.login.consume', 'Consumed a FizaHUB one-time login.', [
                'subject_type' => PartnerOneTimeLogin::class,
                'subject_id' => $login->id,
                'area' => 'user',
                'causer_user_id' => $user->id,
                'metadata' => [
                    'partner_integration_id' => $login->partner_integration_id,
                ],
            ]);

            return redirect()->route('portal.dashboard');
        });
    }

    /**
     * Shared validity check for both peek() and consume() so the two paths can never
     * disagree on what counts as a usable token. Throws (never returns false) so both
     * callers get identical 403 behavior.
     */
    private function rejectUnlessConsumable(?PartnerOneTimeLogin $login): void
    {
        if (! $login) {
            $this->logConsumeFailure('token_not_found', null);
            throw new HttpException(403, 'This one-time login link is invalid or has expired.');
        }

        if ($login->used_at !== null) {
            $this->logConsumeFailure('already_used', $login);
            throw new HttpException(403, 'This one-time login link is invalid or has expired.');
        }

        if ($login->expires_at->isPast()) {
            $this->logConsumeFailure('expired', $login);
            throw new HttpException(403, 'This one-time login link is invalid or has expired.');
        }

        $integration = PartnerIntegration::query()->find($login->partner_integration_id);

        if (! $integration
            || (int) $integration->mlhub_user_id !== (int) $login->user_id
            || ! $integration->mlhub_workspace_id
            || ! $integration->mlhub_business_id) {
            $this->logConsumeFailure('integration_mapping_broken', $login);
            throw new HttpException(403, 'This one-time login link is invalid or has expired.');
        }

        if (! User::query()->whereKey($login->user_id)->exists()) {
            $this->logConsumeFailure('user_missing', $login);
            throw new HttpException(403, 'This one-time login link is invalid or has expired.');
        }
    }

    /**
     * Logs *why* a one-time login link failed (expired vs already used vs unknown token
     * vs broken mapping) without exposing that distinction to the end user — the HTTP
     * response always stays the same generic 403 so a token cannot be enumerated/probed.
     * This is the only place an admin can diagnose "expired or invalid" reports after the
     * fact, since the token itself is never logged in plaintext.
     */
    private function logConsumeFailure(string $reason, ?PartnerOneTimeLogin $login): void
    {
        $this->safeLog('partner.fizahub.login.consume_failed', 'Rejected a FizaHUB one-time login attempt.', [
            'subject_type' => PartnerOneTimeLogin::class,
            'subject_id' => $login?->id,
            'area' => 'user',
            'causer_user_id' => $login?->user_id,
            'metadata' => [
                'reason' => $reason,
                'partner_integration_id' => $login?->partner_integration_id,
                'expires_at' => $login?->expires_at?->toIso8601String(),
                'used_at' => $login?->used_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function safeLog(string $event, string $description, array $attributes): void
    {
        if (! Schema::hasTable('audit_logs') || ! function_exists('log_activity')) {
            return;
        }

        try {
            log_activity($event, $description, $attributes);
        } catch (\Throwable) {
            // Audit logging must not block login flows.
        }
    }
}
