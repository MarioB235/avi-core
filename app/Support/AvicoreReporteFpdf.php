<?php

namespace App\Support;

use FPDF;

/**
 * FPDF con cabecera/pie AviCore para reportes operativos (REP-04).
 */
class AvicoreReporteFpdf extends FPDF
{
    public string $tituloReporte = '';

    public string $lineaEmpresa = '';

    public ?string $logoAbsoluto = null;

    public function Header(): void
    {
        $yInicio = 10;

        if ($this->logoAbsoluto !== null && is_file($this->logoAbsoluto)) {
            $this->Image($this->logoAbsoluto, 10, $yInicio, 28);
            $this->SetX(42);
        } else {
            $this->SetX(10);
        }

        $this->SetFont('Helvetica', 'B', 12);
        $this->Cell(0, 6, PdfTexto::latin1($this->tituloReporte), 0, 1);

        if ($this->lineaEmpresa !== '') {
            $this->SetX($this->logoAbsoluto !== null ? 42 : 10);
            $this->SetFont('Helvetica', '', 9);
            $this->Cell(0, 5, PdfTexto::usuario($this->lineaEmpresa, 120), 0, 1);
        }

        $this->Ln(2);
    }

    public function Footer(): void
    {
        $this->SetY(-12);
        $this->SetFont('Helvetica', 'I', 8);
        $this->Cell(95, 8, PdfTexto::latin1('AviCore — reporte operativo'), 0, 0, 'L');
        $this->Cell(95, 8, PdfTexto::latin1('Página '.$this->PageNo().'/{nb}'), 0, 0, 'R');
    }
}
