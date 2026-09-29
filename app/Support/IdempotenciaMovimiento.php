<?php

namespace App\Support;

use App\Models\MovimientoAves;
use App\Models\User;
use Illuminate\Database\QueryException;

final class IdempotenciaMovimiento
{
    public static function claveSaldoInicialLote(int $loteId): string
    {
        return 'saldo-inicial-lote:'.$loteId;
    }

    public static function claveReaperturaLote(int $loteId): string
    {
        return 'reapertura-lote:'.$loteId;
    }

    public static function claveReversionMovimiento(int $movimientoId): string
    {
        return 'reversion-movimiento:'.$movimientoId;
    }

    public static function normalizarClave(?string $clave): ?string
    {
        $clave = $clave !== null ? trim($clave) : '';

        return $clave !== '' ? $clave : null;
    }

    public static function buscarExistente(User $user, ?string $idempotenciaClave): ?MovimientoAves
    {
        $clave = self::normalizarClave($idempotenciaClave);

        if ($clave === null) {
            return null;
        }

        return self::buscar($user, $clave);
    }

    /**
     * @param  callable(?string): MovimientoAves  $crear
     */
    public static function resolver(User $user, ?string $idempotenciaClave, callable $crear): MovimientoAves
    {
        $clave = self::normalizarClave($idempotenciaClave);

        if ($clave !== null) {
            $existente = self::buscar($user, $clave);

            if ($existente !== null) {
                return $existente;
            }
        }

        try {
            return $crear($clave);
        } catch (QueryException $exception) {
            if ($clave !== null && IdempotenciaCaptura::esViolacionUnica($exception)) {
                $existente = self::buscar($user, $clave);

                if ($existente !== null) {
                    return $existente;
                }
            }

            throw $exception;
        }
    }

    private static function buscar(User $user, string $clave): ?MovimientoAves
    {
        if ($user->empresa_id === null) {
            return null;
        }

        return MovimientoAves::query()
            ->forEmpresa((int) $user->empresa_id)
            ->where('idempotencia_clave', $clave)
            ->first();
    }
}
