<?php

namespace App\Enums;

enum UserRole: string
{
    case AdminAvicore = 'admin_avicore';
    case Dueno = 'dueno';
    case Administrativo = 'administrativo';
    case Encargado = 'encargado';
    case Operario = 'operario';

    public function label(): string
    {
        return match ($this) {
            self::AdminAvicore => 'Admin AviCore',
            self::Dueno => 'Dueño',
            self::Administrativo => 'Administrativo',
            self::Encargado => 'Encargado',
            self::Operario => 'Operario',
        };
    }

    public function isOperario(): bool
    {
        return $this === self::Operario;
    }

    public function usesAdminPanel(): bool
    {
        return ! $this->isOperario();
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
            self::AdminAvicore, self::Operario => false,
        };
    }

    public function canViewUsers(): bool
    {
        return match ($this) {
            self::AdminAvicore, self::Dueno, self::Administrativo, self::Encargado => true,
            self::Operario => false,
        };
    }

    public function canManageUsers(): bool
    {
        return match ($this) {
            self::AdminAvicore, self::Dueno, self::Administrativo => true,
            self::Encargado, self::Operario => false,
        };
    }

    public function canResetUserPassword(): bool
    {
        return match ($this) {
            self::AdminAvicore, self::Dueno, self::Administrativo, self::Encargado => true,
            self::Operario => false,
        };
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
            ],
            self::Administrativo => [
                self::Administrativo,
                self::Encargado,
                self::Operario,
            ],
            self::Encargado, self::Operario => [],
        };
    }
}
