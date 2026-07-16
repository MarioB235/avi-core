<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\User;
use InvalidArgumentException;

class EmpresaContextService
{
    private const SESSION_KEY = 'avicore.empresa_context_id';

    public function empresaId(): ?int
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return null;
        }

        if ($user->isAdminAvicore()) {
            $override = session(self::SESSION_KEY);

            if ($override !== null) {
                return (int) $override;
            }
        }

        return $user->empresa_id;
    }

    /**
     * Override de soporte para Admin AviCore (modo multiempresa futuro).
     * Solo acepta null (limpiar) o un id de empresa existente.
     */
    public function setEmpresaId(?int $empresaId): void
    {
        $user = auth()->user();

        if (! $user instanceof User || ! $user->isAdminAvicore()) {
            return;
        }

        if ($empresaId === null) {
            session()->forget(self::SESSION_KEY);

            return;
        }

        if (! Empresa::query()->whereKey($empresaId)->exists()) {
            throw new InvalidArgumentException("Empresa inexistente: {$empresaId}");
        }

        session([self::SESSION_KEY => $empresaId]);
    }
}
