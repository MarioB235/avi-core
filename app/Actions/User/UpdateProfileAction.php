<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Gate;

class UpdateProfileAction
{
    /**
     * @param  array{name: string, email?: string|null}  $data
     */
    public function execute(User $user, array $data): User
    {
        Gate::forUser($user)->authorize('updateProfile', $user);

        validator(
            [
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
            ],
            [
                'name' => ['required', 'string', 'max:120'],
                'email' => ['nullable', 'email', 'max:255'],
            ],
            [
                'name.required' => 'Ingresá tu nombre.',
                'email.email' => 'El correo no es válido.',
            ]
        )->validate();

        $user->fill([
            'name' => trim($data['name']),
            'email' => filled($data['email'] ?? null) ? trim((string) $data['email']) : null,
        ])->save();

        return $user->refresh();
    }
}
