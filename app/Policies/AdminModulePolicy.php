<?php

namespace App\Policies;

use App\Models\User;

class AdminModulePolicy
{
    public function viewResumen(User $user): bool
    {
        return $user->empresa_id !== null && $user->rol->canViewResumen();
    }

    public function viewEquipo(User $user): bool
    {
        return $user->empresa_id !== null && $user->rol->canViewEquipo();
    }

    public function viewComercial(User $user): bool
    {
        return $user->empresa_id !== null && $user->rol->canViewComercial();
    }
}
