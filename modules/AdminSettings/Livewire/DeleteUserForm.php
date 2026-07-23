<?php

namespace Modules\AdminSettings\Livewire;

use App\Concerns\PasswordValidationRules;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Modules\AdminSettings\Actions\Logout;
use Modules\AdminUser\Actions\DeleteUser;
use Modules\AdminUser\Models\User;

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

        app(DeleteUser::class)->execute($user, (int) $user->id);
        $logout();

        $this->redirect('/', navigate: true);
    }

    public function render(): View
    {
        return view('adminsettings::livewire.delete-user-form');
    }
}
