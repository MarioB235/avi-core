<?php

namespace App\Actions\Operacion;

use App\Enums\RegistroOperativoEstado;
use App\Enums\RegistroOperativoTipo;
use App\Models\Galpon;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Services\EmpresaRelationalGuard;
use App\Support\CapturaCeroEstado;
use App\Support\GalponValidacion;
use App\Support\IdempotenciaCaptura;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class RegistrarCargaMuertesAction
{
    public function __construct(private EmpresaRelationalGuard $relations) {}

    public function execute(
        User $user,
        Galpon $galpon,
        int $muertes,
        ?string $observacion = null,
        ?string $idempotenciaClave = null,
        bool $ceroConfirmado = false,
    ): RegistroOperativo {
        Gate::forUser($user)->authorize('view', $galpon);

        $this->relations->assertGalponOfActor($user, $galpon);

        GalponValidacion::assertDisponibleParaCarga($galpon);
        GalponValidacion::assertLoteActivoParaCargaProductiva($galpon);

        if ($ceroConfirmado) {
            CapturaCeroEstado::assertTipoPermiteCeroConfirmado(RegistroOperativoTipo::Muertes);

            if ($muertes !== 0) {
                throw ValidationException::withMessages([
                    'muertes' => 'Para confirmar cero no ingreses cantidad.',
                ]);
            }
        } elseif ($muertes < 1) {
            throw ValidationException::withMessages([
                'muertes' => 'La cantidad de muertes debe ser mayor a cero.',
            ]);
        }

        return IdempotenciaCaptura::resolverRegistroOperativo(
            $user,
            $idempotenciaClave,
            RegistroOperativoTipo::Muertes,
            fn (?string $clave) => DB::transaction(function () use ($user, $galpon, $muertes, $observacion, $clave, $ceroConfirmado): RegistroOperativo {
                $galponBloqueado = GalponValidacion::bloquearParaMutacion($galpon->id);
                GalponValidacion::revalidarParaCargaBajoLock($galponBloqueado, requiereLoteActivo: true);

                if (! $ceroConfirmado && $muertes > $galponBloqueado->aves_actuales) {
                    throw ValidationException::withMessages([
                        'muertes' => 'La cantidad supera las aves vivas del galpón ('.number_format($galponBloqueado->aves_actuales, 0, ',', '.').').',
                    ]);
                }

                $registro = RegistroOperativo::query()->create([
                    'empresa_id' => $user->empresa_id,
                    'galpon_id' => $galponBloqueado->id,
                    'user_id' => $user->id,
                    'tipo' => RegistroOperativoTipo::Muertes,
                    'idempotencia_clave' => $clave,
                    'cero_confirmado' => $ceroConfirmado,
                    'muertes' => $ceroConfirmado ? 0 : $muertes,
                    'observacion' => $observacion !== '' ? $observacion : null,
                    'estado' => RegistroOperativoEstado::Activo,
                ]);

                if (! $ceroConfirmado) {
                    $galponBloqueado->decrement('aves_actuales', $muertes);
                }

                return $registro;
            }),
        );
    }
}
