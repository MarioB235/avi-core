<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class DemoLoginService
{
    public function isEnabled(): bool
    {
        return (bool) config('avicore.demo_login.enabled_flag', true)
            && app()->environment('local');
    }

    public function credentialsMatch(string $documento, string $password): bool
    {
        $expectedDocumento = (string) config('avicore.demo_login.documento', '');
        $expectedPassword = (string) config('avicore.demo_login.password', '');

        return hash_equals($expectedDocumento, trim($documento))
            && hash_equals($expectedPassword, $password);
    }

    public function resolveUser(string $roleValue): User
    {
        $role = UserRole::tryFrom($roleValue);

        if ($role === null) {
            throw ValidationException::withMessages([
                'demoRole' => 'Seleccioná un perfil válido.',
            ]);
        }

        $documento = config('avicore.demo_login.role_documents.'.$role->value);

        if (! is_string($documento) || $documento === '') {
            throw ValidationException::withMessages([
                'demoRole' => 'No hay usuario demo configurado para este perfil.',
            ]);
        }

        $user = User::query()
            ->with('empresa')
            ->where('documento', $documento)
            ->where('activo', true)
            ->first();

        if ($user === null) {
            throw ValidationException::withMessages([
                'demoRole' => 'Usuario demo no encontrado. Ejecutá php artisan db:seed.',
            ]);
        }

        if ($user->rol !== $role) {
            throw ValidationException::withMessages([
                'demoRole' => 'El usuario demo no coincide con el perfil seleccionado.',
            ]);
        }

        return $user;
    }
}
