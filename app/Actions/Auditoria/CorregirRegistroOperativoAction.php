<?php

namespace App\Actions\Auditoria;

use App\Enums\AuditoriaCategoria;
use App\Enums\RegistroOperativoEstado;
use App\Enums\RegistroOperativoTipo;
use App\Models\CorreccionRegistroOperativo;
use App\Models\Galpon;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Support\RegistroOperativoCorreccion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class CorregirRegistroOperativoAction
{
    public function __construct(private RegistrarAuditoriaAction $auditoria) {}

    /**
     * @param  array<string, mixed>  $valoresEntrada
     */
    public function execute(
        User $user,
        RegistroOperativo $registro,
        string $motivo,
        array $valoresEntrada,
        ?Carbon $fechaEfectiva = null,
    ): CorreccionRegistroOperativo {
        Gate::forUser($user)->authorize('corregir', $registro);

        $motivo = trim($motivo);

        if ($motivo === '') {
            throw ValidationException::withMessages([
                'motivoCorreccion' => 'Ingresá el motivo de la corrección.',
            ]);
        }

        if (mb_strlen($motivo) > 500) {
            throw ValidationException::withMessages([
                'motivoCorreccion' => 'El motivo no puede superar los 500 caracteres.',
            ]);
        }

        $fechaEfectiva = $fechaEfectiva ?? now();

        if ($fechaEfectiva->isFuture()) {
            throw ValidationException::withMessages([
                'fechaEfectivaCorreccion' => 'La fecha efectiva no puede ser futura.',
            ]);
        }

        return DB::transaction(function () use ($user, $registro, $motivo, $valoresEntrada, $fechaEfectiva): CorreccionRegistroOperativo {
            /** @var RegistroOperativo $registroBloqueado */
            $registroBloqueado = RegistroOperativo::query()
                ->whereKey($registro->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($registroBloqueado->estado === RegistroOperativoEstado::Anulado) {
                throw ValidationException::withMessages([
                    'correccion' => 'No se puede corregir un registro anulado.',
                ]);
            }

            RegistroOperativoCorreccion::assertTipoCorregible($registroBloqueado);

            $valoresAnteriores = RegistroOperativoCorreccion::snapshot($registroBloqueado);
            $valoresNuevos = RegistroOperativoCorreccion::normalizarEntrada($registroBloqueado, $valoresEntrada);

            if ($valoresAnteriores === $valoresNuevos) {
                throw ValidationException::withMessages([
                    'correccion' => 'Los valores corregidos deben ser distintos a los actuales.',
                ]);
            }

            $debitoAnterior = RegistroOperativoCorreccion::cantidadAvesDebitadas(
                $registroBloqueado->tipo,
                $valoresAnteriores,
            );
            $debitoNuevo = RegistroOperativoCorreccion::cantidadAvesDebitadas(
                $registroBloqueado->tipo,
                $valoresNuevos,
            );
            $deltaAves = $debitoNuevo - $debitoAnterior;

            if ($deltaAves !== 0 && in_array($registroBloqueado->tipo, [
                RegistroOperativoTipo::Muertes,
                RegistroOperativoTipo::Descarte,
            ], true)) {
                $galpon = Galpon::query()
                    ->whereKey($registroBloqueado->galpon_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($deltaAves > 0 && $deltaAves > $galpon->aves_actuales) {
                    throw ValidationException::withMessages([
                        'correccion' => 'La corrección supera las aves vivas del galpón ('.number_format($galpon->aves_actuales, 0, ',', '.').').',
                    ]);
                }

                if ($deltaAves > 0) {
                    $galpon->decrement('aves_actuales', $deltaAves);
                } elseif ($deltaAves < 0) {
                    $galpon->increment('aves_actuales', abs($deltaAves));
                }
            }

            $correccion = CorreccionRegistroOperativo::query()->create([
                'empresa_id' => $registroBloqueado->empresa_id,
                'registro_operativo_id' => $registroBloqueado->id,
                'valores_anteriores' => $valoresAnteriores,
                'valores_nuevos' => $valoresNuevos,
                'motivo' => $motivo,
                'corregido_por' => $user->id,
                'fecha_efectiva' => $fechaEfectiva,
            ]);

            RegistroOperativoCorreccion::aplicarAlRegistro($registroBloqueado, $valoresNuevos);

            $this->auditoria->execute(
                $user,
                AuditoriaCategoria::Correccion,
                'corregido',
                RegistroOperativo::class,
                $registroBloqueado->id,
                $registroBloqueado->empresa_id,
                $motivo,
                [
                    'correccion_id' => $correccion->id,
                    'valores_anteriores' => $valoresAnteriores,
                    'valores_nuevos' => $valoresNuevos,
                    'fecha_efectiva' => $fechaEfectiva->toIso8601String(),
                ],
                $fechaEfectiva,
            );

            return $correccion->fresh(['corregidoPor', 'registroOperativo']);
        });
    }
}
