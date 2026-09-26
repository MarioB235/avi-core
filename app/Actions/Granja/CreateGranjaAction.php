<?php

namespace App\Actions\Granja;

use App\Models\Granja;
use App\Models\User;
use App\Support\GranjaValidacion;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class CreateGranjaAction
{
    /**
     * @param  array{nombre: string, codigo?: string|null, dicose?: string|null, ubicacion?: string|null, activa?: bool}  $data
     */
    public function execute(User $actor, array $data): Granja
    {
        Gate::forUser($actor)->authorize('create', Granja::class);

        $empresaId = $actor->empresa_id;

        if ($empresaId === null) {
            throw ValidationException::withMessages([
                'nombre' => 'No tenés empresa asignada para crear granjas.',
            ]);
        }

        $normalized = GranjaValidacion::normalize($data);

        $validated = validator(
            $normalized,
            GranjaValidacion::rules($empresaId),
            GranjaValidacion::messages()
        )->validate();

        return Granja::query()->create([
            'empresa_id' => $empresaId,
            'nombre' => $validated['nombre'],
            'codigo' => $validated['codigo'],
            'dicose' => $validated['dicose'],
            'ubicacion' => $validated['ubicacion'],
            'activa' => $validated['activa'],
        ]);
    }
}
