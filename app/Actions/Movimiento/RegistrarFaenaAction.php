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

class RegistrarFaenaAction
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
        string $destinoFaena,
        bool $cerrarCicloLote = true,
        ?string $referenciaRemito = null,
        ?string $referenciaDocumento = null,
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
        $destinoFaena = trim($destinoFaena);

        if ($motivo === '') {
            throw ValidationException::withMessages([
                'motivo' => 'Ingresá el motivo de la salida a faena.',
            ]);
        }

        if ($destinoFaena === '') {
            throw ValidationException::withMessages([
                'destino_faena' => 'Indicá la planta o destino de faena.',
            ]);
        }

        if ($cantidad < 1) {
            throw ValidationException::withMessages([
                'cantidad' => 'La cantidad debe ser al menos 1.',
            ]);
        }

        $referenciaRemito = $referenciaRemito !== null ? trim($referenciaRemito) : null;
        $referenciaDocumento = $referenciaDocumento !== null ? trim($referenciaDocumento) : null;

        if ($referenciaRemito === '') {
            $referenciaRemito = null;
        }

        if ($referenciaDocumento === '') {
            $referenciaDocumento = null;
        }

        $fechaEfectiva ??= now();

        return DB::transaction(function () use (
            $user,
            $galpon,
            $lote,
            $cantidad,
            $motivo,
            $destinoFaena,
            $cerrarCicloLote,
            $referenciaRemito,
            $referenciaDocumento,
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
                $destinoFaena,
                $cerrarCicloLote,
                $referenciaRemito,
                $referenciaDocumento,
                $muertesImputadasLote,
                $descarteImputadoLote,
                $fechaEfectiva,
            ): MovimientoAves {
                $galponBloqueado = GalponValidacion::bloquearParaMutacion($galpon->id);
                $loteBloqueado = $this->conciliacion->assertLoteActivoEnGalpon($galponBloqueado, $lote->id);

                $metadataBase = [
                    'origen' => MovimientoAvesOrigen::FaenaOperativa->value,
                    'impacta_aves_actuales' => true,
                    'trazabilidad_interna' => true,
                    'lote_codigo' => $loteBloqueado->codigo,
                    'destino_faena' => $destinoFaena,
                    'cerrar_ciclo_lote' => $cerrarCicloLote,
                ];

                if ($referenciaRemito !== null) {
                    $metadataBase['referencia_remito'] = $referenciaRemito;
                }

                if ($referenciaDocumento !== null) {
                    $metadataBase['referencia_documento'] = $referenciaDocumento;
                }

                if ($muertesImputadasLote !== null) {
                    $metadataBase['muertes_imputadas_lote'] = $muertesImputadasLote;
                }

                if ($descarteImputadoLote !== null) {
                    $metadataBase['descarte_imputado_lote'] = $descarteImputadoLote;
                }

                $movimiento = new MovimientoAves([
                    'empresa_id' => $user->empresa_id,
                    'tipo' => MovimientoAvesTipo::Faena,
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
                    $saldoDeclarado = $this->saldoDeclaradoParaSalida(
                        $galponBloqueado,
                        $loteBloqueado,
                        $muertesImputadasLote,
                        $descarteImputadoLote,
                    );

                    if ($cantidad !== $saldoDeclarado) {
                        throw ValidationException::withMessages([
                            'cantidad' => 'Para cerrar el ciclo con faena registrá el remanente completo del lote ('.$saldoDeclarado.' aves).',
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
                    $cerrarCicloLote ? 'faena_cierre_ciclo' : 'faena_parcial',
                    MovimientoAves::class,
                    $movimiento->id,
                    $movimiento->empresa_id,
                    $motivo,
                    [
                        'galpon_id' => $galponBloqueado->id,
                        'lote_id' => $loteBloqueado->id,
                        'cantidad' => $cantidad,
                        'destino_faena' => $destinoFaena,
                        'referencia_remito' => $referenciaRemito,
                        'referencia_documento' => $referenciaDocumento,
                        'cerrar_ciclo_lote' => $cerrarCicloLote,
                    ],
                );

                return $movimiento;
            });
        });
    }

    private function saldoDeclaradoParaSalida(
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
                'muertes_imputadas_lote' => 'Conciliá las muertes del galpón imputadas a este lote antes de registrar la faena.',
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
