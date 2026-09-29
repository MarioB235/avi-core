<?php

namespace App\Support;

use App\Models\User;

/**
 * Presentación del módulo Equipo (Dueño, solo lectura — RES-10).
 */
final class EquipoLectura
{
    public const AVISO_SIN_PRODUCTIVIDAD = 'Listado de roles y acceso. No incluye ranking ni productividad laboral.';

    public static function fila(User $viewer, User $miembro, string $segmento): array
    {
        return [
            'id' => $miembro->id,
            'nombre' => $miembro->name,
            'rol_label' => $miembro->rol->label(),
            'segment' => $segmento,
            'segment_label' => self::etiquetaSegmento($segmento),
            'documento' => DatosPersonales::documentoParaVista($viewer, $miembro),
            'estado_acceso' => $miembro->must_change_password ? 'pendiente_clave' : 'activo',
            'estado_label' => $miembro->must_change_password
                ? 'Pendiente cambio de clave'
                : 'Activo',
        ];
    }

    public static function etiquetaSegmento(string $segmento): string
    {
        return match ($segmento) {
            'campo' => 'Campo',
            'supervision' => 'Supervisión',
            'oficina' => 'Oficina',
            default => 'Equipo',
        };
    }
}
