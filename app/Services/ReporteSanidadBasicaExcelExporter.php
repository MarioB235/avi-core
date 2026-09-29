<?php

namespace App\Services;

use App\Support\ExcelExportSeguro;
use App\Support\ReporteExportGuard;
use App\Support\ReporteFiltroProduccion;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use RuntimeException;

class ReporteSanidadBasicaExcelExporter
{
    public function __construct(private ReporteConsultaService $consulta) {}

    public function nombreArchivo(ReporteFiltroProduccion $filtro): string
    {
        $desde = $filtro->fechaDesde->format('Y-m-d');
        $hasta = $filtro->fechaHasta->format('Y-m-d');

        return $desde === $hasta
            ? "sanidad-basica_{$desde}.xlsx"
            : "sanidad-basica_{$desde}_{$hasta}.xlsx";
    }

    public function generar(ReporteFiltroProduccion $filtro, ?int $loteId = null): string
    {
        $datos = $this->consulta->sanidadBasica($filtro, $loteId);
        ReporteExportGuard::assertDescargable($datos);
        $temp = tempnam(sys_get_temp_dir(), 'avicore_rep_san_');
        if ($temp === false) {
            throw new RuntimeException('No se pudo crear archivo temporal.');
        }
        $path = $temp.'.xlsx';
        @unlink($temp);

        $writer = new Writer;
        $writer->openToFile($path);

        $writer->addRow(Row::fromValues(['AviCore — Sanidad básica (vacunaciones)']));
        $writer->addRow(Row::fromValues([
            'Empresa',
            ExcelExportSeguro::texto($filtro->usuario->empresa?->nombre ?? 'Empresa'),
        ]));
        $writer->addRow(Row::fromValues([
            'Período',
            $filtro->fechaDesde->format('d/m/Y').' — '.$filtro->fechaHasta->format('d/m/Y'),
        ]));
        $writer->addRow(Row::fromValues(['Generado', now()->format('d/m/Y H:i')]));
        $writer->addRow(Row::fromValues([]));

        $writer->addRow(Row::fromValues([
            'Fecha',
            'Galpón',
            'Lote',
            'Vacuna',
            'Operario',
            'Estado',
            'Motivo anulación',
            'Observación',
        ]));

        if (ReporteExportGuard::sinDatosEnFilas($datos, $datos['filas'] === [])) {
            $writer->addRow(Row::fromValues([
                ExcelExportSeguro::texto(ReporteExportGuard::filaMensajeSinDatos($datos)),
            ]));
        } else {
            foreach ($datos['filas'] as $fila) {
                $writer->addRow(Row::fromValues([
                    ExcelExportSeguro::texto($fila['fecha']),
                    ExcelExportSeguro::texto($fila['galpon']),
                    ExcelExportSeguro::texto($fila['lote']),
                    ExcelExportSeguro::texto($fila['vacuna']),
                    ExcelExportSeguro::texto($fila['operario']),
                    ExcelExportSeguro::texto($fila['estado']),
                    ExcelExportSeguro::texto((string) ($fila['motivo_anulacion'] ?? '')),
                    ExcelExportSeguro::texto((string) ($fila['observacion'] ?? '')),
                ]));
            }
        }

        $writer->close();
        $contenido = file_get_contents($path);
        @unlink($path);
        if ($contenido === false) {
            throw new RuntimeException('No se pudo leer el Excel generado.');
        }

        return $contenido;
    }
}
