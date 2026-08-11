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
     * Memo intra-request (service scoped): evita re-query de lotes si Home y CargarHub piden lotesActivos en el mismo request.
     *
     * @var array<string, mixed>
     */
    private array $memo = [];

    /**
     * @return Collection<int, Lote>
     */
    public function lotesActivos(Galpon $galpon): Collection
    {
        $memoKey = 'lotes_'.$galpon->id;

        if (isset($this->memo[$memoKey])) {
            /** @var Collection<int, Lote> $cached */
            $cached = $this->memo[$memoKey];

            return $cached;
        }

        return $this->memo[$memoKey] = $galpon->lotes()
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
     *     huevos_descarte_hoy: int,
     *     maples_hoy: int,
     *     muertes_hoy: int,
     *     descarte_aves_hoy: int,
     *     huevos_acumulados: int,
     *     huevos_descarte_acumulados: int,
     *     maples_acumulados: int,
     *     muertes_acumuladas: int,
     *     descarte_aves_acumuladas: int,
     *     lotes: Collection<int, Lote>,
     *     fecha_inicio_ventana: ?Carbon,
     *     multiples_lotes: bool,
     * }
     */
    public function resumen(Galpon $galpon): array
    {
        $lotes = $this->lotesActivos($galpon);
        $fechaInicio = $this->fechaInicioVentana($lotes);

        $totalesHoy = $this->sumarTotales(
            $this->registrosGalpon($galpon)->delDia(),
        );

        $totalesAcumulados = $fechaInicio === null
            ? $this->totalesVacios()
            : $this->sumarTotales(
                $this->registrosGalpon($galpon)->where('created_at', '>=', $fechaInicio),
            );

        return [
            'aves_actuales' => (int) $galpon->aves_actuales,
            'huevos_hoy' => $totalesHoy['huevos'],
            'huevos_descarte_hoy' => $totalesHoy['huevos_descarte'],
            'maples_hoy' => $this->maplesDesdeHuevos($totalesHoy['huevos']),
            'muertes_hoy' => $totalesHoy['muertes'],
            'descarte_aves_hoy' => $totalesHoy['descarte_aves'],
            'huevos_acumulados' => $totalesAcumulados['huevos'],
            'huevos_descarte_acumulados' => $totalesAcumulados['huevos_descarte'],
            'maples_acumulados' => $this->maplesDesdeHuevos($totalesAcumulados['huevos']),
            'muertes_acumuladas' => $totalesAcumulados['muertes'],
            'descarte_aves_acumuladas' => $totalesAcumulados['descarte_aves'],
            'lotes' => $lotes,
            'fecha_inicio_ventana' => $fechaInicio,
            'multiples_lotes' => $lotes->count() > 1,
        ];
    }

    /**
     * @return array{huevos: int, huevos_descarte: int, muertes: int, descarte_aves: int}
     */
    private function sumarTotales(Builder $query): array
    {
        $totales = (clone $query)
            ->selectRaw('COALESCE(SUM(huevos), 0) as huevos')
            ->selectRaw('COALESCE(SUM(huevos_descarte), 0) as huevos_descarte')
            ->selectRaw('COALESCE(SUM(muertes), 0) as muertes')
            ->selectRaw('COALESCE(SUM(descarte_aves), 0) as descarte_aves')
            ->first();

        return [
            'huevos' => (int) ($totales->huevos ?? 0),
            'huevos_descarte' => (int) ($totales->huevos_descarte ?? 0),
            'muertes' => (int) ($totales->muertes ?? 0),
            'descarte_aves' => (int) ($totales->descarte_aves ?? 0),
        ];
    }

    /**
     * @return array{huevos: int, huevos_descarte: int, muertes: int, descarte_aves: int}
     */
    private function totalesVacios(): array
    {
        return [
            'huevos' => 0,
            'huevos_descarte' => 0,
            'muertes' => 0,
            'descarte_aves' => 0,
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
