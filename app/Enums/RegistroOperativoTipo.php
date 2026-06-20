<?php

namespace App\Enums;

enum RegistroOperativoTipo: string
{
    case Huevos = 'huevos';
    case Muertes = 'muertes';
    case Alimento = 'alimento';
    case Combinado = 'combinado';

    public function label(): string
    {
        return match ($this) {
            self::Huevos => 'Huevos',
            self::Muertes => 'Muertes',
            self::Alimento => 'Alimento',
            self::Combinado => 'Combinado',
        };
    }
}
