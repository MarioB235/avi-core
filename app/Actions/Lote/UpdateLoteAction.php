<?php

namespace App\Actions\Lote;

use App\Enums\LoteEstado;
use App\Models\Lote;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateLoteAction
{
    /**
     * @param  array{codigo_sma?: string|null, linea_raza?: string|null, estado: string, observacion?: string|null}  $data
     */
    public function execute(User $actor, Lote $lote, array $data): Lote
    {
        Gate::forUser($actor)->authorize('update', $lote);

        $validated = validator($data, [
            'codigo_sma' => ['nullable', 'string', 'max:80'],
            'linea_raza' => ['nullable', 'string', 'max:120'],
            'estado' => ['required', Rule::enum(LoteEstado::class)],
            'observacion' => ['nullable', 'string', 'max:1000'],
        ])->validate();

        $lote->update([
            'codigo_sma' => filled($validated['codigo_sma'] ?? null) ? trim((string) $validated['codigo_sma']) : null,
            'linea_raza' => filled($validated['linea_raza'] ?? null) ? trim((string) $validated['linea_raza']) : null,
            'estado' => LoteEstado::from($validated['estado']),
            'observacion' => filled($validated['observacion'] ?? null) ? trim((string) $validated['observacion']) : null,
        ]);

        return $lote->fresh();
    }
}
