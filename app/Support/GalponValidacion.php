<?php

namespace App\Support;

use App\Enums\GalponEstado;
use App\Models\Galpon;
use App\Models\Granja;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class GalponValidacion
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(int $granjaId, ?int $ignoreGalponId = null): array
    {
        $codigoUnique = Rule::unique('galpones', 'codigo')->where('granja_id', $granjaId);

        if ($ignoreGalponId !== null) {
            $codigoUnique->ignore($ignoreGalponId);
        }

        return [
            'granja_id' => ['required', 'integer'],
            'nombre' => ['required', 'string', 'min:2', 'max:120'],
            'codigo' => ['nullable', 'string', 'max:50', $codigoUnique],
            'capacidad' => ['nullable', 'integer', 'min:1'],
            'estado' => ['required', Rule::enum(GalponEstado::class)],
            'observacion' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'nombre.required' => 'Indicá el nombre del galpón.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'codigo.unique' => 'Ya existe un galpón con ese código en la granja.',
            'granja_id.required' => 'Elegí una granja.',
        ];
    }

    /**
     * @param  array{granja_id: int, nombre: string, codigo?: string|null, capacidad?: int|null, estado: string, observacion?: string|null, activo?: bool}  $data
     * @return array{granja_id: int, nombre: string, codigo: ?string, capacidad: ?int, estado: GalponEstado, observacion: ?string, activo: bool}
     */
    public static function normalize(array $data, bool $forUpdate = false): array
    {
        $estado = GalponEstado::from($data['estado']);
        $activo = array_key_exists('activo', $data) ? (bool) $data['activo'] : true;

        if (! $estado->permiteCarga()) {
            $activo = false;
        }

        $capacidad = $data['capacidad'] ?? null;

        return [
            'granja_id' => (int) $data['granja_id'],
            'nombre' => trim($data['nombre']),
            'codigo' => self::nullableTrim($data['codigo'] ?? null),
            'capacidad' => $capacidad !== null && $capacidad !== '' ? (int) $capacidad : null,
            'estado' => $estado,
            'observacion' => self::nullableTrim($data['observacion'] ?? null),
            'activo' => $activo,
        ];
    }

    public static function assertGranjaActiva(Granja $granja): void
    {
        if (! $granja->activa) {
            throw ValidationException::withMessages([
                'granja_id' => 'La granja seleccionada no está activa.',
            ]);
        }
    }

    public static function assertDisponibleParaCarga(Galpon $galpon, string $field = 'galpon_id'): void
    {
        if (! $galpon->disponibleParaCargaOperativa()) {
            throw ValidationException::withMessages([
                $field => 'El galpón no está disponible para carga.',
            ]);
        }
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
