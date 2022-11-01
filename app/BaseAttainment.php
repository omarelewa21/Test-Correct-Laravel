<?php namespace tcCore;

use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Casts\EfficientUuid;
use tcCore\Scopes\LearningGoalScope;
use tcCore\Traits\UuidTrait;

class BaseAttainment extends Attainment
{


    public static function boot()
    {
        parent::bootWithoutGlobalScope();
    }

    public function getSubNameWithNumber($number)
    {
        if ($this->is_learning_goal) {
            return __('student.subleerdoel met nummer', ['number' => $number]);
        }
        return __('student.subeindterm met nummer', ['number' =>$number]);

    }

    public function getSubSubNameWithNumber($number)
    {
        if ($this->is_learning_goal) {
            return __('student.subsubleerdoel met nummer', ['number' => $number]);
        }
        return __('student.subsubeindterm met nummer', ['number' =>$number]);

    }

    public function getSubName()
    {
        if ($this->is_learning_goal) {
            return __('student.subleerdoel');
        }
        return __('student.subeindterm');

    }


}
