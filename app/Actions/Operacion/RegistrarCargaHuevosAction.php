<?php

namespace App\Actions\Operacion;

use App\Enums\RegistroOperativoEstado;
use App\Enums\RegistroOperativoTipo;
use App\Models\Galpon;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Services\EmpresaRelationalGuard;
use App\Support\GalponValidacion;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class RegistrarCargaHuevosAction
{
    public function __construct(private EmpresaRelationalGuard $relations) {}

    public function execute(
        User $user,
        Galpon $galpon,
        int $huevosAptos,
        int $huevosDescarte = 0,
        ?string $observacion = null,
        ?string $idempotenciaClave = null,
    ): RegistroOperativo {
        Gate::forUser($user)->authorize('view', $galpon);

        $this->relations->assertGalponOfActor($user, $galpon);

        GalponValidacion::assertDisponibleParaCarga($galpon);
        GalponValidacion::assertLoteActivoParaCargaProductiva($galpon);

        if ($huevosAptos < 0 || $huevosDescarte < 0) {
            throw ValidationException::withMessages([
                'huevos' => 'Las cantidades no pueden ser negativas.',
            ]);
        }

        if ($huevosAptos + $huevosDescarte < 1) {
            throw ValidationException::withMessages([
                'huevos' => 'Ingresá al menos un huevo apto o de descarte.',
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
            return RegistroOperativo::query()->create([
                'empresa_id' => $user->empresa_id,
                'galpon_id' => $galpon->id,
                'user_id' => $user->id,
                'tipo' => RegistroOperativoTipo::Huevos,
                'idempotencia_clave' => $clave,
                'huevos' => $huevosAptos,
                'huevos_descarte' => $huevosDescarte > 0 ? $huevosDescarte : null,
                'observacion' => $observacion !== '' ? $observacion : null,
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

    private function buscarPorClaveIdempotencia(User $user, string $clave): ?RegistroOperativo
    {
        if ($user->empresa_id === null) {
            return null;
        }

        return RegistroOperativo::query()
            ->forEmpresa((int) $user->empresa_id)
            ->where('idempotencia_clave', $clave)
            ->where('tipo', RegistroOperativoTipo::Huevos)
            ->first();
    }

    private function esViolacionUnicaIdempotencia(QueryException $exception): bool
    {
        $sqlState = $exception->errorInfo[0] ?? '';

        return in_array($sqlState, ['23000', '23505'], true);
    }
}
