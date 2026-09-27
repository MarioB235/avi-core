<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'empresa_id',
    'actor_id',
    'motivo',
    'started_at',
    'expires_at',
    'ended_at',
    'end_reason',
    'acciones',
])]
class SoporteSesion extends Model
{
    use HasFactory;

    protected $table = 'soporte_sesiones';

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'ended_at' => 'datetime',
            'acciones' => 'array',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function isOpen(): bool
    {
        return $this->ended_at === null && $this->expires_at->isFuture();
    }
}
