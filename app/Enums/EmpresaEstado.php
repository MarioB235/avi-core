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
}
