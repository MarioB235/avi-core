<?php

namespace App\Services;

use App\Support\ExcelExportSeguro;
use App\Support\ReporteExportGuard;
use App\Support\ReporteFiltroProduccion;
use Illuminate\Support\Carbon;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use RuntimeException;

/**
 * Excel «producción diaria» desde {@see ReporteConsultaService} (REP-03).
 */
class ReporteProduccionDiariaExcelExporter
{
    public function __construct(
        private ReporteConsultaService $consulta,
    ) {}

    public function nombreArchivo(ReporteFiltroProduccion $filtro): string
    {
        $desde = $filtro->fechaDesde->format('Y-m-d');
        $hasta = $filtro->fechaHasta->format('Y-m-d');

        return $desde === $hasta
            ? "produccion-diaria_{$desde}.xlsx"
            : "produccion-diaria_{$desde}_{$hasta}.xlsx";
    }

    public function generar(ReporteFiltroProduccion $filtro): string
    {
        $datos = $this->consulta->produccionDiaria($filtro);
        ReporteExportGuard::assertDescargable($datos);

        $temp = tempnam(sys_get_temp_dir(), 'avicore_rep_');

        if ($temp === false) {
            throw new RuntimeException('No se pudo crear archivo temporal para el Excel.');
        }

        $path = $temp.'.xlsx';
        @unlink($temp);

        $writer = new Writer;
        $writer->openToFile($path);

        $empresa = $filtro->usuario->empresa;
        $empresaNombre = ExcelExportSeguro::texto($empresa?->nombre ?? 'Empresa');

        $writer->addRow(Row::fromValues(['AviCore — Producción diaria']));
        $writer->addRow(Row::fromValues(['Empresa', $empresaNombre]));
        $writer->addRow(Row::fromValues([
            'Período',
            $filtro->fechaDesde->format('d/m/Y').' — '.$filtro->fechaHasta->format('d/m/Y'),
        ]));
        $writer->addRow(Row::fromValues([
            'Filtros',
            ExcelExportSeguro::texto($this->etiquetaFiltros($filtro)),
        ]));
        $writer->addRow(Row::fromValues(['Generado', now()->format('d/m/Y H:i')]));
        $writer->addRow(Row::fromValues([]));

        $etiquetaAlimento = $datos['alimento_etiqueta'];

        $writer->addRow(Row::fromValues([
            'Día',
            'Huevos aptos',
            'Huevos descarte',
            'Muertes',
            'Descarte aves',
            ExcelExportSeguro::texto($etiquetaAlimento),
        ]));

        if (ReporteExportGuard::sinDatosEnFilas($datos, $datos['filas_dia'] === [])) {
            $writer->addRow(Row::fromValues([
                ExcelExportSeguro::texto(ReporteExportGuard::filaMensajeSinDatos($datos)),
            ]));
        } else {
            foreach ($datos['filas_dia'] as $fila) {
                $fecha = Carbon::parse($fila['fecha'])->startOfDay();
                $t = $fila['totales'];

                $writer->addRow(Row::fromValues([
                    $fecha,
                    $t['huevos'],
                    $t['huevos_descarte'],
                    $t['muertes'],
                    $t['descarte_aves'],
                    $t['alimento_kg'],
                ]));
            }

            $total = $datos['totales_periodo'];
            $writer->addRow(Row::fromValues([
                'Total período',
                $total['huevos'],
                $total['huevos_descarte'],
                $total['muertes'],
                $total['descarte_aves'],
                $total['alimento_kg'],
            ]));
        }

        $writer->close();

        $contenido = file_get_contents($path);

        @unlink($path);

        if ($contenido === false || $contenido === '') {
            throw new RuntimeException('El Excel generado está vacío.');
        }

        return $contenido;
    }

    private function etiquetaFiltros(ReporteFiltroProduccion $filtro): string
    {
        $partes = ['Granja: '.($filtro->granjaId ?? 'todas'), 'Galpón: '.($filtro->galponId ?? 'todos')];

        return implode(' · ', $partes);
    }
}
