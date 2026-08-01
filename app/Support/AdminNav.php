<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AdminNav
{
    /**
     * Tabs del panel de gestión (sin módulos de carga en campo).
     *
     * @return list<array{route: string, patterns: list<string>, label: string, icon: string, headerTitle: string}>
     */
    public static function tabs(?User $user = null): array
    {
        // Solo gestión de empresa. La carga en campo vive en `/operario` (módulo aparte).
        return [
            [
                'route' => 'admin.home',
                'patterns' => ['admin.home'],
                'label' => 'Inicio',
                'icon' => 'home',
                'headerTitle' => 'Inicio',
            ],
            [
                'route' => 'admin.usuarios.index',
                'patterns' => ['admin.usuarios.*'],
                'label' => 'Usuarios',
                'icon' => 'users',
                'headerTitle' => 'Usuarios',
            ],
        ];
    }

    public static function headerTitle(?User $user = null): string
    {
        foreach (self::tabs($user) as $tab) {
            if (self::tabIsActive($tab)) {
                return $tab['headerTitle'];
            }
        }

        return 'Panel';
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

    public static function isHeroPage(): bool
    {
        return Request::routeIs('admin.home', 'admin.usuarios.*');
    }

    public static function sidebarSubtitle(?User $user = null): string
    {
        $user ??= Auth::user();

        if ($user?->isAdminAvicore()) {
            return 'Administración AviCore';
        }

        return $user?->empresa?->nombre ?? 'Gestión de empresa';
    }

    public static function roleBadge(?User $user = null): string
    {
        $user ??= Auth::user();

        return $user?->rol->label() ?? 'Panel';
    }
}
