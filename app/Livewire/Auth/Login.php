<?php

namespace App\Livewire\Auth;

use App\Actions\Auth\AttemptLoginAction;
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

    public function login(AttemptLoginAction $attemptLogin): void
    {
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

        $this->redirect(
            $result['must_change_password']
                ? route('password.change')
                : route($result['user']->homeRouteName()),
            navigate: true,
        );
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
