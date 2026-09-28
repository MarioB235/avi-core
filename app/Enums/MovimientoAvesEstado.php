<?php

namespace App\Enums;

enum MovimientoAvesEstado: string
{
    case Activo = 'activo';
    case Reversado = 'reversado';

    public function label(): string
    {
        return match ($this) {
            self::Activo => 'Activo',
            self::Reversado => 'Reversado',
        };
    }

    public function cuentaEnSaldo(): bool
    {
        return $this === self::Activo;
    }
}
