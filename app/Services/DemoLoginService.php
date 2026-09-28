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
        if (app()->environment('production')) {
            return false;
        }

        if (! (bool) config('avicore.demo_login.enabled_flag', false)) {
            return false;
        }

        return $this->demoLoginReady();
    }

    public function isRequestedButUnavailable(): bool
    {
        if (app()->environment('production')) {
            return false;
        }

        if (! (bool) config('avicore.demo_login.enabled_flag', false)) {
            return false;
        }

        return ! $this->demoLoginReady();
    }

    public function resolveUser(string $roleValue): User
    {
        $role = UserRole::tryFrom($roleValue);

        if ($role === null) {
            throw ValidationException::withMessages([
                'demoRole' => 'Seleccioná un perfil válido.',
            ]);
        }

        $documento = $this->documentoForRole($role);

        if ($documento === '') {
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
                'demoRole' => 'No pudimos ingresar con ese perfil. La demo aún no está lista; contactá al equipo de AviCore.',
            ]);
        }

        $this->assertDemoUser($user, $role);

        return $user;
    }

    private function demoLoginReady(): bool
    {
        return $this->demoEmpresaExists() && $this->demoUsersReady();
    }

    private function demoEmpresaExists(): bool
    {
        $codigo = config('avicore.demo_login.empresa_codigo', 'DEMO');

        if (! is_string($codigo) || $codigo === '') {
            return false;
        }

        return Empresa::query()->where('codigo', $codigo)->exists();
    }

    private function demoUsersReady(): bool
    {
        $documentos = config('avicore.demo_login.role_documentos', []);

        if (! is_array($documentos) || $documentos === []) {
            return false;
        }

        foreach ($documentos as $documento) {
            if (! is_string($documento) || trim($documento) === '') {
                return false;
            }

            if (! User::query()
                ->where('documento', trim($documento))
                ->where('activo', true)
                ->exists()) {
                return false;
            }
        }

        return true;
    }

    private function documentoForRole(UserRole $role): string
    {
        $documentos = config('avicore.demo_login.role_documentos', []);

        if (! is_array($documentos)) {
            return '';
        }

        $documento = $documentos[$role->value] ?? null;

        return is_string($documento) ? trim($documento) : '';
    }

    private function assertDemoUser(User $user, UserRole $role): void
    {
        if ($role === UserRole::AdminAvicore) {
            if ($user->rol !== UserRole::AdminAvicore || $user->empresa_id !== null) {
                throw ValidationException::withMessages([
                    'demoRole' => 'El usuario demo de soporte no es válido.',
                ]);
            }

            return;
        }

        $empresaCodigo = config('avicore.demo_login.empresa_codigo', 'DEMO');

        if ($user->empresa === null || $user->empresa->codigo !== $empresaCodigo) {
            throw ValidationException::withMessages([
                'demoRole' => 'El login demo solo está disponible con datos de Avícola Demo.',
            ]);
        }

        if ($user->rol !== $role) {
            throw ValidationException::withMessages([
                'demoRole' => 'El usuario demo no coincide con el perfil seleccionado.',
            ]);
        }
    }
}
