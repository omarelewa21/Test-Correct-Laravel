<?php

namespace tcCore\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ArchivedScope implements Scope
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
        if($builder->getQuery()->columns === '') {
            $builder->addSelect(sprintf('%s.*', $model->getTable()));
        }

        $modelsForUserQuery = DB::table('archived_models')
            ->select('archivable_model_id', 'user_id as archivable_user_id');

        $builder->leftJoinSub(
            $modelsForUserQuery,
            't2',
            function ($join) {
                $join->on('test_takes.id', '=', 't2.archivable_model_id')
                    ->where('t2.archivable_user_id', '=', DB::raw(Auth::user()->getKey()));
            }
        );
        $builder->addSelect('archivable_model_id');
    }
}
