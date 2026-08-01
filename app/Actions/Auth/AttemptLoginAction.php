<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Services\DemoLoginService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AttemptLoginAction
{
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 60;

    public function __construct(
        private readonly DemoLoginService $demoLogin,
    ) {}

    /**
     * @return array{user: User, must_change_password: bool}
     */
    public function execute(string $documento, string $password, bool $remember = false): array
    {
        $documento = trim($documento);

        $this->ensureIsNotRateLimited($documento);

        $candidates = User::query()
            ->with('empresa')
            ->where('documento', $documento)
            ->where('activo', true)
            ->get();

        if ($candidates->isEmpty()) {
            $this->hitRateLimiter($documento);

            throw ValidationException::withMessages([
                'documento' => 'Credenciales incorrectas.',
            ]);
        }

        $matches = $candidates->filter(
            fn (User $user) => Hash::check($password, $user->password)
        );

        if ($matches->count() !== 1) {
            $this->hitRateLimiter($documento);

            throw ValidationException::withMessages([
                'documento' => $matches->isEmpty()
                    ? 'Credenciales incorrectas.'
                    : 'No se pudo identificar la cuenta. Contactá al administrador.',
            ]);
        }

        $user = $matches->first();

        $this->assertUserMayLogin($user, $documento);

        return $this->completeLogin($user, $documento, $remember);
    }

    /**
     * Login por selector de perfil (sin documento/contraseña) cuando demo está activo.
     *
     * @return array{user: User, must_change_password: bool}
     */
    public function executeDemo(string $demoRole, bool $remember = false): array
    {
        if (! $this->demoLogin->isEnabled()) {
            throw ValidationException::withMessages([
                'demoRole' => 'El login por perfil no está disponible.',
            ]);
        }

        $user = $this->demoLogin->resolveUser($demoRole);
        $throttleKey = $user->documento;

        $this->ensureIsNotRateLimited($throttleKey, 'demoRole');
        $this->assertUserMayLogin($user, $throttleKey, 'demoRole');

        return $this->completeLogin($user, $throttleKey, $remember);
    }

    private function assertUserMayLogin(User $user, string $documento, string $errorField = 'documento'): void
    {
        if (! $user->isAdminAvicore()) {
            if ($user->empresa_id === null) {
                $this->hitRateLimiter($documento);

                throw ValidationException::withMessages([
                    $errorField => 'Usuario sin empresa asignada.',
                ]);
            }

            if ($user->empresa === null || ! $user->empresa->permiteLogin()) {
                $this->hitRateLimiter($documento);

                throw ValidationException::withMessages([
                    $errorField => 'La empresa no está activa. Contactá al administrador.',
                ]);
            }
        }
    }

    /**
     * @return array{user: User, must_change_password: bool}
     */
    private function completeLogin(User $user, string $documento, bool $remember): array
    {
        RateLimiter::clear($this->throttleKey($documento));

        auth()->login($user, $remember);

        $user->forceFill(['last_login_at' => now()])->save();

        return [
            'user' => $user,
            'must_change_password' => $user->must_change_password,
        ];
    }

    private function ensureIsNotRateLimited(string $documento, string $errorField = 'documento'): void
    {
        $key = $this->throttleKey($documento);

        if (! RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            return;
        }

        $seconds = RateLimiter::availableIn($key);

        throw ValidationException::withMessages([
            $errorField => "Demasiados intentos. Probá de nuevo en {$seconds} segundos.",
        ]);
    }

    private function hitRateLimiter(string $documento): void
    {
        RateLimiter::hit($this->throttleKey($documento), self::DECAY_SECONDS);
    }

    private function throttleKey(string $documento): string
    {
        return mb_strtolower($documento).'|'.request()->ip();
    }
}
