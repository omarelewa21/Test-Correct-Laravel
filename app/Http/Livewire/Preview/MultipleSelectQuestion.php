<?php

namespace tcCore\Http\Livewire\Preview;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithPreviewAttachments;
use tcCore\Http\Traits\WithPreviewGroups;
use tcCore\Http\Traits\WithQuestionTimer;

class MultipleSelectQuestion extends TCComponent
{
    use WithPreviewAttachments, WithNotepad, withCloseable, WithPreviewGroups;

    public $question;
    public $testId;

    public $answer = '';

    public $answers;

    public $answerStruct;

    public $number;

    public $answerText;
    public $shuffledKeys;

    public function mount()
    {
        $this->selectable_answers = $this->question->selectable_answers;


        $this->question->multipleChoiceQuestionAnswers->each(function ($answers) use (&$map) {
            $this->answerStruct[$answers->id] = 0;
        });

        $this->shuffledKeys = array_keys($this->answerStruct);
        if (!$this->question->isCitoQuestion()) {
            if ($this->question->subtype != 'ARQ' && $this->question->subtype != 'TrueFalse' && !$this->question->fix_order) {
                shuffle($this->shuffledKeys);
            }
        }

        $this->question->multipleChoiceQuestionAnswers->each(function ($answers) use (&$map) {
            $this->answerText[$answers->id] = $answers->answer;
        });
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

    public function render()
    {
        return view('livewire.preview.multiple-select-question');
    }
}
