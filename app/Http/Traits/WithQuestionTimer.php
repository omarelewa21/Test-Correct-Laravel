<?php


namespace tcCore\Http\Traits;

use tcCore\Answer;

trait WithQuestionTimer
{
    public $timeSpendOnQuestion;

    public function mountWithQuestionTimer()
    {
        $this->timeSpendOnQuestion = 0;
    }


//
//    public function showCloseQuestionModal()
//    {
//        $this->showCloseQuestionModal = true;
//    }
}