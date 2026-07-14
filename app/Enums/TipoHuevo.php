<?php

namespace App\Enums;

enum TipoHuevo: string
{
    case Blanco = 'blanco';
    case Color = 'color';

    public function labelUi(): string
    {
        return match ($this) {
            self::Blanco => 'Blanca',
            self::Color => 'Colorada',
        };
    }

    public function codigoLote(): string
    {
        return match ($this) {
            self::Blanco => 'B',
            self::Color => 'C',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function optionsUi(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $tipo): array => [$tipo->value => $tipo->labelUi()])
            ->all();
    }
}
