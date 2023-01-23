<?php

namespace tcCore\Http\Livewire\TestOpgavenPrint;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Question;

class MatchingQuestion extends \tcCore\Http\Livewire\TestPrint\MatchingQuestion
{
    public $characters;

    public function render()
    {
        $this->characters = Range('A', 'Z');

        return view('livewire.test_opgaven_print.matching-question');
    }
}
