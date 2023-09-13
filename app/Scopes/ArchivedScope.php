<?php

namespace tcCore\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ArchivedScope implements Scope
{
    public static $skipScope = false;

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if (self::$skipScope || !Auth::user()) {
            return;
        }
        if($builder->getQuery()->columns == '') {
            $builder->addSelect(sprintf('%s.*', $model->getTable()));
        }

        $modelsForUserQuery = DB::table('archived_models')
            ->select('archivable_model_id', 'user_id as archivable_user_id')
            ->where('user_id', '=', DB::raw(Auth::user()->getKey()))
            ->where('archivable_model_type', get_class($model));

        $builder->leftJoinSub(
            $modelsForUserQuery,
            't2',
            function ($join) use ($model){
                $join->on($model->getTable().'.id', '=', 't2.archivable_model_id')
                    ->where('t2.archivable_user_id', '=', DB::raw(Auth::user()->getKey()));
            }
        );
        $builder->addSelect('archivable_model_id');
    }
}
