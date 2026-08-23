<?php

namespace App\Services;

use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class AdminResumenService
{
    public const MORTALIDAD_REFERENCIA_PCT = 1.1;

    public function __construct(private OperarioGalponResumenService $galponResumen) {}

    public function for(User $user, ?int $granjaId = null, array $galponIds = []): AdminResumenViewData
    {
        $galpones = $this->galponesEnScope($user, $granjaId, $galponIds);

        $huevosHoy = 0;
        $huevosDescarteHoy = 0;
        $muertesHoy = 0;
        $avesActuales = 0;
        $alertasCount = 0;

        /** @var list<array{galpon: Galpon, resumen: array<string, mixed>, mortalidad_pct: float, alerta_mortalidad: bool}> $filas */
        $filas = [];

        foreach ($galpones as $galpon) {
            $resumen = $this->galponResumen->resumen($galpon);
            $mortalidadPct = $this->mortalidadAcumuladaPct($resumen);
            $alerta = $mortalidadPct > self::MORTALIDAD_REFERENCIA_PCT;

            if ($alerta) {
                $alertasCount++;
            }

            $huevosHoy += $resumen['huevos_hoy'];
            $huevosDescarteHoy += $resumen['huevos_descarte_hoy'];
            $muertesHoy += $resumen['muertes_hoy'];
            $avesActuales += $resumen['aves_actuales'];

            $filas[] = [
                'galpon' => $galpon,
                'resumen' => $resumen,
                'mortalidad_pct' => $mortalidadPct,
                'alerta_mortalidad' => $alerta,
            ];
        }

        return new AdminResumenViewData(
            huevosHoy: $huevosHoy,
            huevosDescarteHoy: $huevosDescarteHoy,
            muertesHoy: $muertesHoy,
            avesActuales: $avesActuales,
            alertasCount: $alertasCount,
            galponesResumen: $filas,
            galponesActivos: $galpones->count(),
        );
    }

    /**
     * @return array{huevos_hoy: int, muertes_hoy: int, alertas_count: int, galpones_activos: int}
     */
    public function teaserFor(User $user): array
    {
        if ($user->empresa_id === null || ! $user->rol->canViewResumen()) {
            return [
                'huevos_hoy' => 0,
                'muertes_hoy' => 0,
                'alertas_count' => 0,
                'galpones_activos' => 0,
            ];
        }

        $data = $this->for($user);

        return [
            'huevos_hoy' => $data->huevosHoy,
            'muertes_hoy' => $data->muertesHoy,
            'alertas_count' => $data->alertasCount,
            'galpones_activos' => $data->galponesActivos,
        ];
    }

    /**
     * @return Collection<int, Granja>
     */
    public function granjasParaFiltro(User $user): Collection
    {
        if ($user->empresa_id === null) {
            return new Collection;
        }

        return Granja::query()
            ->where('empresa_id', $user->empresa_id)
            ->where('activa', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'dicose']);
    }

    /**
     * @return Collection<int, Galpon>
     */
    public function galponesParaFiltro(User $user, ?int $granjaId = null): Collection
    {
        return $this->galponesEnScope($user, $granjaId, []);
    }

    /**
     * @param  list<int>  $galponIds
     * @return Collection<int, Galpon>
     */
    private function galponesEnScope(User $user, ?int $granjaId, array $galponIds): Collection
    {
        if ($user->empresa_id === null) {
            return new Collection;
        }

        $query = Galpon::query()
            ->with('granja')
            ->where('empresa_id', $user->empresa_id)
            ->where('activo', true)
            ->orderBy('nombre');

        if ($granjaId !== null) {
            $query->where('granja_id', $granjaId);
        }

        if ($galponIds !== []) {
            $query->whereIn('id', $galponIds);
        }

        return $query->get();
    }

    /**
     * @param  array<string, mixed>  $resumen
     */
    private function mortalidadAcumuladaPct(array $resumen): float
    {
        /** @var Collection<int, Lote> $lotes */
        $lotes = $resumen['lotes'];
        $poblacionInicial = (int) $lotes->sum('cantidad_inicial');

        if ($poblacionInicial < 1) {
            return 0.0;
        }

        return round(((int) $resumen['muertes_acumuladas'] / $poblacionInicial) * 100, 2);
    }
}

readonly class AdminResumenViewData
{
    /**
     * @param  list<array{galpon: Galpon, resumen: array<string, mixed>, mortalidad_pct: float, alerta_mortalidad: bool}>  $galponesResumen
     */
    public function __construct(
        public int $huevosHoy,
        public int $huevosDescarteHoy,
        public int $muertesHoy,
        public int $avesActuales,
        public int $alertasCount,
        public array $galponesResumen,
        public int $galponesActivos,
    ) {}
}
