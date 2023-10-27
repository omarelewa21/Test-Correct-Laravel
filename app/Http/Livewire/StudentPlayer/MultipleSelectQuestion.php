<?php

namespace tcCore\Http\Livewire\StudentPlayer;


abstract class MultipleSelectQuestion extends MultipleChoiceQuestion
{
    public function mount()
    {
        $this->selectable_answers = $this->question->selectable_answers;

        parent::mount();
    }

    public function updatedAnswer($value)
    {
        if ($this->answerStruct[$value] === 1) {
            $this->answerStruct[$value] = 0;
        } else {
            $selected = count(array_keys($this->answerStruct, 1));
            if ($selected != $this->question->selectable_answers) {
                $this->answerStruct[$value] = 1;
            }
        }
        $this->answer = '';
    }

}
