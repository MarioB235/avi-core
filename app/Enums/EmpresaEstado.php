<?php

namespace App\Enums;

enum EmpresaEstado: string
{
    case Activa = 'activa';
    case Suspendida = 'suspendida';
    case Inactiva = 'inactiva';

    public function permiteLogin(): bool
    {
        return $this === self::Activa;
    }

    public function label(): string
    {
        return match ($this) {
            self::Activa => 'Activa',
            self::Suspendida => 'Suspendida',
            self::Inactiva => 'Inactiva',
        };
    }
}
