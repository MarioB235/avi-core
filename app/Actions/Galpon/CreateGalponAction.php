<?php

namespace App\Actions\Galpon;

use App\Models\Galpon;
use App\Models\Granja;
use App\Models\User;
use App\Services\EmpresaRelationalGuard;
use App\Support\GalponValidacion;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CreateGalponAction
{
    public function __construct(private EmpresaRelationalGuard $relations) {}

    /**
     * @param  array{granja_id: int, nombre: string, codigo?: string|null, capacidad?: int|null, estado?: string, observacion?: string|null}  $data
     */
    public function execute(User $actor, array $data): Galpon
    {
        Gate::forUser($actor)->authorize('create', Galpon::class);

        $granja = Granja::query()->whereKey($data['granja_id'] ?? 0)->firstOrFail();

        $this->relations->assertGranjaOfEmpresa($granja, (int) $actor->empresa_id);
        GalponValidacion::assertGranjaActiva($granja);

        $payload = array_merge($data, [
            'estado' => $data['estado'] ?? 'activo',
        ]);

        $normalized = GalponValidacion::normalize($payload);

        $validated = validator(
            array_merge($normalized, [
                'estado' => $normalized['estado']->value,
            ]),
            array_merge(GalponValidacion::rules($granja->id), [
                'granja_id' => ['required', 'integer', Rule::exists('granjas', 'id')->where('empresa_id', $actor->empresa_id)],
            ]),
            GalponValidacion::messages()
        )->validate();

        return Galpon::query()->create([
            'empresa_id' => $actor->empresa_id,
            'granja_id' => $granja->id,
            'nombre' => $validated['nombre'],
            'codigo' => $validated['codigo'],
            'capacidad' => $validated['capacidad'],
            'estado' => $normalized['estado'],
            'activo' => $normalized['activo'],
            'aves_actuales' => 0,
            'observacion' => $validated['observacion'],
        ]);
    }
}
