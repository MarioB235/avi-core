<?php

namespace App\Actions\Movimiento;

use App\Actions\Auditoria\RegistrarAuditoriaAction;
use App\Enums\AuditoriaCategoria;
use App\Enums\MovimientoAvesEstado;
use App\Enums\MovimientoAvesOrigen;
use App\Enums\MovimientoAvesTipo;
use App\Models\Galpon;
use App\Models\Lote;
use App\Models\MovimientoAves;
use App\Models\User;
use App\Services\EmpresaRelationalGuard;
use App\Services\LoteUbicacionHistoricaService;
use App\Services\MovimientoAvesConciliacionService;
use App\Support\GalponValidacion;
use App\Support\IdempotenciaMovimiento;
use App\Support\MovimientoAvesValidacion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class RegistrarTrasladoAvesAction
{
    public function __construct(
        private EmpresaRelationalGuard $relations,
        private MovimientoAvesConciliacionService $conciliacion,
        private LoteUbicacionHistoricaService $ubicacionHistorica,
        private RegistrarAuditoriaAction $auditoria,
    ) {}

    public function execute(
        User $user,
        Galpon $galponOrigen,
        Galpon $galponDestino,
        Lote $lote,
        int $cantidad,
        string $motivo,
        ?int $muertesImputadasLote = null,
        ?int $descarteImputadoLote = null,
        ?string $idempotenciaClave = null,
        ?Carbon $fechaEfectiva = null,
    ): MovimientoAves {
        Gate::forUser($user)->authorize('create', MovimientoAves::class);

        $this->relations->assertGalponOfActor($user, $galponOrigen, 'galponOrigenId');
        $this->relations->assertGalponOfActor($user, $galponDestino, 'galponDestinoId');
        $this->relations->assertLoteOfActor($user, $lote, 'lote_id');
        $this->relations->assertLoteBelongsToGalpon($lote, $galponOrigen, 'lote_id');
        $this->conciliacion->assertLoteActivoEnGalpon($galponOrigen, $lote->id);

        if ($galponOrigen->id === $galponDestino->id) {
            throw ValidationException::withMessages([
                'galponDestinoId' => 'Elegí un galpón destino distinto del origen.',
            ]);
        }

        GalponValidacion::assertDisponibleParaCarga($galponDestino, 'galponDestinoId');

        $motivo = trim($motivo);

        if ($motivo === '') {
            throw ValidationException::withMessages([
                'motivo' => 'Ingresá el motivo del traslado.',
            ]);
        }

        if ($cantidad < 1) {
            throw ValidationException::withMessages([
                'cantidad' => 'La cantidad debe ser al menos 1.',
            ]);
        }

        $fechaEfectiva ??= now();

        $metadataBase = [
            'origen' => MovimientoAvesOrigen::TrasladoOperativo->value,
            'impacta_aves_actuales' => true,
            'lote_codigo' => $lote->codigo,
        ];

        if ($muertesImputadasLote !== null) {
            $metadataBase['muertes_imputadas_lote'] = $muertesImputadasLote;
        }

        if ($descarteImputadoLote !== null) {
            $metadataBase['descarte_imputado_lote'] = $descarteImputadoLote;
        }

        return DB::transaction(function () use (
            $user,
            $galponOrigen,
            $galponDestino,
            $lote,
            $cantidad,
            $motivo,
            $metadataBase,
            $idempotenciaClave,
            $fechaEfectiva,
            $muertesImputadasLote,
            $descarteImputadoLote,
        ): MovimientoAves {
            return IdempotenciaMovimiento::resolver($user, $idempotenciaClave, function (?string $clave) use (
                $user,
                $galponOrigen,
                $galponDestino,
                $lote,
                $cantidad,
                $motivo,
                $metadataBase,
                $fechaEfectiva,
                $muertesImputadasLote,
                $descarteImputadoLote,
            ): MovimientoAves {
                [$origenBloqueado, $destinoBloqueado] = GalponValidacion::bloquearParOrdenado(
                    $galponOrigen->id,
                    $galponDestino->id,
                );

                GalponValidacion::revalidarParaCargaBajoLock($destinoBloqueado, 'galponDestinoId');

                $cambiaUbicacionExpediente = $this->ubicacionHistorica->debeReasignarExpedienteLote(
                    $origenBloqueado,
                    $lote,
                    $cantidad,
                    $muertesImputadasLote,
                    $descarteImputadoLote,
                );

                if ($cambiaUbicacionExpediente) {
                    $metadataBase['cambio_ubicacion_expediente'] = true;
                }

                $movimiento = new MovimientoAves([
                    'empresa_id' => $user->empresa_id,
                    'tipo' => MovimientoAvesTipo::Traslado,
                    'estado' => MovimientoAvesEstado::Activo,
                    'galpon_origen_id' => $origenBloqueado->id,
                    'galpon_destino_id' => $destinoBloqueado->id,
                    'lote_id' => $lote->id,
                    'cantidad' => $cantidad,
                    'motivo' => $motivo,
                    'registrado_por' => $user->id,
                    'fecha_efectiva' => $fechaEfectiva,
                    'metadata' => $metadataBase,
                    'idempotencia_clave' => $clave,
                ]);

                MovimientoAvesValidacion::assertEstructuraMinima($movimiento);
                $movimiento->save();

                $origenBloqueado->decrement('aves_actuales', $cantidad);
                $destinoBloqueado->increment('aves_actuales', $cantidad);

                if ($cambiaUbicacionExpediente) {
                    $lote->update(['galpon_id' => $destinoBloqueado->id]);
                }

                $this->auditoria->execute(
                    $user,
                    AuditoriaCategoria::Movimiento,
                    'traslado',
                    MovimientoAves::class,
                    $movimiento->id,
                    $movimiento->empresa_id,
                    $motivo,
                    [
                        'galpon_origen_id' => $origenBloqueado->id,
                        'galpon_destino_id' => $destinoBloqueado->id,
                        'lote_id' => $lote->id,
                        'cantidad' => $cantidad,
                    ],
                );

                return $movimiento;
            });
        });
    }
}
