<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToEmpresa
{
    public function scopeForEmpresa(Builder $query, int $empresaId): Builder
    {
        return $query->where($this->getTable().'.empresa_id', $empresaId);
    }
}
