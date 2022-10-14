<?php

namespace tcCore\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class RemoveUuidScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return Builder
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->select([$model->getKeyName()]+$model->getFillable());

    }
}
