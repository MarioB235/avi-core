<?php

namespace App\Policies;

use App\Models\Lote;
use App\Models\User;

class LotePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->empresa_id !== null
            && ($user->rol->canViewEstructura() || $user->rol->canAccessOperarioMobile());
    }

    public function view(User $user, Lote $lote): bool
    {
        return $user->empresa_id !== null
            && $user->empresa_id === $lote->empresa_id;
    }

    public function create(User $user): bool
    {
        return $user->empresa_id !== null
            && $user->rol->canCreateLote();
    }

    public function update(User $user, Lote $lote): bool
    {
        return $user->empresa_id !== null
            && $user->rol->canManageLotes()
            && $user->empresa_id === $lote->empresa_id;
    }
}
