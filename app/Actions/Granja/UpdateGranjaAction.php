<?php

namespace App\Actions\Granja;

use App\Models\Granja;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateGranjaAction
{
    /**
     * @param  array{nombre: string, codigo?: string|null, dicose?: string|null, ubicacion?: string|null, activa: bool}  $data
     */
    public function execute(User $actor, Granja $granja, array $data): Granja
    {
        Gate::forUser($actor)->authorize('update', $granja);

        $validated = validator($data, [
            'nombre' => ['required', 'string', 'max:120'],
            'codigo' => ['nullable', 'string', 'max:50'],
            'dicose' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('granjas', 'dicose')
                    ->where('empresa_id', $granja->empresa_id)
                    ->ignore($granja->id),
            ],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'activa' => ['required', 'boolean'],
        ], [
            'dicose.unique' => 'Ya existe una granja con ese DICOSE en la empresa.',
        ])->validate();

        $granja->update([
            'nombre' => trim($validated['nombre']),
            'codigo' => filled($validated['codigo'] ?? null) ? trim((string) $validated['codigo']) : null,
            'dicose' => filled($validated['dicose'] ?? null) ? trim((string) $validated['dicose']) : null,
            'ubicacion' => filled($validated['ubicacion'] ?? null) ? trim((string) $validated['ubicacion']) : null,
            'activa' => $validated['activa'],
        ]);

        return $granja->fresh();
    }
}
