<?php

namespace App\Actions\Lote;

use App\Enums\LoteEstado;
use App\Models\Lote;
use App\Models\User;
use App\Support\LoteValidacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TransicionarLoteEstadoAction
{
    /**
     * @param  array{estado: string, motivo: string}  $data
     */
    public function execute(User $actor, Lote $lote, array $data): Lote
    {
        Gate::forUser($actor)->authorize('transition', $lote);

        if (array_key_exists('estado', $data) === false) {
            throw ValidationException::withMessages([
                'estado' => 'Elegí el nuevo estado.',
            ]);
        }

        $validated = validator($data, [
            'estado' => ['required', Rule::enum(LoteEstado::class)],
        ])->validate();

        $estadoNuevo = LoteEstado::from($validated['estado']);
        $motivo = LoteValidacion::assertMotivoTransicion($data['motivo'] ?? '');
        $estadoAnterior = $lote->estado;

        LoteValidacion::assertTransicionEstado($estadoAnterior, $estadoNuevo, $actor);

        return DB::transaction(function () use ($actor, $lote, $estadoAnterior, $estadoNuevo, $motivo): Lote {
            $historial = $lote->estado_historial ?? [];

            $historial[] = [
                'estado_anterior' => $estadoAnterior->value,
                'estado_nuevo' => $estadoNuevo->value,
                'motivo' => $motivo,
                'actor_id' => $actor->id,
                'actor_name' => $actor->name,
                'fecha' => now()->toIso8601String(),
            ];

            $lote->update([
                'estado' => $estadoNuevo,
                'estado_historial' => $historial,
            ]);

            return $lote->fresh();
        });
    }
}
