<?php

namespace tcCore\Http\Livewire\StudentPlayer\Overview;

use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithStudentPlayerOverview;
use tcCore\Http\Livewire\StudentPlayer\MultipleChoiceQuestion as AbstractMultipleChoiceQuestion;
use tcCore\Http\Traits\WithAttachments;

class MultipleChoiceQuestion extends AbstractMultipleChoiceQuestion
{
    use WithGroups;
    use WithStudentPlayerOverview;
    use WithAttachments;

    public $queryString = ['q'];
    public $q;
    public $answered;


    public function mount()
    {
        parent::mount();
    }

    public function render()
    {
        return view('livewire.student-player.overview.' . $this->getTemplateName());
    }

    public function isQuestionFullyAnswered(): bool
    {
        return true;
    }

    protected function setAnswerStruct($whenHasAnswerCallback = null): void
    {
        parent::setAnswerStruct(fn() => $this->answer = array_keys($this->answerStruct, 1)[0]);
    }
}
