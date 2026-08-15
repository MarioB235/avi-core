<?php

namespace App\Policies;

use App\Models\RegistroOperativo;
use App\Models\User;
use App\Policies\Concerns\AuthorizesOperarioAnulacion;

class RegistroOperativoPolicy
{
    use AuthorizesOperarioAnulacion;

    public function anular(User $user, RegistroOperativo $registro): bool
    {
        return $this->userCanAnularOperarioRegistro(
            $user,
            $registro->empresa_id,
            $registro->user_id,
            $registro->estado,
            $registro->created_at,
        );
    }
}
