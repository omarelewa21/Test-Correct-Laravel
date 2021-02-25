<?php


namespace tcCore\Http\Traits;

use tcCore\Answer;
use tcCore\Question;

trait WithCloseable
{
    public $closed;
    public $showCloseQuestionModal = false;

    protected function getListeners()
    {
        return ['close-question', 'closeQuestion'];
    }

    public function mountWithCloseable()
    {
        $this->closed = $this->answers[$this->question->uuid]['closed'];
    }

    public function closeQuestion(Question $question)
    {
        dd('qq clse');
        $this->closed = Answer::whereId($this->answers[$this->question->uuid]['id'])->update(['closed' => 1]);
    }

//
//    public function showCloseQuestionModal()
//    {
//        $this->showCloseQuestionModal = true;
//    }
}