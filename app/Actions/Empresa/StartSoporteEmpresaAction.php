<?php

namespace App\Actions\Empresa;

use App\Models\Empresa;
use App\Models\SoporteSesion;
use App\Models\User;
use App\Services\SoporteEmpresaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class StartSoporteEmpresaAction
{
    public function __construct(private SoporteEmpresaService $soporte) {}

    /**
     * @param  array{motivo: string}  $data
     */
    public function execute(User $actor, Empresa $empresa, array $data): SoporteSesion
    {
        Gate::forUser($actor)->authorize('enterSupport', $empresa);

        $min = max(5, (int) config('avicore.soporte.motivo_min_caracteres', 10));

        $validated = validator($data, [
            'motivo' => ['required', 'string', 'min:'.$min, 'max:500'],
        ], [
            'motivo.required' => 'Indicá el motivo del acceso en soporte.',
            'motivo.min' => "El motivo debe tener al menos {$min} caracteres.",
        ])->validate();

        if (! $empresa->permiteLogin()) {
            throw ValidationException::withMessages([
                'motivo' => 'La empresa no está activa; no podés ingresar en soporte.',
            ]);
        }

        return DB::transaction(function () use ($actor, $empresa, $validated): SoporteSesion {
            $this->soporte->closeOpenSessionsForActor($actor, 'replaced');

            $started = now();

            $sesion = SoporteSesion::query()->create([
                'empresa_id' => $empresa->id,
                'actor_id' => $actor->id,
                'motivo' => trim($validated['motivo']),
                'started_at' => $started,
                'expires_at' => $this->soporte->expiresAtForNewSession(),
            ]);

            $this->soporte->rememberSession($sesion);
            $this->soporte->recordAccion($sesion, 'inicio', [
                'empresa_id' => $empresa->id,
                'empresa_nombre' => $empresa->nombre,
            ]);

            return $sesion;
        });
    }
}
