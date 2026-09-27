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

    public function permiteCargaNormal(): bool
    {
        return match ($this) {
            self::Activo, self::EnProduccion => true,
            self::Trasladado, self::Cerrado => false,
        };
    }

    public function esTerminal(): bool
    {
        return $this === self::Trasladado;
    }

    /**
     * @return list<self>
     */
    public function transicionesPermitidas(bool $puedeReabrir): array
    {
        return match ($this) {
            self::Activo => [self::EnProduccion, self::Cerrado, self::Trasladado],
            self::EnProduccion => [self::Cerrado, self::Trasladado],
            self::Cerrado => $puedeReabrir ? [self::Activo, self::EnProduccion] : [],
            self::Trasladado => [],
        };
    }

    public function puedeTransicionarA(self $destino, bool $puedeReabrir): bool
    {
        return in_array($destino, $this->transicionesPermitidas($puedeReabrir), true);
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
