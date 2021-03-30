<?php

namespace tcCore\Http\Livewire\Preview;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithQuestionTimer;
use tcCore\Question;

class OpenQuestion extends Component
{
    use WithAttachments, WithNotepad, withCloseable, WithGroups;

    public $answer = '';
    public $question;
    public $number;
    public $answers;
    public $editorId;

    public function mount()
    {
        $this->editorId = 'editor_'.$this->question->id;

    }


    public function updatedAnswer($value)
    {

    }

    public function render()
    {
        if ($this->question->subtype === 'short') {
            return view('livewire.preview.open-question', compact('question'));
        }

        return view('livewire.preview.open-medium-question', compact('question'));
    }
}
