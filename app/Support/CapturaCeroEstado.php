<?php

namespace App\Support;

use App\Enums\RegistroOperativoTipo;
use App\Models\RegistroOperativo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

final class CapturaCeroEstado
{
    public const OMISION = 'omision';

    public const CERO_CONFIRMADO = 'cero_confirmado';

    public const REGISTRADO = 'registrado';

    public static function tiposConCeroConfirmado(): array
    {
        return [
            RegistroOperativoTipo::Huevos,
            RegistroOperativoTipo::Muertes,
            RegistroOperativoTipo::Descarte,
        ];
    }

    public static function assertTipoPermiteCeroConfirmado(RegistroOperativoTipo $tipo): void
    {
        if (! in_array($tipo, self::tiposConCeroConfirmado(), true)) {
            throw ValidationException::withMessages([
                'tipo' => 'Este tipo de carga no admite confirmación de cero.',
            ]);
        }
    }

    /**
     * @param  Collection<int, RegistroOperativo>  $registros
     */
    public static function resolverEstadoDia(Collection $registros, RegistroOperativoTipo $tipo): string
    {
        if ($registros->isEmpty()) {
            return self::OMISION;
        }

        if (self::sumaCantidades($registros, $tipo) > 0) {
            return self::REGISTRADO;
        }

        if ($registros->contains(fn (RegistroOperativo $registro): bool => $registro->cero_confirmado)) {
            return self::CERO_CONFIRMADO;
        }

        return self::OMISION;
    }

    public static function etiquetaEstado(string $estado): string
    {
        return match ($estado) {
            self::CERO_CONFIRMADO => '0 confirmado',
            self::REGISTRADO => 'Con registro',
            default => 'Sin registro',
        };
    }

    /**
     * @param  Collection<int, RegistroOperativo>  $registros
     */
    private static function sumaCantidades(Collection $registros, RegistroOperativoTipo $tipo): int|float
    {
        return match ($tipo) {
            RegistroOperativoTipo::Huevos => (int) $registros->sum(
                fn (RegistroOperativo $registro): int => (int) $registro->huevos + (int) $registro->huevos_descarte
            ),
            RegistroOperativoTipo::Muertes => (int) $registros->sum('muertes'),
            RegistroOperativoTipo::Descarte => (int) $registros->sum('descarte_aves'),
            RegistroOperativoTipo::Alimento => (float) $registros->sum('alimento_kg'),
            default => 0,
        };
    }
}
