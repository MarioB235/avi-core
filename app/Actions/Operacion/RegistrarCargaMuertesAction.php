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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class RegistrarCargaMuertesAction
{
    public function __construct(private EmpresaRelationalGuard $relations) {}

    public function execute(
        User $user,
        Galpon $galpon,
        int $muertes,
        ?string $observacion = null,
        ?string $idempotenciaClave = null,
    ): RegistroOperativo {
        Gate::forUser($user)->authorize('view', $galpon);

        $this->relations->assertGalponOfActor($user, $galpon);

        GalponValidacion::assertDisponibleParaCarga($galpon);
        GalponValidacion::assertLoteActivoParaCargaProductiva($galpon);

        if ($muertes < 1) {
            throw ValidationException::withMessages([
                'muertes' => 'La cantidad de muertes debe ser mayor a cero.',
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
            return DB::transaction(function () use ($user, $galpon, $muertes, $observacion, $clave): RegistroOperativo {
                $galponBloqueado = Galpon::query()
                    ->whereKey($galpon->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($muertes > $galponBloqueado->aves_actuales) {
                    throw ValidationException::withMessages([
                        'muertes' => 'La cantidad supera las aves vivas del galpón ('.number_format($galponBloqueado->aves_actuales, 0, ',', '.').').',
                    ]);
                }

                $registro = RegistroOperativo::query()->create([
                    'empresa_id' => $user->empresa_id,
                    'galpon_id' => $galponBloqueado->id,
                    'user_id' => $user->id,
                    'tipo' => RegistroOperativoTipo::Muertes,
                    'idempotencia_clave' => $clave,
                    'muertes' => $muertes,
                    'observacion' => $observacion !== '' ? $observacion : null,
                    'estado' => RegistroOperativoEstado::Activo,
                ]);

                $galponBloqueado->decrement('aves_actuales', $muertes);

                return $registro;
            });
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
            ->where('tipo', RegistroOperativoTipo::Muertes)
            ->first();
    }

    private function esViolacionUnicaIdempotencia(QueryException $exception): bool
    {
        $sqlState = $exception->errorInfo[0] ?? '';

        return in_array($sqlState, ['23000', '23505'], true);
    }
}
