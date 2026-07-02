<?php

namespace App\Actions\Operacion;

use App\Enums\RegistroOperativoEstado;
use App\Enums\RegistroOperativoTipo;
use App\Models\Galpon;
use App\Models\RegistroOperativo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class RegistrarCargaMuertesAction
{
    public function execute(User $user, Galpon $galpon, int $muertes, ?string $observacion = null): RegistroOperativo
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

        if ($muertes < 1) {
            throw ValidationException::withMessages([
                'muertes' => 'La cantidad de muertes debe ser mayor a cero.',
            ]);
        }

        return DB::transaction(function () use ($user, $galpon, $muertes, $observacion): RegistroOperativo {
            $galponBloqueado = Galpon::query()
                ->whereKey($galpon->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($muertes > $galponBloqueado->aves_actuales) {
                throw ValidationException::withMessages([
                    'muertes' => 'La cantidad supera las aves vivas del galpón ('.number_format($galponBloqueado->aves_actuales, 0, ',', '.').').',
                ]);
            }

            $registro = RegistroOperativo::query()->create([
                'empresa_id' => $user->empresa_id,
                'galpon_id' => $galponBloqueado->id,
                'user_id' => $user->id,
                'tipo' => RegistroOperativoTipo::Muertes,
                'muertes' => $muertes,
                'observacion' => $observacion !== '' ? $observacion : null,
                'estado' => RegistroOperativoEstado::Activo,
            ]);

            $galponBloqueado->decrement('aves_actuales', $muertes);

            return $registro;
        });
    }
}
