<?php

namespace tcCore\Http\Livewire\TestOpgavenPrint;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Question;

class MultipleSelectQuestion extends \tcCore\Http\Livewire\TestPrint\MultipleSelectQuestion
{
    public function render()
    {
        return view('livewire.test_opgaven_print.multiple-select-question');
    }
}
