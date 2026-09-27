<?php

namespace App\Support;

use App\Models\Galpon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

final class EstructuraValidacion
{
    public static function assertSinCambioEmpresa(Model $entity, ?int $nuevaEmpresaId, string $field = 'empresa_id'): void
    {
        if ($nuevaEmpresaId === null) {
            return;
        }

        if ((int) $nuevaEmpresaId !== (int) $entity->getAttribute('empresa_id')) {
            throw ValidationException::withMessages([
                $field => 'No se puede cambiar la empresa de un registro con trazabilidad.',
            ]);
        }
    }

    public static function assertReasignacionGranjaGalponSegura(Galpon $galpon, int $nuevaGranjaId): void
    {
        if ($nuevaGranjaId === $galpon->granja_id) {
            return;
        }

        if ($galpon->tieneHistorialTrazable()) {
            throw ValidationException::withMessages([
                'granja_id' => 'No podés cambiar la granja: el galpón ya tiene lotes o registros operativos.',
            ]);
        }
    }

    public static function assertSinReasignacionPadre(array $data, string $field): void
    {
        if (array_key_exists($field, $data)) {
            throw ValidationException::withMessages([
                $field => 'No se puede reasignar este registro; conservá la trazabilidad histórica.',
            ]);
        }
    }
}
