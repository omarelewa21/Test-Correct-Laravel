<?php


namespace tcCore\Http\Traits;

use tcCore\Answer;
use tcCore\GroupQuestionQuestion;
use tcCore\TestQuestion;

trait WithGroups
{
    public $group;

    public function mountWithGroups()
    {
        if ($this->question->is_subquestion) {
            $groupQuestions = GroupQuestionQuestion::whereQuestionId($this->question->getKey())->get();
            if ($groupQuestions->count() > 1) {
                $groupQuestionIds = $groupQuestions->pluck('group_question_id')->toArray();

                $testTake = Answer::whereId($this->answers[$this->question->uuid]['id'])->first()->testParticipant->testTake;
                $this->group =  TestQuestion::whereTestId($testTake->test_id)->whereIn('question_id', $groupQuestionIds)->first()->question;
            } else {
                $this->group = $groupQuestions->first()->groupQuestion;
            }
        }
    }
}