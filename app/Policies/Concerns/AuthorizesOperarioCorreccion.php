<?php

namespace App\Policies\Concerns;

use App\Enums\RegistroOperativoEstado;
use App\Enums\UserRole;
use App\Models\User;

trait AuthorizesOperarioCorreccion
{
    protected function userCanCorregirRegistroOperativo(
        User $user,
        int $registroEmpresaId,
        RegistroOperativoEstado $estado,
    ): bool {
        if ($user->empresa_id === null || $user->empresa_id !== $registroEmpresaId) {
            return false;
        }

        if ($estado === RegistroOperativoEstado::Anulado) {
            return false;
        }

        return match ($user->rol) {
            UserRole::Dueno, UserRole::Administrativo, UserRole::Encargado => true,
            UserRole::AdminAvicore, UserRole::Operario, UserRole::Reparto => false,
        };
    }
}
