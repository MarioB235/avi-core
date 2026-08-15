<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vacunacion;
use App\Policies\Concerns\AuthorizesOperarioAnulacion;

class VacunacionPolicy
{
    use AuthorizesOperarioAnulacion;

    public function anular(User $user, Vacunacion $vacunacion): bool
    {
        return $this->userCanAnularOperarioRegistro(
            $user,
            $vacunacion->empresa_id,
            $vacunacion->user_id,
            $vacunacion->estado,
            $vacunacion->created_at,
        );
    }
}
