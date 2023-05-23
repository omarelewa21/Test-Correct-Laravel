<?php

namespace tcCore\Http\Livewire\Preview;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithPreviewAttachments;
use tcCore\Http\Traits\WithPreviewGroups;
use tcCore\Http\Traits\WithQuestionTimer;

class OpenQuestion extends TCComponent
{
    use WithPreviewAttachments, WithNotepad, withCloseable, WithPreviewGroups;

    public $answer = '';
    public $question;
    public $testId;
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
            return view('livewire.preview.open-question');
        }

        return view('livewire.preview.open-medium-question');
    }
}
