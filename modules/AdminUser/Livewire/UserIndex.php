<?php

namespace Modules\AdminUser\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AdminUser\Actions\DeleteUser;
use Modules\AdminUser\Models\User;
use Throwable;

#[Title('Admin Users')]
class UserIndex extends Component
{
    use WithPagination;

    public array $selectedUserIds = [];

    #[Url(as: 'q', except: '')]
    public string $q = '';

    #[Url(as: 'status', except: 'all')]
    public string $status = 'all';

    #[Url(as: 'sort', except: 'created')]
    public string $sort = 'created';

    #[Url(as: 'direction', except: 'desc')]
    public string $direction = 'desc';

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['q', 'status', 'sort', 'direction']);
        $this->resetPage();
    }

    public function sortBy(string $key): void
    {
        if ($this->sort === $key) {
            $this->direction = $this->direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $key;
            $this->direction = 'asc';
        }

        $this->resetPage();
    }

    public function deleteUser(int $userId): void
    {
        $user = User::query()->find($userId);

        if (! $user) {
            return;
        }

        if ((int) auth()->id() === (int) $user->id) {
            session()->flash('error', __('You cannot delete the account currently signed in.'));

            return;
        }

        try {
            $result = app(DeleteUser::class)->execute($user, (int) auth()->id());
        } catch (Throwable $exception) {
            session()->flash('error', $exception->getMessage());

            return;
        }

        if ($result->status === 'already_deleted' || ! $result->deleted) {
            session()->flash('error', __('The user was already deleted or could not be found.'));

            return;
        }

        $this->selectedUserIds = array_values(array_filter($this->selectedUserIds, fn ($id) => (int) $id !== $userId));

        if ($result->failedVerification()) {
            session()->flash('error', __('The user row was deleted, but verification found :count remaining database references.', [
                'count' => $result->databaseResidueCount,
            ]));
        } elseif ($result->completedWithWarnings()) {
            session()->flash('warning', __('The user and database data were deleted, but :count storage item(s) are pending safe retry.', [
                'count' => $result->storageFailureCount,
            ]));
        } else {
            session()->flash('status', __('User deleted successfully.'));
        }
    }

    public function deleteSelectedUsers(): void
    {
        $selectedIds = collect($this->selectedUserIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($selectedIds->isEmpty()) {
            session()->flash('error', __('Select at least one account to delete.'));

            return;
        }

        $currentUserId = (int) auth()->id();
        $deletableIds = $selectedIds->reject(fn (int $id) => $id === $currentUserId)->values();

        if ($deletableIds->isEmpty()) {
            session()->flash('error', __('Select at least one other account to delete.'));

            return;
        }

        $usersToDelete = User::query()
            ->whereIn('id', $deletableIds)
            ->get();

        if ($usersToDelete->isEmpty()) {
            session()->flash('error', __('No matching users were available for deletion.'));

            return;
        }

        $deleteUser = app(DeleteUser::class);
        $completed = [];
        $warnings = [];
        $verificationFailures = [];
        $alreadyDeleted = [];
        $blocked = [];

        foreach ($usersToDelete as $user) {
            try {
                $result = $deleteUser->execute($user, $currentUserId);

                if ($result->completedCleanly()) {
                    $completed[] = (int) $user->id;
                } elseif ($result->completedWithWarnings()) {
                    $warnings[] = '#'.$user->id.' ('.$result->storageFailureCount.' storage retry)';
                } elseif ($result->failedVerification()) {
                    $verificationFailures[] = '#'.$user->id.' ('.$result->databaseResidueCount.' database residue)';
                } else {
                    $alreadyDeleted[] = '#'.$user->id;
                }
            } catch (Throwable $exception) {
                $blocked[] = '#'.$user->id.': '.$exception->getMessage();
            }
        }

        $this->selectedUserIds = [];

        if ($completed !== []) {
            session()->flash('status', __('Deleted :count users successfully.', ['count' => count($completed)]));
        }

        if ($warnings !== []) {
            session()->flash('warning', __('Deleted with storage retry pending: :users', [
                'users' => implode(' | ', $warnings),
            ]));
        }

        $problems = array_merge(
            array_map(fn (string $item): string => $item.' '.__('failed verification'), $verificationFailures),
            array_map(fn (string $item): string => $item.' '.__('was already deleted'), $alreadyDeleted),
            $blocked,
        );

        if ($problems !== []) {
            session()->flash('error', __('Some users were not cleanly deleted: :failures', [
                'failures' => implode(' | ', $problems),
            ]));
        }
    }

    public function render(): View
    {
        $filters = [
            'q' => trim($this->q),
            'status' => $this->status,
            'sort' => $this->sort,
            'direction' => $this->direction,
        ];

        $sortMap = [
            'user' => 'name',
            'access' => 'role_id',
            'plan' => 'plan_id',
            'contact' => 'email',
            'created' => 'created_at',
        ];

        $sortColumn = $sortMap[$filters['sort']] ?? $sortMap['created'];
        $sortDirection = $filters['direction'] === 'asc' ? 'asc' : 'desc';

        $query = User::query()
            ->with('role')
            ->with('plan');

        if ($filters['q'] !== '') {
            $query->where(function ($builder) use ($filters): void {
                $builder
                    ->where('name', 'like', '%'.$filters['q'].'%')
                    ->orWhere('username', 'like', '%'.$filters['q'].'%')
                    ->orWhere('email', 'like', '%'.$filters['q'].'%');
            });
        }

        if ($filters['status'] === 'ready') {
            $query
                ->whereNotNull('email')
                ->whereNotNull('role_id')
                ->whereNotNull('plan_id');
        } elseif ($filters['status'] === 'review') {
            $query->where(function ($builder): void {
                $builder
                    ->whereNull('email')
                    ->orWhereNull('role_id')
                    ->orWhereNull('plan_id');
            });
        }

        $users = $query
            ->orderBy($sortColumn, $sortDirection)
            ->orderBy('id', 'desc')
            ->paginate(15, ['id', 'name', 'username', 'email', 'locale', 'role_id', 'plan_id', 'plan_expires_at', 'created_at']);

        return view('adminuser::livewire.index', [
            'users' => $users,
            'filters' => $filters,
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Admin Users'),
        ]);
    }
}
