<?php

namespace tcCore\Http\Livewire\StudentPlayer;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithCloseable;

abstract class MatrixQuestion extends TCComponent
{
    use withCloseable;

    public $question;
    public $number;
    public $answers;

    public $answer;
    public $subQuestions;
    public $questionAnswers;
    public $answerStruct;
    public $testTakeUuid;

    public function mount()
    {
        $this->subQuestions = $this->question->matrixQuestionSubQuestions;
        $this->questionAnswers = $this->question->matrixQuestionAnswers;
    }
}
