<?php

namespace App\Services;

use App\Enums\MovimientoAvesEstado;
use App\Enums\MovimientoAvesTipo;
use App\Enums\RegistroOperativoEstado;
use App\Exceptions\ReporteConsultaNoDisponibleException;
use App\Models\Galpon;
use App\Models\Lote;
use App\Models\MovimientoAves;
use App\Models\User;
use App\Models\Vacunacion;
use App\Support\AlimentoEntregaSemantica;
use App\Support\DiaOperativoEmpresa;
use App\Support\ReporteEstadoConsulta;
use App\Support\ReporteFiltroLote;
use App\Support\ReporteFiltroProduccion;
use Illuminate\Support\Carbon;

/**
 * Consultas compartidas para exportaciones (REP-02).
 * Agregados: solo vía {@see TotalesCapturaDiaService} — sin SUM duplicados en reportes.
 */
class ReporteConsultaService
{
    public const REPORTE_ID_PRODUCCION = 'produccion_diaria';

    public const REPORTE_ID_MOVIMIENTOS = 'movimientos_existencias';

    public const REPORTE_ID_HISTORIA_LOTE = 'historia_lote';

    public const REPORTE_ID_SANIDAD = 'sanidad_basica';

    public function __construct(
        private EmpresaContextService $empresaContext,
        private SoporteEmpresaService $soporte,
        private TotalesCapturaDiaService $totalesCapturaDia,
        private MovimientoAvesConciliacionService $movimientoConciliacion,
        private EstructuraFichaService $estructuraFicha,
        private LoteUbicacionHistoricaService $ubicacionHistorica,
        private ReporteAutorizacionFiltrosService $autorizacionFiltros,
    ) {}

    /**
     * @return array{
     *     reporte_id: string,
     *     fuente_agregados: string,
     *     vacio: bool,
     *     filtros: array{granja_id: ?int, galpon_id: ?int, fecha_desde: string, fecha_hasta: string},
     *     alimento_etiqueta: string,
     *     filas_dia: list<array{fecha: string, etiqueta: string, totales: array{huevos: int, huevos_descarte: int, muertes: int, descarte_aves: int, alimento_kg: float}}>,
     *     totales_periodo: array{huevos: int, huevos_descarte: int, muertes: int, descarte_aves: int, alimento_kg: float},
     * }
     */
    public function produccionDiaria(ReporteFiltroProduccion $filtro): array
    {
        $vacio = $this->estructuraVacia($filtro);

        if (! $this->soporte->canViewResumenOperativo($filtro->usuario)) {
            return $this->noDisponible($vacio, 'Sin permiso para consultar el resumen operativo.');
        }

        $empresaId = $this->empresaContext->empresaIdFor($filtro->usuario);

        if ($empresaId === null) {
            return $this->noDisponible($vacio, 'No hay empresa activa para armar el reporte.');
        }

        try {
            $this->autorizacionFiltros->validarGranjaGalpon(
                $filtro->usuario,
                $filtro->granjaId,
                $filtro->galponId,
            );
        } catch (ReporteConsultaNoDisponibleException $e) {
            return $this->noDisponible($vacio, $e->getMessage());
        }

        $galpones = $this->totalesCapturaDia->galponesEnScope(
            $filtro->usuario,
            $filtro->granjaId,
            $filtro->galponId,
        );

        if ($galpones->isEmpty()) {
            return $this->sinDatos($vacio);
        }

        $filasDia = [];
        $acumulado = $this->totalesVacios();

        $cursor = $filtro->fechaDesde->copy()->startOfDay();
        $fin = $filtro->fechaHasta->copy()->startOfDay();

        while ($cursor->lte($fin)) {
            $dia = DiaOperativoEmpresa::enFechaParaEmpresa($empresaId, $cursor->toDateString());
            $totales = $this->totalesCapturaDia->paraUsuario(
                $filtro->usuario,
                $filtro->granjaId,
                $filtro->galponId,
                $dia->fechaLogica,
            );

            $filasDia[] = [
                'fecha' => $dia->fechaLogica->toDateString(),
                'etiqueta' => $dia->fechaLogica->format('d/m/Y'),
                'totales' => $totales,
            ];

            $acumulado = $this->sumarTotales($acumulado, $totales);
            $cursor->addDay();
        }

        $alimento = AlimentoEntregaSemantica::presentacionResumen();

        return $this->ok([
            'reporte_id' => self::REPORTE_ID_PRODUCCION,
            'fuente_agregados' => TotalesCapturaDiaService::class,
            'vacio' => false,
            'filtros' => [
                'granja_id' => $filtro->granjaId,
                'galpon_id' => $filtro->galponId,
                'fecha_desde' => $filtro->fechaDesde->toDateString(),
                'fecha_hasta' => $filtro->fechaHasta->toDateString(),
            ],
            'alimento_etiqueta' => $alimento['etiqueta_columna'],
            'filas_dia' => $filasDia,
            'totales_periodo' => $acumulado,
        ]);
    }

    /**
     * Detalle por galpón en un día (misma agregación que Resumen filtrado por galpón).
     *
     * @return list<array{
     *     galpon_id: int,
     *     galpon_nombre: string,
     *     granja_nombre: string,
     *     totales: array{huevos: int, huevos_descarte: int, muertes: int, descarte_aves: int, alimento_kg: float},
     * }>
     */
    public function produccionPorGalponEnDia(
        User $user,
        ?int $granjaId,
        ?int $galponId,
        Carbon $fecha,
    ): array {
        if (! $this->soporte->canViewResumenOperativo($user)) {
            return [];
        }

        $galpones = $this->totalesCapturaDia->galponesEnScope($user, $granjaId, $galponId);

        $fecha = $fecha->copy()->startOfDay();
        $filas = [];

        foreach ($galpones as $galpon) {
            assert($galpon instanceof Galpon);

            $filas[] = [
                'galpon_id' => $galpon->id,
                'galpon_nombre' => $galpon->nombre,
                'granja_nombre' => $galpon->granja?->nombre ?? 'Sin granja',
                'totales' => $this->totalesCapturaDia->paraUsuario(
                    $user,
                    $granjaId,
                    $galpon->id,
                    $fecha,
                ),
            ];
        }

        return $filas;
    }

    /**
     * Totales del día operativo actual — misma firma que Historial/Resumen (vista en pantalla).
     *
     * @return array{huevos: int, huevos_descarte: int, muertes: int, descarte_aves: int, alimento_kg: float}
     */
    public function totalesVistaHoy(User $user, ?int $granjaId = null, ?int $galponId = null): array
    {
        return $this->totalesCapturaDia->paraUsuario($user, $granjaId, $galponId);
    }

    /**
     * Historia de un lote (REP-06) — misma atribución que EST-10 / RES-05.
     *
     * @return array{
     *     reporte_id: string,
     *     fuente_agregados: string,
     *     vacio: bool,
     *     filtros: array{lote_id: int, fecha_desde: string, fecha_hasta: string},
     *     ficha: array<string, mixed>,
     *     ubicaciones: list<array{galpon_id: int, galpon_nombre: string, desde: string, hasta: ?string}>,
     *     movimientos: list<array<string, mixed>>,
     *     vacunaciones: list<array<string, mixed>>,
     *     produccion_periodo: array{atribuible: bool, huevos_aptos: ?int, aviso: ?string},
     * }
     */
    public function historiaLote(ReporteFiltroLote $filtro): array
    {
        $vacio = $this->estructuraVaciaHistoriaLote($filtro);

        if (! $this->soporte->canViewResumenOperativo($filtro->usuario)) {
            return $this->noDisponible($vacio, 'Sin permiso para consultar el resumen operativo.');
        }

        $empresaId = $this->empresaContext->empresaIdFor($filtro->usuario);

        if ($empresaId === null) {
            return $this->noDisponible($vacio, 'No hay empresa activa para armar el reporte.');
        }

        try {
            $this->autorizacionFiltros->validarLote($filtro->usuario, $filtro->loteId);
        } catch (ReporteConsultaNoDisponibleException $e) {
            return $this->noDisponible($vacio, $e->getMessage());
        }

        $lote = Lote::query()
            ->whereKey($filtro->loteId)
            ->where('empresa_id', $empresaId)
            ->first();

        if (! $lote instanceof Lote) {
            return $this->noDisponible($vacio, 'Lote no encontrado o fuera de alcance.');
        }

        $lote->loadMissing(['galpon.granja']);
        $ficha = $this->estructuraFicha->lote($lote);

        $galponesNombres = Galpon::query()
            ->where('empresa_id', $empresaId)
            ->pluck('nombre', 'id');

        $ubicaciones = collect($this->ubicacionHistorica->segmentosUbicacion($lote))
            ->map(fn (array $segmento): array => [
                'galpon_id' => $segmento['galpon_id'],
                'galpon_nombre' => (string) ($galponesNombres[$segmento['galpon_id']] ?? 'Galpón #'.$segmento['galpon_id']),
                'desde' => $segmento['desde'],
                'hasta' => $segmento['hasta'],
            ])
            ->all();

        $desde = $filtro->fechaDesde->copy()->startOfDay();
        $hasta = $filtro->fechaHasta->copy()->endOfDay();

        $movimientos = MovimientoAves::query()
            ->where('empresa_id', $empresaId)
            ->where('lote_id', $lote->id)
            ->where('estado', MovimientoAvesEstado::Activo->value)
            ->where('fecha_efectiva', '>=', $desde)
            ->where('fecha_efectiva', '<=', $hasta)
            ->with(['galponOrigen', 'galponDestino'])
            ->orderBy('fecha_efectiva')
            ->orderBy('id')
            ->get()
            ->map(fn (MovimientoAves $mov): array => [
                'id' => (int) $mov->id,
                'fecha' => $mov->fecha_efectiva?->format('Y-m-d H:i') ?? '',
                'tipo' => $mov->tipo->label(),
                'es_reversion' => $mov->tipo === MovimientoAvesTipo::Reversion,
                'cantidad' => (int) $mov->cantidad,
                'origen' => $mov->galponOrigen?->nombre,
                'destino' => $mov->galponDestino?->nombre,
                'motivo' => (string) $mov->motivo,
            ])
            ->values()
            ->all();

        $vacunaciones = Vacunacion::query()
            ->where('empresa_id', $empresaId)
            ->where('lote_id', $lote->id)
            ->where('created_at', '>=', $desde)
            ->where('created_at', '<=', $hasta)
            ->with('user')
            ->orderBy('created_at')
            ->get()
            ->map(fn (Vacunacion $vac): array => [
                'fecha' => $vac->created_at?->format('Y-m-d H:i') ?? '',
                'vacuna' => $vac->vacuna->label(),
                'operario' => $vac->user?->name ?? '—',
                'estado' => $vac->estado === RegistroOperativoEstado::Anulado ? 'Anulado' : 'Activo',
                'motivo_anulacion' => $vac->motivo_anulacion,
            ])
            ->values()
            ->all();

        if ($ficha['metricas_atribuibles']) {
            $produccion = [
                'atribuible' => true,
                'huevos_aptos' => $this->ubicacionHistorica->huevosAptosPorGalponEnPeriodo(
                    $lote->galpon,
                    $filtro->fechaDesde,
                    $filtro->fechaHasta,
                ),
                'aviso' => null,
            ];
        } else {
            $produccion = [
                'atribuible' => false,
                'huevos_aptos' => null,
                'aviso' => $ficha['metricas_aviso']
                    ?? 'La producción del galpón no se reparte entre lotes activos.',
            ];
        }

        $payload = [
            'reporte_id' => self::REPORTE_ID_HISTORIA_LOTE,
            'fuente_agregados' => EstructuraFichaService::class,
            'vacio' => false,
            'filtros' => [
                'lote_id' => $lote->id,
                'fecha_desde' => $filtro->fechaDesde->toDateString(),
                'fecha_hasta' => $filtro->fechaHasta->toDateString(),
            ],
            'ficha' => $ficha,
            'ubicaciones' => $ubicaciones,
            'movimientos' => $movimientos,
            'vacunaciones' => $vacunaciones,
            'produccion_periodo' => $produccion,
        ];

        if ($movimientos === [] && $vacunaciones === []) {
            return $this->sinDatos($payload);
        }

        return $this->ok($payload);
    }

    /**
     * Listado de vacunaciones (REP-06).
     *
     * @return array{
     *     reporte_id: string,
     *     fuente_agregados: string,
     *     vacio: bool,
     *     filtros: array{granja_id: ?int, galpon_id: ?int, lote_id: ?int, fecha_desde: string, fecha_hasta: string},
     *     filas: list<array<string, mixed>>,
     * }
     */
    public function sanidadBasica(ReporteFiltroProduccion $filtro, ?int $loteId = null): array
    {
        $vacio = $this->estructuraVaciaSanidad($filtro, $loteId);

        if (! $this->soporte->canViewResumenOperativo($filtro->usuario)) {
            return $this->noDisponible($vacio, 'Sin permiso para consultar el resumen operativo.');
        }

        $empresaId = $this->empresaContext->empresaIdFor($filtro->usuario);

        if ($empresaId === null) {
            return $this->noDisponible($vacio, 'No hay empresa activa para armar el reporte.');
        }

        try {
            $this->autorizacionFiltros->validarGranjaGalpon(
                $filtro->usuario,
                $filtro->granjaId,
                $filtro->galponId,
            );
            if ($loteId !== null) {
                $this->autorizacionFiltros->validarLote($filtro->usuario, $loteId);
            }
        } catch (ReporteConsultaNoDisponibleException $e) {
            return $this->noDisponible($vacio, $e->getMessage());
        }

        $galpones = $this->totalesCapturaDia->galponesEnScope(
            $filtro->usuario,
            $filtro->granjaId,
            $filtro->galponId,
        );

        if ($galpones->isEmpty()) {
            return $this->sinDatos($vacio);
        }

        $desde = $filtro->fechaDesde->copy()->startOfDay();
        $hasta = $filtro->fechaHasta->copy()->endOfDay();

        $query = Vacunacion::query()
            ->where('empresa_id', $empresaId)
            ->whereIn('galpon_id', $galpones->modelKeys())
            ->where('created_at', '>=', $desde)
            ->where('created_at', '<=', $hasta)
            ->with(['user', 'galpon', 'lote']);

        if ($loteId !== null) {
            $query->where('lote_id', $loteId);
        }

        $filas = $query
            ->orderBy('created_at')
            ->get()
            ->map(fn (Vacunacion $vac): array => [
                'fecha' => $vac->created_at?->format('Y-m-d H:i') ?? '',
                'galpon' => $vac->galpon?->nombre ?? '—',
                'lote' => $vac->lote?->codigo ?? '—',
                'vacuna' => $vac->vacuna->label(),
                'operario' => $vac->user?->name ?? '—',
                'estado' => $vac->estado === RegistroOperativoEstado::Anulado ? 'Anulado' : 'Activo',
                'motivo_anulacion' => $vac->motivo_anulacion,
                'observacion' => $vac->observacion,
            ])
            ->values()
            ->all();

        $payload = [
            'reporte_id' => self::REPORTE_ID_SANIDAD,
            'fuente_agregados' => Vacunacion::class,
            'vacio' => false,
            'filtros' => [
                'granja_id' => $filtro->granjaId,
                'galpon_id' => $filtro->galponId,
                'lote_id' => $loteId,
                'fecha_desde' => $filtro->fechaDesde->toDateString(),
                'fecha_hasta' => $filtro->fechaHasta->toDateString(),
            ],
            'filas' => $filas,
        ];

        if ($filas === []) {
            return $this->sinDatos($payload);
        }

        return $this->ok($payload);
    }

    /**
     * Movimientos y conciliación acumulada por galpón (REP-05) — misma aritmética que MOV-09.
     *
     * @return array{
     *     reporte_id: string,
     *     fuente_agregados: string,
     *     vacio: bool,
     *     filtros: array{granja_id: ?int, galpon_id: ?int, fecha_desde: string, fecha_hasta: string},
     *     bloques_galpon: list<array{
     *         galpon_id: int,
     *         galpon_nombre: string,
     *         granja_nombre: string,
     *         conciliacion: array<string, mixed>,
     *         movimientos: list<array<string, mixed>>,
     *     }>,
     * }
     */
    public function movimientosExistencias(ReporteFiltroProduccion $filtro): array
    {
        $vacio = $this->estructuraVaciaMovimientos($filtro);

        if (! $this->usuarioPuedeExportarMovimientos($filtro->usuario)) {
            return $this->noDisponible($vacio, 'Sin permiso para exportar movimientos de aves.');
        }

        try {
            $this->autorizacionFiltros->validarGranjaGalpon(
                $filtro->usuario,
                $filtro->granjaId,
                $filtro->galponId,
            );
        } catch (ReporteConsultaNoDisponibleException $e) {
            return $this->noDisponible($vacio, $e->getMessage());
        }

        $galpones = $this->totalesCapturaDia->galponesEnScope(
            $filtro->usuario,
            $filtro->granjaId,
            $filtro->galponId,
        );

        if ($galpones->isEmpty()) {
            return $this->sinDatos($vacio);
        }

        $bloques = [];

        foreach ($galpones as $galpon) {
            assert($galpon instanceof Galpon);

            $bloques[] = [
                'galpon_id' => $galpon->id,
                'galpon_nombre' => $galpon->nombre,
                'granja_nombre' => $galpon->granja?->nombre ?? 'Sin granja',
                'conciliacion' => $this->movimientoConciliacion->conciliacionAcumulada(
                    $galpon,
                    $filtro->fechaDesde,
                    $filtro->fechaHasta,
                ),
                'movimientos' => $this->movimientoConciliacion->ledgerFilasParaReporte(
                    $galpon,
                    $filtro->fechaDesde,
                    $filtro->fechaHasta,
                ),
            ];
        }

        $payload = [
            'reporte_id' => self::REPORTE_ID_MOVIMIENTOS,
            'fuente_agregados' => MovimientoAvesConciliacionService::class,
            'vacio' => false,
            'filtros' => [
                'granja_id' => $filtro->granjaId,
                'galpon_id' => $filtro->galponId,
                'fecha_desde' => $filtro->fechaDesde->toDateString(),
                'fecha_hasta' => $filtro->fechaHasta->toDateString(),
            ],
            'bloques_galpon' => $bloques,
        ];

        $sinMovimientos = collect($bloques)->every(
            fn (array $bloque): bool => $bloque['movimientos'] === [],
        );

        if ($sinMovimientos) {
            return $this->sinDatos($payload);
        }

        return $this->ok($payload);
    }

    /**
     * @return array{
     *     reporte_id: string,
     *     fuente_agregados: string,
     *     vacio: bool,
     *     filtros: array{lote_id: int, fecha_desde: string, fecha_hasta: string},
     *     ficha: array<string, mixed>,
     *     ubicaciones: list<array<string, mixed>>,
     *     movimientos: list<array<string, mixed>>,
     *     vacunaciones: list<array<string, mixed>>,
     *     produccion_periodo: array{atribuible: bool, huevos_aptos: ?int, aviso: ?string},
     * }
     */
    private function estructuraVaciaHistoriaLote(ReporteFiltroLote $filtro): array
    {
        return [
            'reporte_id' => self::REPORTE_ID_HISTORIA_LOTE,
            'fuente_agregados' => EstructuraFichaService::class,
            'vacio' => true,
            'filtros' => [
                'lote_id' => $filtro->loteId,
                'fecha_desde' => $filtro->fechaDesde->toDateString(),
                'fecha_hasta' => $filtro->fechaHasta->toDateString(),
            ],
            'ficha' => [],
            'ubicaciones' => [],
            'movimientos' => [],
            'vacunaciones' => [],
            'produccion_periodo' => [
                'atribuible' => false,
                'huevos_aptos' => null,
                'aviso' => null,
            ],
        ];
    }

    /**
     * @return array{
     *     reporte_id: string,
     *     fuente_agregados: string,
     *     vacio: bool,
     *     filtros: array{granja_id: ?int, galpon_id: ?int, lote_id: ?int, fecha_desde: string, fecha_hasta: string},
     *     filas: list<array<string, mixed>>,
     * }
     */
    private function estructuraVaciaSanidad(ReporteFiltroProduccion $filtro, ?int $loteId): array
    {
        return [
            'reporte_id' => self::REPORTE_ID_SANIDAD,
            'fuente_agregados' => Vacunacion::class,
            'vacio' => true,
            'filtros' => [
                'granja_id' => $filtro->granjaId,
                'galpon_id' => $filtro->galponId,
                'lote_id' => $loteId,
                'fecha_desde' => $filtro->fechaDesde->toDateString(),
                'fecha_hasta' => $filtro->fechaHasta->toDateString(),
            ],
            'filas' => [],
        ];
    }

    /**
     * @return array{
     *     reporte_id: string,
     *     fuente_agregados: string,
     *     vacio: bool,
     *     filtros: array{granja_id: ?int, galpon_id: ?int, fecha_desde: string, fecha_hasta: string},
     *     bloques_galpon: list<array<string, mixed>>,
     * }
     */
    private function estructuraVaciaMovimientos(ReporteFiltroProduccion $filtro): array
    {
        return [
            'reporte_id' => self::REPORTE_ID_MOVIMIENTOS,
            'fuente_agregados' => MovimientoAvesConciliacionService::class,
            'vacio' => true,
            'filtros' => [
                'granja_id' => $filtro->granjaId,
                'galpon_id' => $filtro->galponId,
                'fecha_desde' => $filtro->fechaDesde->toDateString(),
                'fecha_hasta' => $filtro->fechaHasta->toDateString(),
            ],
            'bloques_galpon' => [],
        ];
    }

    private function usuarioPuedeExportarMovimientos(User $user): bool
    {
        if ($this->soporte->blocksProductionMutations($user)) {
            return false;
        }

        return $user->empresa_id !== null && $user->rol->canManageLotes();
    }

    private function estructuraVacia(ReporteFiltroProduccion $filtro): array
    {
        return [
            'reporte_id' => self::REPORTE_ID_PRODUCCION,
            'fuente_agregados' => TotalesCapturaDiaService::class,
            'vacio' => true,
            'filtros' => [
                'granja_id' => $filtro->granjaId,
                'galpon_id' => $filtro->galponId,
                'fecha_desde' => $filtro->fechaDesde->toDateString(),
                'fecha_hasta' => $filtro->fechaHasta->toDateString(),
            ],
            'alimento_etiqueta' => AlimentoEntregaSemantica::presentacionResumen()['etiqueta_columna'],
            'filas_dia' => [],
            'totales_periodo' => $this->totalesVacios(),
        ];
    }

    /**
     * @return array{huevos: int, huevos_descarte: int, muertes: int, descarte_aves: int, alimento_kg: float}
     */
    private function totalesVacios(): array
    {
        return [
            'huevos' => 0,
            'huevos_descarte' => 0,
            'muertes' => 0,
            'descarte_aves' => 0,
            'alimento_kg' => 0.0,
        ];
    }

    /**
     * @param  array{huevos: int, huevos_descarte: int, muertes: int, descarte_aves: int, alimento_kg: float}  $a
     * @param  array{huevos: int, huevos_descarte: int, muertes: int, descarte_aves: int, alimento_kg: float}  $b
     * @return array{huevos: int, huevos_descarte: int, muertes: int, descarte_aves: int, alimento_kg: float}
     */
    private function sumarTotales(array $a, array $b): array
    {
        return [
            'huevos' => $a['huevos'] + $b['huevos'],
            'huevos_descarte' => $a['huevos_descarte'] + $b['huevos_descarte'],
            'muertes' => $a['muertes'] + $b['muertes'],
            'descarte_aves' => $a['descarte_aves'] + $b['descarte_aves'],
            'alimento_kg' => round($a['alimento_kg'] + $b['alimento_kg'], 2),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function ok(array $payload): array
    {
        $payload['estado_consulta'] = ReporteEstadoConsulta::Ok->value;
        $payload['motivo_no_disponible'] = null;

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function sinDatos(array $payload): array
    {
        $payload['estado_consulta'] = ReporteEstadoConsulta::SinDatos->value;
        $payload['motivo_no_disponible'] = null;

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function noDisponible(array $payload, string $motivo): array
    {
        $payload['estado_consulta'] = ReporteEstadoConsulta::NoDisponible->value;
        $payload['motivo_no_disponible'] = $motivo;
        $payload['vacio'] = true;

        return $payload;
    }
}
