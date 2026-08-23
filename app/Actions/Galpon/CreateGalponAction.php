<?php

namespace App\Actions\Galpon;

use App\Enums\GalponEstado;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CreateGalponAction
{
    /**
     * @param  array{granja_id: int, nombre: string, codigo?: string|null, capacidad?: int|null, estado?: string, observacion?: string|null}  $data
     */
    public function execute(User $actor, array $data): Galpon
    {
        Gate::forUser($actor)->authorize('create', Galpon::class);

        $granja = Granja::query()->whereKey($data['granja_id'] ?? 0)->firstOrFail();

        if ($granja->empresa_id !== $actor->empresa_id) {
            throw ValidationException::withMessages([
                'granja_id' => 'La granja no pertenece a tu empresa.',
            ]);
        }

        $validated = validator($data, [
            'granja_id' => ['required', 'integer', Rule::exists('granjas', 'id')->where('empresa_id', $actor->empresa_id)],
            'nombre' => ['required', 'string', 'max:120'],
            'codigo' => ['nullable', 'string', 'max:50'],
            'capacidad' => ['nullable', 'integer', 'min:1'],
            'estado' => ['nullable', Rule::enum(GalponEstado::class)],
            'observacion' => ['nullable', 'string', 'max:1000'],
        ])->validate();

        return Galpon::query()->create([
            'empresa_id' => $actor->empresa_id,
            'granja_id' => $granja->id,
            'nombre' => trim($validated['nombre']),
            'codigo' => filled($validated['codigo'] ?? null) ? trim((string) $validated['codigo']) : null,
            'capacidad' => $validated['capacidad'] ?? null,
            'estado' => isset($validated['estado']) ? GalponEstado::from($validated['estado']) : GalponEstado::Activo,
            'activo' => true,
            'aves_actuales' => 0,
            'observacion' => filled($validated['observacion'] ?? null) ? trim((string) $validated['observacion']) : null,
        ]);
    }
}
