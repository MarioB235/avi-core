<?php

namespace App\Support;

use App\Enums\MovimientoAvesTipo;
use App\Models\MovimientoAves;
use Illuminate\Support\Collection;

class MovimientoAvesEfecto
{
    /**
     * Delta de aves por galpón que aporta un movimiento al reconstruir saldo (MOV-01).
     *
     * @return array<int, int>
     */
    public static function deltasPorGalpon(MovimientoAves $movimiento): array
    {
        if (! $movimiento->estado->cuentaEnSaldo()) {
            return [];
        }

        if ($movimiento->tipo === MovimientoAvesTipo::Reversion) {
            return [];
        }

        return self::deltasDirectos($movimiento);
    }

    /**
     * Saldo neto atribuible solo a movimientos (sin mortalidad operativa).
     *
     * @param  iterable<int, MovimientoAves>  $movimientos
     */
    public static function saldoNetoEnGalpon(iterable $movimientos, int $galponId): int
    {
        $total = 0;

        foreach ($movimientos as $movimiento) {
            $total += self::deltasPorGalpon($movimiento)[$galponId] ?? 0;
        }

        return $total;
    }

    /**
     * @param  iterable<int, MovimientoAves>  $movimientos
     * @return array<int, int>
     */
    public static function saldoNetoPorGalpon(iterable $movimientos): array
    {
        $acumulado = [];

        foreach ($movimientos as $movimiento) {
            foreach (self::deltasPorGalpon($movimiento) as $galponId => $delta) {
                $acumulado[$galponId] = ($acumulado[$galponId] ?? 0) + $delta;
            }
        }

        return $acumulado;
    }

    /**
     * @return array<int, int>
     */
    private static function deltasDirectos(MovimientoAves $movimiento): array
    {
        $cantidad = (int) $movimiento->cantidad;

        return match ($movimiento->tipo) {
            MovimientoAvesTipo::Entrada => self::soloDestino($movimiento, $cantidad),
            MovimientoAvesTipo::Traslado => self::traslado($movimiento, $cantidad),
            MovimientoAvesTipo::Ajuste => self::ajuste($movimiento),
            MovimientoAvesTipo::CierreLote, MovimientoAvesTipo::Faena => self::soloOrigen($movimiento, -$cantidad),
            MovimientoAvesTipo::Reversion => [],
        };
    }

    /**
     * @return array<int, int>
     */
    private static function soloDestino(MovimientoAves $movimiento, int $delta): array
    {
        if (! $movimiento->galpon_destino_id) {
            return [];
        }

        return [$movimiento->galpon_destino_id => $delta];
    }

    /**
     * @return array<int, int>
     */
    private static function soloOrigen(MovimientoAves $movimiento, int $delta): array
    {
        if (! $movimiento->galpon_origen_id) {
            return [];
        }

        return [$movimiento->galpon_origen_id => $delta];
    }

    /**
     * @return array<int, int>
     */
    private static function traslado(MovimientoAves $movimiento, int $cantidad): array
    {
        if (! $movimiento->galpon_origen_id || ! $movimiento->galpon_destino_id) {
            return [];
        }

        return [
            $movimiento->galpon_origen_id => -$cantidad,
            $movimiento->galpon_destino_id => $cantidad,
        ];
    }

    /**
     * @return array<int, int>
     */
    private static function ajuste(MovimientoAves $movimiento): array
    {
        if (! $movimiento->galpon_origen_id || $movimiento->ajuste_delta === null) {
            return [];
        }

        return [$movimiento->galpon_origen_id => (int) $movimiento->ajuste_delta];
    }

    /**
     * @param  Collection<int, MovimientoAves>|list<MovimientoAves>  $movimientos
     */
    public static function ordenarPorFechaEfectiva(Collection|array $movimientos): Collection
    {
        return Collection::make($movimientos)
            ->sortBy(fn (MovimientoAves $movimiento) => $movimiento->fecha_efectiva->timestamp)
            ->values();
    }
}
