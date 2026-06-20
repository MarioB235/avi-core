<?php

namespace App\Actions\Operacion;

use App\Enums\RegistroOperativoEstado;
use App\Enums\RegistroOperativoTipo;
use App\Models\Galpon;
use App\Models\RegistroOperativo;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class RegistrarCargaHuevosAction
{
    public function execute(User $user, Galpon $galpon, int $huevos, ?string $observacion = null): RegistroOperativo
    {
        Gate::forUser($user)->authorize('view', $galpon);

        if ($user->empresa_id !== $galpon->empresa_id) {
            throw ValidationException::withMessages([
                'galpon_id' => 'No podés cargar en un galpón de otra empresa.',
            ]);
        }

        if (! $galpon->estado->permiteCarga() || ! $galpon->activo) {
            throw ValidationException::withMessages([
                'galpon_id' => 'El galpón no está disponible para carga.',
            ]);
        }

        if ($huevos < 1) {
            throw ValidationException::withMessages([
                'huevos' => 'La cantidad de huevos debe ser mayor a cero.',
            ]);
        }

        return RegistroOperativo::query()->create([
            'empresa_id' => $user->empresa_id,
            'galpon_id' => $galpon->id,
            'user_id' => $user->id,
            'tipo' => RegistroOperativoTipo::Huevos,
            'huevos' => $huevos,
            'observacion' => $observacion !== '' ? $observacion : null,
            'estado' => RegistroOperativoEstado::Activo,
        ]);
    }
}
