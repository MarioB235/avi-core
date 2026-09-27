<?php

namespace App\Actions\Operacion;

use App\Enums\LoteEstado;
use App\Enums\RegistroOperativoEstado;
use App\Enums\VacunaTipo;
use App\Models\Galpon;
use App\Models\Lote;
use App\Models\User;
use App\Models\Vacunacion;
use App\Services\EmpresaRelationalGuard;
use App\Support\GalponValidacion;
use App\Support\VacunacionValidacion;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class RegistrarVacunacionAction
{
    public function __construct(private EmpresaRelationalGuard $relations) {}

    public function execute(
        User $user,
        Galpon $galpon,
        Lote $lote,
        VacunaTipo $vacuna,
        ?string $observacion = null,
        ?string $idempotenciaClave = null,
    ): Vacunacion {
        Gate::forUser($user)->authorize('view', $galpon);

        $this->relations->assertGalponOfActor($user, $galpon);
        $this->relations->assertLoteOfActor($user, $lote);
        $this->relations->assertLoteBelongsToGalpon($lote, $galpon);

        GalponValidacion::assertDisponibleParaCarga($galpon);
        VacunacionValidacion::assertObservacion($observacion);

        if (! in_array($lote->estado, [LoteEstado::Activo, LoteEstado::EnProduccion], true)) {
            throw ValidationException::withMessages([
                'lote_id' => 'El lote no admite vacunación.',
            ]);
        }

        $clave = $this->normalizarClaveIdempotencia($idempotenciaClave);

        if ($clave !== null) {
            $existente = $this->buscarPorClaveIdempotencia($user, $clave);

            if ($existente !== null) {
                return $existente;
            }
        }

        try {
            return Vacunacion::query()->create([
                'empresa_id' => $user->empresa_id,
                'galpon_id' => $galpon->id,
                'lote_id' => $lote->id,
                'user_id' => $user->id,
                'vacuna' => $vacuna,
                'idempotencia_clave' => $clave,
                'observacion' => VacunacionValidacion::normalizarObservacion($observacion),
                'estado' => RegistroOperativoEstado::Activo,
            ]);
        } catch (QueryException $exception) {
            if ($clave !== null && $this->esViolacionUnicaIdempotencia($exception)) {
                $existente = $this->buscarPorClaveIdempotencia($user, $clave);

                if ($existente !== null) {
                    return $existente;
                }
            }

            throw $exception;
        }
    }

    private function normalizarClaveIdempotencia(?string $clave): ?string
    {
        $clave = $clave !== null ? trim($clave) : '';

        return $clave !== '' ? $clave : null;
    }

    private function buscarPorClaveIdempotencia(User $user, string $clave): ?Vacunacion
    {
        if ($user->empresa_id === null) {
            return null;
        }

        return Vacunacion::query()
            ->forEmpresa((int) $user->empresa_id)
            ->where('idempotencia_clave', $clave)
            ->first();
    }

    private function esViolacionUnicaIdempotencia(QueryException $exception): bool
    {
        $sqlState = $exception->errorInfo[0] ?? '';

        return in_array($sqlState, ['23000', '23505'], true);
    }
}
