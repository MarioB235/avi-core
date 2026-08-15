<?php

namespace App\Models;

use App\Enums\LoteEstado;
use App\Enums\TipoHuevo;
use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'empresa_id',
    'galpon_id',
    'codigo',
    'codigo_sma',
    'fecha_nacimiento',
    'fecha_ingreso',
    'cantidad_inicial',
    'linea_raza',
    'tipo_huevo',
    'estado',
    'observacion',
])]
class Lote extends Model
{
    use BelongsToEmpresa, HasFactory;

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'fecha_ingreso' => 'date',
            'cantidad_inicial' => 'integer',
            'tipo_huevo' => TipoHuevo::class,
            'estado' => LoteEstado::class,
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function galpon(): BelongsTo
    {
        return $this->belongsTo(Galpon::class);
    }

    public function vacunaciones(): HasMany
    {
        return $this->hasMany(Vacunacion::class);
    }

    public function etiquetaVacunacion(): string
    {
        $resumen = $this->codigo.' · '.number_format($this->cantidad_inicial, 0, ',', '.').' aves';

        if ($this->codigo_sma) {
            return $resumen.' · SMA '.$this->codigo_sma;
        }

        return $resumen;
    }
}
