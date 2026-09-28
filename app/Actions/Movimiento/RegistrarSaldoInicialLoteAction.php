<?php

namespace App\Actions\Movimiento;

use App\Enums\MovimientoAvesEstado;
use App\Enums\MovimientoAvesOrigen;
use App\Enums\MovimientoAvesTipo;
use App\Models\Galpon;
use App\Models\Lote;
use App\Models\MovimientoAves;
use App\Models\User;
use App\Support\IdempotenciaMovimiento;
use App\Support\MovimientoAvesValidacion;
use Illuminate\Support\Carbon;

class RegistrarSaldoInicialLoteAction
{
    /**
     * Ledger de saldo inicial al alta de lote (MOV-03). No incrementa `aves_actuales`
     * — eso lo hace `RegistrarLoteAction` una sola vez.
     */
    public function execute(User $user, Lote $lote, Galpon $galpon, int $cantidad): MovimientoAves
    {
        $clave = IdempotenciaMovimiento::claveSaldoInicialLote($lote->id);

        return IdempotenciaMovimiento::resolver($user, $clave, function (?string $idempotenciaClave) use ($user, $lote, $galpon, $cantidad): MovimientoAves {
            $movimiento = new MovimientoAves([
                'empresa_id' => $lote->empresa_id,
                'tipo' => MovimientoAvesTipo::Entrada,
                'estado' => MovimientoAvesEstado::Activo,
                'galpon_destino_id' => $galpon->id,
                'lote_id' => $lote->id,
                'cantidad' => $cantidad,
                'motivo' => 'Saldo inicial del lote '.$lote->codigo,
                'registrado_por' => $user->id,
                'fecha_efectiva' => Carbon::parse($lote->fecha_ingreso)->startOfDay(),
                'metadata' => [
                    'origen' => MovimientoAvesOrigen::SaldoInicialLote->value,
                    'impacta_aves_actuales' => false,
                    'lote_codigo' => $lote->codigo,
                ],
                'idempotencia_clave' => $idempotenciaClave,
            ]);

            MovimientoAvesValidacion::assertEstructuraMinima($movimiento);
            $movimiento->save();

            return $movimiento;
        });
    }
}
