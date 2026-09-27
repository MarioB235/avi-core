<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

class AlimentoValidacion
{
    public const MIN_KG = 0.01;

    /** Máximo por entrega (decimal 10,2 en BD; evita errores de tipeo). */
    public const MAX_KG = 999_999.99;

    public static function parseKg(string $valor): ?float
    {
        $valor = trim(str_replace(' ', '', $valor));

        if ($valor === '') {
            return null;
        }

        $tieneComa = str_contains($valor, ',');
        $tienePunto = str_contains($valor, '.');

        if ($tieneComa && $tienePunto) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        } elseif ($tieneComa) {
            $valor = str_replace(',', '.', $valor);
        }

        if (! is_numeric($valor)) {
            return null;
        }

        return (float) $valor;
    }

    public static function assertRango(float $kg): void
    {
        if ($kg < self::MIN_KG) {
            throw ValidationException::withMessages([
                'alimentoKg' => 'Los kilos deben ser mayor a cero.',
            ]);
        }

        if ($kg > self::MAX_KG) {
            throw ValidationException::withMessages([
                'alimentoKg' => 'Los kilos no pueden superar '.number_format(self::MAX_KG, 2, ',', '.').' por entrega.',
            ]);
        }
    }
}
