<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EmpresaScopeService
{
    public function constrainQuery(Builder $query, User $actor): Builder
    {
        if ($actor->isAdminAvicore()) {
            return $query;
        }

        if ($actor->empresa_id === null) {
            return $query->whereRaw('1 = 0');
        }

        $table = $query->getModel()->getTable();

        return $query->where("{$table}.empresa_id", $actor->empresa_id);
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return TModel
     */
    public function findForActor(Builder $query, User $actor, int $id): Model
    {
        /** @var TModel $model */
        $model = $this->constrainQuery($query, $actor)
            ->whereKey($id)
            ->firstOrFail();

        return $model;
    }
}
