<?php

namespace App\Livewire\Profile;

use App\Actions\Auth\ChangePasswordAction;
use App\Actions\User\UpdateProfileAction;
use App\Models\User;
use App\Services\AppBuildService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Mi perfil')]
class Edit extends Component
{
    #[Url(as: 'seccion', except: 'datos')]
    public string $seccion = 'datos';

    public string $name = '';

    public string $email = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $user = auth()->user();

        if ($user === null) {
            return;
        }

        $this->name = $user->name;
        $this->email = $user->email ?? '';

        if (! in_array($this->seccion, ['datos', 'password'], true)) {
            $this->seccion = 'datos';
        }
    }

    public function guardarDatos(UpdateProfileAction $updateProfile): void
    {
        $user = auth()->user();

        if ($user === null) {
            return;
        }

        $updateProfile->execute($user, [
            'name' => $this->name,
            'email' => $this->email,
        ]);

        $this->dispatch('snackbar-show', message: 'Datos actualizados.', variant: 'success');
    }

    public function guardarContrasena(ChangePasswordAction $changePassword): void
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

        if ($user === null) {
            return;
        }

        $changePassword->execute($user, $this->current_password, $this->password);

        $this->reset(['current_password', 'password', 'password_confirmation']);

        $this->dispatch('snackbar-show', message: 'Contraseña actualizada.', variant: 'success');
    }

    public function render(AppBuildService $appBuildService): View
    {
        /** @var User|null $user */
        $user = auth()->user();

        $usesOperarioShell = request()->routeIs('operario.perfil');

        return view('livewire.profile.edit', [
            'user' => $user,
            'buildLabel' => $appBuildService->labelForProfile(),
            'usesOperarioShell' => $usesOperarioShell,
        ])->layout(
            $usesOperarioShell ? 'components.layouts.operario-mobile' : 'components.layouts.admin'
        );
    }
}
