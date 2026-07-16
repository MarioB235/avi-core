<?php

namespace App\Livewire\Auth;

use App\Actions\Auth\AttemptLoginAction;
use App\Enums\UserRole;
use App\Services\DemoLoginService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.public')]
#[Title('Iniciar sesión')]
class Login extends Component
{
    public string $documento = '';

    public string $password = '';

    public bool $remember = false;

    public bool $demoLoginEnabled = false;

    public string $demoRole = '';

    public function mount(DemoLoginService $demoLogin): void
    {
        $this->demoLoginEnabled = $demoLogin->isEnabled();

        if ($this->demoLoginEnabled) {
            $this->demoRole = UserRole::Dueno->value;
        }
    }

    public function login(AttemptLoginAction $attemptLogin): void
    {
        if ($this->demoLoginEnabled) {
            $this->validate([
                'demoRole' => ['required', Rule::enum(UserRole::class)],
            ], [
                'demoRole.required' => 'Seleccioná un perfil para continuar.',
            ]);

            $result = $attemptLogin->executeDemo($this->demoRole, $this->remember);
        } else {
            $this->validate([
                'documento' => ['required', 'string', 'max:50'],
                'password' => ['required', 'string'],
            ], [
                'documento.required' => 'El documento es obligatorio.',
                'password.required' => 'La contraseña es obligatoria.',
            ]);

            $result = $attemptLogin->execute(
                $this->documento,
                $this->password,
                $this->remember,
            );
        }

        // Full page: cambia layout (público → admin u operario-mobile).
        $this->redirect(
            $result['must_change_password']
                ? route('password.change')
                : route($result['user']->homeRouteName()),
        );
    }

    public function render(): View
    {
        $viewData = [];

        if ($this->demoLoginEnabled) {
            $viewData['demoRoleOptions'] = collect(UserRole::cases())
                ->mapWithKeys(fn (UserRole $role): array => [$role->value => $role->label()])
                ->all();
        }

        return view('livewire.auth.login', $viewData);
    }
}
