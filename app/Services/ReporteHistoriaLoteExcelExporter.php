<?php

namespace App\Services;

use App\Support\ExcelExportSeguro;
use App\Support\ReporteExportGuard;
use App\Support\ReporteFiltroLote;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use RuntimeException;

class ReporteHistoriaLoteExcelExporter
{
    public function __construct(private ReporteConsultaService $consulta) {}

    public function nombreArchivo(ReporteFiltroLote $filtro): string
    {
        return 'historia-lote_'.$filtro->loteId.'_'.$filtro->fechaDesde->format('Y-m-d').'.xlsx';
    }

    public function generar(ReporteFiltroLote $filtro): string
    {
        $datos = $this->consulta->historiaLote($filtro);
        ReporteExportGuard::assertDescargable($datos);
        $path = $this->rutaTemporal();

        $writer = new Writer;
        $writer->openToFile($path);

        $empresa = ExcelExportSeguro::texto($filtro->usuario->empresa?->nombre ?? 'Empresa');
        $writer->addRow(Row::fromValues(['AviCore — Historia de lote']));
        $writer->addRow(Row::fromValues(['Empresa', $empresa]));
        $writer->addRow(Row::fromValues([
            'Período',
            $filtro->fechaDesde->format('d/m/Y').' — '.$filtro->fechaHasta->format('d/m/Y'),
        ]));
        $writer->addRow(Row::fromValues(['Generado', now()->format('d/m/Y H:i')]));
        $writer->addRow(Row::fromValues([]));

        if ($datos['ficha'] === []) {
            $writer->addRow(Row::fromValues([
                ExcelExportSeguro::texto(ReporteExportGuard::filaMensajeSinDatos($datos)),
            ]));
        } else {
            $lote = $datos['ficha']['lote'];
            $writer->addRow(Row::fromValues(['Lote', ExcelExportSeguro::texto($lote->codigo)]));
            $writer->addRow(Row::fromValues(['Estado', ExcelExportSeguro::texto($lote->estado->label())]));
            $writer->addRow(Row::fromValues(['Nota saldo', ExcelExportSeguro::texto($datos['ficha']['saldo_nota'])]));
            $writer->addRow(Row::fromValues([]));

            $prod = $datos['produccion_periodo'];
            if ($prod['atribuible']) {
                $writer->addRow(Row::fromValues([
                    'Huevos aptos en período (galpón único activo)',
                    $prod['huevos_aptos'],
                ]));
            } else {
                $writer->addRow(Row::fromValues([
                    'Producción en período',
                    ExcelExportSeguro::texto((string) $prod['aviso']),
                ]));
            }

            $writer->addRow(Row::fromValues([]));
            $writer->addRow(Row::fromValues(['Ubicaciones del expediente']));
            $writer->addRow(Row::fromValues(['Galpón', 'Desde', 'Hasta']));
            foreach ($datos['ubicaciones'] as $u) {
                $writer->addRow(Row::fromValues([
                    ExcelExportSeguro::texto($u['galpon_nombre']),
                    $u['desde'],
                    $u['hasta'] ?? 'actual',
                ]));
            }

            $writer->addRow(Row::fromValues([]));
            $writer->addRow(Row::fromValues(['Movimientos del lote']));
            $writer->addRow(Row::fromValues(['Fecha', 'Tipo', 'Reversión', 'Cantidad', 'Origen', 'Destino', 'Motivo']));
            foreach ($datos['movimientos'] as $mov) {
                $writer->addRow(Row::fromValues([
                    ExcelExportSeguro::texto($mov['fecha']),
                    ExcelExportSeguro::texto($mov['tipo']),
                    $mov['es_reversion'] ? 'Sí' : 'No',
                    $mov['cantidad'],
                    ExcelExportSeguro::texto($mov['origen'] ?? ''),
                    ExcelExportSeguro::texto($mov['destino'] ?? ''),
                    ExcelExportSeguro::texto($mov['motivo']),
                ]));
            }

            $writer->addRow(Row::fromValues([]));
            $writer->addRow(Row::fromValues(['Vacunaciones del lote']));
            $writer->addRow(Row::fromValues(['Fecha', 'Vacuna', 'Operario', 'Estado']));
            foreach ($datos['vacunaciones'] as $vac) {
                $writer->addRow(Row::fromValues([
                    ExcelExportSeguro::texto($vac['fecha']),
                    ExcelExportSeguro::texto($vac['vacuna']),
                    ExcelExportSeguro::texto($vac['operario']),
                    ExcelExportSeguro::texto($vac['estado']),
                ]));
            }
        }

        $writer->close();

        return $this->leerYBorrar($path);
    }

    private function rutaTemporal(): string
    {
        $temp = tempnam(sys_get_temp_dir(), 'avicore_rep_hl_');
        if ($temp === false) {
            throw new RuntimeException('No se pudo crear archivo temporal.');
        }
        $path = $temp.'.xlsx';
        @unlink($temp);

        return $path;
    }

    private function leerYBorrar(string $path): string
    {
        $contenido = file_get_contents($path);
        @unlink($path);
        if ($contenido === false) {
            throw new RuntimeException('No se pudo leer el Excel generado.');
        }

        return $contenido;
    }
}
