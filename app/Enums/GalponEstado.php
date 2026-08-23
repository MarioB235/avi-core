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

    public function label(): string
    {
        return match ($this) {
            self::Activo => 'Activo',
            self::Inactivo => 'Inactivo',
            self::EnMantenimiento => 'En mantenimiento',
            self::VacioSanitario => 'Vacío sanitario',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $estado): array => [$estado->value => $estado->label()])
            ->all();
    }
}
