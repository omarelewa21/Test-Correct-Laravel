<?php


namespace tcCore\Http\Traits;

use tcCore\Answer;
use tcCore\GroupQuestionQuestion;
use tcCore\TestQuestion;

trait WithGroups
{
    public $group;
    public $test;

    public function mountWithGroups()
    {
        if ($this->question->is_subquestion) {
            $groupQuestions = GroupQuestionQuestion::whereQuestionId($this->question->getKey())->get();
            if ($groupQuestions->count() > 1) {
                $groupQuestionIds = $groupQuestions->pluck('group_question_id')->toArray();
                $testId = $this->getTestId();
                $this->group =  TestQuestion::whereTestId($testId)->whereIn('question_id', $groupQuestionIds)->first()->question;
            } else {
                $this->group = $groupQuestions->first()->groupQuestion;
            }
            $this->group->load('attachments');
        }
    }

    private function getTestId()
    {
        if(!is_null($this->test)){
            return $this->test->getKey();
        }
        return Answer::where('answers.id',$this->answers[$this->question->uuid]['id'])
            ->leftJoin('test_participants','answers.test_participant_id','=','test_participants.id')
            ->leftJoin('test_takes','test_participants.test_take_id','=','test_takes.id')
            ->value('test_id');
    }
}