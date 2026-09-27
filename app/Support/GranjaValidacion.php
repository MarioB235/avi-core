<?php

namespace App\Support;

use Illuminate\Validation\Rule;

final class GranjaValidacion
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(int $empresaId, ?int $ignoreGranjaId = null): array
    {
        $dicoseUnique = Rule::unique('granjas', 'dicose')->where('empresa_id', $empresaId);
        $codigoUnique = Rule::unique('granjas', 'codigo')->where('empresa_id', $empresaId);

        if ($ignoreGranjaId !== null) {
            $dicoseUnique->ignore($ignoreGranjaId);
            $codigoUnique->ignore($ignoreGranjaId);
        }

        return [
            'nombre' => ['required', 'string', 'min:2', 'max:120'],
            'codigo' => ['nullable', 'string', 'max:50', $codigoUnique],
            'dicose' => ['nullable', 'string', 'max:20', 'regex:/^[\d\-]+$/', $dicoseUnique],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'activa' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'nombre.required' => 'Indicá el nombre de la granja.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'codigo.unique' => 'Ya existe una granja con ese código interno en la empresa.',
            'dicose.unique' => 'Ya existe una granja con ese DICOSE en la empresa.',
            'dicose.regex' => 'El DICOSE solo puede contener números y guiones.',
        ];
    }

    /**
     * @param  array{nombre: string, codigo?: string|null, dicose?: string|null, ubicacion?: string|null, activa?: bool}  $data
     * @return array{nombre: string, codigo: ?string, dicose: ?string, ubicacion: ?string, activa: bool}
     */
    public static function normalize(array $data): array
    {
        $nombre = trim($data['nombre']);
        $codigo = self::nullableTrim($data['codigo'] ?? null);
        $dicose = self::nullableTrim($data['dicose'] ?? null);
        $ubicacion = self::nullableTrim($data['ubicacion'] ?? null);

        if ($dicose !== null) {
            $dicose = str_replace(' ', '', $dicose);
        }

        return [
            'nombre' => $nombre,
            'codigo' => $codigo,
            'dicose' => $dicose,
            'ubicacion' => $ubicacion,
            'activa' => array_key_exists('activa', $data) ? (bool) $data['activa'] : true,
        ];
    }

    private static function nullableTrim(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
