<?php

namespace App\Actions\User;

use App\Models\User;
use App\Services\TemporaryPasswordGenerator;
use Illuminate\Support\Facades\Gate;

class ResetUserPasswordAction
{
    public function __construct(private TemporaryPasswordGenerator $passwords) {}

    /**
     * @return array{user: User, plainPassword: string}
     */
    public function execute(User $actor, User $target): array
    {
        Gate::forUser($actor)->authorize('resetPassword', $target);

        $plainPassword = $this->passwords->generate();

        $target->forceFill([
            'password' => $plainPassword,
            'must_change_password' => true,
        ])->save();

        return [
            'user' => $target->refresh(),
            'plainPassword' => $plainPassword,
        ];
    }
}
