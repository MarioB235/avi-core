<?php

namespace App\Support;

use App\Enums\TipoHuevo;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

final class LoteValidacion
{
    /**
     * @return array<string, mixed>
     */
    public static function rulesCodigoSma(): array
    {
        return [
            'codigo_sma' => ['nullable', 'string', 'max:64', 'regex:/^[\pL\pN\-_.\/]+$/u'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function rulesFechaNacimiento(): array
    {
        return [
            'fechaNacimiento' => ['required', 'date', 'before_or_equal:today'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'fechaNacimiento.required' => 'Ingresá la fecha aproximada de nacimiento.',
            'fechaNacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'codigo_sma.max' => 'El código SMA no puede superar los 64 caracteres.',
            'codigo_sma.regex' => 'El código SMA solo puede tener letras, números y los símbolos - _ . /',
        ];
    }

    /**
     * @param  array<string, int>  $cantidadesPorTipo
     */
    public static function assertCantidadesPorTipo(array $cantidadesPorTipo): void
    {
        if ($cantidadesPorTipo === []) {
            throw ValidationException::withMessages([
                'tiposHuevo' => 'Marcá al menos un tipo de ave.',
            ]);
        }

        foreach ($cantidadesPorTipo as $tipoValue => $cantidad) {
            if (TipoHuevo::tryFrom($tipoValue) === null) {
                throw ValidationException::withMessages([
                    'tiposHuevo' => 'El tipo de ave no es válido.',
                ]);
            }

            if ($cantidad < 1) {
                throw ValidationException::withMessages([
                    'cantidad_'.$tipoValue => 'La cantidad debe ser mayor a cero.',
                ]);
            }
        }
    }

    public static function assertFechaNacimiento(Carbon $fechaNacimiento): void
    {
        validator(
            ['fechaNacimiento' => $fechaNacimiento->toDateString()],
            self::rulesFechaNacimiento(),
            self::messages()
        )->validate();
    }

    public static function normalizeCodigoSma(?string $codigoSma): ?string
    {
        if ($codigoSma === null) {
            return null;
        }

        $trimmed = trim($codigoSma);

        return $trimmed === '' ? null : $trimmed;
    }

    public static function assertCodigoSma(?string $codigoSma): ?string
    {
        $normalized = self::normalizeCodigoSma($codigoSma);

        validator(
            ['codigo_sma' => $normalized],
            self::rulesCodigoSma(),
            self::messages()
        )->validate();

        return $normalized;
    }
}
