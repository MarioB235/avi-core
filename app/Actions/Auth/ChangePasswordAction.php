<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Services\Auth\UserSessionService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ChangePasswordAction
{
    public function __construct(private UserSessionService $sessions) {}

    public function execute(User $user, string $currentPassword, string $newPassword): void
    {
        Gate::forUser($user)->authorize('updateProfile', $user);

        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'La contraseña actual no es correcta.',
            ]);
        }

        if (Hash::check($newPassword, $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'La nueva contraseña debe ser distinta a la actual.',
            ]);
        }

        $user->forceFill([
            'password' => $newPassword,
            'must_change_password' => false,
        ])->save();

        $this->sessions->invalidateOtherSessionsForUser($user, session()->getId());
    }
}
