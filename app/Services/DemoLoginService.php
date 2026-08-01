<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class DemoLoginService
{
    public function isEnabled(): bool
    {
        return (bool) config('avicore.demo_login.enabled_flag', false);
    }

    public function resolveUser(string $roleValue): User
    {
        $role = UserRole::tryFrom($roleValue);

        if ($role === null) {
            throw ValidationException::withMessages([
                'demoRole' => 'Seleccioná un perfil válido.',
            ]);
        }

        $documento = config('avicore.demo_login.documento');

        if (! is_string($documento) || $documento === '') {
            throw ValidationException::withMessages([
                'demoRole' => 'No hay usuario demo configurado.',
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

        $empresaId = $role === UserRole::AdminAvicore
            ? null
            : Empresa::query()->where('codigo', 'DEMO')->value('id');

        if ($role !== UserRole::AdminAvicore && $empresaId === null) {
            throw ValidationException::withMessages([
                'demoRole' => 'Empresa demo no encontrada. Ejecutá php artisan db:seed.',
            ]);
        }

        $user->forceFill([
            'rol' => $role,
            'empresa_id' => $empresaId,
        ])->save();

        $user->load('empresa');

        return $user;
    }
}
