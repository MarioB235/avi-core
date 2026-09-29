<?php

namespace App\Actions\Movimiento;

use App\Actions\Auditoria\RegistrarAuditoriaAction;
use App\Actions\Lote\TransicionarLoteEstadoAction;
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
use App\Services\MovimientoAvesConciliacionService;
use App\Support\GalponValidacion;
use App\Support\IdempotenciaMovimiento;
use App\Support\MovimientoAvesValidacion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class RegistrarCierreLoteAction
{
    public function __construct(
        private EmpresaRelationalGuard $relations,
        private MovimientoAvesConciliacionService $conciliacion,
        private RegistrarAuditoriaAction $auditoria,
        private TransicionarLoteEstadoAction $transicionarLote,
    ) {}

    public function execute(
        User $user,
        Galpon $galpon,
        Lote $lote,
        int $cantidad,
        string $motivo,
        ?string $destinoSalida = null,
        bool $cerrarCicloLote = true,
        ?int $muertesImputadasLote = null,
        ?int $descarteImputadoLote = null,
        ?string $idempotenciaClave = null,
        ?Carbon $fechaEfectiva = null,
    ): MovimientoAves {
        Gate::forUser($user)->authorize('create', MovimientoAves::class);

        $this->relations->assertGalponOfActor($user, $galpon, 'galponId');
        $this->relations->assertLoteOfActor($user, $lote, 'lote_id');
        $this->relations->assertLoteBelongsToGalpon($lote, $galpon, 'lote_id');

        $reintento = IdempotenciaMovimiento::buscarExistente($user, $idempotenciaClave);

        if ($reintento !== null) {
            return $reintento;
        }

        $this->conciliacion->assertLoteActivoEnGalpon($galpon, $lote->id);

        if ($cerrarCicloLote) {
            Gate::forUser($user)->authorize('transition', $lote);
        }

        $motivo = trim($motivo);

        if ($motivo === '') {
            throw ValidationException::withMessages([
                'motivo' => 'Ingresá el motivo del cierre.',
            ]);
        }

        if ($cantidad < 1) {
            throw ValidationException::withMessages([
                'cantidad' => 'La cantidad debe ser al menos 1.',
            ]);
        }

        $destinoSalida = $destinoSalida !== null ? trim($destinoSalida) : null;

        if ($destinoSalida === '') {
            $destinoSalida = null;
        }

        $fechaEfectiva ??= now();

        return DB::transaction(function () use (
            $user,
            $galpon,
            $lote,
            $cantidad,
            $motivo,
            $destinoSalida,
            $cerrarCicloLote,
            $muertesImputadasLote,
            $descarteImputadoLote,
            $idempotenciaClave,
            $fechaEfectiva,
        ): MovimientoAves {
            return IdempotenciaMovimiento::resolver($user, $idempotenciaClave, function (?string $clave) use (
                $user,
                $galpon,
                $lote,
                $cantidad,
                $motivo,
                $destinoSalida,
                $cerrarCicloLote,
                $muertesImputadasLote,
                $descarteImputadoLote,
                $fechaEfectiva,
            ): MovimientoAves {
                $galponBloqueado = GalponValidacion::bloquearParaMutacion($galpon->id);
                $loteBloqueado = $this->conciliacion->assertLoteActivoEnGalpon($galponBloqueado, $lote->id);

                $metadataBase = [
                    'origen' => MovimientoAvesOrigen::CierreLoteOperativo->value,
                    'impacta_aves_actuales' => true,
                    'lote_codigo' => $loteBloqueado->codigo,
                    'cerrar_ciclo_lote' => $cerrarCicloLote,
                ];

                if ($destinoSalida !== null) {
                    $metadataBase['destino_salida'] = $destinoSalida;
                }

                if ($muertesImputadasLote !== null) {
                    $metadataBase['muertes_imputadas_lote'] = $muertesImputadasLote;
                }

                if ($descarteImputadoLote !== null) {
                    $metadataBase['descarte_imputado_lote'] = $descarteImputadoLote;
                }

                $movimiento = new MovimientoAves([
                    'empresa_id' => $user->empresa_id,
                    'tipo' => MovimientoAvesTipo::CierreLote,
                    'estado' => MovimientoAvesEstado::Activo,
                    'galpon_origen_id' => $galponBloqueado->id,
                    'lote_id' => $loteBloqueado->id,
                    'cantidad' => $cantidad,
                    'motivo' => $motivo,
                    'registrado_por' => $user->id,
                    'fecha_efectiva' => $fechaEfectiva,
                    'metadata' => $metadataBase,
                    'idempotencia_clave' => $clave,
                ]);

                MovimientoAvesValidacion::assertEstructuraMinima($movimiento);

                if ($cerrarCicloLote) {
                    $saldoDeclarado = $this->saldoDeclaradoParaCierre(
                        $galponBloqueado,
                        $loteBloqueado,
                        $muertesImputadasLote,
                        $descarteImputadoLote,
                    );

                    if ($cantidad !== $saldoDeclarado) {
                        throw ValidationException::withMessages([
                            'cantidad' => 'Para cerrar el ciclo registrá el remanente completo del lote ('.$saldoDeclarado.' aves).',
                        ]);
                    }
                }

                $movimiento->save();

                $galponBloqueado->decrement('aves_actuales', $cantidad);

                if ($cerrarCicloLote) {
                    $this->transicionarLote->execute($user, $loteBloqueado->fresh(), [
                        'estado' => LoteEstado::Cerrado->value,
                        'motivo' => $motivo,
                    ]);
                }

                $this->auditoria->execute(
                    $user,
                    AuditoriaCategoria::Movimiento,
                    $cerrarCicloLote ? 'cierre_lote' : 'salida_cierre_parcial',
                    MovimientoAves::class,
                    $movimiento->id,
                    $movimiento->empresa_id,
                    $motivo,
                    [
                        'galpon_id' => $galponBloqueado->id,
                        'lote_id' => $loteBloqueado->id,
                        'cantidad' => $cantidad,
                        'destino_salida' => $destinoSalida,
                        'cerrar_ciclo_lote' => $cerrarCicloLote,
                    ],
                );

                return $movimiento;
            });
        });
    }

    private function saldoDeclaradoParaCierre(
        Galpon $galpon,
        Lote $lote,
        ?int $muertesImputadasLote,
        ?int $descarteImputadoLote,
    ): int {
        $snapshot = $this->conciliacion->snapshot($galpon);

        if (! $snapshot['multiples_lotes']) {
            return (int) $galpon->aves_actuales;
        }

        if ($muertesImputadasLote === null) {
            throw ValidationException::withMessages([
                'muertes_imputadas_lote' => 'Conciliá las muertes del galpón imputadas a este lote antes de cerrar.',
            ]);
        }

        return $this->conciliacion->saldoDeclaradoLoteEnGalpon(
            $galpon,
            $lote,
            $muertesImputadasLote,
            $descarteImputadoLote ?? 0,
        );
    }
}
