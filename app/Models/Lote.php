<?php

namespace App\Models;

use App\Enums\LoteEstado;
use App\Enums\TipoHuevo;
use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'empresa_id',
    'galpon_id',
    'codigo',
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
}
