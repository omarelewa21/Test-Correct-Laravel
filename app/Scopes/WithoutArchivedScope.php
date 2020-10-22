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

        $modelsForUserQuery = DB::table('archived_models')
            ->select('archiveable_model_id', 'user_id');


//        $builder->addSelect('archived_models.user_id as archived');
        $builder->leftJoinSub(
            $modelsForUserQuery,
            't2',
            function ($join) {
                $join->on('test_takes.id', '=', 't2.archiveable_model_id')
                    ->where('t2.user_id', '=', DB::raw(Auth::user()->getKey()));
            }
        );
    }
}
