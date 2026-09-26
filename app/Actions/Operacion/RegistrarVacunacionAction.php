<?php

namespace App\Actions\Operacion;

use App\Enums\LoteEstado;
use App\Enums\RegistroOperativoEstado;
use App\Enums\VacunaTipo;
use App\Models\Galpon;
use App\Models\Lote;
use App\Models\User;
use App\Models\Vacunacion;
use App\Services\EmpresaRelationalGuard;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class RegistrarVacunacionAction
{
    public function __construct(private EmpresaRelationalGuard $relations) {}

    public function execute(
        User $user,
        Galpon $galpon,
        Lote $lote,
        VacunaTipo $vacuna,
        ?string $observacion = null,
    ): Vacunacion {
        Gate::forUser($user)->authorize('view', $galpon);

        $this->relations->assertGalponOfActor($user, $galpon);
        $this->relations->assertLoteOfActor($user, $lote);
        $this->relations->assertLoteBelongsToGalpon($lote, $galpon);

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
