<?php


namespace tcCore\Http\Traits;

use tcCore\Answer;

trait WithQuestionTimer
{
    public $startQuestionTime;
    public $timeSpendOnQuestion;

    public function mountWithQuestionTimer()
    {
        $this->startQuestionTime = false;
        $this->timeSpendOnQuestion = 0;
    }

}