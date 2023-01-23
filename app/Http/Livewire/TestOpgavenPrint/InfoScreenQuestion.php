<?php

namespace tcCore\Http\Livewire\TestOpgavenPrint;

use Livewire\Component;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Question;

class InfoScreenQuestion extends \tcCore\Http\Livewire\TestPrint\InfoScreenQuestion
{
    public function render()
    {
        return view('livewire.test_opgaven_print.info-screen-question');
    }
}
