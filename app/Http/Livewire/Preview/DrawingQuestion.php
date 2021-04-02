<?php

namespace tcCore\Http\Livewire\Preview;

use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;

class DrawingQuestion extends Component
{
    use WithAttachments, WithNotepad, withCloseable, WithGroups;

    public $question;

    public $number;

    public $drawingModalOpened = false;

    public $answers;

    public $answer;

    public $additionalText;

    public $playerInstance;

    public function mount()
    {
        $this->initPlayerInstance();

    }

    public function updatedAnswer($value)
    {

        $this->drawingModalOpened = false;

    }

    public function render()
    {
        return view('livewire.preview.drawing-question');
    }


    private function initPlayerInstance()
    {
        $this->playerInstance = 'eppi_' . rand(1000, 9999999);
    }
}
