<?php

namespace App\Actions\Operacion;

use App\Actions\Auditoria\RegistrarAuditoriaAction;
use App\Enums\AuditoriaCategoria;
use App\Enums\RegistroOperativoEstado;
use App\Models\User;
use App\Models\Vacunacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class AnularVacunacionAction
{
    public function __construct(private RegistrarAuditoriaAction $auditoria) {}

    public function execute(User $user, Vacunacion $vacunacion, string $motivo): Vacunacion
    {
        Gate::forUser($user)->authorize('anular', $vacunacion);

        $motivo = trim($motivo);

        if ($motivo === '') {
            throw ValidationException::withMessages([
                'motivoAnulacion' => 'Ingresá el motivo de la anulación.',
            ]);
        }

        if (mb_strlen($motivo) > 500) {
            throw ValidationException::withMessages([
                'motivoAnulacion' => 'El motivo no puede superar los 500 caracteres.',
            ]);
        }

        return DB::transaction(function () use ($user, $vacunacion, $motivo): Vacunacion {
            /** @var Vacunacion $vacunacionBloqueada */
            $vacunacionBloqueada = Vacunacion::query()
                ->whereKey($vacunacion->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($vacunacionBloqueada->estado === RegistroOperativoEstado::Anulado) {
                throw ValidationException::withMessages([
                    'motivoAnulacion' => 'Este registro ya fue anulado.',
                ]);
            }

            $vacunacionBloqueada->forceFill([
                'estado' => RegistroOperativoEstado::Anulado,
                'anulado_at' => now(),
                'anulado_por' => $user->id,
                'motivo_anulacion' => $motivo,
            ])->save();

            $this->auditoria->execute(
                $user,
                AuditoriaCategoria::Operacion,
                'anulado',
                Vacunacion::class,
                $vacunacionBloqueada->id,
                $vacunacionBloqueada->empresa_id,
                $motivo,
                [
                    'vacuna' => $vacunacionBloqueada->vacuna->value,
                    'lote_id' => $vacunacionBloqueada->lote_id,
                    'galpon_id' => $vacunacionBloqueada->galpon_id,
                ],
            );

            return $vacunacionBloqueada->fresh(['galpon', 'lote']);
        });
    }
}
