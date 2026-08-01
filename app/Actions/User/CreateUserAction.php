<?php

namespace App\Actions\User;

use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use App\Services\TemporaryPasswordGenerator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CreateUserAction
{
    public function __construct(private TemporaryPasswordGenerator $passwords) {}

    /**
     * @param  array{name: string, documento: string, email?: string|null, rol: string, empresa_id?: int|null}  $data
     * @return array{user: User, plainPassword: string}
     */
    public function execute(User $actor, array $data): array
    {
        Gate::forUser($actor)->authorize('create', User::class);

        $rol = UserRole::from($data['rol']);

        if (! in_array($rol, $actor->rol->assignableRoles(), true)) {
            throw ValidationException::withMessages([
                'rol' => 'No podés asignar ese rol.',
            ]);
        }

        $empresaId = $this->resolveEmpresaId($actor, $rol, $data['empresa_id'] ?? null);

        validator(
            [
                'name' => $data['name'],
                'documento' => $data['documento'],
                'email' => $data['email'] ?? null,
                'rol' => $rol->value,
                'empresa_id' => $empresaId,
            ],
            $this->rules($empresaId, $rol),
            [
                'documento.unique' => 'Ya existe un usuario con ese documento en la empresa.',
            ]
        )->validate();

        $plainPassword = $this->passwords->generate();

        $user = User::query()->create([
            'empresa_id' => $empresaId,
            'name' => trim($data['name']),
            'documento' => trim($data['documento']),
            'email' => filled($data['email'] ?? null) ? trim((string) $data['email']) : null,
            'password' => $plainPassword,
            'rol' => $rol,
            'activo' => true,
            'must_change_password' => true,
        ]);

        return [
            'user' => $user,
            'plainPassword' => $plainPassword,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(?int $empresaId, UserRole $rol): array
    {
        $documentoUnique = Rule::unique('users', 'documento')->where(
            fn ($query) => $empresaId === null
                ? $query->whereNull('empresa_id')
                : $query->where('empresa_id', $empresaId)
        );

        return [
            'name' => ['required', 'string', 'max:120'],
            'documento' => ['required', 'string', 'max:50', $documentoUnique],
            'email' => ['nullable', 'email', 'max:255'],
            'rol' => ['required', Rule::enum(UserRole::class)],
            'empresa_id' => $rol === UserRole::AdminAvicore
                ? ['nullable']
                : ['required', 'integer', Rule::exists('empresas', 'id')],
        ];
    }

    private function resolveEmpresaId(User $actor, UserRole $rol, mixed $empresaIdInput): ?int
    {
        if ($rol === UserRole::AdminAvicore) {
            return null;
        }

        if ($actor->isAdminAvicore()) {
            $empresaId = $empresaIdInput !== null && $empresaIdInput !== ''
                ? (int) $empresaIdInput
                : null;

            if ($empresaId === null || ! Empresa::query()->whereKey($empresaId)->exists()) {
                throw ValidationException::withMessages([
                    'empresa_id' => 'Seleccioná una empresa válida.',
                ]);
            }

            return $empresaId;
        }

        if ($actor->empresa_id === null) {
            throw ValidationException::withMessages([
                'empresa_id' => 'Tu cuenta no tiene empresa asignada.',
            ]);
        }

        return $actor->empresa_id;
    }
}
