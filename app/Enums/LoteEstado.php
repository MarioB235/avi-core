<?php

namespace App\Enums;

enum LoteEstado: string
{
    case Activo = 'activo';
    case EnProduccion = 'en_produccion';
    case Trasladado = 'trasladado';
    case Cerrado = 'cerrado';

    public function label(): string
    {
        return match ($this) {
            self::Activo => 'Activo',
            self::EnProduccion => 'En producción',
            self::Trasladado => 'Trasladado',
            self::Cerrado => 'Cerrado',
        };
    }
}
