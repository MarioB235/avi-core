<?php

namespace App\Models;

use App\Enums\DocumentoEmitidoFormato;
use App\Models\Concerns\BelongsToEmpresa;
use App\Models\Concerns\PreventsHardDelete;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'empresa_id',
    'emitido_por',
    'tipo',
    'formato',
    'nombre_archivo',
    'storage_disk',
    'storage_path',
    'checksum_sha256',
    'filtros',
    'metadata',
    'emitido_at',
    'fecha_corte',
])]
class DocumentoEmitido extends Model
{
    use BelongsToEmpresa;
    use PreventsHardDelete;

    protected $table = 'documentos_emitidos';

    protected function casts(): array
    {
        return [
            'formato' => DocumentoEmitidoFormato::class,
            'filtros' => 'array',
            'metadata' => 'array',
            'emitido_at' => 'datetime',
            'fecha_corte' => 'datetime',
        ];
    }

    public function emitidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'emitido_por');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    protected static function hardDeleteRejectionMessage(): string
    {
        return 'No se puede eliminar: los documentos emitidos se conservan según D07.';
    }
}
