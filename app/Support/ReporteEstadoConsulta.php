<?php

namespace App\Support;

/**
 * Resultado de consulta previa a export (REP-07).
 */
enum ReporteEstadoConsulta: string
{
    case Ok = 'ok';
    case SinDatos = 'sin_datos';
    case NoDisponible = 'no_disponible';
}
