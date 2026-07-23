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

        if (! $result->deleted) {
            $this->addError('password', __('The account was already deleted or could not be found.'));

            return;
        }

        $logout();

        $this->redirect('/', navigate: true);
    }

    public function render(): View
    {
        return view('adminsettings::livewire.delete-user-form');
    }
}
