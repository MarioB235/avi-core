<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

/**
 * Filtros del reporte «historia de lote» (REP-06).
 */
final class ReporteFiltroLote
{
    public const MAX_DIAS_PERIODO = 93;

    public function __construct(
        public readonly User $usuario,
        public readonly int $loteId,
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

    public static function desdeParametrosHttp(
        User $usuario,
        ?int $loteId,
        ?string $desde,
        ?string $hasta,
        ?int $empresaId,
    ): self {
        if ($loteId === null || $loteId < 1) {
            throw new InvalidArgumentException('Debe indicar un lote para el reporte de historia.');
        }

        $hoyLogico = $empresaId !== null
            ? DiaOperativoEmpresa::hoyParaEmpresa($empresaId)->fechaLogica->copy()->startOfDay()
            : Carbon::today()->startOfDay();

        $fechaDesde = $desde !== null && $desde !== ''
            ? Carbon::parse($desde)->startOfDay()
            : $hoyLogico->copy()->subDays(30);

        $fechaHasta = $hasta !== null && $hasta !== ''
            ? Carbon::parse($hasta)->startOfDay()
            : $hoyLogico->copy();

        return new self($usuario, $loteId, $fechaDesde, $fechaHasta);
    }
}
