<?php

namespace App\Livewire\Auth;

use App\Actions\Auth\AttemptLoginAction;
use App\Enums\UserRole;
use App\Services\DemoLoginService;
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

        if (! $this->demoLoginEnabled) {
            return;
        }

        $this->documento = (string) config('avicore.demo_login.documento', '');
        $this->password = (string) config('avicore.demo_login.password', '');
        $this->demoRole = UserRole::Dueno->value;
    }

    public function login(AttemptLoginAction $attemptLogin, DemoLoginService $demoLogin): void
    {
        $isDemoAttempt = $this->demoLoginEnabled
            && $demoLogin->credentialsMatch($this->documento, $this->password);

        $rules = [
            'documento' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ];

        if ($isDemoAttempt) {
            $rules['demoRole'] = ['required', Rule::enum(UserRole::class)];
        }

        $this->validate($rules, [
            'documento.required' => 'El documento es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
            'demoRole.required' => 'Seleccioná un perfil para continuar.',
        ]);

        $demoRole = $isDemoAttempt ? $this->demoRole : null;

        $result = $attemptLogin->execute(
            $this->documento,
            $this->password,
            $this->remember,
            $demoRole,
        );

        $this->redirect(
            $result['must_change_password']
                ? route('password.change')
                : route($result['user']->homeRouteName()),
            navigate: true,
        );
    }

    public function render()
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
