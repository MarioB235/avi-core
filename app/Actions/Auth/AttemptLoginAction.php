<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AttemptLoginAction
{
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 60;

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

        if (! $user->isAdminAvicore()) {
            if ($user->empresa_id === null) {
                $this->hitRateLimiter($documento);

                throw ValidationException::withMessages([
                    'documento' => 'Usuario sin empresa asignada.',
                ]);
            }

            if ($user->empresa === null || ! $user->empresa->permiteLogin()) {
                $this->hitRateLimiter($documento);

                throw ValidationException::withMessages([
                    'documento' => 'La empresa no está activa. Contactá al administrador.',
                ]);
            }
        }

        RateLimiter::clear($this->throttleKey($documento));

        auth()->login($user, $remember);

        $user->forceFill(['last_login_at' => now()])->save();

        return [
            'user' => $user,
            'must_change_password' => $user->must_change_password,
        ];
    }

    private function ensureIsNotRateLimited(string $documento): void
    {
        $key = $this->throttleKey($documento);

        if (! RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            return;
        }

        $seconds = RateLimiter::availableIn($key);

        throw ValidationException::withMessages([
            'documento' => "Demasiados intentos. Probá de nuevo en {$seconds} segundos.",
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
