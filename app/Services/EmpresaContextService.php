<?php

namespace App\Services;

use App\Models\User;

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

        session([self::SESSION_KEY => $empresaId]);
    }
}
