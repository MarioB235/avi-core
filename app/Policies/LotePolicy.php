<?php

namespace App\Policies;

use App\Enums\LoteEstado;
use App\Models\Lote;
use App\Models\User;
use App\Services\SoporteEmpresaService;

class LotePolicy
{
    public function __construct(private SoporteEmpresaService $soporte) {}

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
        if ($this->soporte->blocksProductionMutations($user)) {
            return false;
        }

        return $user->empresa_id !== null
            && $user->rol->canCreateLote();
    }

    public function update(User $user, Lote $lote): bool
    {
        if ($this->soporte->blocksProductionMutations($user)) {
            return false;
        }

        return $user->empresa_id !== null
            && $user->rol->canManageLotes()
            && $user->empresa_id === $lote->empresa_id;
    }

    public function transition(User $user, Lote $lote): bool
    {
        if ($this->soporte->blocksProductionMutations($user)) {
            return false;
        }

        if ($user->empresa_id === null
            || ! $user->rol->canManageLotes()
            || $user->empresa_id !== $lote->empresa_id) {
            return false;
        }

        if ($lote->estado->esTerminal()) {
            return false;
        }

        if ($lote->estado === LoteEstado::Cerrado && ! $user->rol->canReabrirLote()) {
            return false;
        }

        return true;
    }
}
