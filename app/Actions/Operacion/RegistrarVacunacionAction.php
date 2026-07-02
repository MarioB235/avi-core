<?php

namespace App\Actions\Operacion;

use App\Enums\LoteEstado;
use App\Enums\RegistroOperativoEstado;
use App\Enums\VacunaTipo;
use App\Models\Galpon;
use App\Models\Lote;
use App\Models\User;
use App\Models\Vacunacion;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class RegistrarVacunacionAction
{
    public function execute(
        User $user,
        Galpon $galpon,
        Lote $lote,
        VacunaTipo $vacuna,
        ?string $observacion = null,
    ): Vacunacion {
        Gate::forUser($user)->authorize('view', $galpon);

        if ($user->empresa_id !== $galpon->empresa_id || $user->empresa_id !== $lote->empresa_id) {
            throw ValidationException::withMessages([
                'lote_id' => 'No podés vacunar en un lote de otra empresa.',
            ]);
        }

        if ($lote->galpon_id !== $galpon->id) {
            throw ValidationException::withMessages([
                'lote_id' => 'El lote no pertenece al galpón seleccionado.',
            ]);
        }

        if (! $galpon->estado->permiteCarga() || ! $galpon->activo) {
            throw ValidationException::withMessages([
                'galpon_id' => 'El galpón no está disponible para carga.',
            ]);
        }

        if (! in_array($lote->estado, [LoteEstado::Activo, LoteEstado::EnProduccion], true)) {
            throw ValidationException::withMessages([
                'lote_id' => 'El lote no admite vacunación.',
            ]);
        }

        return Vacunacion::query()->create([
            'empresa_id' => $user->empresa_id,
            'galpon_id' => $galpon->id,
            'lote_id' => $lote->id,
            'user_id' => $user->id,
            'vacuna' => $vacuna,
            'observacion' => $observacion !== '' ? $observacion : null,
            'estado' => RegistroOperativoEstado::Activo,
        ]);
    }
}
