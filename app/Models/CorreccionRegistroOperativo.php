<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use App\Models\Concerns\PreventsHardDelete;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'empresa_id',
    'registro_operativo_id',
    'valores_anteriores',
    'valores_nuevos',
    'motivo',
    'corregido_por',
    'fecha_efectiva',
])]
class CorreccionRegistroOperativo extends Model
{
    use BelongsToEmpresa;
    use PreventsHardDelete;

    protected $table = 'correcciones_registro_operativo';

    protected function casts(): array
    {
        return [
            'valores_anteriores' => 'array',
            'valores_nuevos' => 'array',
            'fecha_efectiva' => 'datetime',
        ];
    }

    public function registroOperativo(): BelongsTo
    {
        return $this->belongsTo(RegistroOperativo::class, 'registro_operativo_id');
    }

    public function corregidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'corregido_por');
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    public function lineasDetalle(): array
    {
        $this->loadMissing('corregidoPor');

        return [
            ['label' => 'Corregido por', 'value' => $this->corregidoPor?->name ?? '—'],
            ['label' => 'Fecha efectiva', 'value' => $this->fecha_efectiva?->format('d/m/Y H:i') ?? '—'],
            ['label' => 'Motivo', 'value' => $this->motivo],
            ['label' => 'Antes', 'value' => self::formatearValores($this->valores_anteriores ?? [])],
            ['label' => 'Después', 'value' => self::formatearValores($this->valores_nuevos ?? [])],
        ];
    }

    /**
     * @param  array<string, mixed>  $valores
     */
    private static function formatearValores(array $valores): string
    {
        if ($valores === []) {
            return '—';
        }

        $partes = [];

        foreach ($valores as $campo => $valor) {
            if ($campo === 'cero_confirmado') {
                $partes[] = $valor ? 'cero confirmado' : 'sin cero confirmado';

                continue;
            }

            $partes[] = match ($campo) {
                'huevos' => number_format((int) $valor, 0, ',', '.').' huevos aptos',
                'huevos_descarte' => number_format((int) $valor, 0, ',', '.').' huevos descarte',
                'muertes' => number_format((int) $valor, 0, ',', '.').' muertes',
                'descarte_aves' => number_format((int) $valor, 0, ',', '.').' descarte de aves',
                'alimento_kg' => number_format((float) $valor, 2, ',', '.').' kg',
                default => $campo.': '.$valor,
            };
        }

        return implode(' · ', $partes);
    }

    protected static function hardDeleteRejectionMessage(): string
    {
        return 'No se puede eliminar: las correcciones históricas son inmutables (D07).';
    }
}
