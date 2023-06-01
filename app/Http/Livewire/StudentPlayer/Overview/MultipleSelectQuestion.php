<?php

namespace tcCore\Http\Livewire\StudentPlayer\Overview;

use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithStudentPlayerOverview;
use tcCore\Http\Livewire\StudentPlayer\MultipleSelectQuestion as AbstractMultipleSelectQuestion;

class MultipleSelectQuestion extends AbstractMultipleSelectQuestion
{
    use WithGroups;
    use WithStudentPlayerOverview;

    public $answered;

    public function mount()
    {
        parent::mount();
    }

    public function render()
    {
        return view('livewire.student-player.overview.multiple-select-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        $selectedAnswers = count(array_keys($this->answerStruct, 1));
        return $this->question->selectable_answers === $selectedAnswers;
    }

    protected function setAnswerStruct($whenHasAnswerCallback = null): void
    {
        parent::setAnswerStruct(fn () => $this->answer = 'answered');
    }
}
