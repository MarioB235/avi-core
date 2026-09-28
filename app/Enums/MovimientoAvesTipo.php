<?php

namespace App\Enums;

enum MovimientoAvesTipo: string
{
    case Entrada = 'entrada';
    case Traslado = 'traslado';
    case Ajuste = 'ajuste';
    case CierreLote = 'cierre_lote';
    case Faena = 'faena';
    case Reversion = 'reversion';

    public function label(): string
    {
        return match ($this) {
            self::Entrada => 'Entrada',
            self::Traslado => 'Traslado',
            self::Ajuste => 'Ajuste de inventario',
            self::CierreLote => 'Cierre de lote',
            self::Faena => 'Faena',
            self::Reversion => 'Reversión',
        };
    }

    public function requiereGalponOrigen(): bool
    {
        return match ($this) {
            self::Traslado, self::Ajuste, self::CierreLote, self::Faena => true,
            default => false,
        };
    }

    public function requiereGalponDestino(): bool
    {
        return match ($this) {
            self::Entrada, self::Traslado => true,
            default => false,
        };
    }

    public function requiereConciliacionD01(): bool
    {
        return match ($this) {
            self::Traslado, self::CierreLote, self::Faena => true,
            default => false,
        };
    }
}
