<?php

namespace App\Support;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\Concerns\MapsNavTabsToTabBar;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AdminNav
{
    use MapsNavTabsToTabBar;

    /**
     * Tabs del panel de gestión del rol autenticado (sin módulos de carga en campo).
     *
     * @return list<array{route: string, patterns: list<string>, label: string, icon: string, headerTitle: string}>
     */
    public static function tabs(?User $user = null): array
    {
        $user ??= Auth::user();

        if ($user === null || ! $user->rol->usesAdminPanel() || $user->rol === UserRole::Reparto) {
            return [];
        }

        $prefix = $user->rol->routePrefix();
        $route = fn (string $name): string => "{$prefix}.{$name}";
        $patterns = fn (string $name): array => ["{$prefix}.{$name}"];

        $tabs = [
            [
                'route' => $route('home'),
                'patterns' => $patterns('home'),
                'label' => 'Inicio',
                'icon' => 'home',
                'headerTitle' => 'Inicio',
            ],
        ];

        if ($user->rol->canViewResumen()) {
            $tabs[] = [
                'route' => $route('resumen.index'),
                'patterns' => ["{$prefix}.resumen.*"],
                'label' => 'Resumen',
                'icon' => 'chart',
                'headerTitle' => 'Resumen',
            ];
        }

        if ($user->rol->canViewEquipo()) {
            $tabs[] = [
                'route' => $route('equipo.index'),
                'patterns' => ["{$prefix}.equipo.*"],
                'label' => 'Equipo',
                'icon' => 'users',
                'headerTitle' => 'Equipo',
            ];
        }

        if ($user->rol->canViewComercial()) {
            $tabs[] = [
                'route' => $route('comercial.index'),
                'patterns' => ["{$prefix}.comercial.*"],
                'label' => 'Comercial',
                'icon' => 'truck',
                'headerTitle' => 'Comercial',
            ];
        }

        if ($user->rol->canViewEstructura()) {
            $tabs[] = [
                'route' => $route('estructura.index'),
                'patterns' => ["{$prefix}.estructura.*"],
                'label' => 'Estructura',
                'icon' => 'layers',
                'headerTitle' => 'Estructura',
            ];
        }

        if ($user->rol->canViewUsers()) {
            $tabs[] = [
                'route' => $route('usuarios.index'),
                'patterns' => ["{$prefix}.usuarios.*"],
                'label' => 'Usuarios',
                'icon' => 'users',
                'headerTitle' => 'Usuarios',
            ];
        }

        return $tabs;
    }

    public static function headerTitle(?User $user = null): string
    {
        if (Request::routeIs('profile.edit')) {
            return 'Mi perfil';
        }

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
        if (Request::routeIs('profile.edit')) {
            return true;
        }

        $name = Request::route()?->getName();

        if ($name === null) {
            return false;
        }

        return str_ends_with($name, '.home')
            || str_contains($name, '.resumen.')
            || str_contains($name, '.equipo.')
            || str_contains($name, '.comercial.')
            || str_contains($name, '.usuarios.')
            || str_contains($name, '.estructura.');
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
