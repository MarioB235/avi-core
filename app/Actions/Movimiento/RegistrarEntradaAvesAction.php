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
use App\Services\MovimientoAvesConciliacionService;
use App\Support\GalponValidacion;
use App\Support\IdempotenciaMovimiento;
use App\Support\MovimientoAvesValidacion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class RegistrarEntradaAvesAction
{
    public function __construct(
        private EmpresaRelationalGuard $relations,
        private MovimientoAvesConciliacionService $conciliacion,
        private RegistrarAuditoriaAction $auditoria,
    ) {}

    public function execute(
        User $user,
        Galpon $galpon,
        Lote $lote,
        int $cantidad,
        string $motivo,
        ?string $idempotenciaClave = null,
        ?Carbon $fechaEfectiva = null,
    ): MovimientoAves {
        Gate::forUser($user)->authorize('create', MovimientoAves::class);

        $this->relations->assertGalponOfActor($user, $galpon, 'galponId');
        $this->relations->assertLoteOfActor($user, $lote, 'lote_id');
        $this->conciliacion->assertLoteActivoEnGalpon($galpon, $lote->id);

        if ($lote->galpon_id !== $galpon->id) {
            throw ValidationException::withMessages([
                'loteId' => 'El lote no pertenece al galpón destino.',
            ]);
        }

        GalponValidacion::assertDisponibleParaCarga($galpon, 'galponId');

        $motivo = trim($motivo);

        if ($motivo === '') {
            throw ValidationException::withMessages([
                'motivo' => 'Ingresá el motivo de la entrada.',
            ]);
        }

        if ($cantidad < 1) {
            throw ValidationException::withMessages([
                'cantidad' => 'La cantidad debe ser al menos 1.',
            ]);
        }

        $fechaEfectiva ??= now();

        return DB::transaction(function () use ($user, $galpon, $lote, $cantidad, $motivo, $idempotenciaClave, $fechaEfectiva): MovimientoAves {
            return IdempotenciaMovimiento::resolver($user, $idempotenciaClave, function (?string $clave) use ($user, $galpon, $lote, $cantidad, $motivo, $fechaEfectiva): MovimientoAves {
                /** @var Galpon $galponBloqueado */
                $galponBloqueado = Galpon::query()
                    ->whereKey($galpon->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $movimiento = new MovimientoAves([
                    'empresa_id' => $user->empresa_id,
                    'tipo' => MovimientoAvesTipo::Entrada,
                    'estado' => MovimientoAvesEstado::Activo,
                    'galpon_destino_id' => $galponBloqueado->id,
                    'lote_id' => $lote->id,
                    'cantidad' => $cantidad,
                    'motivo' => $motivo,
                    'registrado_por' => $user->id,
                    'fecha_efectiva' => $fechaEfectiva,
                    'metadata' => [
                        'origen' => MovimientoAvesOrigen::EntradaExterna->value,
                        'impacta_aves_actuales' => true,
                        'lote_codigo' => $lote->codigo,
                    ],
                    'idempotencia_clave' => $clave,
                ]);

                MovimientoAvesValidacion::assertEstructuraMinima($movimiento);
                $movimiento->save();

                $galponBloqueado->increment('aves_actuales', $cantidad);

                $this->auditoria->execute(
                    $user,
                    AuditoriaCategoria::Movimiento,
                    'entrada_externa',
                    MovimientoAves::class,
                    $movimiento->id,
                    $movimiento->empresa_id,
                    $motivo,
                    [
                        'galpon_id' => $galponBloqueado->id,
                        'lote_id' => $lote->id,
                        'cantidad' => $cantidad,
                    ],
                );

                return $movimiento;
            });
        });
    }
}
