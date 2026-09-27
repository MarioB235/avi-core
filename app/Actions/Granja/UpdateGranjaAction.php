<?php

namespace App\Actions\Granja;

use App\Models\Granja;
use App\Models\User;
use App\Support\GranjaValidacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class UpdateGranjaAction
{
    /**
     * @param  array{nombre: string, codigo?: string|null, dicose?: string|null, ubicacion?: string|null, activa: bool}  $data
     */
    public function execute(User $actor, Granja $granja, array $data): Granja
    {
        Gate::forUser($actor)->authorize('update', $granja);

        $normalized = GranjaValidacion::normalize($data);
        $normalized['activa'] = (bool) ($data['activa'] ?? $normalized['activa']);

        $validated = validator(
            $normalized,
            array_merge(GranjaValidacion::rules($granja->empresa_id, $granja->id), [
                'activa' => ['required', 'boolean'],
            ]),
            GranjaValidacion::messages()
        )->validate();

        $wasActive = $granja->activa;

        DB::transaction(function () use ($granja, $validated, $wasActive): void {
            $granja->update([
                'nombre' => $validated['nombre'],
                'codigo' => $validated['codigo'],
                'dicose' => $validated['dicose'],
                'ubicacion' => $validated['ubicacion'],
                'activa' => $validated['activa'],
            ]);

            if ($wasActive && ! $validated['activa']) {
                $granja->galpones()->update(['activo' => false]);
            }
        });

        return $granja->fresh();
    }
}
