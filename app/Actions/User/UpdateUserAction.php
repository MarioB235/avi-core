<?php

namespace App\Actions\User;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateUserAction
{
    /**
     * @param  array{name: string, documento: string, email?: string|null, rol: string, activo: bool}  $data
     */
    public function execute(User $actor, User $target, array $data): User
    {
        Gate::forUser($actor)->authorize('update', $target);

        $rol = UserRole::from($data['rol']);

        if ($rol !== $target->rol && ! in_array($rol, $actor->rol->assignableRoles(), true)) {
            throw ValidationException::withMessages([
                'rol' => 'No podés asignar ese rol.',
            ]);
        }

        if ($rol === UserRole::AdminAvicore && $target->empresa_id !== null) {
            throw ValidationException::withMessages([
                'rol' => 'No se puede convertir un usuario de empresa en Admin AviCore.',
            ]);
        }

        if ($rol !== UserRole::AdminAvicore && $target->isAdminAvicore()) {
            throw ValidationException::withMessages([
                'rol' => 'Un Admin AviCore no puede cambiarse a un rol de empresa desde aquí.',
            ]);
        }

        $activo = (bool) $data['activo'];

        if (! $activo && $actor->is($target)) {
            throw ValidationException::withMessages([
                'activo' => 'No podés desactivar tu propia cuenta.',
            ]);
        }

        validator(
            [
                'name' => $data['name'],
                'documento' => $data['documento'],
                'email' => $data['email'] ?? null,
                'rol' => $rol->value,
                'activo' => $activo,
            ],
            [
                'name' => ['required', 'string', 'max:120'],
                'documento' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('users', 'documento')
                        ->ignore($target->id)
                        ->where(fn ($query) => $target->empresa_id === null
                            ? $query->whereNull('empresa_id')
                            : $query->where('empresa_id', $target->empresa_id)),
                ],
                'email' => ['nullable', 'email', 'max:255'],
                'rol' => ['required', Rule::enum(UserRole::class)],
                'activo' => ['required', 'boolean'],
            ],
            [
                'documento.unique' => 'Ya existe un usuario con ese documento en la empresa.',
            ]
        )->validate();

        $target->fill([
            'name' => trim($data['name']),
            'documento' => trim($data['documento']),
            'email' => filled($data['email'] ?? null) ? trim((string) $data['email']) : null,
            'rol' => $rol,
            'activo' => $activo,
        ])->save();

        return $target->refresh();
    }
}
