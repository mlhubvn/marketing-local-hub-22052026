<?php

namespace Modules\AdminSettings\Livewire;

use App\Concerns\PasswordValidationRules;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Modules\AdminSettings\Actions\Logout;
use Modules\AdminUser\Actions\DeleteUser;
use Modules\AdminUser\Models\User;
use Throwable;

class DeleteUserForm extends Component
{
    use PasswordValidationRules;

    public string $password = '';

    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => $this->currentPasswordRules(),
        ]);

        $user = Auth::user();

        if (! $user instanceof User) {
            return;
        }

        try {
            $result = app(DeleteUser::class)->execute($user, (int) $user->id);
        } catch (Throwable $exception) {
            $this->addError('password', $exception->getMessage());

            return;
        }

        if ($result->status === 'already_deleted' || ! $result->deleted) {
            $this->addError('password', __('The account was already deleted or could not be found.'));

            return;
        }

        if ($result->failedVerification()) {
            session()->flash('error', __('The account row was deleted, but verification found :count remaining database references.', [
                'count' => $result->databaseResidueCount,
            ]));
            $logout();
            $this->redirect('/', navigate: true);

            return;
        }

        if ($result->completedWithWarnings()) {
            session()->flash('warning', __('The account was deleted, but :count storage item(s) are pending safe retry.', [
                'count' => $result->storageFailureCount,
            ]));
        }

        $logout();

        $this->redirect('/', navigate: true);
    }

    public function render(): View
    {
        return view('adminsettings::livewire.delete-user-form');
    }
}
