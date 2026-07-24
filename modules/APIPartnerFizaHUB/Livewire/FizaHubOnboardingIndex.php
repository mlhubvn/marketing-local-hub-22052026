<?php

namespace Modules\APIPartnerFizaHUB\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AdminUser\Actions\DeleteUser;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\APIPartnerFizaHUB\Services\OnboardingAdminService;
use Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine;
use Throwable;

#[Title('FizaHUB onboarding')]
class FizaHubOnboardingIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: 'all')]
    public string $statusFilter = 'all';

    public ?string $statusMessage = null;

    public ?string $errorMessage = null;

    /** @var array<int|string, string> */
    public array $stageSelections = [];

    /** @var array<int|string, string> */
    public array $packageSelections = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'statusFilter']);
        $this->resetPage();
    }

    public function applyStage(int $id): void
    {
        $toStatus = trim((string) ($this->stageSelections[$id] ?? ''));

        if ($toStatus === '') {
            $this->statusMessage = null;
            $this->errorMessage = __('Please choose a stage.');

            return;
        }

        $this->runAction($id, function (PartnerOnboardingRequest $row) use ($toStatus): void {
            if (! in_array($toStatus, OnboardingStatusMachine::PUBLIC_STATUSES, true)) {
                throw new InvalidArgumentException(__('The selected stage is not allowed from the current status.'));
            }

            $reason = match ($toStatus) {
                OnboardingStatusMachine::CANCELLED => __('Cancelled by admin.'),
                default => __('Status updated by admin.'),
            };

            app(OnboardingAdminService::class)->adminSetStatus(
                $row,
                $toStatus,
                auth()->id(),
                $reason
            );
        }, __('Stage updated.'));

        if ($this->errorMessage === null) {
            $this->stageSelections[$id] = $toStatus;
        }
    }

    public function applyPackage(int $id): void
    {
        $packageCode = trim((string) ($this->packageSelections[$id] ?? ''));

        if ($packageCode === '') {
            $this->statusMessage = null;
            $this->errorMessage = __('Please choose a package.');

            return;
        }

        $this->runAction($id, function (PartnerOnboardingRequest $row) use ($packageCode): void {
            app(OnboardingAdminService::class)->adminAssignPackage(
                $row,
                $packageCode,
                auth()->id(),
                __('Package updated by admin.')
            );
        }, __('Package updated.'));

        if ($this->errorMessage === null) {
            $this->packageSelections[$id] = $packageCode;
        }
    }

    public function resendWebhook(int $id): void
    {
        $this->runAction($id, function (PartnerOnboardingRequest $row): ?string {
            app(OnboardingAdminService::class)->resendWebhook($row);
        }, __('Webhook re-queued for delivery.'));
    }

    public function deleteOnboarding(int $id): void
    {
        $this->runAction($id, function (PartnerOnboardingRequest $row): ?string {
            app(OnboardingAdminService::class)->adminPurgeOnboarding($row, auth()->id());

            unset($this->stageSelections[$row->id], $this->packageSelections[$row->id]);
        }, __('Onboarding data deleted. The MLHUB user account was kept.'));
    }

    public function deleteUserAndData(int $id): void
    {
        $this->runAction($id, function (PartnerOnboardingRequest $row): ?string {
            $service = app(OnboardingAdminService::class);
            $user = $service->resolveUserForDeletion($row);

            if ((int) auth()->id() === (int) $user->id) {
                throw new InvalidArgumentException(__('You cannot delete the account currently signed in.'));
            }

            $result = app(DeleteUser::class)->execute($user, (int) auth()->id());

            if ($result->status === 'already_deleted' || ! $result->deleted) {
                throw new InvalidArgumentException(__('The MLHUB user was already deleted or could not be found.'));
            }

            if ($result->failedVerification()) {
                throw new InvalidArgumentException(__('The user row was deleted, but verification found :count remaining database references.', [
                    'count' => $result->databaseResidueCount,
                ]));
            }

            unset($this->stageSelections[$row->id], $this->packageSelections[$row->id]);

            return $result->completedWithWarnings()
                ? __('The MLHUB user and database data were deleted, but :count storage item(s) are pending safe retry.', [
                    'count' => $result->storageFailureCount,
                ])
                : null;
        }, __('The MLHUB user and all owned operational data were deleted.'));
    }

    public function render(): View
    {
        $requests = $this->baseQuery()->paginate(15);

        $counts = PartnerOnboardingRequest::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $stageOptionsById = [];
        $userDeletionPreviews = [];
        foreach ($requests as $request) {
            $stageOptionsById[$request->id] = $this->adminStageOptionsFor($request);
            $userDeletionPreviews[$request->id] = $this->userDeletionPreview($request);
            $this->syncRowSelections($request);
        }

        return view('apipartnerfizahub::livewire.onboarding-index', [
            'requests' => $requests,
            'counts' => $counts,
            'totalCount' => array_sum($counts),
            'statusOptions' => OnboardingStatusMachine::labels(),
            'stageOptionsById' => $stageOptionsById,
            'packageOptions' => $this->packageOptions(),
            'userDeletionPreviews' => $userDeletionPreviews,
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('FizaHUB onboarding'),
        ]);
    }

    /**
     * @return array{name:string,identity:string,business:string,resolvable:bool}
     */
    protected function userDeletionPreview(PartnerOnboardingRequest $request): array
    {
        try {
            $user = app(OnboardingAdminService::class)->resolveUserForDeletion($request);

            return [
                'name' => (string) $user->name,
                'identity' => $this->maskIdentity((string) ($user->email ?: $user->username)),
                'business' => (string) ($request->business?->name
                    ?: data_get($request->payload, 'business.name')
                    ?: $request->external_business_id),
                'resolvable' => true,
            ];
        } catch (Throwable) {
            return [
                'name' => __('Unresolved user'),
                'identity' => '—',
                'business' => (string) ($request->business?->name
                    ?: data_get($request->payload, 'business.name')
                    ?: $request->external_business_id),
                'resolvable' => false,
            ];
        }
    }

    protected function maskIdentity(string $identity): string
    {
        $identity = trim($identity);

        if ($identity === '') {
            return '—';
        }

        if (str_contains($identity, '@')) {
            [$local, $domain] = array_pad(explode('@', $identity, 2), 2, '');

            return mb_substr($local, 0, 1).'***@'.$domain;
        }

        return mb_substr($identity, 0, min(2, mb_strlen($identity))).'***';
    }

    /**
     * @return array<string, string>
     */
    protected function adminStageOptionsFor(PartnerOnboardingRequest $request): array
    {
        $labels = OnboardingStatusMachine::labels();
        $options = [];

        foreach (OnboardingStatusMachine::PUBLIC_STATUSES as $code) {
            $options[$code] = $labels[$code] ?? $code;
        }

        return $options;
    }

    /**
     * Prefill selects with the row's current status/package so admins see the live value.
     */
    protected function syncRowSelections(PartnerOnboardingRequest $request): void
    {
        $id = $request->id;
        $currentStatus = OnboardingStatusMachine::publicStatus((string) $request->status);
        $currentPackage = strtolower(trim((string) ($request->package_code ?: '')));
        $packageCodes = array_keys($this->packageOptions());

        $stageSelection = trim((string) ($this->stageSelections[$id] ?? ''));
        if ($stageSelection === '' || ! in_array($stageSelection, OnboardingStatusMachine::PUBLIC_STATUSES, true)) {
            $this->stageSelections[$id] = $currentStatus;
        }

        $packageSelection = strtolower(trim((string) ($this->packageSelections[$id] ?? '')));
        if ($packageSelection === '' || ! in_array($packageSelection, $packageCodes, true)) {
            $this->packageSelections[$id] = in_array($currentPackage, $packageCodes, true)
                ? $currentPackage
                : (string) ($packageCodes[0] ?? '');
        }
    }

    /**
     * @return array<string, string>
     */
    protected function packageOptions(): array
    {
        $options = [];

        foreach (array_keys((array) config('modules.apipartnerfizahub.package_map', [])) as $code) {
            $options[(string) $code] = strtoupper((string) $code);
        }

        return $options;
    }

    protected function baseQuery()
    {
        return PartnerOnboardingRequest::query()
            ->with(['user', 'business', 'consultant'])
            ->when($this->search !== '', function ($builder): void {
                $search = trim($this->search);

                $builder->where(function ($nested) use ($search): void {
                    $nested->where('external_business_id', 'like', "%{$search}%")
                        ->orWhere('request_id', 'like', "%{$search}%");
                });
            })
            ->when($this->statusFilter !== 'all', fn ($builder) => $builder->where('status', $this->statusFilter))
            ->orderByDesc('id');
    }

    protected function runAction(int $id, callable $callback, string $successMessage): void
    {
        $this->statusMessage = null;
        $this->errorMessage = null;

        $row = PartnerOnboardingRequest::query()->find($id);

        if (! $row) {
            $this->errorMessage = __('Onboarding request was not found.');

            return;
        }

        try {
            $outcomeMessage = $callback($row);
            $this->statusMessage = is_string($outcomeMessage) && $outcomeMessage !== ''
                ? $outcomeMessage
                : $successMessage;
        } catch (Throwable $exception) {
            $this->errorMessage = $exception->getMessage();
        }
    }
}
