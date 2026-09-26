<?php

namespace App\Actions\Empresa;

use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use App\Services\TemporaryPasswordGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CreateEmpresaAction
{
    public function __construct(private TemporaryPasswordGenerator $passwords) {}

    /**
     * @param  array{
     *     nombre: string,
     *     codigo: string,
     *     estado?: string,
     *     admin_name: string,
     *     admin_documento: string,
     *     admin_email?: string|null
     * }  $data
     * @return array{empresa: Empresa, admin: User, plainPassword: string}
     */
    public function execute(User $actor, array $data): array
    {
        Gate::forUser($actor)->authorize('create', Empresa::class);

        $validated = validator($data, [
            'nombre' => ['required', 'string', 'max:120'],
            'codigo' => ['required', 'string', 'max:50', Rule::unique('empresas', 'codigo')],
            'estado' => ['nullable', Rule::enum(EmpresaEstado::class)],
            'admin_name' => ['required', 'string', 'max:120'],
            'admin_documento' => ['required', 'string', 'max:50'],
            'admin_email' => ['nullable', 'email', 'max:255'],
        ], [
            'codigo.unique' => 'Ya existe una empresa con ese identificador.',
            'admin_documento.unique' => 'Ya existe un usuario con ese documento en la empresa.',
        ])->validate();

        $codigo = strtoupper(trim($validated['codigo']));
        $estado = isset($validated['estado'])
            ? EmpresaEstado::from($validated['estado'])
            : EmpresaEstado::Activa;

        $plainPassword = $this->passwords->generate();

        return DB::transaction(function () use ($validated, $codigo, $estado, $plainPassword): array {
            $empresa = Empresa::query()->create([
                'nombre' => trim($validated['nombre']),
                'codigo' => $codigo,
                'estado' => $estado,
            ]);

            $documento = trim($validated['admin_documento']);

            $documentoExists = User::query()
                ->where('empresa_id', $empresa->id)
                ->where('documento', $documento)
                ->exists();

            if ($documentoExists) {
                throw ValidationException::withMessages([
                    'admin_documento' => 'Ya existe un usuario con ese documento en la empresa.',
                ]);
            }

            $admin = User::query()->create([
                'empresa_id' => $empresa->id,
                'name' => trim($validated['admin_name']),
                'documento' => $documento,
                'email' => filled($validated['admin_email'] ?? null)
                    ? trim((string) $validated['admin_email'])
                    : null,
                'password' => $plainPassword,
                'rol' => UserRole::Dueno,
                'activo' => true,
                'must_change_password' => true,
            ]);

            return [
                'empresa' => $empresa,
                'admin' => $admin,
                'plainPassword' => $plainPassword,
            ];
        });
    }
}
