<?php

namespace tcCore\Http\Livewire\TestOpgavenPrint;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Question;

class OpenQuestion extends \tcCore\Http\Livewire\TestPrint\OpenQuestion
{

    public function render()
    {
        if ($this->question->subtype === 'short') {
            return view('livewire.test_opgaven_print.open-question');
        }
        return view('livewire.test_opgaven_print.open-medium-question');
    }
}
