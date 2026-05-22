<?php

namespace Modules\AppCustomDomain\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppCustomDomain\Models\AppCustomDomain;
use Modules\AppCustomDomain\Support\DomainHealthChecker;
use Modules\AppTeams\Support\ActivityLogger;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

#[Title('Custom Domains')]
class DomainsIndex extends Component
{
    public string $domain = '';

    public array $domainErrors = [];

    public function mount(): void
    {
        abort_unless($this->canUseCustomDomains(), 403);
    }

    public function addDomain(): void
    {
        if (! $this->canUseCustomDomains()) {
            throw ValidationException::withMessages([
                'domain' => __('Your current plan does not include custom domains.'),
            ]);
        }

        $this->resetErrorBag('domain');
        $this->domain = Str::lower(trim(preg_replace('#^https?://#i', '', $this->domain)));
        $this->domain = rtrim(strtok($this->domain, '/') ?: $this->domain, '/');

        $validated = validator(['domain' => $this->domain], [
            'domain' => ['required', 'string', 'max:190', 'regex:/^[a-z0-9.-]+\.[a-z]{2,}$/i'],
        ], [
            'domain.required' => __('Enter a domain first.'),
            'domain.regex' => __('Enter a valid domain, for example go.yourbrand.com.'),
        ])->validate();
        $team = TeamWorkspaceAccess::activeTeam(auth()->user());
        $ownerId = TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());
        $domain = strtolower($validated['domain']);

        if (AppCustomDomain::query()->where('domain', $domain)->exists()) {
            $this->addError('domain', __('This domain already exists.'));

            return;
        }

        $this->ensureDomainLimit($ownerId);

        $createdDomain = AppCustomDomain::query()->create([
            'owner_user_id' => $ownerId,
            'team_id' => $team?->id,
            'domain' => $domain,
            'status' => 'pending',
            'verification_token' => AppCustomDomain::verificationToken(),
        ]);

        app(ActivityLogger::class)->log('domain.created', [
            'team_id' => $team?->id,
            'owner_user_id' => $ownerId,
            'subject_type' => AppCustomDomain::class,
            'subject_id' => $createdDomain->id,
            'metadata' => ['domain' => $domain, 'scope' => 'shared'],
        ]);

        $this->domain = '';
        $this->dispatch('app-toast', type: 'success', message: __('Domain added.'));
    }

    public function markVerified(string $scope, int $domainId): void
    {
        $domain = AppCustomDomain::query()
            ->where('owner_user_id', TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user()))
            ->whereKey($domainId)
            ->firstOrFail();

        $errorKey = $scope.'-'.$domainId;
        unset($this->domainErrors[$errorKey]);

        if (! $this->hasVerificationTxtRecord((string) $domain->domain, (string) $domain->verification_token)) {
            $this->domainErrors[$errorKey] = __('TXT record not found yet. Add the verification token in DNS, wait for propagation, then verify again.');

            return;
        }

        $domain->update(['status' => 'verified', 'verified_at' => now()]);
        app(ActivityLogger::class)->log('domain.verified', [
            'team_id' => $domain->team_id,
            'owner_user_id' => $domain->owner_user_id,
            'subject_type' => AppCustomDomain::class,
            'subject_id' => $domain->id,
            'metadata' => ['domain' => $domain->domain, 'scope' => 'shared'],
        ]);
        $this->dispatch('app-toast', type: 'success', message: __('Domain verified.'));
    }

    public function deleteDomain(string $scope, int $domainId): void
    {
        $domain = AppCustomDomain::query()
            ->where('owner_user_id', TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user()))
            ->whereKey($domainId)
            ->firstOrFail();

        app(ActivityLogger::class)->log('domain.deleted', [
            'team_id' => $domain->team_id,
            'owner_user_id' => $domain->owner_user_id,
            'subject_type' => AppCustomDomain::class,
            'subject_id' => $domain->id,
            'metadata' => ['domain' => $domain->domain, 'scope' => 'shared'],
        ]);

        $domain->delete();

        $this->dispatch('app-toast', type: 'success', message: __('Domain deleted.'));
    }

    public function setDefault(string $scope, int $domainId): void
    {
        $ownerId = TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());

        $domain = AppCustomDomain::query()
            ->where('owner_user_id', $ownerId)
            ->where('status', 'verified')
            ->whereKey($domainId)
            ->firstOrFail();

        AppCustomDomain::query()
            ->where('owner_user_id', $ownerId)
            ->whereKeyNot($domain->id)
            ->update(['is_default' => false]);

        $domain->forceFill(['is_default' => true])->save();

        $this->dispatch('app-toast', type: 'success', message: __('Default domain updated.'));
    }

    public function unsetDefault(string $scope, int $domainId): void
    {
        AppCustomDomain::query()
            ->where('owner_user_id', TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user()))
            ->whereKey($domainId)
            ->update(['is_default' => false]);

        $this->dispatch('app-toast', type: 'success', message: __('Default domain removed.'));
    }

    public function render(): View
    {
        $ownerId = TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());

        return view('appcustomdomain::index', [
            'domains' => $this->domains($ownerId),
            'domainLimit' => $this->domainLimit(),
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('Custom Domains')]);
    }

    protected function domains(int $ownerId): Collection
    {
        $health = app(DomainHealthChecker::class);
        return AppCustomDomain::query()
            ->where('owner_user_id', $ownerId)
            ->latest()
            ->get()
            ->map(fn (AppCustomDomain $domain): array => [
                'id' => $domain->id,
                'scope' => 'shared',
                'domain' => $domain->domain,
                'status' => $domain->status,
                'is_default' => (bool) $domain->is_default,
                'verification_token' => $domain->verification_token,
                'health' => $health->inspect((string) $domain->domain, (string) $domain->verification_token),
                'created_at' => $domain->created_at,
            ])
            ->values();
    }

    protected function hasVerificationTxtRecord(string $domain, string $token): bool
    {
        return app(DomainHealthChecker::class)->hasTxt($domain, $token);
    }

    protected function canUseCustomDomains(): bool
    {
        $user = auth()->user();

        return ! $user?->plan || ($user->canUsePlanFeature('qr_custom_domains') ?? false);
    }

    protected function domainLimit(): int
    {
        return (int) (auth()->user()?->planLimit('max_custom_domains', -1) ?? -1);
    }

    protected function ensureDomainLimit(int $ownerId): void
    {
        $limit = $this->domainLimit();

        if ($limit < 0) {
            return;
        }

        $used = AppCustomDomain::query()->where('owner_user_id', $ownerId)->count();

        if ($used < $limit) {
            return;
        }

        throw ValidationException::withMessages([
            'domain' => __('Your current plan allows up to :limit custom domains.', ['limit' => $limit]),
        ]);
    }
}
