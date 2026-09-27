<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

class VacunacionValidacion
{
    public const OBSERVACION_MAX = 500;

    public static function normalizarObservacion(?string $observacion): ?string
    {
        $observacion = $observacion !== null ? trim($observacion) : '';

        return $observacion !== '' ? $observacion : null;
    }

    public static function assertObservacion(?string $observacion): void
    {
        $observacion = self::normalizarObservacion($observacion);

        if ($observacion !== null && mb_strlen($observacion) > self::OBSERVACION_MAX) {
            throw ValidationException::withMessages([
                'observacionVacunacion' => 'La observación no puede superar los '.self::OBSERVACION_MAX.' caracteres.',
            ]);
        }
    }
}
