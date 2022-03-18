<?php namespace tcCore;


use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Casts\EfficientUuid;
use tcCore\Scopes\LearningGoalScope;
use tcCore\Traits\UuidTrait;

class LearningGoal extends Attainment {

    use SoftDeletes;
    use UuidTrait;



    public static function boot()
    {
        parent::boot();
        static::addGlobalScope(new LearningGoalScope);
    }



}
