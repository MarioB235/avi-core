<?php

namespace App\Support;

use App\Enums\MovimientoAvesTipo;
use App\Models\Galpon;
use App\Models\MovimientoAves;
use App\Services\MovimientoAvesConciliacionService;
use Illuminate\Validation\ValidationException;

class MovimientoAvesValidacion
{
    public static function assertEstructuraMinima(MovimientoAves $movimiento): void
    {
        if ($movimiento->cantidad < 1 && $movimiento->tipo !== MovimientoAvesTipo::Ajuste) {
            throw ValidationException::withMessages([
                'cantidad' => 'La cantidad debe ser al menos 1.',
            ]);
        }

        match ($movimiento->tipo) {
            MovimientoAvesTipo::Entrada => self::assertEntrada($movimiento),
            MovimientoAvesTipo::Traslado => self::assertTraslado($movimiento),
            MovimientoAvesTipo::Ajuste => self::assertAjuste($movimiento),
            MovimientoAvesTipo::CierreLote, MovimientoAvesTipo::Faena => self::assertSalida($movimiento),
            MovimientoAvesTipo::Reversion => self::assertReversion($movimiento),
        };

        if (trim((string) $movimiento->motivo) === '') {
            throw ValidationException::withMessages([
                'motivo' => 'El motivo es obligatorio.',
            ]);
        }

        if ($movimiento->tipo->requiereConciliacionD01()) {
            self::assertConciliacionD01($movimiento);
        }
    }

    public static function assertConciliacionD01(MovimientoAves $movimiento): void
    {
        $galpon = Galpon::query()->find($movimiento->galpon_origen_id);

        if (! $galpon instanceof Galpon) {
            throw ValidationException::withMessages([
                'galpon_origen_id' => 'No se encontró el galpón origen para conciliar.',
            ]);
        }

        $conciliacion = app(MovimientoAvesConciliacionService::class);
        $snapshot = $conciliacion->snapshot($galpon);
        $loteUnico = $conciliacion->loteActivoUnico($galpon);

        $loteId = $movimiento->lote_id ?? $loteUnico?->id;

        if ($loteId === null) {
            throw ValidationException::withMessages([
                'lote_id' => 'Con varios lotes activos debés identificar el lote del movimiento.',
            ]);
        }

        $lote = $conciliacion->assertLoteActivoEnGalpon($galpon, $loteId);

        if ($snapshot['multiples_lotes']) {
            $metadata = is_array($movimiento->metadata) ? $movimiento->metadata : [];
            $muertesImputadas = $metadata['muertes_imputadas_lote'] ?? null;
            $descarteImputado = (int) ($metadata['descarte_imputado_lote'] ?? 0);

            if ($muertesImputadas === null || ! is_numeric($muertesImputadas)) {
                throw ValidationException::withMessages([
                    'muertes_imputadas_lote' => 'Conciliá las muertes del galpón imputadas a este lote antes de trasladar o cerrar.',
                ]);
            }

            $muertesImputadas = (int) $muertesImputadas;

            if ($muertesImputadas < 0 || $descarteImputado < 0) {
                throw ValidationException::withMessages([
                    'muertes_imputadas_lote' => 'Las imputaciones no pueden ser negativas.',
                ]);
            }

            if ($muertesImputadas > $snapshot['muertes_galpon']) {
                throw ValidationException::withMessages([
                    'muertes_imputadas_lote' => 'No podés imputar más muertes que las registradas en el galpón ('.$snapshot['muertes_galpon'].').',
                ]);
            }

            if ($descarteImputado > $snapshot['descarte_galpon']) {
                throw ValidationException::withMessages([
                    'descarte_imputado_lote' => 'No podés imputar más descarte que el registrado en el galpón ('.$snapshot['descarte_galpon'].').',
                ]);
            }

            $saldoDeclarado = $conciliacion->saldoDeclaradoLoteEnGalpon(
                $galpon,
                $lote,
                $muertesImputadas,
                $descarteImputado,
            );

            if ($movimiento->cantidad > $saldoDeclarado) {
                throw ValidationException::withMessages([
                    'cantidad' => 'La cantidad supera el saldo declarado del lote en este galpón ('.$saldoDeclarado.' aves).',
                ]);
            }

            return;
        }

        if ($movimiento->cantidad > (int) $galpon->aves_actuales) {
            throw ValidationException::withMessages([
                'cantidad' => 'La cantidad supera el saldo vivo del galpón ('.$galpon->aves_actuales.' aves).',
            ]);
        }
    }

    private static function assertEntrada(MovimientoAves $movimiento): void
    {
        if (! $movimiento->galpon_destino_id) {
            throw ValidationException::withMessages([
                'galpon_destino_id' => 'La entrada requiere galpón destino.',
            ]);
        }
    }

    private static function assertTraslado(MovimientoAves $movimiento): void
    {
        if (! $movimiento->galpon_origen_id || ! $movimiento->galpon_destino_id) {
            throw ValidationException::withMessages([
                'galpon' => 'El traslado requiere galpón origen y destino.',
            ]);
        }

        if ($movimiento->galpon_origen_id === $movimiento->galpon_destino_id) {
            throw ValidationException::withMessages([
                'galpon_destino_id' => 'El traslado no puede tener el mismo origen y destino.',
            ]);
        }
    }

    private static function assertAjuste(MovimientoAves $movimiento): void
    {
        if (! $movimiento->galpon_origen_id) {
            throw ValidationException::withMessages([
                'galpon_origen_id' => 'El ajuste requiere galpón.',
            ]);
        }

        if ($movimiento->ajuste_delta === null || $movimiento->ajuste_delta === 0) {
            throw ValidationException::withMessages([
                'ajuste_delta' => 'El ajuste requiere una diferencia distinta de cero.',
            ]);
        }

        if ($movimiento->cantidad !== abs((int) $movimiento->ajuste_delta)) {
            throw ValidationException::withMessages([
                'cantidad' => 'La cantidad del ajuste debe coincidir con el valor absoluto del delta.',
            ]);
        }
    }

    private static function assertSalida(MovimientoAves $movimiento): void
    {
        if (! $movimiento->galpon_origen_id) {
            throw ValidationException::withMessages([
                'galpon_origen_id' => 'La salida requiere galpón origen.',
            ]);
        }
    }

    private static function assertReversion(MovimientoAves $movimiento): void
    {
        if (! $movimiento->reversa_de_id) {
            throw ValidationException::withMessages([
                'reversa_de_id' => 'La reversión debe referenciar el movimiento original.',
            ]);
        }
    }
}
