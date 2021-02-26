<?php


namespace tcCore\Http\Traits;

use tcCore\Answer;
use tcCore\Question;

trait WithCloseable
{
    public $closed;
    public $showCloseQuestionModal = false;
    public $nextQuestion;

    protected function getListeners()
    {
        return ['close-question', 'closeQuestion'];
    }

    public function mountWithCloseable()
    {
        $this->closed = $this->answers[$this->question->uuid]['closed'];
    }

    public function closeQuestion($nextQuestion = null)
    {
        $this->closed = Answer::whereId($this->answers[$this->question->uuid]['id'])->update(['closed' => 1]);

        if ($nextQuestion) {
            $navInfo = [
                'closed_question' => $this->question->getKey(),
                'next_question' => $nextQuestion
            ];
            $this->emitTo('question.navigation', 'redirect-from-closing-a-question', $navInfo);
        } else {
            $this->emitTo('question.navigation', 'update-nav-with-closed-question', $this->question->getKey());
        }
    }

//
//    public function showCloseQuestionModal()
//    {
//        $this->showCloseQuestionModal = true;
//    }
}