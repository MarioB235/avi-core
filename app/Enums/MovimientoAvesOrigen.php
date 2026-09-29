<?php

namespace App\Enums;

enum MovimientoAvesOrigen: string
{
    case SaldoInicialLote = 'saldo_inicial_lote';
    case EntradaExterna = 'entrada_externa';
    case TrasladoOperativo = 'traslado_operativo';
    case AjusteInventario = 'ajuste_inventario';
    case CierreLoteOperativo = 'cierre_lote_operativo';
    case FaenaOperativa = 'faena_operativa';
    case ReaperturaLote = 'reapertura_lote';

    public function impactaAvesActuales(): bool
    {
        return match ($this) {
            self::SaldoInicialLote => false,
            self::EntradaExterna, self::TrasladoOperativo, self::AjusteInventario, self::CierreLoteOperativo, self::FaenaOperativa, self::ReaperturaLote => true,
        };
    }
}
