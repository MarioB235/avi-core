<?php

namespace App\Services;

use App\Enums\RegistroOperativoTipo;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Support\CompletitudDiariaD03;
use App\Support\DiaOperativoEmpresa;
use App\Support\MortalidadVentanaGalpon;
use App\Support\ResumenMetricasCatalog;
use Illuminate\Database\Eloquent\Collection;

class AdminResumenService
{
    public const MORTALIDAD_REFERENCIA_PCT = ResumenMetricasCatalog::UMBRAL_MORTALIDAD_REFERENCIA_PCT;

    public function __construct(
        private OperarioGalponResumenService $galponResumen,
        private EmpresaContextService $empresaContext,
        private SoporteEmpresaService $soporte,
        private TotalesCapturaDiaService $totalesCapturaDia,
        private MortalidadVentanaGalpon $mortalidadVentana,
    ) {}

    public function for(User $user, ?int $granjaId = null, ?int $galponId = null): AdminResumenViewData
    {
        if (! $this->soporte->canViewResumenOperativo($user)) {
            return $this->resumenVacio();
        }

        $empresaId = $this->empresaContext->empresaIdFor($user);

        if ($empresaId === null) {
            return $this->resumenVacio();
        }

        $galpones = $this->totalesCapturaDia->galponesEnScope($user, $granjaId, $galponId);
        $galponIds = $galpones->modelKeys();

        $totalesDia = $this->totalesCapturaDia->paraUsuario($user, $granjaId, $galponId);

        /** @var array<int, float> $alimentoPorGalpon */
        $alimentoPorGalpon = $galponIds === []
            ? []
            : RegistroOperativo::query()
                ->activos()
                ->where('empresa_id', $empresaId)
                ->where('tipo', RegistroOperativoTipo::Alimento)
                ->whereIn('galpon_id', $galponIds)
                ->delDia($empresaId)
                ->selectRaw('galpon_id, COALESCE(SUM(alimento_kg), 0) as total')
                ->groupBy('galpon_id')
                ->pluck('total', 'galpon_id')
                ->map(fn ($total): float => (float) $total)
                ->all();

        $huevosHoy = $totalesDia['huevos'];
        $huevosDescarteHoy = $totalesDia['huevos_descarte'];
        $muertesHoy = $totalesDia['muertes'];
        $avesActuales = 0;
        $alertasCount = 0;
        $alimentoKgHoy = $totalesDia['alimento_kg'];

        /** @var list<array{galpon: Galpon, resumen: array<string, mixed>, mortalidad_pct: float, alerta_mortalidad: bool, alimento_kg_hoy: float, mortalidad_solo_galpon: bool, mortalidad_incluye_cerrados: bool}> $filas */
        $filas = [];

        foreach ($galpones as $galpon) {
            $resumen = $this->galponResumen->resumen($galpon);
            $mortalidad = $this->mortalidadVentana->metricaParaGalpon($galpon);
            $mortalidadPct = $mortalidad['mortalidad_pct'];
            $alerta = ResumenMetricasCatalog::superaReferenciaMortalidad($mortalidadPct);
            $alimentoGalpon = $alimentoPorGalpon[$galpon->id] ?? 0.0;

            if ($alerta) {
                $alertasCount++;
            }

            $avesActuales += $resumen['aves_actuales'];

            $filas[] = [
                'galpon' => $galpon,
                'resumen' => $resumen,
                'mortalidad_pct' => $mortalidadPct,
                'alerta_mortalidad' => $alerta,
                'alimento_kg_hoy' => $alimentoGalpon,
                'mortalidad_solo_galpon' => $mortalidad['solo_galpon'],
                'mortalidad_incluye_cerrados' => $mortalidad['incluye_cerrados'],
            ];
        }

        return new AdminResumenViewData(
            huevosHoy: $huevosHoy,
            huevosDescarteHoy: $huevosDescarteHoy,
            muertesHoy: $muertesHoy,
            avesActuales: $avesActuales,
            alertasCount: $alertasCount,
            alimentoKgHoy: $alimentoKgHoy,
            galponesResumen: $filas,
            galponesActivos: $galpones->count(),
        );
    }

    /**
     * Pulso ejecutivo para Inicio admin: estado del día, comparación vs ayer y pendientes de campo.
     *
     * @return array{
     *     estado: string,
     *     estado_label: string,
     *     estado_hint: string,
     *     huevos_hoy: int,
     *     huevos_ayer: int,
     *     delta_huevos: int,
     *     delta_huevos_pct: ?float,
     *     muertes_hoy: int,
     *     alertas_count: int,
     *     galpones_activos: int,
     *     galpones_sin_carga: list<array{id: int, nombre: string, granja: string}>,
     *     alertas: list<array{galpon_id: int, nombre: string, granja: string, mortalidad_pct: float}>
     * }
     */
    public function pulsoFor(User $user): array
    {
        if (! $this->soporte->canViewResumenOperativo($user)) {
            return $this->pulsoVacio();
        }

        $empresaId = $this->empresaContext->empresaIdFor($user);

        if ($empresaId === null) {
            return $this->pulsoVacio();
        }

        $data = $this->for($user);
        $galpones = collect($data->galponesResumen);
        $galponIds = $galpones->pluck('galpon.id')->all();

        /** @var list<array{id: int, nombre: string, granja: string}> $galponesSinCarga */
        $galponesSinCarga = $galpones
            ->filter(fn (array $fila): bool => CompletitudDiariaD03::tieneOmisionProductiva($fila['resumen']))
            ->map(fn (array $fila): array => [
                'id' => $fila['galpon']->id,
                'nombre' => $fila['galpon']->nombre,
                'granja' => $fila['galpon']->granja?->nombre ?? 'Sin granja',
            ])
            ->values()
            ->all();

        /** @var list<array{galpon_id: int, nombre: string, granja: string, mortalidad_pct: float}> $alertas */
        $alertas = $galpones
            ->filter(fn (array $fila): bool => $fila['alerta_mortalidad'])
            ->map(fn (array $fila): array => [
                'galpon_id' => $fila['galpon']->id,
                'nombre' => $fila['galpon']->nombre,
                'granja' => $fila['galpon']->granja?->nombre ?? 'Sin granja',
                'mortalidad_pct' => $fila['mortalidad_pct'],
            ])
            ->values()
            ->all();

        $huevosAyer = $galponIds === []
            ? 0
            : (int) RegistroOperativo::query()
                ->activos()
                ->where('empresa_id', $empresaId)
                ->where('tipo', RegistroOperativoTipo::Huevos)
                ->whereIn('galpon_id', $galponIds)
                ->delDia($empresaId, DiaOperativoEmpresa::ayerParaEmpresa($empresaId)->fechaLogica)
                ->sum('huevos');

        $deltaHuevos = $data->huevosHoy - $huevosAyer;
        $deltaHuevosPct = $huevosAyer > 0
            ? round(($deltaHuevos / $huevosAyer) * 100, 1)
            : null;

        $estado = $this->resolverEstadoPulso(count($alertas), count($galponesSinCarga));

        return [
            'estado' => $estado,
            'estado_label' => match ($estado) {
                'revision' => 'Revisar galpones',
                'atencion' => 'Atención en campo',
                default => 'Todo en orden',
            },
            'estado_hint' => match ($estado) {
                'revision' => 'Hay galpones sobre la referencia de mortalidad acumulada o capturas productivas pendientes.',
                'atencion' => 'Faltan huevos, muertes o descarte hoy (dato o cero confirmado). Solo alimento no alcanza.',
                default => 'Huevos, muertes y descarte resueltos hoy; sin alertas de mortalidad.',
            },
            'huevos_hoy' => $data->huevosHoy,
            'huevos_ayer' => $huevosAyer,
            'delta_huevos' => $deltaHuevos,
            'delta_huevos_pct' => $deltaHuevosPct,
            'muertes_hoy' => $data->muertesHoy,
            'alertas_count' => $data->alertasCount,
            'galpones_activos' => $data->galponesActivos,
            'galpones_sin_carga' => $galponesSinCarga,
            'alertas' => $alertas,
        ];
    }

    /**
     * @return array{huevos_hoy: int, muertes_hoy: int, alertas_count: int, galpones_activos: int}
     */
    public function teaserFor(User $user): array
    {
        if (! $this->soporte->canViewResumenOperativo($user)) {
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
        $empresaId = $this->empresaContext->empresaIdFor($user);

        if ($empresaId === null) {
            return new Collection;
        }

        return Granja::query()
            ->where('empresa_id', $empresaId)
            ->where('activa', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'dicose']);
    }

    /**
     * @return Collection<int, Galpon>
     */
    public function galponesParaFiltro(User $user, ?int $granjaId = null): Collection
    {
        return $this->totalesCapturaDia->galponesEnScope($user, $granjaId, null);
    }

    /**
     * Huevos aptos por día (últimos 7 días, incluido hoy) para el scope de filtros.
     *
     * @return list<array{label: string, value: int, date: string}>
     */
    public function posturaSemanal(User $user, ?int $granjaId = null, ?int $galponId = null): array
    {
        $empresaId = $this->empresaContext->empresaIdFor($user);

        if ($empresaId === null) {
            return $this->posturaSemanalVacia();
        }

        $galponIds = $this->totalesCapturaDia->galponesEnScope($user, $granjaId, $galponId)->modelKeys();

        if ($galponIds === []) {
            return $this->posturaSemanalVacia($empresaId);
        }

        $hoy = DiaOperativoEmpresa::hoyParaEmpresa($empresaId);
        $puntos = [];

        for ($i = 6; $i >= 0; $i--) {
            $dia = DiaOperativoEmpresa::forEmpresa(
                $empresaId,
                $hoy->fechaLogica->copy()->subDays($i),
            );

            $total = (int) RegistroOperativo::query()
                ->activos()
                ->where('empresa_id', $empresaId)
                ->where('tipo', RegistroOperativoTipo::Huevos)
                ->whereIn('galpon_id', $galponIds)
                ->delDia($empresaId, $dia->fechaLogica)
                ->sum('huevos');

            $puntos[] = [
                'label' => $dia->fechaLogica->format('j/n'),
                'value' => $total,
                'date' => $dia->fechaLogica->toDateString(),
            ];
        }

        return $puntos;
    }

    /**
     * @return list<array{label: string, value: int, date: string}>
     */
    private function posturaSemanalVacia(?int $empresaId = null): array
    {
        $puntos = [];

        if ($empresaId !== null) {
            $hoy = DiaOperativoEmpresa::hoyParaEmpresa($empresaId);

            for ($i = 6; $i >= 0; $i--) {
                $fecha = $hoy->fechaLogica->copy()->subDays($i);

                $puntos[] = [
                    'label' => $fecha->format('j/n'),
                    'value' => 0,
                    'date' => $fecha->toDateString(),
                ];
            }

            return $puntos;
        }

        $inicio = now()->subDays(6)->startOfDay();

        for ($i = 0; $i < 7; $i++) {
            $fecha = $inicio->copy()->addDays($i);

            $puntos[] = [
                'label' => $fecha->format('j/n'),
                'value' => 0,
                'date' => $fecha->toDateString(),
            ];
        }

        return $puntos;
    }

    private function resumenVacio(): AdminResumenViewData
    {
        return new AdminResumenViewData(
            huevosHoy: 0,
            huevosDescarteHoy: 0,
            muertesHoy: 0,
            avesActuales: 0,
            alertasCount: 0,
            alimentoKgHoy: 0.0,
            galponesResumen: [],
            galponesActivos: 0,
        );
    }

    /**
     * @return array{
     *     estado: string,
     *     estado_label: string,
     *     estado_hint: string,
     *     huevos_hoy: int,
     *     huevos_ayer: int,
     *     delta_huevos: int,
     *     delta_huevos_pct: ?float,
     *     muertes_hoy: int,
     *     alertas_count: int,
     *     galpones_activos: int,
     *     galpones_sin_carga: list<array{id: int, nombre: string, granja: string}>,
     *     alertas: list<array{galpon_id: int, nombre: string, granja: string, mortalidad_pct: float}>
     * }
     */
    private function pulsoVacio(): array
    {
        return [
            'estado' => 'ok',
            'estado_label' => 'Sin datos operativos',
            'estado_hint' => 'Cuando haya galpones activos, verás el pulso del día aquí.',
            'huevos_hoy' => 0,
            'huevos_ayer' => 0,
            'delta_huevos' => 0,
            'delta_huevos_pct' => null,
            'muertes_hoy' => 0,
            'alertas_count' => 0,
            'galpones_activos' => 0,
            'galpones_sin_carga' => [],
            'alertas' => [],
        ];
    }

    private function resolverEstadoPulso(int $alertasCount, int $sinCargaCount): string
    {
        if ($alertasCount > 0) {
            return 'revision';
        }

        if ($sinCargaCount > 0) {
            return 'atencion';
        }

        return 'ok';
    }
}

readonly class AdminResumenViewData
{
    /**
     * @param  list<array{galpon: Galpon, resumen: array<string, mixed>, mortalidad_pct: float, alerta_mortalidad: bool, alimento_kg_hoy: float}>  $galponesResumen
     */
    public function __construct(
        public int $huevosHoy,
        public int $huevosDescarteHoy,
        public int $muertesHoy,
        public int $avesActuales,
        public int $alertasCount,
        public float $alimentoKgHoy,
        public array $galponesResumen,
        public int $galponesActivos,
    ) {}
}
