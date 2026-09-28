<?php

namespace App\Policies;

use App\Models\MovimientoAves;
use App\Models\User;
use App\Services\SoporteEmpresaService;

class MovimientoAvesPolicy
{
    public function __construct(private SoporteEmpresaService $soporte) {}

    public function create(User $user): bool
    {
        if ($this->soporte->blocksProductionMutations($user)) {
            return false;
        }

        return $user->empresa_id !== null
            && $user->rol->canManageLotes();
    }

    public function view(User $user, MovimientoAves $movimiento): bool
    {
        return $user->empresa_id !== null
            && $user->empresa_id === $movimiento->empresa_id;
    }
}
