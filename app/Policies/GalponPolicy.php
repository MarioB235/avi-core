<?php

namespace App\Policies;

use App\Models\Galpon;
use App\Models\User;

class GalponPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->empresa_id !== null
            && ($user->rol->canViewEstructura() || $user->rol->canAccessOperarioMobile());
    }

    public function view(User $user, Galpon $galpon): bool
    {
        return $user->empresa_id !== null
            && $user->empresa_id === $galpon->empresa_id;
    }

    public function create(User $user): bool
    {
        return $user->rol->canManageEstructura()
            && $user->empresa_id !== null;
    }

    public function update(User $user, Galpon $galpon): bool
    {
        return $user->rol->canManageEstructura()
            && $user->empresa_id === $galpon->empresa_id;
    }
}
