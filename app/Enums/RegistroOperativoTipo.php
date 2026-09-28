<?php

namespace App\Enums;

enum RegistroOperativoTipo: string
{
    case Huevos = 'huevos';
    case Muertes = 'muertes';
    case Descarte = 'descarte';
    case Alimento = 'alimento';
    case Combinado = 'combinado';

    public function label(): string
    {
        return match ($this) {
            self::Huevos => 'Huevos',
            self::Muertes => 'Muertes',
            self::Descarte => 'Descarte',
            self::Alimento => 'Alimento',
            self::Combinado => 'Combinado (legado)',
        };
    }

    public function esLegado(): bool
    {
        return $this === self::Combinado;
    }

    public function admiteCapturaNueva(): bool
    {
        return ! $this->esLegado();
    }
}
