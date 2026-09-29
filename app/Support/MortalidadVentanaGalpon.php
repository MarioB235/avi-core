<?php

namespace App\Support;

use App\Enums\LoteEstado;
use App\Enums\RegistroOperativoTipo;
use App\Models\Galpon;
use App\Models\Lote;
use App\Models\RegistroOperativo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 * Ventana y % de mortalidad acumulada por galpón (RES-05).
 * No infiere tasa por lote con varios activos; al cerrar ciclo conserva numerador y denominador del ciclo.
 */
final class MortalidadVentanaGalpon
{
    /**
     * @return array{
     *     muertes_acumuladas: int,
     *     poblacion_inicial: int,
     *     mortalidad_pct: float,
     *     solo_galpon: bool,
     *     incluye_cerrados: bool,
     *     fecha_inicio_ventana: ?Carbon,
     *     lotes_en_ciclo: Collection<int, Lote>,
     * }
     */
    public function metricaParaGalpon(Galpon $galpon): array
    {
        $lotes = $this->lotesEnCiclo($galpon);
        $activos = $this->lotesActivos($galpon);
        $incluyeCerrados = $activos->isEmpty() && $lotes->isNotEmpty();
        $fechaInicio = $this->fechaInicioVentana($lotes);
        $poblacion = (int) $lotes->sum('cantidad_inicial');
        $muertes = $fechaInicio === null
            ? 0
            : $this->muertesAcumuladasDesde($galpon, $fechaInicio);

        $pct = 0.0;

        if ($poblacion > 0) {
            $pct = round(($muertes / $poblacion) * 100, 2);
        }

        return [
            'muertes_acumuladas' => $muertes,
            'poblacion_inicial' => $poblacion,
            'mortalidad_pct' => $pct,
            'solo_galpon' => $activos->count() > 1,
            'incluye_cerrados' => $incluyeCerrados,
            'fecha_inicio_ventana' => $fechaInicio,
            'lotes_en_ciclo' => $lotes,
        ];
    }

    /**
     * Lotes que definen ventana y denominador: activos/en producción, o último ciclo cerrado si no queda ninguno.
     *
     * @return Collection<int, Lote>
     */
    public function lotesEnCiclo(Galpon $galpon): Collection
    {
        $activos = $this->lotesActivos($galpon);

        if ($activos->isNotEmpty()) {
            return $activos;
        }

        return $this->lotesCerradosUltimoCierre($galpon);
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

    /**
     * @return Collection<int, Lote>
     */
    private function lotesActivos(Galpon $galpon): Collection
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
     * @return Collection<int, Lote>
     */
    private function lotesCerradosUltimoCierre(Galpon $galpon): Collection
    {
        $cerrados = $galpon->lotes()
            ->where('estado', LoteEstado::Cerrado->value)
            ->orderByDesc('updated_at')
            ->get();

        if ($cerrados->isEmpty()) {
            return $cerrados;
        }

        $ancla = $cerrados->first();
        $diaAncla = $ancla?->updated_at?->copy()->startOfDay();

        if ($diaAncla === null) {
            return $cerrados->take(1)->values();
        }

        return $cerrados
            ->filter(
                fn (Lote $lote): bool => $lote->updated_at?->copy()->startOfDay()->equalTo($diaAncla) ?? false,
            )
            ->sortBy('fecha_ingreso')
            ->values();
    }

    private function muertesAcumuladasDesde(Galpon $galpon, Carbon $fechaInicio): int
    {
        return (int) RegistroOperativo::query()
            ->forEmpresa((int) $galpon->empresa_id)
            ->where('galpon_id', $galpon->id)
            ->activos()
            ->where('tipo', RegistroOperativoTipo::Muertes)
            ->where('created_at', '>=', $fechaInicio)
            ->sum('muertes');
    }
}
