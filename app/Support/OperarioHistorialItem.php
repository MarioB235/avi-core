<?php

namespace App\Support;

use App\Models\RegistroOperativo;
use App\Models\Vacunacion;
use Illuminate\Support\Carbon;

readonly class OperarioHistorialItem
{
    public function __construct(
        public string $key,
        public Carbon $createdAt,
        public string $label,
        public ?string $observacion,
        public bool $esMortalidad,
        public bool $esVacunacion,
    ) {}

    public static function fromRegistro(RegistroOperativo $registro): self
    {
        return new self(
            key: 'registro-'.$registro->id,
            createdAt: $registro->created_at,
            label: $registro->cantidadResumen(),
            observacion: $registro->observacion,
            esMortalidad: $registro->esMortalidad(),
            esVacunacion: false,
        );
    }

    public static function fromVacunacion(Vacunacion $vacunacion): self
    {
        $vacunacion->loadMissing('lote');

        return new self(
            key: 'vacunacion-'.$vacunacion->id,
            createdAt: $vacunacion->created_at,
            label: $vacunacion->cantidadResumen(),
            observacion: $vacunacion->observacion,
            esMortalidad: false,
            esVacunacion: true,
        );
    }
}
