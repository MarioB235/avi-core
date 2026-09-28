<?php

namespace App\Enums;

enum AuditoriaCategoria: string
{
    case Usuario = 'usuario';
    case Empresa = 'empresa';
    case Soporte = 'soporte';
    case Lote = 'lote';
    case Operacion = 'operacion';
    case Correccion = 'correccion';
    case Movimiento = 'movimiento';
    case Ajuste = 'ajuste';

    public function label(): string
    {
        return match ($this) {
            self::Usuario => 'Usuarios',
            self::Empresa => 'Empresas',
            self::Soporte => 'Soporte',
            self::Lote => 'Lotes',
            self::Operacion => 'Operación',
            self::Correccion => 'Corrección',
            self::Movimiento => 'Movimientos',
            self::Ajuste => 'Ajustes',
        };
    }
}
