<?php

namespace App\Policies;

use App\Models\User;
use App\Services\SoporteEmpresaService;

class AdminModulePolicy
{
    public function __construct(private SoporteEmpresaService $soporte) {}

    public function viewResumen(User $user): bool
    {
        return $this->soporte->canViewResumenOperativo($user);
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
