<?php

namespace App\Services;

use App\Models\User;

class EmpresaContextService
{
    public function __construct(private SoporteEmpresaService $soporte) {}

    public function empresaId(): ?int
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return null;
        }

        return $this->empresaIdFor($user);
    }

    public function empresaIdFor(User $user): ?int
    {
        if ($user->isAdminAvicore()) {
            $authUser = auth()->user();

            if ($authUser instanceof User && $authUser->id === $user->id) {
                return $this->soporte->empresaId();
            }

            return null;
        }

        return $user->empresa_id;
    }

    public function isSupportMode(): bool
    {
        return $this->soporte->isActive();
    }
}
