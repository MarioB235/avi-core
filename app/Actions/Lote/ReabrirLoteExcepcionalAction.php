<?php

namespace App\Actions\Lote;

use App\Actions\Auditoria\RegistrarAuditoriaAction;
use App\Enums\AuditoriaCategoria;
use App\Enums\LoteEstado;
use App\Enums\MovimientoAvesEstado;
use App\Enums\MovimientoAvesOrigen;
use App\Enums\MovimientoAvesTipo;
use App\Models\Galpon;
use App\Models\Lote;
use App\Models\MovimientoAves;
use App\Models\User;
use App\Services\EmpresaRelationalGuard;
use App\Support\GalponValidacion;
use App\Support\IdempotenciaMovimiento;
use App\Support\LoteValidacion;
use App\Support\MovimientoAvesValidacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ReabrirLoteExcepcionalAction
{
    public function __construct(
        private EmpresaRelationalGuard $relations,
        private TransicionarLoteEstadoAction $transicionarLote,
        private RegistrarAuditoriaAction $auditoria,
    ) {}

    public function execute(
        User $user,
        Lote $lote,
        string $motivo,
        LoteEstado $estadoDestino = LoteEstado::Activo,
    ): Lote {
        $this->relations->assertLoteOfActor($user, $lote, 'lote_id');

        $reintento = IdempotenciaMovimiento::buscarExistente(
            $user,
            IdempotenciaMovimiento::claveReaperturaLote($lote->id),
        );

        if ($reintento !== null) {
            return $lote->fresh();
        }

        Gate::forUser($user)->authorize('transition', $lote);

        if ($lote->estado !== LoteEstado::Cerrado) {
            throw ValidationException::withMessages([
                'lote_id' => 'Solo podés reabrir un lote en estado cerrado.',
            ]);
        }

        if (! in_array($estadoDestino, [LoteEstado::Activo, LoteEstado::EnProduccion], true)) {
            throw ValidationException::withMessages([
                'estado' => 'La reapertura solo puede volver a activo o en producción.',
            ]);
        }

        $motivo = LoteValidacion::assertMotivoTransicion($motivo);

        $galpon = $lote->galpon;

        if (! $galpon instanceof Galpon) {
            throw ValidationException::withMessages([
                'lote_id' => 'No se encontró el galpón del lote.',
            ]);
        }

        $this->relations->assertGalponOfActor($user, $galpon, 'galponId');
        $this->assertSinCicloActivoConflictivo($galpon, $lote);

        $cierreReferencia = $this->ultimoCierreCiclo($lote);
        $cantidadRestaurar = $cierreReferencia?->cantidad ?? 0;

        return DB::transaction(function () use (
            $user,
            $lote,
            $galpon,
            $motivo,
            $estadoDestino,
            $cierreReferencia,
            $cantidadRestaurar,
        ): Lote {
            $galponBloqueado = GalponValidacion::bloquearParaMutacion($galpon->id);
            $this->assertSinCicloActivoConflictivo($galponBloqueado, $lote);

            if ($cantidadRestaurar > 0 && $galponBloqueado->aves_actuales > 0) {
                throw ValidationException::withMessages([
                    'galponId' => 'El galpón ya tiene aves vivas; no podés reabrir este lote encima de otro ciclo.',
                ]);
            }

            $loteReabierto = $this->transicionarLote->execute($user, $lote, [
                'estado' => $estadoDestino->value,
                'motivo' => $motivo,
            ]);

            if ($cantidadRestaurar > 0) {
                $movimiento = new MovimientoAves([
                    'empresa_id' => $user->empresa_id,
                    'tipo' => MovimientoAvesTipo::Entrada,
                    'estado' => MovimientoAvesEstado::Activo,
                    'galpon_destino_id' => $galponBloqueado->id,
                    'lote_id' => $loteReabierto->id,
                    'cantidad' => $cantidadRestaurar,
                    'motivo' => 'Reapertura excepcional del lote '.$loteReabierto->codigo,
                    'registrado_por' => $user->id,
                    'fecha_efectiva' => now(),
                    'metadata' => [
                        'origen' => MovimientoAvesOrigen::ReaperturaLote->value,
                        'impacta_aves_actuales' => true,
                        'cierre_referencia_id' => $cierreReferencia?->id,
                        'lote_codigo' => $loteReabierto->codigo,
                    ],
                    'idempotencia_clave' => IdempotenciaMovimiento::claveReaperturaLote($loteReabierto->id),
                ]);

                MovimientoAvesValidacion::assertEstructuraMinima($movimiento);
                $movimiento->save();

                $galponBloqueado->increment('aves_actuales', $cantidadRestaurar);
            }

            $this->auditoria->execute(
                $user,
                AuditoriaCategoria::Lote,
                'reapertura_excepcional',
                Lote::class,
                $loteReabierto->id,
                $loteReabierto->empresa_id,
                $motivo,
                [
                    'estado_destino' => $estadoDestino->value,
                    'cantidad_restaurada' => $cantidadRestaurar,
                    'cierre_referencia_id' => $cierreReferencia?->id,
                ],
            );

            return $loteReabierto->fresh();
        });
    }

    private function assertSinCicloActivoConflictivo(Galpon $galpon, Lote $lote): void
    {
        $otroActivo = $galpon->lotes()
            ->whereKeyNot($lote->id)
            ->whereIn('estado', [
                LoteEstado::Activo->value,
                LoteEstado::EnProduccion->value,
            ])
            ->exists();

        if ($otroActivo) {
            throw ValidationException::withMessages([
                'lote_id' => 'Hay otro lote activo en el galpón; no podés reabrir este ciclo.',
            ]);
        }
    }

    private function ultimoCierreCiclo(Lote $lote): ?MovimientoAves
    {
        return MovimientoAves::query()
            ->where('lote_id', $lote->id)
            ->whereIn('tipo', [MovimientoAvesTipo::CierreLote, MovimientoAvesTipo::Faena])
            ->where('estado', MovimientoAvesEstado::Activo)
            ->where('metadata->cerrar_ciclo_lote', true)
            ->orderByDesc('fecha_efectiva')
            ->orderByDesc('id')
            ->first();
    }
}
