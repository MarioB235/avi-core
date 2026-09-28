<?php

namespace App\Actions\Empresa;

use App\Actions\Auditoria\RegistrarAuditoriaAction;
use App\Enums\AuditoriaCategoria;
use App\Enums\EmpresaEstado;
use App\Models\Empresa;
use App\Models\User;
use App\Services\Auth\UserSessionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateEmpresaEstadoAction
{
    public function __construct(
        private UserSessionService $sessions,
        private RegistrarAuditoriaAction $auditoria,
    ) {}

    /**
     * @param  array{estado: string, motivo: string}  $data
     */
    public function execute(User $actor, Empresa $empresa, array $data): Empresa
    {
        Gate::forUser($actor)->authorize('updateEstado', $empresa);

        $validated = validator($data, [
            'estado' => ['required', Rule::enum(EmpresaEstado::class)],
            'motivo' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'motivo.required' => 'Indicá el motivo del cambio de estado.',
            'motivo.min' => 'El motivo debe tener al menos 5 caracteres.',
        ])->validate();

        $nuevoEstado = EmpresaEstado::from($validated['estado']);
        $motivo = trim($validated['motivo']);

        if ($nuevoEstado === $empresa->estado) {
            throw ValidationException::withMessages([
                'estado' => 'La empresa ya está en ese estado.',
            ]);
        }

        if ($this->isDemoEmpresa($empresa)) {
            throw ValidationException::withMessages([
                'estado' => 'No podés cambiar el estado de la empresa demo.',
            ]);
        }

        $estadoAnterior = $empresa->estado;

        return DB::transaction(function () use ($actor, $empresa, $nuevoEstado, $motivo, $estadoAnterior): Empresa {
            $configuracion = $empresa->configuracion ?? [];
            $historial = $configuracion['estado_historial'] ?? [];

            $historial[] = [
                'estado_anterior' => $estadoAnterior->value,
                'estado_nuevo' => $nuevoEstado->value,
                'motivo' => $motivo,
                'actor_id' => $actor->id,
                'actor_name' => $actor->name,
                'fecha' => now()->toIso8601String(),
            ];

            $configuracion['estado_historial'] = $historial;

            $empresa->update([
                'estado' => $nuevoEstado,
                'configuracion' => $configuracion,
            ]);

            if (! $nuevoEstado->permiteLogin()) {
                $this->sessions->invalidateAllForEmpresa($empresa->id);
            }

            $this->auditoria->execute(
                $actor,
                AuditoriaCategoria::Empresa,
                'estado_cambiado',
                Empresa::class,
                $empresa->id,
                $empresa->id,
                $motivo,
                [
                    'estado_anterior' => $estadoAnterior->value,
                    'estado_nuevo' => $nuevoEstado->value,
                ],
            );

            return $empresa->fresh();
        });
    }

    private function isDemoEmpresa(Empresa $empresa): bool
    {
        $demoCodigo = strtoupper((string) config('avicore.demo_login.empresa_codigo', 'DEMO'));

        return strtoupper((string) $empresa->codigo) === $demoCodigo;
    }
}
