<?php

namespace App\Exceptions;

use Exception;

/**
 * La consulta no puede exportarse (permiso, parámetro o alcance inválido) — REP-07.
 */
class ReporteConsultaNoDisponibleException extends Exception
{
    public function __construct(string $mensaje = 'No se pudo generar el reporte con los filtros indicados.')
    {
        parent::__construct($mensaje);
    }
}
