<?php

namespace App\Actions\Lote;

use App\Models\Lote;
use App\Models\User;
use App\Support\EstructuraValidacion;
use App\Support\LoteValidacion;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class UpdateLoteAction
{
    /**
     * @param  array{codigo_sma?: string|null, linea_raza?: string|null, observacion?: string|null}  $data
     */
    public function execute(User $actor, Lote $lote, array $data): Lote
    {
        Gate::forUser($actor)->authorize('update', $lote);

        EstructuraValidacion::assertSinCambioEmpresa($lote, $data['empresa_id'] ?? null);
        EstructuraValidacion::assertSinReasignacionPadre($data, 'galpon_id');

        if (array_key_exists('estado', $data)) {
            throw ValidationException::withMessages([
                'estado' => 'El estado del lote se cambia desde «Cambiar estado», con motivo.',
            ]);
        }

        $codigoSma = LoteValidacion::assertCodigoSma($data['codigo_sma'] ?? null);

        $validated = validator($data, [
            'linea_raza' => ['nullable', 'string', 'max:120'],
            'observacion' => ['nullable', 'string', 'max:1000'],
        ])->validate();

        $lote->update([
            'codigo_sma' => $codigoSma,
            'linea_raza' => filled($validated['linea_raza'] ?? null) ? trim((string) $validated['linea_raza']) : null,
            'observacion' => filled($validated['observacion'] ?? null) ? trim((string) $validated['observacion']) : null,
        ]);

        return $lote->fresh();
    }
}
