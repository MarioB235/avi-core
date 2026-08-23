<?php

namespace App\Policies;

use App\Models\Granja;
use App\Models\User;

class GranjaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->rol->canViewEstructura();
    }

    public function view(User $user, Granja $granja): bool
    {
        return $user->rol->canViewEstructura()
            && $user->empresa_id === $granja->empresa_id;
    }

    public function create(User $user): bool
    {
        return $user->rol->canManageEstructura()
            && $user->empresa_id !== null;
    }

    public function update(User $user, Granja $granja): bool
    {
        return $user->rol->canManageEstructura()
            && $user->empresa_id === $granja->empresa_id;
    }
}
