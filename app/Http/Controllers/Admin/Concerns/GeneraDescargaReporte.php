<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Exceptions\ReporteConsultaNoDisponibleException;
use Symfony\Component\HttpFoundation\Response;

trait GeneraDescargaReporte
{
    protected function respuestaExcel(string $contenido, string $nombre): Response
    {
        return response($contenido, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$nombre.'"',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    protected function respuestaPdf(string $contenido, string $nombre): Response
    {
        return response($contenido, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$nombre.'"',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    /**
     * @param  callable(): string  $generar
     */
    protected function descargarExcel(callable $generar, string $nombre): Response
    {
        try {
            return $this->respuestaExcel($generar(), $nombre);
        } catch (ReporteConsultaNoDisponibleException $e) {
            abort(422, $e->getMessage());
        }
    }

    /**
     * @param  callable(): string  $generar
     */
    protected function descargarPdf(callable $generar, string $nombre): Response
    {
        try {
            return $this->respuestaPdf($generar(), $nombre);
        } catch (ReporteConsultaNoDisponibleException $e) {
            abort(422, $e->getMessage());
        }
    }
}
