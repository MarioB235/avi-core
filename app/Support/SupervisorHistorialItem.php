<?php

namespace App\Support;

use App\Enums\RegistroOperativoEstado;
use App\Enums\RegistroOperativoTipo;
use App\Models\CorreccionRegistroOperativo;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Models\Vacunacion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;

readonly class SupervisorHistorialItem
{
    /**
     * @param  list<array{label: string, value: string}>  $detalleLineas
     * @param  list<array{label: string, value: string}>  $correccionesHistorial
     * @param  array<string, int|float|string>  $valoresCorregibles
     * @param  list<string>  $camposCorregibles
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
        public string $operarioNombre,
        public bool $anulado,
        public ?string $motivoAnulacion,
        public array $detalleLineas,
        public bool $puedeCorregir = false,
        public ?RegistroOperativoTipo $tipoRegistro = null,
        public array $valoresCorregibles = [],
        public array $camposCorregibles = [],
        public array $correccionesHistorial = [],
    ) {}

    public static function fromRegistro(RegistroOperativo $registro, ?User $viewer = null): self
    {
        $registro->loadMissing(['galpon', 'user', 'correcciones.corregidoPor']);

        $tipoRegistro = $registro->tipo;
        $valoresCorregibles = self::valoresCorregiblesDesdeRegistro($registro);
        $camposCorregibles = RegistroOperativoCorreccion::camposFormulario($tipoRegistro);

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
            operarioNombre: $registro->user?->name ?? '—',
            anulado: $registro->estado === RegistroOperativoEstado::Anulado,
            motivoAnulacion: $registro->motivo_anulacion,
            detalleLineas: $registro->lineasDetalle(),
            puedeCorregir: $viewer !== null
                && $camposCorregibles !== []
                && Gate::forUser($viewer)->allows('corregir', $registro),
            tipoRegistro: $tipoRegistro,
            valoresCorregibles: $valoresCorregibles,
            camposCorregibles: $camposCorregibles,
            correccionesHistorial: $registro->correcciones
                ->map(fn (CorreccionRegistroOperativo $correccion): array => $correccion->lineasDetalle())
                ->all(),
        );
    }

    public static function fromVacunacion(Vacunacion $vacunacion): self
    {
        $vacunacion->loadMissing(['lote', 'galpon', 'user']);

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
            operarioNombre: $vacunacion->user?->name ?? '—',
            anulado: $vacunacion->estado === RegistroOperativoEstado::Anulado,
            motivoAnulacion: $vacunacion->motivo_anulacion,
            detalleLineas: $vacunacion->lineasDetalle(),
        );
    }

    public static function resolve(string $key, int $empresaId, ?User $viewer = null): ?self
    {
        if (str_starts_with($key, 'registro-')) {
            $id = (int) substr($key, strlen('registro-'));
            $registro = RegistroOperativo::query()
                ->forEmpresa($empresaId)
                ->with(['galpon', 'user', 'correcciones.corregidoPor'])
                ->find($id);

            return $registro !== null
                ? self::fromRegistro($registro, $viewer)
                : null;
        }

        if (str_starts_with($key, 'vacunacion-')) {
            $id = (int) substr($key, strlen('vacunacion-'));
            $vacunacion = Vacunacion::query()
                ->forEmpresa($empresaId)
                ->with(['galpon', 'lote', 'user'])
                ->find($id);

            return $vacunacion !== null
                ? self::fromVacunacion($vacunacion)
                : null;
        }

        return null;
    }

    /**
     * @return array<string, int|float|string>
     */
    private static function valoresCorregiblesDesdeRegistro(RegistroOperativo $registro): array
    {
        return match ($registro->tipo) {
            RegistroOperativoTipo::Huevos => [
                'huevos' => (string) (int) $registro->huevos,
                'huevos_descarte' => (string) (int) $registro->huevos_descarte,
            ],
            RegistroOperativoTipo::Muertes => [
                'muertes' => (string) RegistroOperativoCorreccion::cantidadMuertesEfectiva($registro),
            ],
            RegistroOperativoTipo::Descarte => [
                'descarte_aves' => (string) RegistroOperativoCorreccion::cantidadDescarteEfectiva($registro),
            ],
            RegistroOperativoTipo::Alimento => [
                'alimento_kg' => number_format((float) $registro->alimento_kg, 2, '.', ''),
            ],
            default => [],
        };
    }
}
