<?php

namespace tcCore;

use Illuminate\Database\Eloquent\Model;

class ArchivedModel extends Model
{
    protected $guarded = [];

    public static function archiveWithModelAndUser(Model $model, User $user)
    {
        $archivedEntry = self::where([
            'archivable_model_type' => get_class($model),
            'archivable_model_id'   => $model->getKey(),
            'user_id'               => $user->getKey(),
        ])->first();

        if (is_null($archivedEntry)) {
            return self::create([
                'archivable_model_type' => get_class($model),
                'archivable_model_id'   => $model->getKey(),
                'user_id'               => $user->getKey(),
            ]);
        }
        return $archivedEntry;
    }

    public static function unArchiveWithModelAndUser(Model $model, User $user)
    {
        self::where([
            'archivable_model_type' => get_class($model),
            'archivable_model_id'   => $model->getKey(),
            'user_id'               => $user->getKey(),
        ])->forceDelete();
    }


}
