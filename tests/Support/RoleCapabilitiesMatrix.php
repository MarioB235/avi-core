<?php

namespace Tests\Support;

use App\Enums\UserRole;

/**
 * Matriz canónica Dueño vs Administrativo (D02 / SEG-07).
 * Debe coincidir con `UserRole` y `permisos.md` §2 y §10.
 */
final class RoleCapabilitiesMatrix
{
    /**
     * @return list<string>
     */
    public static function capabilityMethods(): array
    {
        return [
            'canAccessOperarioMobile',
            'canCreateLote',
            'canViewUsers',
            'canManageUsers',
            'canResetUserPassword',
            'canViewEstructura',
            'canViewResumen',
            'canViewEquipo',
            'canViewComercial',
            'canManageEstructura',
            'canManageLotes',
        ];
    }

    /**
     * @return array<string, array<string, bool>>
     */
    public static function expectations(): array
    {
        return [
            UserRole::AdminAvicore->value => [
                'canAccessOperarioMobile' => false,
                'canCreateLote' => false,
                'canViewUsers' => true,
                'canManageUsers' => true,
                'canResetUserPassword' => true,
                'canViewEstructura' => false,
                'canViewResumen' => false,
                'canViewEquipo' => false,
                'canViewComercial' => false,
                'canManageEstructura' => false,
                'canManageLotes' => false,
            ],
            UserRole::Dueno->value => [
                'canAccessOperarioMobile' => true,
                'canCreateLote' => true,
                'canViewUsers' => false,
                'canManageUsers' => false,
                'canResetUserPassword' => false,
                'canViewEstructura' => false,
                'canViewResumen' => true,
                'canViewEquipo' => true,
                'canViewComercial' => false,
                'canManageEstructura' => false,
                'canManageLotes' => true,
            ],
            UserRole::Administrativo->value => [
                'canAccessOperarioMobile' => true,
                'canCreateLote' => true,
                'canViewUsers' => true,
                'canManageUsers' => true,
                'canResetUserPassword' => true,
                'canViewEstructura' => true,
                'canViewResumen' => true,
                'canViewEquipo' => false,
                'canViewComercial' => false,
                'canManageEstructura' => true,
                'canManageLotes' => true,
            ],
            UserRole::Encargado->value => [
                'canAccessOperarioMobile' => true,
                'canCreateLote' => true,
                'canViewUsers' => true,
                'canManageUsers' => false,
                'canResetUserPassword' => true,
                'canViewEstructura' => true,
                'canViewResumen' => true,
                'canViewEquipo' => false,
                'canViewComercial' => false,
                'canManageEstructura' => false,
                'canManageLotes' => true,
            ],
            UserRole::Operario->value => [
                'canAccessOperarioMobile' => true,
                'canCreateLote' => false,
                'canViewUsers' => false,
                'canManageUsers' => false,
                'canResetUserPassword' => false,
                'canViewEstructura' => false,
                'canViewResumen' => false,
                'canViewEquipo' => false,
                'canViewComercial' => false,
                'canManageEstructura' => false,
                'canManageLotes' => false,
            ],
            UserRole::Reparto->value => [
                'canAccessOperarioMobile' => false,
                'canCreateLote' => false,
                'canViewUsers' => false,
                'canManageUsers' => false,
                'canResetUserPassword' => false,
                'canViewEstructura' => false,
                'canViewResumen' => false,
                'canViewEquipo' => false,
                'canViewComercial' => false,
                'canManageEstructura' => false,
                'canManageLotes' => false,
            ],
        ];
    }

    /**
     * Módulos admin: ruta relativa bajo el prefijo del rol.
     *
     * @return array<string, array{dueno: bool, administrativo: bool}>
     */
    public static function panelModuleAccess(): array
    {
        return [
            'resumen' => ['dueno' => true, 'administrativo' => true],
            'equipo' => ['dueno' => true, 'administrativo' => false],
            'comercial' => ['dueno' => false, 'administrativo' => false],
            'estructura' => ['dueno' => false, 'administrativo' => true],
            'usuarios' => ['dueno' => false, 'administrativo' => true],
        ];
    }

    /**
     * @return list<UserRole>
     */
    public static function assignableByDueno(): array
    {
        return [
            UserRole::Dueno,
            UserRole::Administrativo,
            UserRole::Encargado,
            UserRole::Operario,
            UserRole::Reparto,
        ];
    }

    /**
     * @return list<UserRole>
     */
    public static function assignableByAdministrativo(): array
    {
        return [
            UserRole::Administrativo,
            UserRole::Encargado,
            UserRole::Operario,
            UserRole::Reparto,
        ];
    }
}
