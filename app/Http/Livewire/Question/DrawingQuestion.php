<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Question;

class DrawingQuestion extends Component
{
    use WithAttachments, WithNotepad;

    public $question;

    public $number;

    public $drawingModalOpened = false;

    public function questionUpdated($uuid)
    {
        $this->uuid = $uuid;
    }

    public function render()
    {
        return view('livewire.question.drawing-question');
    }
}
