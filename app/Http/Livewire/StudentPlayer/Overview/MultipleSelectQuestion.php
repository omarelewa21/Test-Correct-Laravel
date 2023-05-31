<?php

namespace tcCore\Http\Livewire\StudentPlayer\Overview;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Question;
use tcCore\Http\Livewire\StudentPlayer\MultipleSelectQuestion as AbstractMultipleSelectQuestionAlias;

class MultipleSelectQuestion extends AbstractMultipleSelectQuestionAlias
{
    use WithGroups;

    public $answered;

    public function mount()
    {
        parent::mount();
        $this->answered = $this->answers[$this->question->uuid]['answered'];

        if(!is_null($this->question->belongs_to_groupquestion_id)){
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
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
