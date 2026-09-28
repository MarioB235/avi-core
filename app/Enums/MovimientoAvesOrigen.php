<?php

namespace App\Enums;

enum MovimientoAvesOrigen: string
{
    case SaldoInicialLote = 'saldo_inicial_lote';
    case EntradaExterna = 'entrada_externa';

    public function impactaAvesActuales(): bool
    {
        return match ($this) {
            self::SaldoInicialLote => false,
            self::EntradaExterna => true,
        };
    }
}
