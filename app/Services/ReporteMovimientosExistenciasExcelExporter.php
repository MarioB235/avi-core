<?php

namespace App\Services;

use App\Support\ExcelExportSeguro;
use App\Support\ReporteExportGuard;
use App\Support\ReporteFiltroProduccion;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use RuntimeException;

/**
 * Excel «movimientos y existencias» desde {@see ReporteConsultaService} (REP-05).
 */
class ReporteMovimientosExistenciasExcelExporter
{
    public function __construct(
        private ReporteConsultaService $consulta,
    ) {}

    public function nombreArchivo(ReporteFiltroProduccion $filtro): string
    {
        $desde = $filtro->fechaDesde->format('Y-m-d');
        $hasta = $filtro->fechaHasta->format('Y-m-d');

        return $desde === $hasta
            ? "movimientos-existencias_{$desde}.xlsx"
            : "movimientos-existencias_{$desde}_{$hasta}.xlsx";
    }

    public function generar(ReporteFiltroProduccion $filtro): string
    {
        $datos = $this->consulta->movimientosExistencias($filtro);
        ReporteExportGuard::assertDescargable($datos);

        $temp = tempnam(sys_get_temp_dir(), 'avicore_rep_mov_');

        if ($temp === false) {
            throw new RuntimeException('No se pudo crear archivo temporal para el Excel.');
        }

        $path = $temp.'.xlsx';
        @unlink($temp);

        $writer = new Writer;
        $writer->openToFile($path);

        $empresa = $filtro->usuario->empresa;
        $empresaNombre = ExcelExportSeguro::texto($empresa?->nombre ?? 'Empresa');

        $writer->addRow(Row::fromValues(['AviCore — Movimientos y existencias de aves']));
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

        if (ReporteExportGuard::sinDatosEnFilas($datos, $datos['bloques_galpon'] === [])) {
            $writer->addRow(Row::fromValues([
                ExcelExportSeguro::texto(ReporteExportGuard::filaMensajeSinDatos($datos)),
            ]));
        } else {
            foreach ($datos['bloques_galpon'] as $bloque) {
                $writer->addRow(Row::fromValues([
                    'Galpón',
                    ExcelExportSeguro::texto($bloque['galpon_nombre']),
                    'Granja',
                    ExcelExportSeguro::texto($bloque['granja_nombre']),
                ]));

                $c = $bloque['conciliacion'];
                $writer->addRow(Row::fromValues([
                    'Inicial',
                    'Entradas',
                    'Salidas',
                    'Muertes',
                    'Descartes',
                    'Ajustes',
                    'Reversiones',
                    'Saldo esperado',
                    'Aves actuales',
                    'Diferencia',
                    'Cuadra',
                ]));
                $writer->addRow(Row::fromValues([
                    $c['inicial'],
                    $c['entradas'],
                    $c['salidas'],
                    $c['muertes'],
                    $c['descartes'],
                    $c['ajustes'],
                    $c['reversiones_registradas'],
                    $c['saldo_esperado'],
                    $c['aves_actuales'],
                    $c['diferencia'],
                    $c['cuadra'] ? 'Sí' : 'No',
                ]));
                $writer->addRow(Row::fromValues([]));

                $writer->addRow(Row::fromValues([
                    'ID',
                    'Fecha',
                    'Tipo',
                    'Reversión',
                    'Ref. reversión',
                    'Cantidad',
                    'Delta ajuste',
                    'Origen',
                    'Destino',
                    'Lote',
                    'Efecto en galpón',
                ]));

                if ($bloque['movimientos'] === []) {
                    $writer->addRow(Row::fromValues(['Sin movimientos en el período.']));
                } else {
                    foreach ($bloque['movimientos'] as $mov) {
                        $writer->addRow(Row::fromValues([
                            $mov['id'],
                            ExcelExportSeguro::texto($mov['fecha']),
                            ExcelExportSeguro::texto($mov['tipo']),
                            $mov['es_reversion'] ? 'Sí' : 'No',
                            $mov['reversa_de_id'] ?? '',
                            $mov['cantidad'],
                            $mov['ajuste_delta'] ?? '',
                            ExcelExportSeguro::texto($mov['origen'] ?? ''),
                            ExcelExportSeguro::texto($mov['destino'] ?? ''),
                            ExcelExportSeguro::texto($mov['lote'] ?? ''),
                            ExcelExportSeguro::texto($mov['efecto_resumen']),
                        ]));
                    }
                }

                $writer->addRow(Row::fromValues([]));
            }
        }

        $writer->close();

        $contenido = file_get_contents($path);

        if ($contenido === false) {
            throw new RuntimeException('No se pudo leer el Excel generado.');
        }

        @unlink($path);

        return $contenido;
    }

    private function etiquetaFiltros(ReporteFiltroProduccion $filtro): string
    {
        $partes = ['Empresa activa'];

        if ($filtro->granjaId !== null) {
            $partes[] = 'granja #'.$filtro->granjaId;
        }

        if ($filtro->galponId !== null) {
            $partes[] = 'galpón #'.$filtro->galponId;
        }

        return implode(' · ', $partes);
    }
}
