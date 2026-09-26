<?php

namespace App\Services\Auth;

use App\Models\User;

class AccountAccessService
{
    public function mayUseApplication(User $user): bool
    {
        if (! $user->activo) {
            return false;
        }

        if ($user->isAdminAvicore()) {
            return true;
        }

        if ($user->empresa_id === null) {
            return false;
        }

        $user->loadMissing('empresa');

        return $user->empresa !== null && $user->empresa->permiteLogin();
    }
}
