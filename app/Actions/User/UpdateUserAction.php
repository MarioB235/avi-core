<?php

namespace App\Actions\User;

use App\Actions\Auditoria\RegistrarAuditoriaAction;
use App\Enums\AuditoriaCategoria;
use App\Enums\UserRole;
use App\Models\User;
use App\Services\Auth\UserSessionService;
use App\Services\UserManagementGuard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateUserAction
{
    public function __construct(
        private UserManagementGuard $userManagement,
        private UserSessionService $sessions,
        private RegistrarAuditoriaAction $auditoria,
    ) {}

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

        $this->userManagement->assertCompanyRetainsActiveManager($target, $rol, $activo);

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

        $wasActive = $target->activo;
        $antes = [
            'rol' => $target->rol->value,
            'activo' => $target->activo,
            'documento' => $target->documento,
        ];

        DB::transaction(function () use ($actor, $target, $data, $rol, $activo, $antes, $wasActive): void {
            $target->fill([
                'name' => trim($data['name']),
                'documento' => trim($data['documento']),
                'email' => filled($data['email'] ?? null) ? trim((string) $data['email']) : null,
                'rol' => $rol,
                'activo' => $activo,
            ])->save();

            $this->auditoria->execute(
                $actor,
                AuditoriaCategoria::Usuario,
                'actualizado',
                User::class,
                $target->id,
                $target->empresa_id,
                metadata: [
                    'antes' => $antes,
                    'despues' => [
                        'rol' => $rol->value,
                        'activo' => $activo,
                        'documento' => trim($data['documento']),
                    ],
                ],
            );

            if ($wasActive && ! $activo) {
                $this->sessions->invalidateAllForUser($target);
            }
        });

        return $target->refresh();
    }
}
