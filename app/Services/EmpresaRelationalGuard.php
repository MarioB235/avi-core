<?php

namespace App\Services;

use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class EmpresaRelationalGuard
{
    public function assertActorEmpresa(User $actor, int $empresaId, string $field = 'empresa_id'): void
    {
        if ($actor->empresa_id !== $empresaId) {
            throw ValidationException::withMessages([
                $field => 'No podés usar recursos de otra empresa.',
            ]);
        }
    }

    public function assertGranjaOfEmpresa(Granja $granja, int $empresaId, string $field = 'granja_id'): void
    {
        if ($granja->empresa_id !== $empresaId) {
            throw ValidationException::withMessages([
                $field => 'La granja no pertenece a tu empresa.',
            ]);
        }
    }

    public function assertGalponOfEmpresa(Galpon $galpon, int $empresaId, string $field = 'galpon_id'): void
    {
        if ($galpon->empresa_id !== $empresaId) {
            throw ValidationException::withMessages([
                $field => 'El galpón no pertenece a tu empresa.',
            ]);
        }
    }

    public function assertGalponOfActor(User $actor, Galpon $galpon, string $field = 'galpon_id'): void
    {
        if ($actor->empresa_id === null) {
            throw ValidationException::withMessages([
                $field => 'Tu cuenta no tiene empresa asignada.',
            ]);
        }

        $this->assertGalponOfEmpresa($galpon, (int) $actor->empresa_id, $field);
    }

    public function assertGranjaMatchesGalponEmpresa(Granja $granja, Galpon $galpon, string $field = 'granja_id'): void
    {
        if ($granja->empresa_id !== $galpon->empresa_id) {
            throw ValidationException::withMessages([
                $field => 'La granja no pertenece a tu empresa.',
            ]);
        }
    }

    public function assertLoteOfActor(User $actor, Lote $lote, string $field = 'lote_id'): void
    {
        if ($actor->empresa_id === null) {
            throw ValidationException::withMessages([
                $field => 'Tu cuenta no tiene empresa asignada.',
            ]);
        }

        if ($lote->empresa_id !== $actor->empresa_id) {
            throw ValidationException::withMessages([
                $field => 'El lote no pertenece a tu empresa.',
            ]);
        }
    }

    public function assertLoteBelongsToGalpon(Lote $lote, Galpon $galpon, string $field = 'lote_id'): void
    {
        if ($lote->galpon_id !== $galpon->id || $lote->empresa_id !== $galpon->empresa_id) {
            throw ValidationException::withMessages([
                $field => 'El lote no pertenece al galpón seleccionado.',
            ]);
        }
    }
}
