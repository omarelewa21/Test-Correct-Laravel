<?php


namespace tcCore\Http\Traits;

use tcCore\TestQuestion;

trait WithPreviewGroups
{
    public $group;

    public function mountWithPreviewGroups()
    {
        if ($this->question->is_subquestion) {
            $this->group = TestQuestion::whereQuestionId($this->question->getGroupQuestionIdByTest($this->testId))->whereTestId($this->testId)->first()->question;
        }
    }
}