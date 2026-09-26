<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\User;
use Illuminate\Support\Facades\Route;

class EmpresaOnboardingService
{
    /**
     * Checklist de puesta en marcha operativa (EMP-05).
     *
     * @return array{
     *     show: bool,
     *     title: string,
     *     subtitle: string,
     *     pending_count: int,
     *     total_count: int,
     *     items: list<array{
     *         key: string,
     *         label: string,
     *         description: string,
     *         icon: string,
     *         status: string,
     *         href: ?string
     *     }>
     * }
     */
    public function panelFor(User $user): array
    {
        if (! $this->shouldShow($user)) {
            return $this->emptyPanel();
        }

        $empresa = $user->empresa;
        if ($empresa === null) {
            return $this->emptyPanel();
        }

        $items = $this->buildItems($user, $empresa);
        $pendingCount = count(array_filter($items, fn (array $item): bool => $item['status'] === 'Pendiente'));

        if ($pendingCount === 0) {
            return $this->emptyPanel();
        }

        return [
            'show' => true,
            'title' => 'Primeros pasos',
            'subtitle' => 'Completá estos pasos para dejar la empresa lista para operar en campo.',
            'pending_count' => $pendingCount,
            'total_count' => count($items),
            'items' => $items,
        ];
    }

    public function isCompleteForEmpresa(Empresa $empresa): bool
    {
        return $this->hasAdministrador($empresa)
            && $this->hasGranjaActiva($empresa)
            && $this->hasGalponActivo($empresa)
            && $this->hasSaldoInicial($empresa)
            && $this->hasOperarioActivo($empresa);
    }

    private function shouldShow(User $user): bool
    {
        if ($user->empresa_id === null) {
            return false;
        }

        return match ($user->rol) {
            UserRole::Dueno, UserRole::Administrativo, UserRole::Encargado => true,
            default => false,
        };
    }

    /**
     * @return list<array{
     *     key: string,
     *     label: string,
     *     description: string,
     *     icon: string,
     *     status: string,
     *     href: ?string
     * }>
     */
    private function buildItems(User $user, Empresa $empresa): array
    {
        return [
            $this->empresaItem($empresa),
            $this->administradorItem($user, $empresa),
            $this->granjaItem($user, $empresa),
            $this->galponItem($user, $empresa),
            $this->loteItem($user, $empresa),
            $this->operarioItem($user, $empresa),
        ];
    }

    /**
     * @return array{key: string, label: string, description: string, icon: string, status: string, href: ?string}
     */
    private function empresaItem(Empresa $empresa): array
    {
        $lista = $empresa->permiteLogin();

        return [
            'key' => 'empresa',
            'label' => 'Empresa activa',
            'description' => $lista
                ? 'Tu empresa está habilitada para operar.'
                : 'La empresa debe estar activa para que el equipo pueda ingresar.',
            'icon' => 'building',
            'status' => $lista ? 'Listo' : 'Pendiente',
            'href' => null,
        ];
    }

    /**
     * @return array{key: string, label: string, description: string, icon: string, status: string, href: ?string}
     */
    private function administradorItem(User $user, Empresa $empresa): array
    {
        $listo = $this->hasAdministrador($empresa);

        return [
            'key' => 'administrador',
            'label' => 'Administrador',
            'description' => $listo
                ? 'Hay al menos un dueño o administrativo activo.'
                : 'Definí quién administra la empresa (dueño o administrativo).',
            'icon' => 'users',
            'status' => $listo ? 'Listo' : 'Pendiente',
            'href' => $listo ? null : $this->usuariosRoute($user),
        ];
    }

    /**
     * @return array{key: string, label: string, description: string, icon: string, status: string, href: ?string}
     */
    private function granjaItem(User $user, Empresa $empresa): array
    {
        $listo = $this->hasGranjaActiva($empresa);

        return [
            'key' => 'granja',
            'label' => 'Granja',
            'description' => $listo
                ? 'Hay al menos una granja activa registrada.'
                : ($user->rol->canManageEstructura()
                    ? 'Registrá la primera granja en Estructura.'
                    : 'Pedile al administrativo que registre la primera granja.'),
            'icon' => 'layers',
            'status' => $listo ? 'Listo' : 'Pendiente',
            'href' => $listo ? null : $this->estructuraRoute($user),
        ];
    }

    /**
     * @return array{key: string, label: string, description: string, icon: string, status: string, href: ?string}
     */
    private function galponItem(User $user, Empresa $empresa): array
    {
        $listo = $this->hasGalponActivo($empresa);

        return [
            'key' => 'galpon',
            'label' => 'Galpón',
            'description' => $listo
                ? 'Hay al menos un galpón activo para cargar en campo.'
                : ($user->rol->canManageEstructura()
                    ? 'Creá un galpón dentro de la granja en Estructura.'
                    : 'Pedile al administrativo que cree el primer galpón.'),
            'icon' => 'warehouse',
            'status' => $listo ? 'Listo' : 'Pendiente',
            'href' => $listo ? null : $this->estructuraRoute($user),
        ];
    }

    /**
     * @return array{key: string, label: string, description: string, icon: string, status: string, href: ?string}
     */
    private function loteItem(User $user, Empresa $empresa): array
    {
        $listo = $this->hasSaldoInicial($empresa);

        return [
            'key' => 'lote',
            'label' => 'Lote y saldo inicial',
            'description' => $listo
                ? 'Hay un lote con aves iniciales cargadas.'
                : 'Registrá el primer lote con la cantidad de aves del galpón.',
            'icon' => 'bird',
            'status' => $listo ? 'Listo' : 'Pendiente',
            'href' => $listo ? null : $this->loteRoute($user),
        ];
    }

    /**
     * @return array{key: string, label: string, description: string, icon: string, status: string, href: ?string}
     */
    private function operarioItem(User $user, Empresa $empresa): array
    {
        $listo = $this->hasOperarioActivo($empresa);

        return [
            'key' => 'operario',
            'label' => 'Operario',
            'description' => $listo
                ? 'Hay al menos un operario activo para cargar en campo.'
                : ($user->rol->canManageUsers()
                    ? 'Creá un usuario operario en Usuarios.'
                    : 'Pedile al administrativo que dé de alta un operario.'),
            'icon' => 'smartphone',
            'status' => $listo ? 'Listo' : 'Pendiente',
            'href' => $listo ? null : $this->usuariosRoute($user),
        ];
    }

    private function hasAdministrador(Empresa $empresa): bool
    {
        return User::query()
            ->where('empresa_id', $empresa->id)
            ->where('activo', true)
            ->whereIn('rol', [UserRole::Dueno, UserRole::Administrativo])
            ->exists();
    }

    private function hasGranjaActiva(Empresa $empresa): bool
    {
        return Granja::query()
            ->where('empresa_id', $empresa->id)
            ->where('activa', true)
            ->exists();
    }

    private function hasGalponActivo(Empresa $empresa): bool
    {
        return Galpon::query()
            ->where('empresa_id', $empresa->id)
            ->where('activo', true)
            ->exists();
    }

    private function hasSaldoInicial(Empresa $empresa): bool
    {
        if (Galpon::query()
            ->where('empresa_id', $empresa->id)
            ->where('aves_actuales', '>', 0)
            ->exists()) {
            return true;
        }

        return Lote::query()
            ->where('empresa_id', $empresa->id)
            ->where('cantidad_inicial', '>', 0)
            ->exists();
    }

    private function hasOperarioActivo(Empresa $empresa): bool
    {
        return User::query()
            ->where('empresa_id', $empresa->id)
            ->where('activo', true)
            ->where('rol', UserRole::Operario)
            ->exists();
    }

    private function estructuraRoute(User $user): ?string
    {
        if (! $user->rol->canViewEstructura()) {
            return null;
        }

        $route = $user->rol->panelRouteName('estructura.index');

        return Route::has($route) ? route($route) : null;
    }

    private function usuariosRoute(User $user): ?string
    {
        if (! $user->rol->canManageUsers()) {
            return null;
        }

        $route = $user->rol->panelRouteName('usuarios.index');

        return Route::has($route) ? route($route) : null;
    }

    private function loteRoute(User $user): ?string
    {
        if ($user->rol->canViewEstructura()) {
            return $this->estructuraRoute($user);
        }

        if (! $user->rol->canCreateLote() || ! $user->rol->canAccessOperarioMobile()) {
            return null;
        }

        return route('operario.cargar', ['form' => 'lote']);
    }

    /**
     * @return array{
     *     show: bool,
     *     title: string,
     *     subtitle: string,
     *     pending_count: int,
     *     total_count: int,
     *     items: list<array{
     *         key: string,
     *         label: string,
     *         description: string,
     *         icon: string,
     *         status: string,
     *         href: ?string
     *     }>
     * }
     */
    private function emptyPanel(): array
    {
        return [
            'show' => false,
            'title' => '',
            'subtitle' => '',
            'pending_count' => 0,
            'total_count' => 0,
            'items' => [],
        ];
    }
}
