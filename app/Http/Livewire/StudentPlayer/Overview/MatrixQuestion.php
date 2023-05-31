<?php

namespace tcCore\Http\Livewire\StudentPlayer\Overview;

use tcCore\Http\Traits\WithGroups;
use tcCore\Question;
use tcCore\Http\Livewire\StudentPlayer\MatrixQuestion as AbstractMatrixQuestion;

class MatrixQuestion extends AbstractMatrixQuestion
{
    use WithGroups;

    public $answered;
    public function mount()
    {
        parent::mount();

        if (!empty(json_decode($this->answers[$this->question->uuid]['answer']))) {
            $this->answerStruct = json_decode($this->answers[$this->question->uuid]['answer'], true);
            $this->answered = true;
        }

        if(!is_null($this->question->belongs_to_groupquestion_id)){
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }

    }

    public function render()
    {
        return view('livewire.student-player.overview.matrix-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        $selectedAnswers = count(array_filter($this->answerStruct));
        return $this->subQuestions->count() === $selectedAnswers;
    }
}
