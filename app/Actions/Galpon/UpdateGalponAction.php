<?php

namespace App\Actions\Galpon;

use App\Enums\GalponEstado;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateGalponAction
{
    /**
     * @param  array{granja_id: int, nombre: string, codigo?: string|null, capacidad?: int|null, estado: string, activo: bool, observacion?: string|null}  $data
     */
    public function execute(User $actor, Galpon $galpon, array $data): Galpon
    {
        Gate::forUser($actor)->authorize('update', $galpon);

        $granja = Granja::query()->whereKey($data['granja_id'] ?? 0)->firstOrFail();

        if ($granja->empresa_id !== $galpon->empresa_id) {
            throw ValidationException::withMessages([
                'granja_id' => 'La granja no pertenece a tu empresa.',
            ]);
        }

        $validated = validator($data, [
            'granja_id' => ['required', 'integer', Rule::exists('granjas', 'id')->where('empresa_id', $galpon->empresa_id)],
            'nombre' => ['required', 'string', 'max:120'],
            'codigo' => ['nullable', 'string', 'max:50'],
            'capacidad' => ['nullable', 'integer', 'min:1'],
            'estado' => ['required', Rule::enum(GalponEstado::class)],
            'activo' => ['required', 'boolean'],
            'observacion' => ['nullable', 'string', 'max:1000'],
        ])->validate();

        $galpon->update([
            'granja_id' => $granja->id,
            'nombre' => trim($validated['nombre']),
            'codigo' => filled($validated['codigo'] ?? null) ? trim((string) $validated['codigo']) : null,
            'capacidad' => $validated['capacidad'] ?? null,
            'estado' => GalponEstado::from($validated['estado']),
            'activo' => $validated['activo'],
            'observacion' => filled($validated['observacion'] ?? null) ? trim((string) $validated['observacion']) : null,
        ]);

        return $galpon->fresh();
    }
}
