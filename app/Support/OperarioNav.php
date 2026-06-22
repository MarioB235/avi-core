<?php

namespace App\Support;

use Illuminate\Support\Facades\Request;

class OperarioNav
{
    /**
     * @return list<array{route: string, patterns: list<string>, label: string, icon: string, headerTitle: string}>
     */
    public static function tabs(): array
    {
        return [
            [
                'route' => 'operario.home',
                'patterns' => ['operario.home'],
                'label' => 'Inicio',
                'icon' => 'home',
                'headerTitle' => 'Inicio',
            ],
            [
                'route' => 'operario.cargar',
                'patterns' => ['operario.cargar', 'operario.carga.*'],
                'label' => 'Cargar',
                'icon' => 'plus',
                'headerTitle' => 'Cargar',
            ],
            [
                'route' => 'operario.historial',
                'patterns' => ['operario.historial'],
                'label' => 'Historial',
                'icon' => 'clock',
                'headerTitle' => 'Historial',
            ],
        ];
    }

    public static function headerTitle(): string
    {
        foreach (self::tabs() as $tab) {
            if (self::tabIsActive($tab)) {
                return $tab['headerTitle'];
            }
        }

        return 'Operario';
    }

    /**
     * @param  array{patterns: list<string>}  $tab
     */
    public static function tabIsActive(array $tab): bool
    {
        foreach ($tab['patterns'] as $pattern) {
            if (Request::routeIs($pattern)) {
                return true;
            }
        }

        return false;
    }
}
