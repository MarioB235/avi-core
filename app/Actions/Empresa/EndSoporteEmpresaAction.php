<?php

namespace App\Actions\Empresa;

use App\Models\User;
use App\Services\SoporteEmpresaService;

class EndSoporteEmpresaAction
{
    public function __construct(private SoporteEmpresaService $soporte) {}

    public function execute(User $actor, string $reason = 'manual'): bool
    {
        if (! $actor->isAdminAvicore()) {
            return false;
        }

        $sesion = $this->soporte->activeSesion();

        if ($sesion === null) {
            $this->soporte->clearSession();

            return false;
        }

        $this->soporte->endSesionRecord($sesion, $reason);

        return true;
    }
}
