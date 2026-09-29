<?php

namespace App\Actions\Movimiento;

use App\Actions\Auditoria\RegistrarAuditoriaAction;
use App\Enums\AuditoriaCategoria;
use App\Enums\MovimientoAvesEstado;
use App\Enums\MovimientoAvesOrigen;
use App\Enums\MovimientoAvesTipo;
use App\Models\Galpon;
use App\Models\MovimientoAves;
use App\Models\User;
use App\Services\EmpresaRelationalGuard;
use App\Services\MovimientoAvesConciliacionService;
use App\Support\GalponValidacion;
use App\Support\IdempotenciaMovimiento;
use App\Support\MovimientoAvesValidacion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class RegistrarAjusteInventarioAvesAction
{
    public function __construct(
        private EmpresaRelationalGuard $relations,
        private MovimientoAvesConciliacionService $conciliacion,
        private RegistrarAuditoriaAction $auditoria,
    ) {}

    public function execute(
        User $user,
        Galpon $galpon,
        int $conteoFisico,
        string $motivo,
        ?string $idempotenciaClave = null,
        ?Carbon $fechaEfectiva = null,
    ): MovimientoAves {
        Gate::forUser($user)->authorize('create', MovimientoAves::class);

        $this->relations->assertGalponOfActor($user, $galpon, 'galponId');

        if ($conteoFisico < 0) {
            throw ValidationException::withMessages([
                'conteoFisico' => 'El conteo físico no puede ser negativo.',
            ]);
        }

        $motivo = trim($motivo);

        if ($motivo === '') {
            throw ValidationException::withMessages([
                'motivo' => 'Ingresá el motivo del ajuste.',
            ]);
        }

        $fechaEfectiva ??= now();

        return DB::transaction(function () use (
            $user,
            $galpon,
            $conteoFisico,
            $motivo,
            $idempotenciaClave,
            $fechaEfectiva,
        ): MovimientoAves {
            return IdempotenciaMovimiento::resolver($user, $idempotenciaClave, function (?string $clave) use (
                $user,
                $galpon,
                $conteoFisico,
                $motivo,
                $fechaEfectiva,
            ): MovimientoAves {
                $galponBloqueado = GalponValidacion::bloquearParaMutacion($galpon->id);

                $saldoSistema = (int) $galponBloqueado->aves_actuales;
                $ajusteDelta = $conteoFisico - $saldoSistema;

                if ($ajusteDelta === 0) {
                    throw ValidationException::withMessages([
                        'conteoFisico' => 'El conteo coincide con el saldo del sistema; no hace falta ajuste.',
                    ]);
                }

                if ($saldoSistema + $ajusteDelta < 0) {
                    throw ValidationException::withMessages([
                        'conteoFisico' => 'El ajuste dejaría saldo negativo en el galpón.',
                    ]);
                }

                $loteUnico = $this->conciliacion->loteActivoUnico($galponBloqueado);

                $movimiento = new MovimientoAves([
                    'empresa_id' => $user->empresa_id,
                    'tipo' => MovimientoAvesTipo::Ajuste,
                    'estado' => MovimientoAvesEstado::Activo,
                    'galpon_origen_id' => $galponBloqueado->id,
                    'lote_id' => $loteUnico?->id,
                    'cantidad' => abs($ajusteDelta),
                    'ajuste_delta' => $ajusteDelta,
                    'motivo' => $motivo,
                    'registrado_por' => $user->id,
                    'fecha_efectiva' => $fechaEfectiva,
                    'metadata' => [
                        'origen' => MovimientoAvesOrigen::AjusteInventario->value,
                        'impacta_aves_actuales' => true,
                        'conteo_fisico' => $conteoFisico,
                        'saldo_sistema_antes' => $saldoSistema,
                        'ajuste_delta' => $ajusteDelta,
                    ],
                    'idempotencia_clave' => $clave,
                ]);

                MovimientoAvesValidacion::assertEstructuraMinima($movimiento);
                $movimiento->save();

                if ($ajusteDelta > 0) {
                    $galponBloqueado->increment('aves_actuales', $ajusteDelta);
                } else {
                    $galponBloqueado->decrement('aves_actuales', abs($ajusteDelta));
                }

                $this->auditoria->execute(
                    $user,
                    AuditoriaCategoria::Movimiento,
                    'ajuste_inventario',
                    MovimientoAves::class,
                    $movimiento->id,
                    $movimiento->empresa_id,
                    $motivo,
                    [
                        'galpon_id' => $galponBloqueado->id,
                        'conteo_fisico' => $conteoFisico,
                        'saldo_sistema_antes' => $saldoSistema,
                        'ajuste_delta' => $ajusteDelta,
                    ],
                );

                return $movimiento;
            });
        });
    }
}
