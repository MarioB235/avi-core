<?php

namespace App\Policies\Concerns;

use App\Enums\RegistroOperativoEstado;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Carbon;

trait AuthorizesOperarioAnulacion
{
    protected function userCanAnularOperarioRegistro(
        User $user,
        int $registroEmpresaId,
        int $registroUserId,
        RegistroOperativoEstado $estado,
        ?Carbon $createdAt,
    ): bool {
        if ($user->empresa_id === null || $user->empresa_id !== $registroEmpresaId) {
            return false;
        }

        if ($estado === RegistroOperativoEstado::Anulado) {
            return false;
        }

        if (! $createdAt?->isToday()) {
            return false;
        }

        if ($registroUserId === $user->id) {
            return $this->puedeAnularPropio($user);
        }

        return $this->puedeAnularAjeno($user);
    }

    private function puedeAnularPropio(User $user): bool
    {
        return match ($user->rol) {
            UserRole::Dueno, UserRole::Administrativo, UserRole::Encargado, UserRole::Operario => true,
            UserRole::AdminAvicore => false,
        };
    }

    private function puedeAnularAjeno(User $user): bool
    {
        return match ($user->rol) {
            UserRole::Dueno, UserRole::Administrativo, UserRole::Encargado => true,
            UserRole::AdminAvicore, UserRole::Operario => false,
        };
    }
}
