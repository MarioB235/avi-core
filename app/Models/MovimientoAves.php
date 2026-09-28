<?php

namespace App\Models;

use App\Enums\MovimientoAvesEstado;
use App\Enums\MovimientoAvesTipo;
use App\Models\Concerns\BelongsToEmpresa;
use App\Models\Concerns\PreventsHardDelete;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'empresa_id',
    'tipo',
    'estado',
    'galpon_origen_id',
    'galpon_destino_id',
    'lote_id',
    'cantidad',
    'ajuste_delta',
    'motivo',
    'registrado_por',
    'fecha_efectiva',
    'reversa_de_id',
    'reversado_por_id',
    'metadata',
    'idempotencia_clave',
])]
class MovimientoAves extends Model
{
    use BelongsToEmpresa;
    use HasFactory;
    use PreventsHardDelete;

    protected $table = 'movimientos_aves';

    protected function casts(): array
    {
        return [
            'tipo' => MovimientoAvesTipo::class,
            'estado' => MovimientoAvesEstado::class,
            'cantidad' => 'integer',
            'ajuste_delta' => 'integer',
            'fecha_efectiva' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function galponOrigen(): BelongsTo
    {
        return $this->belongsTo(Galpon::class, 'galpon_origen_id');
    }

    public function galponDestino(): BelongsTo
    {
        return $this->belongsTo(Galpon::class, 'galpon_destino_id');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function reversaDe(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversa_de_id');
    }

    public function reversadoPor(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversado_por_id');
    }

    protected static function hardDeleteRejectionMessage(): string
    {
        return 'No se puede eliminar: los movimientos de aves son inmutables (D07).';
    }
}
