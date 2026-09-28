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
use App\Support\GalponValidacion;
use App\Support\IdempotenciaCaptura;
use App\Support\VacunacionValidacion;
use Illuminate\Support\Facades\DB;
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
        ?string $idempotenciaClave = null,
    ): Vacunacion {
        Gate::forUser($user)->authorize('view', $galpon);

        $this->relations->assertGalponOfActor($user, $galpon);
        $this->relations->assertLoteOfActor($user, $lote);
        $this->relations->assertLoteBelongsToGalpon($lote, $galpon);

        GalponValidacion::assertDisponibleParaCarga($galpon);
        VacunacionValidacion::assertObservacion($observacion);

        if (! in_array($lote->estado, [LoteEstado::Activo, LoteEstado::EnProduccion], true)) {
            throw ValidationException::withMessages([
                'lote_id' => 'El lote no admite vacunación.',
            ]);
        }

        return IdempotenciaCaptura::resolverVacunacion(
            $user,
            $idempotenciaClave,
            fn (?string $clave) => DB::transaction(function () use ($user, $galpon, $lote, $vacuna, $observacion, $clave): Vacunacion {
                $galponBloqueado = GalponValidacion::bloquearParaMutacion($galpon->id);
                GalponValidacion::revalidarParaCargaBajoLock($galponBloqueado);

                $loteBloqueado = Lote::query()
                    ->whereKey($lote->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->relations->assertLoteBelongsToGalpon($loteBloqueado, $galponBloqueado);

                if (! in_array($loteBloqueado->estado, [LoteEstado::Activo, LoteEstado::EnProduccion], true)) {
                    throw ValidationException::withMessages([
                        'lote_id' => 'El lote no admite vacunación.',
                    ]);
                }

                return Vacunacion::query()->create([
                    'empresa_id' => $user->empresa_id,
                    'galpon_id' => $galponBloqueado->id,
                    'lote_id' => $loteBloqueado->id,
                    'user_id' => $user->id,
                    'vacuna' => $vacuna,
                    'idempotencia_clave' => $clave,
                    'observacion' => VacunacionValidacion::normalizarObservacion($observacion),
                    'estado' => RegistroOperativoEstado::Activo,
                ]);
            }),
        );
    }
}
