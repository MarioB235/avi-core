<?php

namespace App\Models;

use App\Enums\RegistroOperativoEstado;
use App\Enums\VacunaTipo;
use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'empresa_id',
    'galpon_id',
    'lote_id',
    'user_id',
    'vacuna',
    'observacion',
    'estado',
    'anulado_at',
    'anulado_por',
    'motivo_anulacion',
])]
class Vacunacion extends Model
{
    use BelongsToEmpresa, HasFactory;

    protected $table = 'vacunaciones';

    protected function casts(): array
    {
        return [
            'vacuna' => VacunaTipo::class,
            'estado' => RegistroOperativoEstado::class,
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

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
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

    public function scopeEnFecha(Builder $query, ?string $fecha): Builder
    {
        if ($fecha === null || $fecha === '') {
            return $query;
        }

        return $query->whereDate('created_at', $fecha);
    }

    public function cantidadResumen(): string
    {
        return 'Vacuna '.$this->vacuna->label().' · lote '.$this->lote->codigo;
    }

    public function esMortalidad(): bool
    {
        return false;
    }
}
