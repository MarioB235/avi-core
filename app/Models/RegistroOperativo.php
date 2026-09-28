<?php

namespace App\Models;

use App\Enums\RegistroOperativoEstado;
use App\Enums\RegistroOperativoTipo;
use App\Models\Concerns\BelongsToEmpresa;
use App\Models\Concerns\PreventsHardDelete;
use App\Support\DiaOperativoEmpresa;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[Fillable([
    'empresa_id',
    'galpon_id',
    'user_id',
    'tipo',
    'idempotencia_clave',
    'cero_confirmado',
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
    use BelongsToEmpresa, HasFactory, PreventsHardDelete;

    protected $table = 'registros_operativos';

    protected function casts(): array
    {
        return [
            'tipo' => RegistroOperativoTipo::class,
            'estado' => RegistroOperativoEstado::class,
            'cero_confirmado' => 'boolean',
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

    public function correcciones(): HasMany
    {
        return $this->hasMany(CorreccionRegistroOperativo::class, 'registro_operativo_id')
            ->orderByDesc('fecha_efectiva');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado', RegistroOperativoEstado::Activo->value);
    }

    public function scopeDelDia(Builder $query, int $empresaId, ?Carbon $referencia = null): Builder
    {
        return DiaOperativoEmpresa::forEmpresa($empresaId, $referencia)->aplicarAlQuery($query);
    }

    public function scopeEnFecha(Builder $query, ?string $fecha, int $empresaId): Builder
    {
        if ($fecha === null || $fecha === '') {
            return $query;
        }

        return DiaOperativoEmpresa::enFechaParaEmpresa($empresaId, $fecha)->aplicarAlQuery($query);
    }

    public function cantidadResumen(): string
    {
        $formatInt = fn (?int $value): string => number_format((int) $value, 0, ',', '.');

        return match ($this->tipo) {
            RegistroOperativoTipo::Huevos => $this->resumenHuevos($formatInt),
            RegistroOperativoTipo::Muertes => $this->cero_confirmado
                ? '0 muertes (confirmado)'
                : $formatInt($this->muertes).' muertes',
            RegistroOperativoTipo::Descarte => $this->cero_confirmado
                ? '0 descarte de aves (confirmado)'
                : $formatInt($this->descarte_aves).' descarte de aves',
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
            RegistroOperativoTipo::Combinado => (int) $this->muertes > 0 || (int) $this->descarte_aves > 0,
            default => false,
        };
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    public function lineasDetalle(): array
    {
        $this->loadMissing('user', 'galpon');

        $lineas = [
            ['label' => 'Tipo', 'value' => $this->tipo->label()],
            ['label' => 'Galpón', 'value' => $this->galpon?->displayName() ?? '—'],
            ['label' => 'Registrado por', 'value' => $this->user?->name ?? '—'],
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
        if ($this->cero_confirmado) {
            return '0 huevos (confirmado)';
        }

        $aptos = (int) $this->huevos;
        $descarte = (int) $this->huevos_descarte;

        if ($descarte > 0) {
            return $formatInt($aptos).' aptos · '.$formatInt($descarte).' descarte';
        }

        return $formatInt($aptos).' huevos aptos';
    }

    protected static function hardDeleteRejectionMessage(): string
    {
        return 'No se puede eliminar: use anulación lógica para conservar la trazabilidad (D07).';
    }
}
