<?php

namespace App\Support;

use App\Enums\RegistroOperativoTipo;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Models\Vacunacion;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

final class IdempotenciaCaptura
{
    public static function generarClave(): string
    {
        return (string) Str::uuid();
    }

    public static function normalizarClave(?string $clave): ?string
    {
        $clave = $clave !== null ? trim($clave) : '';

        return $clave !== '' ? $clave : null;
    }

    public static function esViolacionUnica(QueryException $exception): bool
    {
        $sqlState = $exception->errorInfo[0] ?? '';

        return in_array($sqlState, ['23000', '23505'], true);
    }

    /**
     * @param  callable(?string): RegistroOperativo  $crear
     */
    public static function resolverRegistroOperativo(
        User $user,
        ?string $idempotenciaClave,
        RegistroOperativoTipo $tipo,
        callable $crear,
    ): RegistroOperativo {
        $clave = self::normalizarClave($idempotenciaClave);

        if ($clave !== null) {
            $existente = self::buscarRegistroOperativo($user, $clave, $tipo);

            if ($existente !== null) {
                return $existente;
            }
        }

        try {
            return $crear($clave);
        } catch (QueryException $exception) {
            if ($clave !== null && self::esViolacionUnica($exception)) {
                $existente = self::buscarRegistroOperativo($user, $clave, $tipo);

                if ($existente !== null) {
                    return $existente;
                }
            }

            throw $exception;
        }
    }

    /**
     * @param  callable(?string): Vacunacion  $crear
     */
    public static function resolverVacunacion(
        User $user,
        ?string $idempotenciaClave,
        callable $crear,
    ): Vacunacion {
        $clave = self::normalizarClave($idempotenciaClave);

        if ($clave !== null) {
            $existente = self::buscarVacunacion($user, $clave);

            if ($existente !== null) {
                return $existente;
            }
        }

        try {
            return $crear($clave);
        } catch (QueryException $exception) {
            if ($clave !== null && self::esViolacionUnica($exception)) {
                $existente = self::buscarVacunacion($user, $clave);

                if ($existente !== null) {
                    return $existente;
                }
            }

            throw $exception;
        }
    }

    private static function buscarRegistroOperativo(
        User $user,
        string $clave,
        RegistroOperativoTipo $tipo,
    ): ?RegistroOperativo {
        if ($user->empresa_id === null) {
            return null;
        }

        return RegistroOperativo::query()
            ->forEmpresa((int) $user->empresa_id)
            ->where('idempotencia_clave', $clave)
            ->where('tipo', $tipo)
            ->first();
    }

    private static function buscarVacunacion(User $user, string $clave): ?Vacunacion
    {
        if ($user->empresa_id === null) {
            return null;
        }

        return Vacunacion::query()
            ->forEmpresa((int) $user->empresa_id)
            ->where('idempotencia_clave', $clave)
            ->first();
    }
}
