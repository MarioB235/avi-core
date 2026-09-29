<?php

namespace App\Support;

use App\Exceptions\ReporteConsultaNoDisponibleException;
use App\Services\ReporteConsultaService;

/**
 * Puerta REP-07: no confundir fallo de consulta con export vacío legítimo.
 */
final class ReporteExportGuard
{
    /**
     * @param  array{estado_consulta?: string, motivo_no_disponible?: ?string}  $datos
     */
    public static function assertDescargable(array $datos): void
    {
        $estado = $datos['estado_consulta'] ?? ReporteEstadoConsulta::NoDisponible->value;

        if ($estado === ReporteEstadoConsulta::NoDisponible->value) {
            throw new ReporteConsultaNoDisponibleException(
                $datos['motivo_no_disponible'] ?? 'No se pudo generar el reporte con los filtros indicados.',
            );
        }
    }

    /**
     * @param  array{reporte_id?: string, estado_consulta?: string}  $datos
     */
    public static function filaMensajeSinDatos(array $datos): string
    {
        return match ($datos['reporte_id'] ?? '') {
            ReporteConsultaService::REPORTE_ID_HISTORIA_LOTE => 'Sin movimientos ni vacunaciones en el período (ficha del lote sí incluida si aplica).',
            ReporteConsultaService::REPORTE_ID_SANIDAD => 'Sin vacunaciones en el alcance y período seleccionados.',
            ReporteConsultaService::REPORTE_ID_MOVIMIENTOS => 'Sin galpones o sin movimientos en el alcance seleccionado.',
            default => 'Sin registros en el alcance seleccionado.',
        };
    }

    public static function sinDatosEnFilas(array $datos, bool $filasVacias): bool
    {
        $estado = $datos['estado_consulta'] ?? ReporteEstadoConsulta::Ok->value;

        return $estado === ReporteEstadoConsulta::SinDatos->value || $filasVacias;
    }
}
