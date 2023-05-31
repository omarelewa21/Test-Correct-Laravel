<?php

namespace tcCore\Http\Livewire\StudentPlayer\Overview;

use tcCore\Http\Traits\WithGroups;
use tcCore\Question;
use tcCore\Http\Livewire\StudentPlayer\MultipleChoiceQuestion as AbstractMultipleChoiceQuestionAlias;

class MultipleChoiceQuestion extends AbstractMultipleChoiceQuestionAlias
{
    use WithGroups;

    public $queryString = ['q'];
    public $q;
    public $answered;


    public function mount()
    {
        parent::mount();

        $this->answered = $this->answers[$this->question->uuid]['answered'];

        if (!is_null($this->question->belongs_to_groupquestion_id)) {
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    public function render()
    {
        return view('livewire.student-player.preview.' . $this->getTemplateName());
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
