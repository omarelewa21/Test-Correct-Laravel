<?php


namespace tcCore\Http\Traits;

use tcCore\Answer;
use tcCore\GroupQuestion;
use tcCore\GroupQuestionQuestion;
use tcCore\Question;

trait WithGroups
{
    public $group;

    public function mountWithGroups()
    {
        if ($this->question->is_subquestion) {
            $this->group = GroupQuestionQuestion::whereQuestionId($this->question->getKey())->first()->groupQuestion;
        }
    }
}