<?php

namespace App\Actions\Operacion;

use App\Enums\RegistroOperativoEstado;
use App\Enums\RegistroOperativoTipo;
use App\Models\Galpon;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Services\EmpresaRelationalGuard;
use App\Support\AlimentoValidacion;
use App\Support\GalponValidacion;
use App\Support\IdempotenciaCaptura;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class RegistrarCargaAlimentoAction
{
    public function __construct(private EmpresaRelationalGuard $relations) {}

    public function execute(
        User $user,
        Galpon $galpon,
        float $alimentoKg,
        ?string $observacion = null,
        ?string $idempotenciaClave = null,
    ): RegistroOperativo {
        Gate::forUser($user)->authorize('view', $galpon);

        $this->relations->assertGalponOfActor($user, $galpon);

        GalponValidacion::assertDisponibleParaCarga($galpon);

        AlimentoValidacion::assertRango($alimentoKg);

        return IdempotenciaCaptura::resolverRegistroOperativo(
            $user,
            $idempotenciaClave,
            RegistroOperativoTipo::Alimento,
            fn (?string $clave) => DB::transaction(function () use ($user, $galpon, $alimentoKg, $observacion, $clave): RegistroOperativo {
                $galponBloqueado = GalponValidacion::bloquearParaMutacion($galpon->id);
                GalponValidacion::revalidarParaCargaBajoLock($galponBloqueado);

                return RegistroOperativo::query()->create([
                    'empresa_id' => $user->empresa_id,
                    'galpon_id' => $galponBloqueado->id,
                    'user_id' => $user->id,
                    'tipo' => RegistroOperativoTipo::Alimento,
                    'idempotencia_clave' => $clave,
                    'alimento_kg' => round($alimentoKg, 2),
                    'observacion' => $observacion !== '' ? $observacion : null,
                    'estado' => RegistroOperativoEstado::Activo,
                ]);
            }),
        );
    }
}
