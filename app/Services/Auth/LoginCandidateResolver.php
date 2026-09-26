<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginCandidateResolver
{
    public function __construct(private AccountAccessService $accountAccess) {}

    /**
     * Resuelve la cuenta única elegible para login (documento + contraseña + vigencia).
     *
     * @throws ValidationException
     */
    public function resolveUniqueUser(string $documento, string $password): User
    {
        $documento = trim($documento);

        $candidates = User::query()
            ->with('empresa')
            ->where('documento', $documento)
            ->where('activo', true)
            ->get();

        if ($candidates->isEmpty()) {
            throw ValidationException::withMessages([
                'documento' => 'Credenciales incorrectas.',
            ]);
        }

        $passwordMatches = $candidates->filter(
            fn (User $user) => Hash::check($password, $user->password)
        );

        if ($passwordMatches->isEmpty()) {
            throw ValidationException::withMessages([
                'documento' => 'Credenciales incorrectas.',
            ]);
        }

        $eligible = $passwordMatches->filter(
            fn (User $user) => $this->accountAccess->mayUseApplication($user)
        );

        if ($eligible->count() === 1) {
            return $eligible->first();
        }

        if ($eligible->count() > 1) {
            throw ValidationException::withMessages([
                'documento' => 'No se pudo identificar la cuenta. Contactá al administrador.',
            ]);
        }

        throw ValidationException::withMessages([
            'documento' => $this->blockedAccountMessage($passwordMatches->first()),
        ]);
    }

    private function blockedAccountMessage(User $user): string
    {
        if ($user->empresa_id === null && ! $user->isAdminAvicore()) {
            return 'Usuario sin empresa asignada.';
        }

        return 'La empresa no está activa. Contactá al administrador.';
    }
}
