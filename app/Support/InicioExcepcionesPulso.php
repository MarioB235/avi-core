<?php

namespace App\Support;

/**
 * Lista accionable de excepciones en Inicio admin (RES-09): mortalidad y capturas pendientes primero.
 */
final class InicioExcepcionesPulso
{
    /**
     * @param  list<array{galpon_id: int, nombre: string, granja: string, mortalidad_pct: float}>  $alertas
     * @param  list<array{id: int, nombre: string, granja: string}>  $galponesSinCarga
     * @return list<array{
     *     tipo: string,
     *     prioridad: int,
     *     titulo: string,
     *     detalle: string,
     *     accion_label: string,
     *     accion_url: string,
     *     galpon_id: int
     * }>
     */
    public static function construir(array $alertas, array $galponesSinCarga, string $resumenRouteName): array
    {
        $items = [];

        foreach ($alertas as $alerta) {
            $items[] = [
                'tipo' => 'mortalidad_referencia',
                'prioridad' => 1,
                'titulo' => $alerta['nombre'].' — mortalidad acumulada sobre referencia',
                'detalle' => $alerta['granja'].' · '.number_format($alerta['mortalidad_pct'], 1, ',', '.').'% acumulado',
                'accion_label' => 'Ver galpón en Resumen',
                'accion_url' => route($resumenRouteName, ['galpon' => $alerta['galpon_id']]),
                'galpon_id' => $alerta['galpon_id'],
            ];
        }

        foreach ($galponesSinCarga as $galpon) {
            $items[] = [
                'tipo' => 'captura_pendiente',
                'prioridad' => 2,
                'titulo' => $galpon['nombre'].' — faltan capturas de hoy',
                'detalle' => $galpon['granja'].' · huevos, muertes o descarte sin cerrar',
                'accion_label' => 'Ver galpón en Resumen',
                'accion_url' => route($resumenRouteName, ['galpon' => $galpon['id']]),
                'galpon_id' => $galpon['id'],
            ];
        }

        usort($items, fn (array $a, array $b): int => $a['prioridad'] <=> $b['prioridad']);

        return $items;
    }
}
