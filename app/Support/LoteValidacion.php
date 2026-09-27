<?php

namespace App\Support;

use App\Enums\LoteEstado;
use App\Enums\TipoHuevo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

final class LoteValidacion
{
    public const CANTIDAD_MAXIMA = 9_999_999;

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
            'fechaIngreso.before_or_equal' => 'La fecha de ingreso no puede ser futura.',
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

            if (! is_int($cantidad)) {
                throw ValidationException::withMessages([
                    'cantidad_'.$tipoValue => 'La cantidad debe ser un número entero.',
                ]);
            }

            if ($cantidad < 1) {
                throw ValidationException::withMessages([
                    'cantidad_'.$tipoValue => 'La cantidad debe ser mayor a cero.',
                ]);
            }

            if ($cantidad > self::CANTIDAD_MAXIMA) {
                throw ValidationException::withMessages([
                    'cantidad_'.$tipoValue => 'La cantidad supera el máximo permitido.',
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

    public static function assertFechaIngreso(Carbon $fechaIngreso): void
    {
        validator(
            ['fechaIngreso' => $fechaIngreso->toDateString()],
            [
                'fechaIngreso' => ['required', 'date', 'before_or_equal:today'],
            ],
            self::messages()
        )->validate();
    }

    public static function assertFechasCoherentes(Carbon $fechaNacimiento, Carbon $fechaIngreso): void
    {
        if ($fechaNacimiento->copy()->startOfDay()->gt($fechaIngreso->copy()->startOfDay())) {
            throw ValidationException::withMessages([
                'fechaNacimiento' => 'La fecha de nacimiento no puede ser posterior al ingreso.',
            ]);
        }
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

    public static function assertMotivoTransicion(string $motivo): string
    {
        $motivo = trim($motivo);

        validator(
            ['motivo' => $motivo],
            [
                'motivo' => ['required', 'string', 'min:5', 'max:500'],
            ],
            [
                'motivo.required' => 'Indicá el motivo del cambio de estado.',
                'motivo.min' => 'El motivo debe tener al menos 5 caracteres.',
                'motivo.max' => 'El motivo no puede superar los 500 caracteres.',
            ]
        )->validate();

        return $motivo;
    }

    public static function assertTransicionEstado(
        LoteEstado $estadoActual,
        LoteEstado $estadoNuevo,
        User $actor,
    ): void {
        if ($estadoActual === $estadoNuevo) {
            throw ValidationException::withMessages([
                'estado' => 'El lote ya está en ese estado.',
            ]);
        }

        if ($estadoActual->esTerminal()) {
            throw ValidationException::withMessages([
                'estado' => 'Un lote trasladado no admite más cambios de estado.',
            ]);
        }

        $puedeReabrir = $actor->rol->canReabrirLote();

        if (! $estadoActual->puedeTransicionarA($estadoNuevo, $puedeReabrir)) {
            $mensaje = $estadoActual === LoteEstado::Cerrado && ! $puedeReabrir
                ? 'La reapertura de un lote cerrado requiere perfil Dueño o Administrativo.'
                : 'La transición de estado no está permitida.';

            throw ValidationException::withMessages([
                'estado' => $mensaje,
            ]);
        }
    }
}
