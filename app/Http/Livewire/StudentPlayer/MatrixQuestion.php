<?php

namespace tcCore\Http\Livewire\StudentPlayer;

abstract class MatrixQuestion extends StudentPlayerQuestion
{
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
