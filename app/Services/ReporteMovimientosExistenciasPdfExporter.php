<?php

namespace App\Services;

use App\Models\Empresa;
use App\Support\AvicoreReporteFpdf;
use App\Support\PdfTexto;
use App\Support\ReporteExportGuard;
use App\Support\ReporteFiltroProduccion;
use Illuminate\Support\Facades\Storage;

/**
 * PDF A4 «movimientos y existencias» (REP-05).
 */
class ReporteMovimientosExistenciasPdfExporter
{
    public function __construct(
        private ReporteConsultaService $consulta,
    ) {}

    public function nombreArchivo(ReporteFiltroProduccion $filtro): string
    {
        $desde = $filtro->fechaDesde->format('Y-m-d');
        $hasta = $filtro->fechaHasta->format('Y-m-d');

        return $desde === $hasta
            ? "movimientos-existencias_{$desde}.pdf"
            : "movimientos-existencias_{$desde}_{$hasta}.pdf";
    }

    public function generar(ReporteFiltroProduccion $filtro): string
    {
        $datos = $this->consulta->movimientosExistencias($filtro);
        ReporteExportGuard::assertDescargable($datos);
        $filtro->usuario->loadMissing('empresa');
        $empresa = $filtro->usuario->empresa;

        $pdf = new AvicoreReporteFpdf('P', 'mm', 'A4');
        $pdf->AliasNbPages();
        $pdf->SetAutoPageBreak(true, 18);
        $pdf->tituloReporte = 'Movimientos y existencias';
        $pdf->lineaEmpresa = $this->lineaEmpresa($empresa);
        $pdf->logoAbsoluto = $this->rutaLogoAbsoluta($empresa?->logo_path);

        $pdf->AddPage();
        $pdf->SetFont('Helvetica', '', 8);

        $pdf->Cell(0, 4, PdfTexto::latin1('Período: '.$filtro->fechaDesde->format('d/m/Y').' — '.$filtro->fechaHasta->format('d/m/Y')), 0, 1);
        $pdf->Cell(0, 4, PdfTexto::latin1('Generado: '.now()->format('d/m/Y H:i')), 0, 1);
        $pdf->Ln(2);

        if (ReporteExportGuard::sinDatosEnFilas($datos, $datos['bloques_galpon'] === [])) {
            $pdf->Cell(0, 5, PdfTexto::latin1(ReporteExportGuard::filaMensajeSinDatos($datos)), 0, 1);

            return $pdf->Output('S');
        }

        foreach ($datos['bloques_galpon'] as $bloque) {
            $pdf->SetFont('Helvetica', 'B', 9);
            $tituloBloque = $bloque['galpon_nombre'].' — '.$bloque['granja_nombre'];
            $pdf->Cell(0, 5, PdfTexto::usuario($tituloBloque, 120), 0, 1);
            $pdf->SetFont('Helvetica', '', 8);

            $c = $bloque['conciliacion'];
            $lineaConc = sprintf(
                'Inicial %d | +Ent %d | -Sal %d | Muertes %d | Desc %d | Aj %d | Rev %d | Esperado %d | Actual %d | %s',
                $c['inicial'],
                $c['entradas'],
                $c['salidas'],
                $c['muertes'],
                $c['descartes'],
                $c['ajustes'],
                $c['reversiones_registradas'],
                $c['saldo_esperado'],
                $c['aves_actuales'],
                $c['cuadra'] ? 'CUADRA' : 'DIF '.$c['diferencia'],
            );
            $pdf->MultiCell(0, 4, PdfTexto::latin1($lineaConc), 0, 'L');
            $pdf->Ln(1);

            $pdf->SetFont('Helvetica', 'B', 7);
            $pdf->Cell(12, 4, PdfTexto::latin1('Fecha'), 1);
            $pdf->Cell(22, 4, PdfTexto::latin1('Tipo'), 1);
            $pdf->Cell(8, 4, PdfTexto::latin1('Rev'), 1);
            $pdf->Cell(12, 4, PdfTexto::latin1('Cant'), 1);
            $pdf->Cell(0, 4, PdfTexto::latin1('Efecto'), 1, 1);
            $pdf->SetFont('Helvetica', '', 7);

            if ($bloque['movimientos'] === []) {
                $pdf->Cell(0, 4, PdfTexto::latin1('Sin movimientos en el período.'), 1, 1);
            } else {
                foreach ($bloque['movimientos'] as $mov) {
                    $fechaCorta = strlen($mov['fecha']) >= 10 ? substr($mov['fecha'], 0, 10) : $mov['fecha'];
                    $pdf->Cell(12, 4, PdfTexto::latin1($fechaCorta), 1);
                    $pdf->Cell(22, 4, PdfTexto::usuario($mov['tipo'], 14), 1);
                    $pdf->Cell(8, 4, PdfTexto::latin1($mov['es_reversion'] ? 'Sí' : ''), 1);
                    $pdf->Cell(12, 4, PdfTexto::latin1((string) $mov['cantidad']), 1);
                    $pdf->Cell(0, 4, PdfTexto::usuario($mov['efecto_resumen'], 60), 1, 1);
                }
            }

            $pdf->Ln(3);
        }

        return $pdf->Output('S');
    }

    private function lineaEmpresa(?Empresa $empresa): string
    {
        if ($empresa === null) {
            return '';
        }

        return $empresa->nombre;
    }

    private function rutaLogoAbsoluta(?string $logoPath): ?string
    {
        if ($logoPath === null || $logoPath === '') {
            return null;
        }

        if (! Storage::disk('public')->exists($logoPath)) {
            return null;
        }

        $absoluta = Storage::disk('public')->path($logoPath);
        $ext = strtolower(pathinfo($absoluta, PATHINFO_EXTENSION));

        if (! in_array($ext, ['png', 'jpg', 'jpeg'], true)) {
            return null;
        }

        return $absoluta;
    }
}
