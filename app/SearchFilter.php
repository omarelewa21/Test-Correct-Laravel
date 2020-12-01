<?php

namespace tcCore;

use Illuminate\Database\Eloquent\Model;
use Dyrynda\Database\Casts\EfficientUuid;
use tcCore\Traits\UuidTrait;

class SearchFilter extends Model
{
    protected $guarded = [];

    use UuidTrait;

    protected $casts = [
        'uuid'    => EfficientUuid::class,
        'filters' => 'array',
    ];

    public function activate()
    {
        SearchFilter::where('user_id', $this->user_id)->where('key', $this->key)->update(['active' => false]);
        $this->active = true;
        $this->save();

        return $this;
    }

    public static function boot()
    {
        parent::boot();

        self::created(function($model){
            if(!$model->cached_filter){
                return;
            }
            SearchFilter::where('user_id', '=', $model->user_id)
                        ->where('key', '=', $model->key)
                        ->where('id','!=',$model->id)
                        ->where('cached_filter','=',true)
                        ->delete();
            if(!is_null($model->name)){
                return;
            }
            $model->name = 'Bewaard filter';
            $model->save();
        });
    }
}
