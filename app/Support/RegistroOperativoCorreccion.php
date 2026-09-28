<?php

namespace App\Support;

use App\Enums\RegistroOperativoTipo;
use App\Models\RegistroOperativo;
use Illuminate\Validation\ValidationException;

class RegistroOperativoCorreccion
{
    /**
     * @return array<string, int|float|bool>
     */
    public static function snapshot(RegistroOperativo $registro): array
    {
        return match ($registro->tipo) {
            RegistroOperativoTipo::Huevos => [
                'huevos' => (int) $registro->huevos,
                'huevos_descarte' => (int) $registro->huevos_descarte,
                'cero_confirmado' => (bool) $registro->cero_confirmado,
            ],
            RegistroOperativoTipo::Muertes => [
                'muertes' => self::cantidadMuertesEfectiva($registro),
                'cero_confirmado' => (bool) $registro->cero_confirmado,
            ],
            RegistroOperativoTipo::Descarte => [
                'descarte_aves' => self::cantidadDescarteEfectiva($registro),
                'cero_confirmado' => (bool) $registro->cero_confirmado,
            ],
            RegistroOperativoTipo::Alimento => [
                'alimento_kg' => (float) $registro->alimento_kg,
            ],
            RegistroOperativoTipo::Combinado => [],
        };
    }

    public static function assertTipoCorregible(RegistroOperativo $registro): void
    {
        if ($registro->tipo === RegistroOperativoTipo::Combinado) {
            throw ValidationException::withMessages([
                'correccion' => 'Los registros combinados (legado) no admiten corrección; anulá y volvé a cargar por tipo.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, int|float|bool>
     */
    public static function normalizarEntrada(RegistroOperativo $registro, array $input): array
    {
        self::assertTipoCorregible($registro);

        return match ($registro->tipo) {
            RegistroOperativoTipo::Huevos => self::normalizarHuevos($input),
            RegistroOperativoTipo::Muertes => self::normalizarMuertes($input),
            RegistroOperativoTipo::Descarte => self::normalizarDescarte($input),
            RegistroOperativoTipo::Alimento => self::normalizarAlimento($input),
            default => throw ValidationException::withMessages([
                'correccion' => 'Este tipo de registro no admite corrección.',
            ]),
        };
    }

    /**
     * @param  array<string, int|float|bool>  $valores
     */
    public static function cantidadAvesDebitadas(RegistroOperativoTipo $tipo, array $valores): int
    {
        return match ($tipo) {
            RegistroOperativoTipo::Muertes => ($valores['cero_confirmado'] ?? false)
                ? 0
                : (int) ($valores['muertes'] ?? 0),
            RegistroOperativoTipo::Descarte => ($valores['cero_confirmado'] ?? false)
                ? 0
                : (int) ($valores['descarte_aves'] ?? 0),
            default => 0,
        };
    }

    /**
     * @param  array<string, int|float|bool>  $valores
     */
    public static function aplicarAlRegistro(RegistroOperativo $registro, array $valores): void
    {
        $datos = match ($registro->tipo) {
            RegistroOperativoTipo::Huevos => [
                'huevos' => (int) $valores['huevos'],
                'huevos_descarte' => (int) $valores['huevos_descarte'],
                'cero_confirmado' => (bool) ($valores['cero_confirmado'] ?? false),
            ],
            RegistroOperativoTipo::Muertes => [
                'muertes' => (int) $valores['muertes'],
                'cero_confirmado' => (bool) ($valores['cero_confirmado'] ?? false),
            ],
            RegistroOperativoTipo::Descarte => [
                'descarte_aves' => (int) $valores['descarte_aves'],
                'cero_confirmado' => (bool) ($valores['cero_confirmado'] ?? false),
            ],
            RegistroOperativoTipo::Alimento => [
                'alimento_kg' => $valores['alimento_kg'],
            ],
            default => [],
        };

        if ($datos === []) {
            return;
        }

        $registro->forceFill($datos)->save();
    }

    /**
     * @return list<string>
     */
    public static function camposFormulario(RegistroOperativoTipo $tipo): array
    {
        return match ($tipo) {
            RegistroOperativoTipo::Huevos => ['huevos', 'huevos_descarte'],
            RegistroOperativoTipo::Muertes => ['muertes'],
            RegistroOperativoTipo::Descarte => ['descarte_aves'],
            RegistroOperativoTipo::Alimento => ['alimento_kg'],
            default => [],
        };
    }

    public static function cantidadMuertesEfectiva(RegistroOperativo $registro): int
    {
        if ($registro->cero_confirmado) {
            return 0;
        }

        return (int) $registro->muertes;
    }

    public static function cantidadDescarteEfectiva(RegistroOperativo $registro): int
    {
        if ($registro->cero_confirmado) {
            return 0;
        }

        return (int) $registro->descarte_aves;
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, int|bool>
     */
    private static function normalizarHuevos(array $input): array
    {
        if (! array_key_exists('huevos', $input) || ! array_key_exists('huevos_descarte', $input)) {
            throw ValidationException::withMessages([
                'corregirHuevos' => 'Ingresá huevos aptos y descarte.',
            ]);
        }

        $huevos = filter_var($input['huevos'], FILTER_VALIDATE_INT);
        $descarte = filter_var($input['huevos_descarte'], FILTER_VALIDATE_INT);

        if ($huevos === false || $descarte === false || $huevos < 0 || $descarte < 0) {
            throw ValidationException::withMessages([
                'corregirHuevos' => 'Las cantidades de huevos deben ser números enteros mayores o iguales a cero.',
            ]);
        }

        return [
            'huevos' => $huevos,
            'huevos_descarte' => $descarte,
            'cero_confirmado' => $huevos === 0 && $descarte === 0,
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, int|bool>
     */
    private static function normalizarMuertes(array $input): array
    {
        if (! array_key_exists('muertes', $input)) {
            throw ValidationException::withMessages([
                'corregirMuertes' => 'Ingresá la cantidad corregida de muertes.',
            ]);
        }

        $muertes = filter_var($input['muertes'], FILTER_VALIDATE_INT);

        if ($muertes === false || $muertes < 0) {
            throw ValidationException::withMessages([
                'corregirMuertes' => 'La cantidad de muertes debe ser un número entero mayor o igual a cero.',
            ]);
        }

        return [
            'muertes' => $muertes,
            'cero_confirmado' => $muertes === 0,
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, int|bool>
     */
    private static function normalizarDescarte(array $input): array
    {
        if (! array_key_exists('descarte_aves', $input)) {
            throw ValidationException::withMessages([
                'corregirDescarteAves' => 'Ingresá la cantidad corregida de descarte de aves.',
            ]);
        }

        $descarte = filter_var($input['descarte_aves'], FILTER_VALIDATE_INT);

        if ($descarte === false || $descarte < 0) {
            throw ValidationException::withMessages([
                'corregirDescarteAves' => 'La cantidad debe ser un número entero mayor o igual a cero.',
            ]);
        }

        return [
            'descarte_aves' => $descarte,
            'cero_confirmado' => $descarte === 0,
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, float>
     */
    private static function normalizarAlimento(array $input): array
    {
        if (! array_key_exists('alimento_kg', $input)) {
            throw ValidationException::withMessages([
                'corregirAlimentoKg' => 'Ingresá los kilogramos corregidos.',
            ]);
        }

        $valor = str_replace(',', '.', trim((string) $input['alimento_kg']));

        if ($valor === '' || ! is_numeric($valor) || (float) $valor < 0) {
            throw ValidationException::withMessages([
                'corregirAlimentoKg' => 'Los kilogramos deben ser un número mayor o igual a cero.',
            ]);
        }

        return [
            'alimento_kg' => round((float) $valor, 2),
        ];
    }
}
