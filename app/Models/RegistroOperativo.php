<?php

namespace App\Models;

use App\Enums\RegistroOperativoEstado;
use App\Enums\RegistroOperativoTipo;
use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'empresa_id',
    'galpon_id',
    'user_id',
    'tipo',
    'huevos',
    'muertes',
    'alimento_kg',
    'observacion',
    'estado',
    'anulado_at',
    'anulado_por',
    'motivo_anulacion',
])]
class RegistroOperativo extends Model
{
    use BelongsToEmpresa, HasFactory;

    protected $table = 'registros_operativos';

    protected function casts(): array
    {
        return [
            'tipo' => RegistroOperativoTipo::class,
            'estado' => RegistroOperativoEstado::class,
            'huevos' => 'integer',
            'muertes' => 'integer',
            'alimento_kg' => 'decimal:2',
            'anulado_at' => 'datetime',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function anuladoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anulado_por');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado', RegistroOperativoEstado::Activo->value);
    }

    public function scopeDelDia(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    public function cantidadResumen(): string
    {
        $formatInt = fn (?int $value): string => number_format((int) $value, 0, ',', '.');

        return match ($this->tipo) {
            RegistroOperativoTipo::Huevos => $formatInt($this->huevos).' huevos',
            RegistroOperativoTipo::Muertes => $formatInt($this->muertes).' muertes',
            RegistroOperativoTipo::Alimento => number_format((float) $this->alimento_kg, 2, ',', '.').' kg',
            RegistroOperativoTipo::Combinado => collect([
                $this->huevos ? $formatInt($this->huevos).' huevos' : null,
                $this->muertes ? $formatInt($this->muertes).' muertes' : null,
                $this->alimento_kg ? number_format((float) $this->alimento_kg, 2, ',', '.').' kg' : null,
            ])->filter()->implode(' · '),
        };
    }
}
