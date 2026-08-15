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
    'huevos_descarte',
    'muertes',
    'descarte_aves',
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
            'huevos_descarte' => 'integer',
            'muertes' => 'integer',
            'descarte_aves' => 'integer',
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

    public function scopeEnFecha(Builder $query, ?string $fecha): Builder
    {
        if ($fecha === null || $fecha === '') {
            return $query;
        }

        return $query->whereDate('created_at', $fecha);
    }

    public function cantidadResumen(): string
    {
        $formatInt = fn (?int $value): string => number_format((int) $value, 0, ',', '.');

        return match ($this->tipo) {
            RegistroOperativoTipo::Huevos => $this->resumenHuevos($formatInt),
            RegistroOperativoTipo::Muertes => $formatInt($this->muertes).' muertes',
            RegistroOperativoTipo::Descarte => $formatInt($this->descarte_aves).' descarte de aves',
            RegistroOperativoTipo::Alimento => number_format((float) $this->alimento_kg, 2, ',', '.').' kg entregados',
            RegistroOperativoTipo::Combinado => collect([
                $this->huevos ? $formatInt($this->huevos).' huevos aptos' : null,
                $this->muertes ? $formatInt($this->muertes).' muertes' : null,
                $this->alimento_kg ? number_format((float) $this->alimento_kg, 2, ',', '.').' kg' : null,
            ])->filter()->implode(' · '),
        };
    }

    public function esMortalidad(): bool
    {
        return match ($this->tipo) {
            RegistroOperativoTipo::Muertes, RegistroOperativoTipo::Descarte => true,
            RegistroOperativoTipo::Combinado => (int) $this->muertes > 0,
            default => false,
        };
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    public function lineasDetalle(): array
    {
        $lineas = [
            ['label' => 'Tipo', 'value' => $this->tipo->label()],
            ['label' => 'Galpón', 'value' => $this->galpon?->displayName() ?? '—'],
            ['label' => 'Fecha y hora', 'value' => $this->created_at?->format('d/m/Y H:i') ?? '—'],
            ['label' => 'Resumen', 'value' => $this->cantidadResumen()],
        ];

        if ($this->estado === RegistroOperativoEstado::Anulado) {
            $lineas[] = ['label' => 'Estado', 'value' => 'Anulado'];
            $lineas[] = ['label' => 'Motivo', 'value' => $this->motivo_anulacion ?? '—'];
            if ($this->anulado_at !== null) {
                $lineas[] = ['label' => 'Anulado el', 'value' => $this->anulado_at->format('d/m/Y H:i')];
            }
        }

        if ($this->observacion) {
            $lineas[] = ['label' => 'Observación', 'value' => $this->observacion];
        }

        return $lineas;
    }

    private function resumenHuevos(callable $formatInt): string
    {
        $aptos = (int) $this->huevos;
        $descarte = (int) $this->huevos_descarte;

        if ($descarte > 0) {
            return $formatInt($aptos).' aptos · '.$formatInt($descarte).' descarte';
        }

        return $formatInt($aptos).' huevos aptos';
    }
}
