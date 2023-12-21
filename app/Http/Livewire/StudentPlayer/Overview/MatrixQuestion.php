<?php

namespace tcCore\Http\Livewire\StudentPlayer\Overview;

use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithStudentPlayerOverview;
use tcCore\Http\Livewire\StudentPlayer\MatrixQuestion as AbstractMatrixQuestion;
use tcCore\Http\Traits\WithAttachments;

class MatrixQuestion extends AbstractMatrixQuestion
{
    use WithGroups;
    use WithStudentPlayerOverview;
    use WithAttachments;

    public $answered;
    public function mount()
    {
        parent::mount();

        if (!empty(json_decode($this->answers[$this->question->uuid]['answer']))) {
            $this->answerStruct = json_decode($this->answers[$this->question->uuid]['answer'], true);
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
