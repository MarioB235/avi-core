<?php

namespace App\Models;

use App\Enums\AuditoriaCategoria;
use App\Models\Concerns\BelongsToEmpresa;
use App\Models\Concerns\PreventsHardDelete;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'empresa_id',
    'actor_id',
    'categoria',
    'accion',
    'entidad_tipo',
    'entidad_id',
    'motivo',
    'metadata',
    'occurred_at',
])]
class Auditoria extends Model
{
    use BelongsToEmpresa;
    use PreventsHardDelete;

    protected $table = 'auditorias';

    protected function casts(): array
    {
        return [
            'categoria' => AuditoriaCategoria::class,
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    protected static function hardDeleteRejectionMessage(): string
    {
        return 'No se puede eliminar: la bitácora de auditoría es inmutable (D07).';
    }
}
