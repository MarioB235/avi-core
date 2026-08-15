<?php

namespace App\Support;

use App\Enums\RegistroOperativoEstado;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Models\Vacunacion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;

readonly class OperarioHistorialItem
{
    /**
     * @param  list<array{label: string, value: string}>  $detalleLineas
     */
    public function __construct(
        public string $key,
        public string $sourceType,
        public int $sourceId,
        public Carbon $createdAt,
        public string $label,
        public ?string $observacion,
        public bool $esMortalidad,
        public bool $esVacunacion,
        public string $tipoEtiqueta,
        public string $galponEtiqueta,
        public bool $anulado,
        public bool $puedeAnular,
        public ?string $motivoAnulacion,
        public array $detalleLineas,
    ) {}

    public static function fromRegistro(RegistroOperativo $registro, User $viewer): self
    {
        $registro->loadMissing('galpon');

        return new self(
            key: 'registro-'.$registro->id,
            sourceType: 'registro',
            sourceId: $registro->id,
            createdAt: $registro->created_at,
            label: $registro->cantidadResumen(),
            observacion: $registro->observacion,
            esMortalidad: $registro->esMortalidad(),
            esVacunacion: false,
            tipoEtiqueta: $registro->tipo->label(),
            galponEtiqueta: $registro->galpon?->displayName() ?? '—',
            anulado: $registro->estado === RegistroOperativoEstado::Anulado,
            puedeAnular: Gate::forUser($viewer)->allows('anular', $registro),
            motivoAnulacion: $registro->motivo_anulacion,
            detalleLineas: $registro->lineasDetalle(),
        );
    }

    public static function fromVacunacion(Vacunacion $vacunacion, User $viewer): self
    {
        $vacunacion->loadMissing(['lote', 'galpon']);

        return new self(
            key: 'vacunacion-'.$vacunacion->id,
            sourceType: 'vacunacion',
            sourceId: $vacunacion->id,
            createdAt: $vacunacion->created_at,
            label: $vacunacion->cantidadResumen(),
            observacion: $vacunacion->observacion,
            esMortalidad: false,
            esVacunacion: true,
            tipoEtiqueta: 'Vacunación',
            galponEtiqueta: $vacunacion->galpon?->displayName() ?? '—',
            anulado: $vacunacion->estado === RegistroOperativoEstado::Anulado,
            puedeAnular: Gate::forUser($viewer)->allows('anular', $vacunacion),
            motivoAnulacion: $vacunacion->motivo_anulacion,
            detalleLineas: $vacunacion->lineasDetalle(),
        );
    }

    public static function resolve(string $key, User $viewer): ?self
    {
        if (str_starts_with($key, 'registro-')) {
            $id = (int) substr($key, strlen('registro-'));
            $registro = RegistroOperativo::query()
                ->forEmpresa((int) $viewer->empresa_id)
                ->where('user_id', $viewer->id)
                ->with('galpon')
                ->find($id);

            return $registro !== null
                ? self::fromRegistro($registro, $viewer)
                : null;
        }

        if (str_starts_with($key, 'vacunacion-')) {
            $id = (int) substr($key, strlen('vacunacion-'));
            $vacunacion = Vacunacion::query()
                ->forEmpresa((int) $viewer->empresa_id)
                ->where('user_id', $viewer->id)
                ->with(['galpon', 'lote'])
                ->find($id);

            return $vacunacion !== null
                ? self::fromVacunacion($vacunacion, $viewer)
                : null;
        }

        return null;
    }
}
