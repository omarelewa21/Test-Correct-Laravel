<?php namespace tcCore;

use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Casts\EfficientUuid;
use tcCore\Scopes\LearningGoalScope;
use tcCore\Traits\UuidTrait;

class LearningGoal extends Attainment {

    use SoftDeletes;
    use UuidTrait;

    const TYPE = 'LEARNING_GOAL';



    public static function boot()
    {
        parent::bootWithoutGlobalScope();
        static::addGlobalScope(new LearningGoalScope);

        static::saving(function (LearningGoal $learningGoal) {
            $learningGoal->is_learning_goal = true;
        });
    }



}
