<?php

namespace tcCore;

use Illuminate\Database\Eloquent\Model;

class FeatureSetting extends Model
{
    protected $guarded = [];

    public function settingable()
    {
        $this->morphTo();
    }

    //todo create trait to handle feature settings
    // can() ... creathlon etc. or something like that

//    public function getFeatureSetting($model, string $title)
//    {
//        return (bool)$this->where('settingable_id', '=', $model->getKey())->where('title', '=', $title)->pluck('value')->first();
//    }
//
//    public function setFeatureSetting($model, string $title, $value)
//    {
//        if(!$value){
//            $this->where('settingable_id', '=', $model->getKey())->where('title', '=', $title)->delete();
//        }
//        $this->where('settingable_id', '=', $model->getKey())->where('title', '=', $title)->updateOrCreate([
//            'title' => 'allow_creathlon'
//        ], [
//            'value' => $value,
//        ]);
//        return $value;
//    }

}
