<?php

namespace App\Actions\Operacion;

use App\Actions\Auditoria\RegistrarAuditoriaAction;
use App\Enums\AuditoriaCategoria;
use App\Enums\RegistroOperativoEstado;
use App\Models\Galpon;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Support\RegistroOperativoImpactoAves;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class AnularRegistroOperativoAction
{
    public function __construct(private RegistrarAuditoriaAction $auditoria) {}

    public function execute(User $user, RegistroOperativo $registro, string $motivo): RegistroOperativo
    {
        Gate::forUser($user)->authorize('anular', $registro);

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

        return DB::transaction(function () use ($user, $registro, $motivo): RegistroOperativo {
            /** @var RegistroOperativo $registroBloqueado */
            $registroBloqueado = RegistroOperativo::query()
                ->whereKey($registro->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($registroBloqueado->estado === RegistroOperativoEstado::Anulado) {
                throw ValidationException::withMessages([
                    'motivoAnulacion' => 'Este registro ya fue anulado.',
                ]);
            }

            $cantidad = RegistroOperativoImpactoAves::cantidadARestaurarEnAnulacion($registroBloqueado);

            if ($cantidad > 0) {
                $galpon = Galpon::query()
                    ->whereKey($registroBloqueado->galpon_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $galpon->increment('aves_actuales', $cantidad);
            }

            $registroBloqueado->forceFill([
                'estado' => RegistroOperativoEstado::Anulado,
                'anulado_at' => now(),
                'anulado_por' => $user->id,
                'motivo_anulacion' => $motivo,
            ])->save();

            $this->auditoria->execute(
                $user,
                AuditoriaCategoria::Operacion,
                'anulado',
                RegistroOperativo::class,
                $registroBloqueado->id,
                $registroBloqueado->empresa_id,
                $motivo,
                [
                    'tipo' => $registroBloqueado->tipo->value,
                    'galpon_id' => $registroBloqueado->galpon_id,
                    'registro_user_id' => $registroBloqueado->user_id,
                ],
            );

            return $registroBloqueado->fresh(['galpon']);
        });
    }
}
