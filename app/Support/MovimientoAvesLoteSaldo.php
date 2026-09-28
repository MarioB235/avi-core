<?php

namespace App\Support;

use App\Enums\MovimientoAvesTipo;
use App\Models\MovimientoAves;

class MovimientoAvesLoteSaldo
{
    /**
     * Delta de aves del lote atribuible a un galpón (MOV-02 / D01).
     */
    public static function deltaLoteEnGalpon(MovimientoAves $movimiento, int $loteId, int $galponId): int
    {
        if ((int) $movimiento->lote_id !== $loteId || ! $movimiento->estado->cuentaEnSaldo()) {
            return 0;
        }

        $cantidad = (int) $movimiento->cantidad;

        return match ($movimiento->tipo) {
            MovimientoAvesTipo::Entrada => (int) $movimiento->galpon_destino_id === $galponId ? $cantidad : 0,
            MovimientoAvesTipo::Traslado => match (true) {
                (int) $movimiento->galpon_origen_id === $galponId => -$cantidad,
                (int) $movimiento->galpon_destino_id === $galponId => $cantidad,
                default => 0,
            },
            MovimientoAvesTipo::CierreLote, MovimientoAvesTipo::Faena => (int) $movimiento->galpon_origen_id === $galponId
                ? -$cantidad
                : 0,
            MovimientoAvesTipo::Ajuste => (int) $movimiento->galpon_origen_id === $galponId
                ? (int) $movimiento->ajuste_delta
                : 0,
            default => 0,
        };
    }

    /**
     * @param  iterable<int, MovimientoAves>  $movimientos
     */
    public static function saldoMovimientosLoteEnGalpon(iterable $movimientos, int $loteId, int $galponId): int
    {
        $total = 0;

        foreach ($movimientos as $movimiento) {
            $total += self::deltaLoteEnGalpon($movimiento, $loteId, $galponId);
        }

        return $total;
    }
}
