<?php

namespace App\Policies;

use App\Models\Empresa;
use App\Models\User;

class EmpresaPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->rol->canManageEmpresas();
    }

    public function view(User $actor, Empresa $empresa): bool
    {
        return $actor->rol->canManageEmpresas();
    }

    public function create(User $actor): bool
    {
        return $actor->rol->canManageEmpresas();
    }

    public function updateEstado(User $actor, Empresa $empresa): bool
    {
        return $actor->rol->canManageEmpresas();
    }

    public function update(User $actor, Empresa $empresa): bool
    {
        return $actor->rol->canManageEmpresas();
    }

    public function enterSupport(User $actor, Empresa $empresa): bool
    {
        return $actor->rol->canManageEmpresas() && $empresa->permiteLogin();
    }
}
