<?php

namespace App\Support;

use App\Models\Empresa;

final class EmpresaConfiguracion
{
    public const DEFAULT_ZONA_HORARIA = 'America/Montevideo';

    public const DEFAULT_HUEVOS_POR_MAPLE = 30;

    public const DEFAULT_MAPLES_POR_CAJON = 12;

    public function __construct(
        public readonly string $zonaHoraria,
        public readonly int $huevosPorMaple,
        public readonly int $maplesPorCajon,
    ) {}

    public static function fromEmpresa(Empresa $empresa): self
    {
        $configuracion = $empresa->configuracion ?? [];
        $unidades = is_array($configuracion['unidades'] ?? null) ? $configuracion['unidades'] : [];

        return new self(
            zonaHoraria: is_string($configuracion['zona_horaria'] ?? null) && $configuracion['zona_horaria'] !== ''
                ? $configuracion['zona_horaria']
                : self::DEFAULT_ZONA_HORARIA,
            huevosPorMaple: max(1, (int) ($unidades['huevos_por_maple'] ?? self::DEFAULT_HUEVOS_POR_MAPLE)),
            maplesPorCajon: max(1, (int) ($unidades['maples_por_cajon'] ?? self::DEFAULT_MAPLES_POR_CAJON)),
        );
    }

    public function huevosPorCaja(): int
    {
        return $this->huevosPorMaple * $this->maplesPorCajon;
    }

    /**
     * @return array{zona_horaria: string, unidades: array{huevos_por_maple: int, maples_por_cajon: int}}
     */
    public function toConfiguracionArray(): array
    {
        return [
            'zona_horaria' => $this->zonaHoraria,
            'unidades' => [
                'huevos_por_maple' => $this->huevosPorMaple,
                'maples_por_cajon' => $this->maplesPorCajon,
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function zonasHorariasPermitidas(): array
    {
        return array_values(array_filter(
            timezone_identifiers_list(),
            fn (string $zone): bool => str_starts_with($zone, 'America/'),
        ));
    }
}
