<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

/**
 * Filtros del reporte «producción diaria» (REP-02).
 */
final class ReporteFiltroProduccion
{
    public const MAX_DIAS_PERIODO = 93;

    public function __construct(
        public readonly User $usuario,
        public readonly ?int $granjaId,
        public readonly ?int $galponId,
        public readonly Carbon $fechaDesde,
        public readonly Carbon $fechaHasta,
    ) {
        if ($this->fechaHasta->lt($this->fechaDesde)) {
            throw new InvalidArgumentException('La fecha hasta no puede ser anterior a la fecha desde.');
        }

        $dias = $this->fechaDesde->diffInDays($this->fechaHasta) + 1;

        if ($dias > self::MAX_DIAS_PERIODO) {
            throw new InvalidArgumentException('El período no puede superar '.self::MAX_DIAS_PERIODO.' días.');
        }
    }

    public static function diaUnico(User $usuario, ?int $granjaId, ?int $galponId, Carbon $fecha): self
    {
        $fecha = $fecha->copy()->startOfDay();

        return new self($usuario, $granjaId, $galponId, $fecha, $fecha);
    }

    public static function desdeParametrosHttp(
        User $usuario,
        ?string $desde,
        ?string $hasta,
        ?int $granjaId,
        ?int $galponId,
        ?int $empresaId,
    ): self {
        $hoyLogico = $empresaId !== null
            ? DiaOperativoEmpresa::hoyParaEmpresa($empresaId)->fechaLogica->copy()->startOfDay()
            : Carbon::today()->startOfDay();

        $fechaDesde = $desde !== null && $desde !== ''
            ? Carbon::parse($desde)->startOfDay()
            : $hoyLogico->copy();

        $fechaHasta = $hasta !== null && $hasta !== ''
            ? Carbon::parse($hasta)->startOfDay()
            : $hoyLogico->copy();

        return new self($usuario, $granjaId, $galponId, $fechaDesde, $fechaHasta);
    }
}
