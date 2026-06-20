<?php

namespace App\Enums;

enum GalponEstado: string
{
    case Activo = 'activo';
    case Inactivo = 'inactivo';
    case EnMantenimiento = 'en_mantenimiento';
    case VacioSanitario = 'vacio_sanitario';

    public function permiteCarga(): bool
    {
        return $this === self::Activo;
    }
}
