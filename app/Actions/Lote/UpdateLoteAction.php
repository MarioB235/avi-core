<?php

namespace App\Actions\Lote;

use App\Actions\Auditoria\RegistrarAuditoriaAction;
use App\Enums\AuditoriaCategoria;
use App\Models\Lote;
use App\Models\User;
use App\Support\EstructuraValidacion;
use App\Support\LoteValidacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class UpdateLoteAction
{
    public function __construct(private RegistrarAuditoriaAction $auditoria) {}

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

        $antes = [
            'codigo_sma' => $lote->codigo_sma,
            'linea_raza' => $lote->linea_raza,
            'observacion' => $lote->observacion,
        ];

        DB::transaction(function () use ($actor, $lote, $codigoSma, $validated, $antes): void {
            $lote->update([
                'codigo_sma' => $codigoSma,
                'linea_raza' => filled($validated['linea_raza'] ?? null) ? trim((string) $validated['linea_raza']) : null,
                'observacion' => filled($validated['observacion'] ?? null) ? trim((string) $validated['observacion']) : null,
            ]);

            $this->auditoria->execute(
                $actor,
                AuditoriaCategoria::Lote,
                'actualizado',
                Lote::class,
                $lote->id,
                $lote->empresa_id,
                metadata: [
                    'codigo' => $lote->codigo,
                    'antes' => $antes,
                    'despues' => [
                        'codigo_sma' => $codigoSma,
                        'linea_raza' => filled($validated['linea_raza'] ?? null) ? trim((string) $validated['linea_raza']) : null,
                        'observacion' => filled($validated['observacion'] ?? null) ? trim((string) $validated['observacion']) : null,
                    ],
                ],
            );
        });

        return $lote->fresh();
    }
}
