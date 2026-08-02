<?php

namespace App\Services;

use App\Enums\LoteEstado;
use App\Models\Galpon;
use App\Models\Lote;
use App\Models\RegistroOperativo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class OperarioGalponResumenService
{
    /**
     * @return Collection<int, Lote>
     */
    public function lotesActivos(Galpon $galpon): Collection
    {
        return $galpon->lotes()
            ->whereIn('estado', [
                LoteEstado::Activo->value,
                LoteEstado::EnProduccion->value,
            ])
            ->orderBy('fecha_ingreso')
            ->get();
    }

    /**
     * @param  Collection<int, Lote>  $lotes
     */
    public function fechaInicioVentana(Collection $lotes): ?Carbon
    {
        $fecha = $lotes->min('fecha_ingreso');

        if ($fecha === null) {
            return null;
        }

        return Carbon::parse($fecha)->startOfDay();
    }

    public function edadSemanas(Lote $lote): int
    {
        return (int) $lote->fecha_nacimiento->diffInWeeks(now());
    }

    public function maplesDesdeHuevos(int $huevos): int
    {
        return intdiv($huevos, 30);
    }

    /**
     * @return array{
     *     aves_actuales: int,
     *     huevos_hoy: int,
     *     maples_hoy: int,
     *     muertes_hoy: int,
     *     huevos_acumulados: int,
     *     maples_acumulados: int,
     *     muertes_acumuladas: int,
     *     lotes: Collection<int, Lote>,
     *     fecha_inicio_ventana: ?Carbon,
     *     multiples_lotes: bool,
     * }
     */
    public function resumen(Galpon $galpon): array
    {
        $lotes = $this->lotesActivos($galpon);
        $fechaInicio = $this->fechaInicioVentana($lotes);

        $totalesHoy = $this->sumarHuevosYMuertes(
            $this->registrosGalpon($galpon)->delDia(),
        );

        $totalesAcumulados = $fechaInicio === null
            ? ['huevos' => 0, 'muertes' => 0]
            : $this->sumarHuevosYMuertes(
                $this->registrosGalpon($galpon)->where('created_at', '>=', $fechaInicio),
            );

        return [
            'aves_actuales' => (int) $galpon->aves_actuales,
            'huevos_hoy' => $totalesHoy['huevos'],
            'maples_hoy' => $this->maplesDesdeHuevos($totalesHoy['huevos']),
            'muertes_hoy' => $totalesHoy['muertes'],
            'huevos_acumulados' => $totalesAcumulados['huevos'],
            'maples_acumulados' => $this->maplesDesdeHuevos($totalesAcumulados['huevos']),
            'muertes_acumuladas' => $totalesAcumulados['muertes'],
            'lotes' => $lotes,
            'fecha_inicio_ventana' => $fechaInicio,
            'multiples_lotes' => $lotes->count() > 1,
        ];
    }

    /**
     * @return array{huevos: int, muertes: int}
     */
    private function sumarHuevosYMuertes(Builder $query): array
    {
        $totales = (clone $query)
            ->selectRaw('COALESCE(SUM(huevos), 0) as huevos, COALESCE(SUM(muertes), 0) as muertes')
            ->first();

        return [
            'huevos' => (int) ($totales->huevos ?? 0),
            'muertes' => (int) ($totales->muertes ?? 0),
        ];
    }

    /**
     * @return Builder<RegistroOperativo>
     */
    private function registrosGalpon(Galpon $galpon): Builder
    {
        return RegistroOperativo::query()
            ->forEmpresa((int) $galpon->empresa_id)
            ->where('galpon_id', $galpon->id)
            ->activos();
    }
}
