<?php

namespace tcCore\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WithoutArchivedScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->addSelect('archived_models.user_id as archived');
        $builder->leftJoin(
            'archived_models',
            function ($join) {
                $join->on('test_takes.id', '=', 'archived_models.archiveable_model_id')
                    ->where('archived_models.user_id', '=', DB::raw(Auth::user()->getKey()));
            }
        )->whereNull('archiveable_model_id');
    }
}
