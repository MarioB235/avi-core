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
use App\Support\GalponValidacion;
use App\Support\IdempotenciaMovimiento;
use App\Support\MovimientoAvesEfecto;
use App\Support\MovimientoAvesValidacion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class RevertirMovimientoAvesAction
{
    public function __construct(private RegistrarAuditoriaAction $auditoria) {}

    public function execute(
        User $user,
        MovimientoAves $movimientoOriginal,
        string $motivo,
        ?Carbon $fechaEfectiva = null,
    ): MovimientoAves {
        Gate::forUser($user)->authorize('create', MovimientoAves::class);
        Gate::forUser($user)->authorize('view', $movimientoOriginal);

        $reintento = IdempotenciaMovimiento::buscarExistente(
            $user,
            IdempotenciaMovimiento::claveReversionMovimiento($movimientoOriginal->id),
        );

        if ($reintento !== null) {
            return $reintento;
        }

        $this->assertReversible($movimientoOriginal);

        $motivo = trim($motivo);

        if ($motivo === '') {
            throw ValidationException::withMessages([
                'motivo' => 'Ingresá el motivo de la reversión.',
            ]);
        }

        $fechaEfectiva ??= now();

        return DB::transaction(function () use (
            $user,
            $movimientoOriginal,
            $motivo,
            $fechaEfectiva,
        ): MovimientoAves {
            return IdempotenciaMovimiento::resolver(
                $user,
                IdempotenciaMovimiento::claveReversionMovimiento($movimientoOriginal->id),
                function (?string $clave) use ($user, $movimientoOriginal, $motivo, $fechaEfectiva): MovimientoAves {
                    $original = MovimientoAves::query()
                        ->whereKey($movimientoOriginal->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $this->assertReversible($original);
                    $this->assertSinMovimientosPosteriores($original);

                    $deltasInversos = $this->deltasInversosSobreAvesActuales($original);
                    $galponesBloqueados = $this->bloquearGalponesOrdenados(array_keys($deltasInversos));
                    $this->aplicarDeltasConValidacion($galponesBloqueados, $deltasInversos);

                    $reversion = new MovimientoAves([
                        'empresa_id' => $original->empresa_id,
                        'tipo' => MovimientoAvesTipo::Reversion,
                        'estado' => MovimientoAvesEstado::Activo,
                        'galpon_origen_id' => $original->galpon_origen_id,
                        'galpon_destino_id' => $original->galpon_destino_id,
                        'lote_id' => $original->lote_id,
                        'cantidad' => $original->cantidad,
                        'ajuste_delta' => $original->ajuste_delta !== null ? -(int) $original->ajuste_delta : null,
                        'motivo' => $motivo,
                        'registrado_por' => $user->id,
                        'fecha_efectiva' => $fechaEfectiva,
                        'reversa_de_id' => $original->id,
                        'metadata' => [
                            'origen' => 'reversion_movimiento',
                            'tipo_original' => $original->tipo->value,
                            'impacta_aves_actuales' => $deltasInversos !== [],
                        ],
                        'idempotencia_clave' => $clave,
                    ]);

                    MovimientoAvesValidacion::assertEstructuraMinima($reversion);
                    $reversion->save();

                    $original->update([
                        'estado' => MovimientoAvesEstado::Reversado,
                        'reversado_por_id' => $reversion->id,
                    ]);

                    $this->auditoria->execute(
                        $user,
                        AuditoriaCategoria::Movimiento,
                        'reversion',
                        MovimientoAves::class,
                        $reversion->id,
                        $reversion->empresa_id,
                        $motivo,
                        [
                            'movimiento_original_id' => $original->id,
                            'tipo_original' => $original->tipo->value,
                        ],
                    );

                    return $reversion;
                },
            );
        });
    }

    private function assertReversible(MovimientoAves $movimiento): void
    {
        if ($movimiento->tipo === MovimientoAvesTipo::Reversion) {
            throw ValidationException::withMessages([
                'movimientoId' => 'No podés revertir una reversión.',
            ]);
        }

        if ($movimiento->estado !== MovimientoAvesEstado::Activo) {
            throw ValidationException::withMessages([
                'movimientoId' => 'El movimiento ya fue revertido o no está activo.',
            ]);
        }

        if ($movimiento->reversado_por_id !== null) {
            throw ValidationException::withMessages([
                'movimientoId' => 'El movimiento ya tiene una reversión enlazada.',
            ]);
        }

        $origen = is_array($movimiento->metadata) ? ($movimiento->metadata['origen'] ?? null) : null;

        if ($origen === MovimientoAvesOrigen::SaldoInicialLote->value) {
            throw ValidationException::withMessages([
                'movimientoId' => 'El saldo inicial del lote no se revierte por este circuito.',
            ]);
        }
    }

    private function assertSinMovimientosPosteriores(MovimientoAves $original): void
    {
        $galponIds = array_keys(MovimientoAvesEfecto::deltasPorGalpon($original));

        if ($galponIds === []) {
            return;
        }

        $posterior = MovimientoAves::query()
            ->where('empresa_id', $original->empresa_id)
            ->where('estado', MovimientoAvesEstado::Activo)
            ->whereKeyNot($original->id)
            ->where('tipo', '!=', MovimientoAvesTipo::Reversion)
            ->where(function ($query): void {
                $query->where('metadata->origen', '!=', MovimientoAvesOrigen::SaldoInicialLote->value)
                    ->orWhereNull('metadata->origen');
            })
            ->where(function ($query) use ($galponIds): void {
                $query->whereIn('galpon_origen_id', $galponIds)
                    ->orWhereIn('galpon_destino_id', $galponIds);
            })
            ->where(function ($query) use ($original): void {
                $query->where('fecha_efectiva', '>', $original->fecha_efectiva)
                    ->orWhere(function ($nested) use ($original): void {
                        $nested->where('fecha_efectiva', $original->fecha_efectiva)
                            ->where('id', '>', $original->id);
                    });
            })
            ->exists();

        if ($posterior) {
            throw ValidationException::withMessages([
                'movimientoId' => 'Hay movimientos posteriores en el galpón; revertí en orden inverso.',
            ]);
        }
    }

    /**
     * @return array<int, int>
     */
    private function deltasInversosSobreAvesActuales(MovimientoAves $movimiento): array
    {
        if (! $this->impactaAvesActuales($movimiento)) {
            return [];
        }

        $inversos = [];

        foreach (MovimientoAvesEfecto::deltasPorGalpon($movimiento) as $galponId => $delta) {
            if ($delta === 0) {
                continue;
            }

            $inversos[$galponId] = -$delta;
        }

        return $inversos;
    }

    private function impactaAvesActuales(MovimientoAves $movimiento): bool
    {
        $metadata = is_array($movimiento->metadata) ? $movimiento->metadata : [];

        if (array_key_exists('impacta_aves_actuales', $metadata)) {
            return (bool) $metadata['impacta_aves_actuales'];
        }

        return match ($movimiento->tipo) {
            MovimientoAvesTipo::Entrada,
            MovimientoAvesTipo::Traslado,
            MovimientoAvesTipo::Ajuste,
            MovimientoAvesTipo::CierreLote,
            MovimientoAvesTipo::Faena => true,
            default => false,
        };
    }

    /**
     * @param  list<int>  $galponIds
     * @return array<int, Galpon>
     */
    private function bloquearGalponesOrdenados(array $galponIds): array
    {
        sort($galponIds, SORT_NUMERIC);

        $bloqueados = [];

        foreach ($galponIds as $galponId) {
            $bloqueados[$galponId] = GalponValidacion::bloquearParaMutacion($galponId);
        }

        return $bloqueados;
    }

    /**
     * @param  array<int, Galpon>  $galponesBloqueados
     * @param  array<int, int>  $deltasInversos
     */
    private function aplicarDeltasConValidacion(array $galponesBloqueados, array $deltasInversos): void
    {
        foreach ($deltasInversos as $galponId => $delta) {
            $galpon = $galponesBloqueados[$galponId] ?? null;

            if (! $galpon instanceof Galpon) {
                continue;
            }

            $saldo = (int) $galpon->aves_actuales;

            if ($saldo + $delta < 0) {
                throw ValidationException::withMessages([
                    'movimientoId' => 'La reversión dejaría saldo negativo en el galpón.',
                ]);
            }

            if ($delta > 0) {
                $galpon->increment('aves_actuales', $delta);
            } elseif ($delta < 0) {
                $galpon->decrement('aves_actuales', abs($delta));
            }
        }
    }
}
