<?php

namespace tcCore;

use Illuminate\Database\Eloquent\Model;

class ArchivedModel extends Model
{
    protected $guarded = [];

    public static function archiveWithModelAndUser(Model $model, User $user)
    {
        return self::create([
            'archiveable_model_type' => get_class($model),
            'archiveable_model_id' => $model->getKey(),
            'user_id' => $user->getKey(),
        ]);
    }

    public static function unArchiveWithModelAndUser(Model $model, User $user)
    {
        self::where([
            'archiveable_model_type' => get_class($model),
            'archiveable_model_id' => $model->getKey(),
            'user_id' => $user->getKey(),
        ])->forceDelete();
    }


}
