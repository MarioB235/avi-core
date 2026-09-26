<?php

namespace App\Actions\Operacion;

use App\Enums\RegistroOperativoEstado;
use App\Enums\RegistroOperativoTipo;
use App\Models\Galpon;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Services\EmpresaRelationalGuard;
use App\Support\GalponValidacion;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class RegistrarCargaAlimentoAction
{
    public function __construct(private EmpresaRelationalGuard $relations) {}

    public function execute(User $user, Galpon $galpon, float $alimentoKg, ?string $observacion = null): RegistroOperativo
    {
        Gate::forUser($user)->authorize('view', $galpon);

        $this->relations->assertGalponOfActor($user, $galpon);

        GalponValidacion::assertDisponibleParaCarga($galpon);

        if ($alimentoKg <= 0) {
            throw ValidationException::withMessages([
                'alimento_kg' => 'Los kilos entregados deben ser mayor a cero.',
            ]);
        }

        return RegistroOperativo::query()->create([
            'empresa_id' => $user->empresa_id,
            'galpon_id' => $galpon->id,
            'user_id' => $user->id,
            'tipo' => RegistroOperativoTipo::Alimento,
            'alimento_kg' => round($alimentoKg, 2),
            'observacion' => $observacion !== '' ? $observacion : null,
            'estado' => RegistroOperativoEstado::Activo,
        ]);
    }
}
