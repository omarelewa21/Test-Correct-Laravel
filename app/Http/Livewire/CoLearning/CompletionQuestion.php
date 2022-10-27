<?php

namespace tcCore\Http\Livewire\CoLearning;

use Livewire\Component;
use tcCore\Answer;

class CompletionQuestion extends CoLearningQuestion
{
    public $ratings = [
        null,null,null,null,null
    ];

    public $searchPattern = "/\[([0-9]+)\]/i";
    public $questionTextPartials;
    public $finalQuestionTextPartial;

    //temp
    public $yesno;

    public function render()
    {
        return view('livewire.co-learning.completion-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        //todo implement check
        return true;
    }

    protected function handleGetAnswerData()
    {
        //todo implement
        // I need to know:
        //  * what are the answers
        //  * are they answered or not
        //  * how many answerOptions are there
        $answer = Answer::find(494);
        $this->answer = (array) json_decode($answer->json);

//        $this->answer = (array) json_decode($this->answerRating->answer->json);
        // 0 => "deels"
        //  1 => "beantwo" ( 2 was not answered, so is missing/not set )
        //  3 => "oors"
        //  4 => "vijf"
//        dd($this->answerRating->answer->question->converted_question_html);

        $question_text = $answer->question->converted_question_html;

//        $replacementFunction = function ($matches) {
//            $tag_id = $matches[1] - 1; // the completion_question_answers list is 1 based but the inputs need to be 0 based
//
//            return sprintf(
//                '<x-button.true-false-toggle wireModel="ratings.%d" ></x-button.true-false-toggle>',
//                $tag_id
//            );
//        };

        $this->questionTextPartials = collect(explode('(##)',preg_replace($this->searchPattern, '(##)', $question_text)));
        $this->finalQuestionTextPartial =$this->questionTextPartials->pop();



//        dd($this->questionTextPartials, $this->finalQuestionTextPartial);

        return;
        $this->answer = 'todo';
    }
}
