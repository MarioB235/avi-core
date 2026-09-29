<?php

namespace App\Support;

use App\Enums\RegistroOperativoTipo;

/**
 * Completitud diaria productiva (D03): huevos, muertes y descarte deben quedar
 * registrados o con cero confirmado. Alimento no cuenta para «día completo».
 */
final class CompletitudDiariaD03
{
    /**
     * @param  array{huevos_estado_hoy?: string, muertes_estado_hoy?: string, descarte_estado_hoy?: string}  $resumenGalpon
     */
    public static function tieneOmisionProductiva(array $resumenGalpon): bool
    {
        foreach (self::clavesEstadoHoy() as $clave) {
            if (($resumenGalpon[$clave] ?? CapturaCeroEstado::OMISION) === CapturaCeroEstado::OMISION) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array{huevos_estado_hoy?: string, muertes_estado_hoy?: string, descarte_estado_hoy?: string}  $resumenGalpon
     * @return list<RegistroOperativoTipo>
     */
    public static function tiposEnOmision(array $resumenGalpon): array
    {
        $tipos = [];

        if (($resumenGalpon['huevos_estado_hoy'] ?? CapturaCeroEstado::OMISION) === CapturaCeroEstado::OMISION) {
            $tipos[] = RegistroOperativoTipo::Huevos;
        }

        if (($resumenGalpon['muertes_estado_hoy'] ?? CapturaCeroEstado::OMISION) === CapturaCeroEstado::OMISION) {
            $tipos[] = RegistroOperativoTipo::Muertes;
        }

        if (($resumenGalpon['descarte_estado_hoy'] ?? CapturaCeroEstado::OMISION) === CapturaCeroEstado::OMISION) {
            $tipos[] = RegistroOperativoTipo::Descarte;
        }

        return $tipos;
    }

    /**
     * @return list<string>
     */
    private static function clavesEstadoHoy(): array
    {
        return [
            'huevos_estado_hoy',
            'muertes_estado_hoy',
            'descarte_estado_hoy',
        ];
    }
}
