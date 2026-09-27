<?php

namespace App\Actions\Operacion;

use App\Enums\RegistroOperativoEstado;
use App\Enums\RegistroOperativoTipo;
use App\Models\Galpon;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Services\EmpresaRelationalGuard;
use App\Support\AlimentoValidacion;
use App\Support\GalponValidacion;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Gate;

class RegistrarCargaAlimentoAction
{
    public function __construct(private EmpresaRelationalGuard $relations) {}

    public function execute(
        User $user,
        Galpon $galpon,
        float $alimentoKg,
        ?string $observacion = null,
        ?string $idempotenciaClave = null,
    ): RegistroOperativo {
        Gate::forUser($user)->authorize('view', $galpon);

        $this->relations->assertGalponOfActor($user, $galpon);

        GalponValidacion::assertDisponibleParaCarga($galpon);

        AlimentoValidacion::assertRango($alimentoKg);

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
                'tipo' => RegistroOperativoTipo::Alimento,
                'idempotencia_clave' => $clave,
                'alimento_kg' => round($alimentoKg, 2),
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
            ->where('tipo', RegistroOperativoTipo::Alimento)
            ->first();
    }

    private function esViolacionUnicaIdempotencia(QueryException $exception): bool
    {
        $sqlState = $exception->errorInfo[0] ?? '';

        return in_array($sqlState, ['23000', '23505'], true);
    }
}
