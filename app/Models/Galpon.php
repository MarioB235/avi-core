<?php

namespace App\Models;

use App\Enums\GalponEstado;
use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'empresa_id',
    'granja_id',
    'nombre',
    'codigo',
    'capacidad',
    'estado',
    'activo',
    'aves_actuales',
    'observacion',
])]
class Galpon extends Model
{
    use BelongsToEmpresa, HasFactory;

    protected $table = 'galpones';

    protected function casts(): array
    {
        return [
            'estado' => GalponEstado::class,
            'activo' => 'boolean',
            'capacidad' => 'integer',
            'aves_actuales' => 'integer',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function granja(): BelongsTo
    {
        return $this->belongsTo(Granja::class);
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class);
    }

    public function registrosOperativos(): HasMany
    {
        return $this->hasMany(RegistroOperativo::class);
    }

    public function scopeDisponiblesParaCarga(Builder $query): Builder
    {
        return $query
            ->where('activo', true)
            ->where('estado', GalponEstado::Activo->value);
    }

    public function displayName(): string
    {
        $codigo = $this->codigo ? " ({$this->codigo})" : '';

        return $this->nombre.$codigo;
    }
}
