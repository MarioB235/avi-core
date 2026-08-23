<?php

namespace App\Enums;

enum UserRole: string
{
    case AdminAvicore = 'admin_avicore';
    case Dueno = 'dueno';
    case Administrativo = 'administrativo';
    case Encargado = 'encargado';
    case Operario = 'operario';
    case Reparto = 'reparto';

    public function label(): string
    {
        return match ($this) {
            self::AdminAvicore => 'Admin AviCore',
            self::Dueno => 'Dueño',
            self::Administrativo => 'Administrativo',
            self::Encargado => 'Encargado',
            self::Operario => 'Operario',
            self::Reparto => 'Reparto',
        };
    }

    public function routePrefix(): string
    {
        return match ($this) {
            self::AdminAvicore => 'avicore',
            self::Dueno => 'dueno',
            self::Administrativo => 'administrativo',
            self::Encargado => 'encargado',
            self::Operario => 'operario',
            self::Reparto => 'reparto',
        };
    }

    public function homeRouteName(): string
    {
        return $this->panelRouteName('home');
    }

    public function panelRouteName(string $name): string
    {
        return $this->routePrefix().'.'.$name;
    }

    /**
     * Roles con panel de gestión (no operario en campo).
     *
     * @return list<self>
     */
    public static function panelRoles(): array
    {
        return [
            self::Dueno,
            self::Administrativo,
            self::Encargado,
            self::AdminAvicore,
            self::Reparto,
        ];
    }

    public function isOperario(): bool
    {
        return $this === self::Operario;
    }

    public function usesAdminPanel(): bool
    {
        return in_array($this, self::panelRoles(), true);
    }

    public function canAccessOperarioMobile(): bool
    {
        return match ($this) {
            self::Operario, self::Dueno, self::Administrativo, self::Encargado => true,
            self::AdminAvicore => false,
        };
    }

    public function canCreateLote(): bool
    {
        return match ($this) {
            self::Dueno, self::Administrativo, self::Encargado => true,
            self::AdminAvicore, self::Operario, self::Reparto => false,
        };
    }

    public function canViewUsers(): bool
    {
        return match ($this) {
            self::AdminAvicore, self::Administrativo, self::Encargado => true,
            self::Dueno, self::Operario, self::Reparto => false,
        };
    }

    public function canManageUsers(): bool
    {
        return match ($this) {
            self::AdminAvicore, self::Administrativo => true,
            self::Dueno, self::Encargado, self::Operario, self::Reparto => false,
        };
    }

    public function canResetUserPassword(): bool
    {
        return match ($this) {
            self::AdminAvicore, self::Administrativo, self::Encargado => true,
            self::Dueno, self::Operario, self::Reparto => false,
        };
    }

    public function canViewEstructura(): bool
    {
        return match ($this) {
            self::Administrativo, self::Encargado => true,
            self::AdminAvicore, self::Dueno, self::Operario, self::Reparto => false,
        };
    }

    public function canViewResumen(): bool
    {
        return match ($this) {
            self::Dueno, self::Administrativo, self::Encargado => true,
            self::AdminAvicore, self::Operario => false,
        };
    }

    /**
     * Vista de solo lectura del equipo (sin CRUD de usuarios).
     */
    public function canViewEquipo(): bool
    {
        return $this === self::Dueno;
    }

    /**
     * Módulo comercial (clientes, ventas, pedidos) — preview post-MVP.
     */
    public function canViewComercial(): bool
    {
        return $this === self::Dueno;
    }

    /**
     * Gestión de granjas y galpones en panel de estructura.
     * Operación de oficina: Administrativo; Encargado solo lotes (`canManageLotes`).
     */
    public function canManageEstructura(): bool
    {
        return match ($this) {
            self::Administrativo => true,
            self::AdminAvicore, self::Dueno, self::Encargado, self::Operario, self::Reparto => false,
        };
    }

    public function canManageLotes(): bool
    {
        return $this->canCreateLote();
    }

    /**
     * Roles que este actor puede asignar al crear o editar usuarios.
     *
     * @return list<self>
     */
    public function assignableRoles(): array
    {
        return match ($this) {
            self::AdminAvicore => self::cases(),
            self::Dueno => [
                self::Dueno,
                self::Administrativo,
                self::Encargado,
                self::Operario,
                self::Reparto,
            ],
            self::Administrativo => [
                self::Administrativo,
                self::Encargado,
                self::Operario,
                self::Reparto,
            ],
            self::Encargado, self::Operario => [],
        };
    }
}
