<?php

namespace App\Actions\Galpon;

use App\Models\Galpon;
use App\Models\Granja;
use App\Models\User;
use App\Services\EmpresaRelationalGuard;
use App\Support\GalponValidacion;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateGalponAction
{
    public function __construct(private EmpresaRelationalGuard $relations) {}

    /**
     * @param  array{granja_id: int, nombre: string, codigo?: string|null, capacidad?: int|null, estado: string, activo: bool, observacion?: string|null}  $data
     */
    public function execute(User $actor, Galpon $galpon, array $data): Galpon
    {
        Gate::forUser($actor)->authorize('update', $galpon);

        $granja = Granja::query()->whereKey($data['granja_id'] ?? 0)->firstOrFail();

        $this->relations->assertGranjaMatchesGalponEmpresa($granja, $galpon);

        if ($granja->id !== $galpon->granja_id) {
            GalponValidacion::assertGranjaActiva($granja);
        }

        $normalized = GalponValidacion::normalize($data, forUpdate: true);

        $validated = validator(
            array_merge($normalized, [
                'estado' => $normalized['estado']->value,
            ]),
            array_merge(GalponValidacion::rules($granja->id, $galpon->id), [
                'granja_id' => ['required', 'integer', Rule::exists('granjas', 'id')->where('empresa_id', $galpon->empresa_id)],
                'activo' => ['required', 'boolean'],
            ]),
            GalponValidacion::messages()
        )->validate();

        $galpon->update([
            'granja_id' => $granja->id,
            'nombre' => $validated['nombre'],
            'codigo' => $validated['codigo'],
            'capacidad' => $validated['capacidad'],
            'estado' => $normalized['estado'],
            'activo' => $normalized['activo'],
            'observacion' => $validated['observacion'],
        ]);

        return $galpon->fresh();
    }
}
