<?php

namespace tcCore\Http\Livewire\StudentPlayer\Preview;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithPreviewAttachments;
use tcCore\Http\Traits\WithPreviewGroups;

class MultipleChoiceQuestion extends TCComponent
{
    use WithPreviewAttachments, WithNotepad, withCloseable, WithPreviewGroups;

    public $question;
    public $testId;

    public $answer = '';

    public $answers;

    public $answerStruct;
    public $shuffledKeys;

    public $number;

    public $arqStructure = [];

    protected $listeners = ['questionUpdated' => 'questionUpdated'];

    public $answerText;


    public function mount()
    {
        $this->arqStructure = \tcCore\MultipleChoiceQuestion::getArqStructure();
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
        $this->answerStruct = array_fill_keys(array_keys($this->answerStruct), 0);
        $this->answerStruct[$value] = 1;
    }

    public function render()
    {
        if ($this->question->subtype == 'ARQ') {
            return view('livewire.student-player.preview.arq-question');
        } elseif ($this->question->subtype == 'TrueFalse') {
            return view('livewire.student-player.preview.true-false-question');
        }

        return view('livewire.student-player.preview.multiple-choice-question');
    }
}
