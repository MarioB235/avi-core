<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\Granja;
use App\Support\AvicoreReporteFpdf;
use App\Support\PdfTexto;
use App\Support\ReporteExportGuard;
use App\Support\ReporteFiltroProduccion;
use Illuminate\Support\Facades\Storage;

/**
 * PDF A4 «producción diaria» desde {@see ReporteConsultaService} (REP-04).
 */
class ReporteProduccionDiariaPdfExporter
{
    public function __construct(
        private ReporteConsultaService $consulta,
    ) {}

    public function nombreArchivo(ReporteFiltroProduccion $filtro): string
    {
        $desde = $filtro->fechaDesde->format('Y-m-d');
        $hasta = $filtro->fechaHasta->format('Y-m-d');

        return $desde === $hasta
            ? "produccion-diaria_{$desde}.pdf"
            : "produccion-diaria_{$desde}_{$hasta}.pdf";
    }

    public function generar(ReporteFiltroProduccion $filtro): string
    {
        $datos = $this->consulta->produccionDiaria($filtro);
        ReporteExportGuard::assertDescargable($datos);
        $filtro->usuario->loadMissing('empresa');
        $empresa = $filtro->usuario->empresa;

        $pdf = new AvicoreReporteFpdf('P', 'mm', 'A4');
        $pdf->AliasNbPages();
        $pdf->SetAutoPageBreak(true, 18);
        $pdf->tituloReporte = 'Producción diaria';
        $pdf->lineaEmpresa = $this->lineaEmpresa($filtro, $empresa);
        $pdf->logoAbsoluto = $this->rutaLogoAbsoluta($empresa?->logo_path);

        $pdf->AddPage();
        $pdf->SetFont('Helvetica', '', 9);

        $pdf->Cell(0, 5, PdfTexto::latin1('Período: '.$filtro->fechaDesde->format('d/m/Y').' — '.$filtro->fechaHasta->format('d/m/Y')), 0, 1);
        $pdf->Cell(0, 5, PdfTexto::latin1('Filtros: '.$this->etiquetaFiltros($filtro)), 0, 1);
        $pdf->Cell(0, 5, PdfTexto::latin1('Generado: '.now()->format('d/m/Y H:i')), 0, 1);
        $pdf->Ln(3);

        $anchos = [28, 28, 28, 22, 28, 30];
        $this->filaTabla($pdf, $anchos, [
            'Día',
            'Huevos aptos',
            'Huevos desc.',
            'Muertes',
            'Desc. aves',
            PdfTexto::usuario($datos['alimento_etiqueta'], 40),
        ], true);

        if (ReporteExportGuard::sinDatosEnFilas($datos, $datos['filas_dia'] === [])) {
            $this->filaTabla($pdf, [190], [ReporteExportGuard::filaMensajeSinDatos($datos)], false);
        } else {
            foreach ($datos['filas_dia'] as $fila) {
                $t = $fila['totales'];
                $this->filaTabla($pdf, $anchos, [
                    $fila['etiqueta'],
                    $this->num($t['huevos']),
                    $this->num($t['huevos_descarte']),
                    $this->num($t['muertes']),
                    $this->num($t['descarte_aves']),
                    $this->numKg($t['alimento_kg']),
                ], false);
            }

            $total = $datos['totales_periodo'];
            $this->filaTabla($pdf, $anchos, [
                'Total período',
                $this->num($total['huevos']),
                $this->num($total['huevos_descarte']),
                $this->num($total['muertes']),
                $this->num($total['descarte_aves']),
                $this->numKg($total['alimento_kg']),
            ], true);
        }

        return $pdf->Output('S');
    }

    /**
     * @param  list<float>  $anchos
     * @param  list<string>  $celdas
     */
    private function filaTabla(AvicoreReporteFpdf $pdf, array $anchos, array $celdas, bool $negrita): void
    {
        $pdf->SetFont('Helvetica', $negrita ? 'B' : '', 8);

        foreach ($celdas as $i => $texto) {
            $ancho = $anchos[$i] ?? ($anchos[0] ?? 190);
            $pdf->Cell($ancho, 6, PdfTexto::usuario($texto, 80), 1, 0, $i === 0 ? 'L' : 'R');
        }

        $pdf->Ln();
    }

    private function lineaEmpresa(ReporteFiltroProduccion $filtro, ?Empresa $empresa): string
    {
        $nombre = $empresa?->nombre ?? 'Empresa';
        $dicose = '';

        if ($filtro->granjaId !== null) {
            $granja = Granja::query()->find($filtro->granjaId);
            if ($granja?->dicose) {
                $dicose = ' · DICOSE '.$granja->dicose;
            }
        }

        return $nombre.$dicose;
    }

    private function etiquetaFiltros(ReporteFiltroProduccion $filtro): string
    {
        return 'Granja: '.($filtro->granjaId ?? 'todas').' · Galpón: '.($filtro->galponId ?? 'todos');
    }

    private function rutaLogoAbsoluta(?string $logoPath): ?string
    {
        if ($logoPath === null || $logoPath === '') {
            return null;
        }

        if (! Storage::disk('public')->exists($logoPath)) {
            return null;
        }

        $path = Storage::disk('public')->path($logoPath);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (! in_array($extension, ['png', 'jpg', 'jpeg'], true)) {
            return null;
        }

        return $path;
    }

    private function num(int $valor): string
    {
        return number_format($valor, 0, ',', '.');
    }

    private function numKg(float $valor): string
    {
        return number_format($valor, 2, ',', '.');
    }
}
