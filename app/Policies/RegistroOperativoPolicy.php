<?php

namespace App\Policies;

use App\Models\RegistroOperativo;
use App\Models\User;
use App\Policies\Concerns\AuthorizesOperarioAnulacion;
use App\Policies\Concerns\AuthorizesOperarioCorreccion;

class RegistroOperativoPolicy
{
    use AuthorizesOperarioAnulacion;
    use AuthorizesOperarioCorreccion;

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

    public function corregir(User $user, RegistroOperativo $registro): bool
    {
        return $this->userCanCorregirRegistroOperativo(
            $user,
            $registro->empresa_id,
            $registro->estado,
        );
    }
}
