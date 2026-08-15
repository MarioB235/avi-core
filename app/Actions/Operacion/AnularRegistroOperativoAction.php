<?php

namespace App\Actions\Operacion;

use App\Enums\RegistroOperativoEstado;
use App\Enums\RegistroOperativoTipo;
use App\Models\Galpon;
use App\Models\RegistroOperativo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class AnularRegistroOperativoAction
{
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

            if ($this->afectaAvesVivas($registroBloqueado)) {
                $galpon = Galpon::query()
                    ->whereKey($registroBloqueado->galpon_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $cantidad = match ($registroBloqueado->tipo) {
                    RegistroOperativoTipo::Muertes => (int) $registroBloqueado->muertes,
                    RegistroOperativoTipo::Descarte => (int) $registroBloqueado->descarte_aves,
                    default => 0,
                };

                if ($cantidad > 0) {
                    $galpon->increment('aves_actuales', $cantidad);
                }
            }

            $registroBloqueado->forceFill([
                'estado' => RegistroOperativoEstado::Anulado,
                'anulado_at' => now(),
                'anulado_por' => $user->id,
                'motivo_anulacion' => $motivo,
            ])->save();

            return $registroBloqueado->fresh(['galpon']);
        });
    }

    private function afectaAvesVivas(RegistroOperativo $registro): bool
    {
        return match ($registro->tipo) {
            RegistroOperativoTipo::Muertes, RegistroOperativoTipo::Descarte => true,
            default => false,
        };
    }
}
