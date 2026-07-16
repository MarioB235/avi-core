<?php

namespace App\Livewire\Auth;

use App\Actions\Auth\ChangePasswordAction;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.public')]
#[Title('Cambiar contraseña')]
class ChangePassword extends Component
{
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function save(ChangePasswordAction $changePassword): void
    {
        $this->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
        ], [
            'current_password.required' => 'Ingresá la contraseña actual.',
            'password.required' => 'Ingresá la nueva contraseña.',
            'password.confirmed' => 'La confirmación no coincide.',
        ]);

        $user = auth()->user();

        $changePassword->execute($user, $this->current_password, $this->password);

        session()->flash('status', 'Contraseña actualizada correctamente.');

        $this->redirect(route($user->homeRouteName()));
    }

    public function render(): View
    {
        return view('livewire.auth.change-password');
    }
}
